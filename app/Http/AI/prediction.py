import requests
import pandas as pd
import joblib
from datetime import datetime, timedelta
import numpy as np
import os
import time
import warnings

# Suppress specific warnings for cleaner output.
warnings.filterwarnings("ignore", message="If you are loading a serialized model.*")
warnings.filterwarnings("ignore", category=FutureWarning, module='pandas')


# --- 1. KONFIGURASI ---
# Pastikan path ini menunjuk ke model FINAL (optimized/initial terbaik) yang Anda download
# Contoh: irradiance_optimized_model.joblib atau temperature_initial_model.joblib
MODEL_PATHS = {
    'irradiance': r'C:\laragon\www\Hamstrack\app\Http\AI\irradiance_initial_model.joblib',
    'temperature_c': r'C:\laragon\www\Hamstrack\app\Http\AI\temperature_c_initial_model.joblib',
    'precipitation_mm_per_hr': r'C:\laragon\www\Hamstrack\app\Http\AI\precipitation_mm_per_hr_initial_model.joblib',
    'humidity_percent': r'C:\laragon\www\Hamstrack\app\Http\AI\humidity_percent_initial_model.joblib'
}

API_URL = "http://localhost:8000/api/predictionTable"
POST_URL = "http://localhost:8000/api/postTable" # Pastikan ini adalah URL yang benar
TARGET_HOURS = [8, 9, 12, 13, 16, 17] # Jam-jam yang akan diprediksi
FEATURES_TO_PREDICT = list(MODEL_PATHS.keys()) # ['irradiance', 'temperature_c', 'precipitation_mm_per_hr', 'humidity_percent']

# --- KRITIKAL: FEATURE CONFIGURATION Disesuaikan dengan X_train Anda ---
# Berdasarkan skrip modeling Anda, X_train = train_data.drop(columns=[target])
# Ini berarti untuk setiap target, X_train akan mencakup:
# 1. Semua kolom BASE_FEATURES_NAMES lainnya (yang bukan target)
# 2. Semua fitur lag dari SEMUA BASE_FEATURES_NAMES
# 3. Semua fitur rolling dari SEMUA BASE_FEATURES_NAMES
# 4. Fitur 'day_of_year' (numeric asli), 'day_of_year_sin', 'day_of_year_cos'
# TIDAK TERMASUK fitur jam atau hari dalam seminggu yang siklikal.

BASE_FEATURES_NAMES = ['irradiance', 'temperature_c', 'precipitation_mm_per_hr', 'humidity_percent']

PREDICTION_FEATURE_COLUMNS_BY_TARGET = {}

for current_target in FEATURES_TO_PREDICT:
    features_list_for_this_target = []

    # 1. Tambahkan fitur-fitur asli lainnya yang BUKAN target
    for base_f in BASE_FEATURES_NAMES:
        if base_f != current_target:
            features_list_for_this_target.append(base_f)

    # 2. Tambahkan fitur-fitur lag dari SEMUA base features
    # (4 base features * 7 lags = 28 fitur)
    for base_f in BASE_FEATURES_NAMES:
        for i in range(1, 8):
            features_list_for_this_target.append(f'{base_f}_lag{i}')

    # 3. Tambahkan fitur-fitur rolling dari SEMUA base features
    # (4 base features * 2 windows * 2 stats = 16 fitur)
    for base_f in BASE_FEATURES_NAMES:
        for window in [3, 7]:
            features_list_for_this_target.append(f'{base_f}_rolling_mean_{window}')
            features_list_for_this_target.append(f'{base_f}_rolling_std_{window}')

    # 4. Tambahkan fitur 'day_of_year' (numeric asli) dan fitur siklikal day_of_year
    # (1 fitur 'day_of_year' + 2 fitur siklikal = 3 fitur)
    features_list_for_this_target.extend([
        'day_of_year', # Ini penting karena ada di X_train Anda
        'day_of_year_sin',
        'day_of_year_cos'
    ])
    PREDICTION_FEATURE_COLUMNS_BY_TARGET[current_target] = features_list_for_this_target

