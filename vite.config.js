import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});


// import { defineConfig } from 'vite';
// import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//     ],
//     // បន្ថែមផ្នែកនេះ
//     server: {
//         host: '0.0.0.0', // អនុញ្ញាតឱ្យ run លើគ្រប់ Network Interface
//         hmr: {
//             host: '172.30.126.71', // ដាក់ IP Address ម៉ាស៊ីនរបស់អ្នកនៅទីនេះ (IP ដែលអ្នកប្រើពេល serve)
//         },
//         port: 5173, // Port default របស់ Vite
//     },
// });