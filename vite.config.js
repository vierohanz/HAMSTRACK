import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/css/navigation.css",
                "resources/css/title.css",
                "resources/css/chart.css",
                "resources/js/app.js",
                "resources/js/chart.js",
                "resources/js/bootstrap.js",
                "resources/js/table.js",
                "resources/api/fetchChart.js",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