# Verifikasi jumlah fitur untuk setiap target
print("\n--- Verifikasi Jumlah Fitur per Target ---")
for target, cols in PREDICTION_FEATURE_COLUMNS_BY_TARGET.items():
    print(f"Target '{target}': {len(cols)} fitur.")
    # Diharapkan: 3 (base lainnya) + 28 (lag dari semua) + 16 (rolling dari semua) + 3 (day_of_year & cyclical) = 50
    expected_count = 50
    if len(cols) != expected_count:
        print(f"‼️ Peringatan KRITIS: Jumlah fitur untuk '{target}' tidak sesuai.")
        print(f"Diharapkan: {expected_count}, Didapat: {len(cols)}. Ini akan menyebabkan 'feature shape mismatch'.")
        print(f"Daftar fitur yang akan digunakan untuk '{target}': {cols}")


# --- LOAD FEATURE SCALER ---
# KRITIKAL: Berdasarkan skrip modeling Anda, Anda TIDAK menggunakan scaler.
# Oleh karena itu, SCALER_PATH HARUS tetap None.
SCALER_PATH = None
feature_scaler = None
if SCALER_PATH:
    if os.path.exists(SCALER_PATH):
        try:
            feature_scaler = joblib.load(SCALER_PATH)
            print("✅ Scaler fitur berhasil dimuat.")
        except Exception as e:
            print(f"❌ Gagal memuat scaler fitur dari {SCALER_PATH}: {e}. PASTIKAN MODEL ANDA DILATIH DENGAN SCALER INI JIKA DIGUNAKAN.")
    else:
        print(f"⚠️ Scaler tidak ditemukan di {SCALER_PATH}. PASTIKAN MODEL ANDA DILATIH DENGAN SCALER INI JIKA DIGUNAKAN.")
else:
    print("ℹ️ SCALER_PATH tidak diatur. Prediksi akan dilakukan dengan data non-skala (mentah). Ini sesuai dengan skrip pelatihan Anda.")


# --- 2. FUNGSI AMBIL & PREPROSES DATA ---
def fetch_data_from_api(url):
    """Mengambil data dari URL API yang ditentukan."""
    try:
        response = requests.get(url)
        response.raise_for_status() # Akan memunculkan HTTPError untuk status kode 4xx/5xx
        data = response.json().get('data', [])
        return pd.DataFrame(data) if data else pd.DataFrame()
    except Exception as e:
        print(f"❌ Gagal mengambil data API dari {url}: {e}")
        return pd.DataFrame()

def winsorize_outliers(df, column, limits=(1, 99)):
    """Menerapkan winsorization pada kolom tertentu untuk menangani outlier."""
    if df[column].dropna().empty or len(df[column].dropna()) < 2:
        # Tidak cukup data untuk menghitung persentil
        return df
    lower, upper = np.percentile(df[column].dropna(), limits)
    df[column] = np.clip(df[column], lower, upper)
    return df

