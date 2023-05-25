const {resolve} = require('path');
import vue from '@vitejs/plugin-vue';


export default ({command}) => ({
    base: command === 'serve' ? '' : '/dist/',
    publicDir: 'hot',
    build: {
        manifest: true,
        outDir: resolve(__dirname, 'public/dist'),
        rollupOptions: {
            input: 'resources/js/app.js',
        },
    },
    server: {
        host: '0.0.0.0',
    },
    plugins: [vue()],
    resolve: {
        alias: {
            '@': resolve('./resources/js'),
        },
    },
});
