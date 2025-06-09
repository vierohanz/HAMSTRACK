import requests
import pandas as pd
import joblib
from datetime import datetime, timedelta
import numpy as np
import os

# --- 1. KONFIGURASI ---
MODEL_PATHS = {
    'irradiance': r'C:\laragon\www\Hamstrack\app\Http\AI\irradiance_model.joblib',
    'temperature_c': r'C:\laragon\www\Hamstrack\app\Http\AI\temperature_c_model.joblib',
    'precipitation_mm_per_hr': r'C:\laragon\www\Hamstrack\app\Http\AI\precipitation_mm_per_hr_model.joblib',
    'humidity_percent': r'C:\laragon\www\Hamstrack\app\Http\AI\humidity_percent_model.joblib'
}
API_URL = "http://localhost:8000/api/allTable"
POST_URL = "http://localhost:8000/api/postTable" # URL untuk mengirim data prediksi
TARGET_HOURS = [8, 9, 12, 13, 16, 17]
FEATURES_TO_PREDICT = list(MODEL_PATHS.keys())
# Sesuaikan angka ini dengan jumlah input yang diterima model Anda
EXPECTED_FEATURES = 50 # Ganti jika model Anda dilatih dengan angka lain

# --- 2. FUNGSI UNTUK MENGAMBIL DAN MEMPROSES DATA ---
def fetch_data_from_api(url):
    try:
        response = requests.get(url)
        response.raise_for_status()
        data = response.json().get('data', [])
        if not data:
            print("Peringatan: Tidak ada data yang diterima dari API.")
            return pd.DataFrame()
        return pd.DataFrame(data)
    except requests.exceptions.RequestException as e:
        print(f"Error saat mengambil data dari API: {e}")
        return None

def preprocess_data(df):
    if df.empty:
        return df
    df['date'] = pd.to_datetime(df['date'])
    for col in FEATURES_TO_PREDICT:
        df[col] = pd.to_numeric(df[col], errors='coerce')
    df.dropna(subset=FEATURES_TO_PREDICT, inplace=True)
    df.sort_values(by='date', inplace=True)
    return df

# --- 3. FUNGSI INTI UNTUK PREDIKSI ---
def run_prediction_pipeline(historical_df):
    try:
        models = {feature: joblib.load(path) for feature, path in MODEL_PATHS.items()}
        print("Semua model berhasil dimuat.")
    except Exception as e:
        print(f"Error saat memuat model: {e}")
        return None

    today = datetime.now().date()
    prediction_dates = [today, today + timedelta(days=1), today + timedelta(days=2)]

    predictions_df = pd.DataFrame()
    combined_df = historical_df.copy()

    for pred_date in prediction_dates:
        print(f"\n--- Memulai Prediksi untuk Tanggal: {pred_date.strftime('%Y-%m-%d')} ---")
        for hour in TARGET_HOURS:
            new_prediction_row = {'date': pd.Timestamp(f"{pred_date} {hour}:00:00")}

            for feature in FEATURES_TO_PREDICT:
                relevant_data = combined_df[combined_df['date'].dt.hour == hour][feature]

                if len(relevant_data) < EXPECTED_FEATURES:
                    print(f"Peringatan: Data untuk jam {hour}:00 ({feature}) hanya {len(relevant_data)} baris. Butuh {EXPECTED_FEATURES}. Prediksi dilewati.")
                    new_prediction_row[feature] = np.nan
                    continue

                last_n_points = relevant_data.tail(EXPECTED_FEATURES).values
                input_for_prediction = np.array([last_n_points])

                model = models[feature]
                predicted_value = model.predict(input_for_prediction)[0]
                predicted_value = abs(predicted_value)
                new_prediction_row[feature] = predicted_value

            new_row_df = pd.DataFrame([new_prediction_row])
            predictions_df = pd.concat([predictions_df, new_row_df], ignore_index=True)

            if not new_row_df.isnull().values.any():
                combined_df = pd.concat([combined_df, new_row_df], ignore_index=True)

    return predictions_df

# --- 4. BLOK EKSEKUSI UTAMA ---
if __name__ == "__main__":
    print("Memulai skrip prediksi...")
    raw_data_df = fetch_data_from_api(API_URL)

    if raw_data_df is not None and not raw_data_df.empty:
        all_processed_data = preprocess_data(raw_data_df)
        print(f"Data berhasil diproses. Total {len(all_processed_data)} baris data valid ditemukan.")

        today_date = datetime.now().date()
        true_historical_df = all_processed_data[all_processed_data['date'].dt.date < today_date].copy()

        print(f"Data historis yang digunakan untuk prediksi (tanggal < {today_date}): {len(true_historical_df)} baris.")

        if true_historical_df.empty:
            print("\n❌ Tidak ada data historis dari hari-hari sebelumnya. Skrip tidak dapat melanjutkan.")
        else:
            final_predictions = run_prediction_pipeline(true_historical_df)

            if final_predictions is not None and not final_predictions.empty:
                print("\n✅ --- HASIL PREDIKSI FINAL --- ✅")
                # Format kolom tanggal dan angka
                final_predictions['date_str'] = final_predictions['date'].dt.strftime('%Y-%m-%d %H:%M:%S')
                for feature in FEATURES_TO_PREDICT:
                    if feature in final_predictions.columns:
                        final_predictions[feature] = final_predictions[feature].round(2)

                print(final_predictions[['date_str'] + FEATURES_TO_PREDICT].to_string(index=False))

                # --- BARU: Mengirim data prediksi ke API ---
                print("\n--- Memulai Pengiriman Data Prediksi ke API ---")
                for _, row in final_predictions.iterrows():
                    # Hanya kirim baris yang tidak mengandung nilai NaN
                    if not row.isnull().any():
                        payload = {
                            "date": row['date_str'],
                            "irradiance": float(row['irradiance']),
                            "temperature_c": float(row['temperature_c']),
                            "precipitation_mm_per_hr": float(row['precipitation_mm_per_hr']),
                            "humidity_percent": float(row['humidity_percent'])
                        }
                        try:
                            response = requests.post(POST_URL, json=payload)
                            if response.status_code in [200, 201]:
                                print(f"✅ Berhasil mengirim prediksi untuk {row['date_str']}")
                            else:
                                print(f"❌ Gagal mengirim prediksi untuk {row['date_str']}: {response.status_code} {response.text}")
                        except Exception as e:
                            print(f"❌ Error saat mengirim prediksi untuk {row['date_str']}: {e}")
                    else:
                        print(f"⏩ Melewati pengiriman untuk {row['date'].strftime('%Y-%m-%d %H:%M:%S')} karena data tidak lengkap (NaN).")

                # --- BARU: Simpan hasil ke file CSV ---
                try:
                    output_filename = "hasil_prediksi.csv"
                    # Simpan dengan kolom date_str yang sudah diformat
                    final_predictions.to_csv(output_filename, columns=['date_str'] + FEATURES_TO_PREDICT, index=False)
                    print(f"\n📁 Prediksi juga telah disimpan ke file: {output_filename}")
                except Exception as e:
                    print(f"\n❌ Gagal menyimpan prediksi ke file CSV: {e}")
            else:
                 print("\nℹ️ Proses prediksi selesai tetapi tidak ada hasil yang dihasilkan.")
    else:
        print("\n❌ Skrip dihentikan karena tidak ada data awal yang bisa diolah.")