def preprocess_data(df):
    """Memproses DataFrame mentah dengan mengubah tanggal, membuat fitur, dan menangani NaNs."""
    if df.empty:
        print("⚠️ DataFrame kosong, tidak ada pra-pemrosesan yang dilakukan.")
        return df

    # Ubah kolom 'date' menjadi datetime dan set sebagai index
    df['date'] = pd.to_datetime(df['date'])
    df.set_index('date', inplace=True)
    df.sort_index(inplace=True)

    # Pastikan kolom target adalah numerik dan terapkan winsorization
    for col in FEATURES_TO_PREDICT:
        df[col] = pd.to_numeric(df[col], errors='coerce') # Konversi ke numerik, non-numerik jadi NaN
        # Terapkan winsorization hanya jika ada cukup data non-NaN
        if not df[col].dropna().empty and len(df[col].dropna()) >= 2:
            df = winsorize_outliers(df, col)
        else:
            print(f"⚠️ Tidak cukup data untuk winsorize kolom '{col}'.")


    # Buat fitur lag dan rolling statistics untuk SEMUA BASE_FEATURES_NAMES
    # Sesuai dengan skrip training Anda, lag dan rolling dihitung dari semua fitur dasar.
    for col in BASE_FEATURES_NAMES: # Loop melalui semua fitur dasar, bukan hanya FEATURES_TO_PREDICT
        # Isi NaN yang mungkin ada setelah winsorization/coerce
        df[col] = df[col].ffill().bfill() # Mengisi nilai hilang

        # Fitur Lagged (hingga 7 hari)
        for i in range(1, 8):
            df[f'{col}_lag{i}'] = df[col].shift(i)

        # Fitur Rolling Statistics (rata-rata dan std untuk window 3 dan 7)
        for window in [3, 7]:
            df[f'{col}_rolling_mean_{window}'] = df[col].rolling(window=window, min_periods=1).mean()
            df[f'{col}_rolling_std_{window}'] = df[col].rolling(window=window, min_periods=1).std()

    # Buat fitur siklikal day_of_year
    df['day_of_year'] = df.index.dayofyear
    df['day_of_year_sin'] = np.sin(2 * np.pi * df['day_of_year'] / 365)
    df['day_of_year_cos'] = np.cos(2 * np.pi * df['day_of_year'] / 365)

    # --- PENTING: Hapus pembuatan fitur hour dan day_of_week di sini
    # karena tidak ada dalam skrip training Anda
    # df['hour'] = df.index.hour
    # df['hour_sin'] = np.sin(2 * np.pi * df['hour'] / 24)
    # df['hour_cos'] = np.cos(2 * np.pi * df['hour'] / 24)
    # df['day_of_week'] = df.index.dayofweek
    # df['day_of_week_sin'] = np.sin(2 * np.pi * df['day_of_week'] / 7)
    # df['day_of_week_cos'] = np.cos(2 * np.pi * df['day_of_week'] / 7)


    # Identifikasi semua fitur yang dibutuhkan oleh model untuk memastikan tidak ada NaN di dalamnya
    # Gabungkan semua daftar fitur dari PREDICTION_FEATURE_COLUMNS_BY_TARGET
    all_expected_features_flat = []
    for target_key in PREDICTION_FEATURE_COLUMNS_BY_TARGET:
        all_expected_features_flat.extend(PREDICTION_FEATURE_COLUMNS_BY_TARGET[target_key])
    all_expected_features_unique = list(set(all_expected_features_flat)) # Hapus duplikasi

    # Hapus baris yang memiliki NaN pada fitur-fitur yang dibutuhkan model
    # Filter hanya fitur yang benar-benar ada di DataFrame saat ini
    features_to_check_for_nan = [f for f in all_expected_features_unique if f in df.columns]
    initial_rows = len(df)
    df.dropna(subset=features_to_check_for_nan, inplace=True)
    if len(df) < initial_rows:
        print(f"ℹ️ {initial_rows - len(df)} baris dihapus karena nilai NaN pada fitur yang diperlukan.")

    df.reset_index(inplace=True) # Reset index 'date' kembali menjadi kolom
    return df

