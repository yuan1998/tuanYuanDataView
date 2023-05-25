<?php

use Illuminate\Support\HtmlString;

if (!function_exists('vite_assets')) {
    /**
     * @return HtmlString
     * @throws Exception
     */
    function vite_assets(): HtmlString
    {
        $devServerIsRunning = false;

        $manifest = @json_decode(@file_get_contents(
            public_path('dist/manifest.json')
        ), true);
        if (isset($manifest['resources/js/app.js'])) {
            return new HtmlString(<<<HTML
        <script type="module" src="/dist/{$manifest['resources/js/app.js']['file']}"></script>
        <link rel="stylesheet" href="/dist/{$manifest['resources/js/app.js']['css'][0]}">
    HTML
            );
        }

        return new HtmlString(<<<HTML
            <script type="module" src="http://localhost:3002/@vite/client"></script>
            <script type="module" src="http://localhost:3002/resources/js/app.js"></script>
        HTML
        );
    }
}