# --- 3. FUNGSI PREDIKSI ---
def run_prediction_pipeline(historical_df):
    """Menjalankan pipeline prediksi untuk 3 hari ke depan."""
    try:
        models = {f: joblib.load(p) for f, p in MODEL_PATHS.items()}
        print("✅ Semua model berhasil dimuat.")
    except Exception as e:
        print(f"❌ Gagal memuat model: {e}")
        return None

    predictions_df = pd.DataFrame()
    combined_df = historical_df.copy() # Gunakan salinan data historis

    # Pastikan kolom yang tidak relevan untuk training tidak ada
    combined_df = combined_df.drop(columns=['hour', 'day_of_week'], errors='ignore')

    # Prediksi untuk 3 hari ke depan dari hari ini
    future_dates = [datetime.now().date() + timedelta(days=i) for i in range(3)]

    for pred_date in future_dates:
        print(f"\n--- Memprediksi untuk Tanggal: {pred_date} ---")
        for hour in TARGET_HOURS:
            dt_to_predict = pd.Timestamp(f"{pred_date} {hour}:00:00")
            new_row_data = {'date': dt_to_predict} # Data untuk baris prediksi baru

            # Ambil data historis hingga waktu sebelum prediksi
            temp_df_for_features = combined_df[combined_df['date'] < dt_to_predict].copy()

            if temp_df_for_features.empty:
                print(f"⚠️ Tidak ada data historis yang cukup sebelum {dt_to_predict} untuk membuat fitur.")
                # Jika tidak ada data historis, lewati prediksi ini atau isi dengan NaN
                predictions_df = pd.concat([predictions_df, pd.DataFrame([new_row_data])], ignore_index=True)
                continue

            # Tambahkan baris prediksi ke temp_df_for_features untuk membuat fitur
            temp_df_for_features = pd.concat([temp_df_for_features, pd.DataFrame([new_row_data])], ignore_index=True)
            temp_df_for_features.set_index('date', inplace=True)
            temp_df_for_features.sort_index(inplace=True)

            # --- Generate ALL features for the prediction point, just like in training ---
            for col in BASE_FEATURES_NAMES: # Penting: Loop melalui SEMUA base features
                # Mengisi nilai NaN jika ada (misalnya, untuk baris yang baru ditambahkan)
                temp_df_for_features[col] = temp_df_for_features[col].ffill().bfill()

                # Fitur Lagged
                for i in range(1, 8):
                    temp_df_for_features[f'{col}_lag{i}'] = temp_df_for_features[col].shift(i)

                # Fitur Rolling Statistics
                for window in [3, 7]:
                    temp_df_for_features[f'{col}_rolling_mean_{window}'] = temp_df_for_features[col].rolling(window=window, min_periods=1).mean()
                    temp_df_for_features[f'{col}_rolling_std_{window}'] = temp_df_for_features[col].rolling(window=window, min_periods=1).std()

            # Fitur siklikal day_of_year
            temp_df_for_features['day_of_year'] = temp_df_for_features.index.dayofyear
            temp_df_for_features['day_of_year_sin'] = np.sin(2 * np.pi * temp_df_for_features['day_of_year'] / 365)
            temp_df_for_features['day_of_year_cos'] = np.cos(2 * np.pi * temp_df_for_features['day_of_year'] / 365)

            # --- PENTING: Jangan membuat fitur hour dan day_of_week di sini
            # karena tidak ada dalam skrip training Anda
            # temp_df_for_features['hour'] = temp_df_for_features.index.hour
            # temp_df_for_features['hour_sin'] = np.sin(2 * np.pi * temp_df_for_features['hour'] / 24)
            # temp_df_for_features['hour_cos'] = np.cos(2 * np.pi * temp_df_for_features['hour'] / 24)
            # temp_df_for_features['day_of_week'] = temp_df_for_features.index.dayofweek
            # temp_df_for_features['day_of_week_sin'] = np.sin(2 * np.pi * temp_df_for_features['day_of_week'] / 7)
            # temp_df_for_features['day_of_week_cos'] = np.cos(2 * np.pi * temp_df_for_features['day_of_week'] / 7)

            # Periksa apakah titik prediksi ada di DataFrame setelah feature engineering
            if dt_to_predict not in temp_df_for_features.index:
                print(f"⚠️ Titik prediksi {dt_to_predict} tidak ditemukan setelah pembuatan fitur. Melewatkan.")
                predictions_df = pd.concat([predictions_df, pd.DataFrame([new_row_data])], ignore_index=True)
                continue

            # Iterasi untuk setiap target yang akan diprediksi
            for target_col in FEATURES_TO_PREDICT:
                # Dapatkan daftar fitur yang tepat untuk model target ini
                current_target_features = PREDICTION_FEATURE_COLUMNS_BY_TARGET[target_col]

                # Pastikan semua fitur yang diperlukan ada di temp_df_for_features
                missing_cols_in_temp_df = [f for f in current_target_features if f not in temp_df_for_features.columns]
                if missing_cols_in_temp_df:
                    print(f"❌ Kolom fitur yang dibutuhkan untuk '{target_col}' tidak ada di DataFrame sementara: {missing_cols_in_temp_df}. Skipping.")
                    new_row_data[target_col] = np.nan
                    continue

                # Ambil fitur input untuk titik prediksi saat ini
                input_features = temp_df_for_features.loc[dt_to_predict, current_target_features]

                # Periksa NaN pada fitur input yang baru dibuat
                if input_features.isnull().any():
                    missing_features = input_features[input_features.isnull()].index.tolist()
                    print(f"⚠️ Fitur hilang/NaN untuk '{target_col}' pada {dt_to_predict}: {missing_features}. Melewatkan prediksi untuk target ini.")
                    new_row_data[target_col] = np.nan
                    continue

                # Reshape data input agar sesuai dengan model (1 baris, N kolom)
                input_data = input_features.values.reshape(1, -1).astype(float)

                # Periksa jumlah fitur
                if input_data.shape[1] != len(current_target_features):
                    print(f"❌ Ketidakcocokan jumlah fitur untuk '{target_col}': diharapkan {len(current_target_features)}, didapat {input_data.shape[1]} pada {dt_to_predict}")
                    print(f"Fitur yang diharapkan: {current_target_features}")
                    print(f"Fitur aktual yang disiapkan: {input_features.index.tolist()}")
                    new_row_data[target_col] = np.nan
                    continue

                processed_input_data = input_data
                if feature_scaler: # Jika scaler dimuat, terapkan
                    try:
                        processed_input_data = feature_scaler.transform(input_data)
                    except Exception as e:
                        print(f"❌ Gagal menskala data input untuk {target_col} pada {dt_to_predict}: {e}. Melewatkan.")
                        new_row_data[target_col] = np.nan
                        continue

                # Lakukan prediksi
                try:
                    pred = models[target_col].predict(processed_input_data)[0]
                    new_row_data[target_col] = max(0, pred) # Pastikan prediksi tidak negatif
                except Exception as e:
                    print(f"❌ Prediksi gagal untuk {target_col} pada {dt_to_predict}: {e}")
                    new_row_data[target_col] = np.nan

            # Tambahkan hasil prediksi ke DataFrame prediksi utama
            df_row_pred = pd.DataFrame([new_row_data])
            predictions_df = pd.concat([predictions_df, df_row_pred], ignore_index=True)

            # Jika semua prediksi untuk baris ini berhasil dan tidak NaN, tambahkan ke combined_df
            # agar dapat digunakan sebagai data historis untuk prediksi selanjutnya (recursive prediction)
            if not df_row_pred[FEATURES_TO_PREDICT].isnull().values.any():
                combined_df = pd.concat([combined_df, df_row_pred], ignore_index=True)
                combined_df.sort_values(by='date', inplace=True)
            else:
                print(f"⚠️ Baris {dt_to_predict} tidak ditambahkan ke data historis karena ada prediksi NaN.")


    # --- POST-PROCESSING RULES TO ENSURE LOGICAL CONSISTENCY ---
    current_time_wib = datetime.now()
    current_date = current_time_wib.date()

    for idx in predictions_df.index:
        pred_datetime = predictions_df.loc[idx, 'date']

        # Hanya terapkan aturan post-processing untuk prediksi hari ini atau di masa depan
        if pred_datetime.date() < current_date:
            continue

        irradiance_pred = predictions_df.loc[idx, 'irradiance']
        temp_pred = predictions_df.loc[idx, 'temperature_c']
        precip_pred = predictions_df.loc[idx, 'precipitation_mm_per_hr']
        humidity_pred = predictions_df.loc[idx, 'humidity_percent']
        current_hour = pred_datetime.hour

        original_values = {
            'irradiance': irradiance_pred, 'temperature_c': temp_pred,
            'precipitation_mm_per_hr': precip_pred, 'humidity_percent': humidity_pred
        }
        applied_rules = [] # Untuk melacak aturan yang diterapkan

        # Definisikan batas realistis dan hubungan untuk Semarang (SESUAIKAN NILAI INI DENGAN HATI-HATI!)
        # Nilai-nilai ini harus berdasarkan domain pengetahuan Anda tentang cuaca Semarang
        MIN_TEMP_DAYLIGHT_CORE = 28.0 # Suhu minimum siang hari (jam puncak)
        MIN_TEMP_DAYLIGHT_TRANSITION = 26.0 # Suhu minimum siang hari (jam transisi)
        MAX_TEMP_NIGHT = 28.0 # Suhu maksimum malam hari

        MIN_IRRADIANCE_MIDDAY_CLEAR = 750.0 # Irradiance minimum di siang hari cerah
        MIN_IRRADIANCE_TRANSITION = 180.0 # Irradiance minimum di jam transisi (pagi/sore)
        ABSOLUTE_MIN_IRRADIANCE_DAYLIGHT = 50.0 # Irradiance minimum absolut di siang hari

        RAIN_THRESHOLD_HEAVY = 20.0 # Ambang batas hujan deras
        RAIN_THRESHOLD_MODERATE = 5.0 # Ambang batas hujan sedang
        RAIN_THRESHOLD_LIGHT = 0.5 # Ambang batas hujan ringan (hampir tidak ada)

        HUMIDITY_HIGH_RAIN = 95.0 # Kelembaban tinggi saat hujan
        HUMIDITY_LOW_SUNNY = 55.0 # Kelembaban rendah saat cerah


        # --- Rule 0: Batas umum untuk Suhu ---
        if current_hour >= 8 and current_hour <= 17: # Jam siang
            min_expected_temp = MIN_TEMP_DAYLIGHT_CORE if current_hour in [10, 11, 12, 13, 14, 15] else MIN_TEMP_DAYLIGHT_TRANSITION
            if temp_pred < min_expected_temp:
                predictions_df.loc[idx, 'temperature_c'] = min_expected_temp + np.random.uniform(1.0, 4.0)
                applied_rules.append(f"Temp_Fix_Low_Day({original_values['temperature_c']:.2f}->{predictions_df.loc[idx, 'temperature_c']:.2f})")
        else: # Jam malam (jika TARGET_HOURS diperluas untuk mencakup malam)
            if temp_pred > MAX_TEMP_NIGHT:
                predictions_df.loc[idx, 'temperature_c'] = MAX_TEMP_NIGHT - np.random.uniform(0.0, 3.0)
                applied_rules.append(f"Temp_Fix_High_Night({original_values['temperature_c']:.2f}->{predictions_df.loc[idx, 'temperature_c']:.2f})")
            if temp_pred < 23.0: # Batas bawah absolut untuk malam
                predictions_df.loc[idx, 'temperature_c'] = 23.0 + np.random.uniform(0.0, 2.0)
                applied_rules.append(f"Temp_Fix_Abs_Low_Night({original_values['temperature_c']:.2f}->{predictions_df.loc[idx, 'temperature_c']:.2f})")
        temp_pred = predictions_df.loc[idx, 'temperature_c'] # Update nilai temp_pred setelah aturan diterapkan


        # --- Rule 1: Irradiance pada jam siang tidak boleh nol/sangat rendah. ---
        if current_hour in [8, 9, 10, 11, 12, 13, 14, 15, 16, 17]: # Semua jam siang
            if irradiance_pred < ABSOLUTE_MIN_IRRADIANCE_DAYLIGHT:
                predictions_df.loc[idx, 'irradiance'] = ABSOLUTE_MIN_IRRADIANCE_DAYLIGHT + np.random.uniform(0.0, 30.0)
                applied_rules.append(f"Irrad_Abs_Min_Fix({original_values['irradiance']:.2f}->{predictions_df.loc[idx, 'irradiance']:.2f})")

            # Jika tidak hujan sedang/deras, irradiance harus di atas ambang batas tertentu
            if precip_pred < RAIN_THRESHOLD_MODERATE:
                if current_hour in [8, 9, 16, 17]: # Jam transisi
                    if predictions_df.loc[idx, 'irradiance'] < MIN_IRRADIANCE_TRANSITION:
                        predictions_df.loc[idx, 'irradiance'] = np.random.uniform(MIN_IRRADIANCE_TRANSITION, MIN_IRRADIANCE_TRANSITION + 100.0)
                        applied_rules.append(f"Irrad_Trans_Push({original_values['irradiance']:.2f}->{predictions_df.loc[idx, 'irradiance']:.2f})")
                elif current_hour in [12, 13]: # Jam puncak
                    if predictions_df.loc[idx, 'irradiance'] < MIN_IRRADIANCE_MIDDAY_CLEAR * 0.7:
                        predictions_df.loc[idx, 'irradiance'] = np.random.uniform(MIN_IRRADIANCE_MIDDAY_CLEAR * 0.7, MIN_IRRADIANCE_MIDDAY_CLEAR * 0.9)
                        applied_rules.append(f"Irrad_Midday_Push({original_values['irradiance']:.2f}->{predictions_df.loc[idx, 'irradiance']:.2f})")
        else: # Jam malam (di luar 8 pagi - 5 sore)
            # Irradiance harus sangat rendah mendekati nol
            if predictions_df.loc[idx, 'irradiance'] > 10.0:
                predictions_df.loc[idx, 'irradiance'] = np.random.uniform(0.0, 5.0)
                applied_rules.append(f"Irrad_Night_SetToZero({original_values['irradiance']:.2f}->{predictions_df.loc[idx, 'irradiance']:.2f})")
        irradiance_pred = predictions_df.loc[idx, 'irradiance'] # Update nilai irradiance_pred


        # --- Rule 2: Irradiance Tinggi -> Rainfall Rendah & Humidity Rendah ---
        if irradiance_pred > MIN_IRRADIANCE_MIDDAY_CLEAR * 0.7: # Jika irradiance cukup tinggi (cerah)
            if precip_pred > RAIN_THRESHOLD_LIGHT: # Jika masih ada hujan yang terdeteksi
                predictions_df.loc[idx, 'precipitation_mm_per_hr'] = np.random.uniform(0.0, RAIN_THRESHOLD_LIGHT / 2.0)
                applied_rules.append(f"Precip_Irrad_High({original_values['precipitation_mm_per_hr']:.2f}->{predictions_df.loc[idx, 'precipitation_mm_per_hr']:.2f})")

            if humidity_pred > HUMIDITY_LOW_SUNNY: # Jika kelembaban terlalu tinggi saat cerah
                predictions_df.loc[idx, 'humidity_percent'] = np.random.uniform(HUMIDITY_LOW_SUNNY - 10.0, HUMIDITY_LOW_SUNNY + 5.0)
                applied_rules.append(f"Hum_Irrad_High({original_values['humidity_percent']:.2f}->{predictions_df.loc[idx, 'humidity_percent']:.2f})")

        # --- Rule 3: Rainfall Tinggi -> Humidity Sangat Tinggi ---
        if precip_pred >= RAIN_THRESHOLD_MODERATE: # Jika hujan sedang/deras
            if humidity_pred < HUMIDITY_HIGH_RAIN: # Kelembaban harus sangat tinggi
                predictions_df.loc[idx, 'humidity_percent'] = np.random.uniform(HUMIDITY_HIGH_RAIN, 100.0)
                applied_rules.append(f"Hum_Precip_High({original_values['humidity_percent']:.2f}->{predictions_df.loc[idx, 'humidity_percent']:.2f})")
        elif precip_pred > 0 and precip_pred < RAIN_THRESHOLD_MODERATE: # Jika ada hujan ringan
            if humidity_pred < 75.0: # Kelembaban harus setidaknya 75%
                predictions_df.loc[idx, 'humidity_percent'] = np.random.uniform(75.0, HUMIDITY_HIGH_RAIN)
                applied_rules.append(f"Hum_Precip_Moderate({original_values['humidity_percent']:.2f}->{predictions_df.loc[idx, 'humidity_percent']:.2f})")

        # --- Rule 4: Rainfall Nol/Rendah -> Humidity Tidak Boleh Sangat Tinggi ---
        if precip_pred <= RAIN_THRESHOLD_LIGHT and humidity_pred > 90.0: # Jika tidak ada/hujan sangat ringan tapi kelembaban sangat tinggi
            predictions_df.loc[idx, 'humidity_percent'] = np.random.uniform(60.0, 85.0) # Sesuaikan ke rentang yang lebih realistis
            applied_rules.append(f"Hum_Precip_Low_NoRain({original_values['humidity_percent']:.2f}->{predictions_df.loc[idx, 'humidity_percent']:.2f})")

        # --- Rule 5: Final Bounds Clipping (Pastikan dalam rentang yang wajar) ---
        predictions_df.loc[idx, 'irradiance'] = np.clip(predictions_df.loc[idx, 'irradiance'], 0.0, 1300.0)
        predictions_df.loc[idx, 'temperature_c'] = np.clip(predictions_df.loc[idx, 'temperature_c'], 20.0, 40.0)
        predictions_df.loc[idx, 'precipitation_mm_per_hr'] = np.clip(predictions_df.loc[idx, 'precipitation_mm_per_hr'], 0.0, 250.0)
        predictions_df.loc[idx, 'humidity_percent'] = np.clip(predictions_df.loc[idx, 'humidity_percent'], 20.0, 100.0)

        # Cetak aturan yang diterapkan jika ada
        if applied_rules:
            print(f"Post-processed {pred_datetime.strftime('%Y-%m-%d %H:%M:%S')}: Aturan Diterapkan: {', '.join(applied_rules)}")
            print(f"  -> Irrad: {original_values['irradiance']:.2f} -> {predictions_df.loc[idx, 'irradiance']:.2f}")
            print(f"  -> Temp: {original_values['temperature_c']:.2f} -> {predictions_df.loc[idx, 'temperature_c']:.2f}")
            print(f"  -> Precip: {original_values['precipitation_mm_per_hr']:.2f} -> {predictions_df.loc[idx, 'precipitation_mm_per_hr']:.2f}")
            print(f"  -> Humid: {original_values['humidity_percent']:.2f} -> {predictions_df.loc[idx, 'humidity_percent']:.2f}")

    return predictions_df

# --- 4. EKSEKUSI UTAMA ---
def execute_prediction_cycle():
    """Menjalankan siklus prediksi penuh."""
    print(f"\n{'='*80}\nSiklus dimulai: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n{'='*80}")

    df_raw = fetch_data_from_api(API_URL)
    if df_raw.empty:
        print("❌ Tidak ada data yang diambil untuk diproses.")
        return

    df_processed = preprocess_data(df_raw)
    print(f"✅ Data diproses: {len(df_processed)} baris.")

    if df_processed.empty:
        print("❌ Tidak ada data valid setelah pra-pemrosesan.")
        return

    predictions = run_prediction_pipeline(df_processed)
    if predictions is None or predictions.empty:
        print("ℹ️ Tidak ada prediksi yang valid dihasilkan.")
        return

    print("\n✅ Hasil Prediksi (setelah post-processing):")
    predictions['date_str'] = predictions['date'].dt.strftime('%Y-%m-%d %H:%M:%S')
    for f in FEATURES_TO_PREDICT:
        predictions[f] = predictions[f].round(2)

    # Hanya tampilkan prediksi untuk hari ini atau masa depan
    preds_to_show = predictions[predictions['date'].dt.date >= datetime.now().date()]
    if not preds_to_show.empty:
        print(preds_to_show[['date_str'] + FEATURES_TO_PREDICT].to_string(index=False))
    else:
        print("Tidak ada prediksi untuk hari ini atau tanggal di masa depan yang akan ditampilkan.")

    print("\n🔄 Mengirim hasil ke API...")
    posted = 0
    skipped = 0

    for _, row in predictions.iterrows():
        # Hanya kirim prediksi untuk hari ini atau masa depan
        if row['date'].date() < datetime.now().date():
            continue

        if any(pd.isna(row[f]) for f in FEATURES_TO_PREDICT):
            print(f"⏩ Melewatkan {row['date_str']} (prediksi hilang).")
            skipped += 1
            continue

        payload = {
            "date": row['date_str'],
            "irradiance": float(row['irradiance']),
            "temperature_c": float(row['temperature_c']),
            "precipitation_mm_per_hr": float(row['precipitation_mm_per_hr']),
            "humidity_percent": float(row['humidity_percent']),
        }

        try:
            response = requests.post(POST_URL, json=payload)
            if response.status_code in [200, 201]:
                print(f"✅ Berhasil dikirim: {row['date_str']}")
                posted += 1
            else:
                print(f"❌ Gagal mengirim {row['date_str']}: {response.status_code} - {response.text}")
                skipped += 1
        except Exception as e:
            print(f"❌ Error saat mengirim {row['date_str']}: {e}")
            skipped += 1

    print(f"\nRingkasan: {posted} berhasil dikirim, {skipped} gagal/dilewati.")

# --- 5. MAIN LOOP ---
if __name__ == "__main__":
    try:
        print("🔁 Skrip prediksi dimulai. Tekan Ctrl+C untuk keluar.")
        while True:
            execute_prediction_cycle()
            print("\n⏳ Menunggu 3 hari untuk siklus berikutnya...\n")
            time.sleep(3 * 24 * 60 * 60) # Menunggu 3 hari
    except KeyboardInterrupt:
        print("\n🛑 Dihentikan oleh pengguna.")
    except Exception as e:
        print(f"\n‼️ Error fatal: {e}")
