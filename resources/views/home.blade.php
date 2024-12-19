<!DOCTYPE html>
<html id="displaycolor" lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="overflow-y-auto">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Ultraball</title>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <!-- Favicon -->
        <link rel="shortcut icon" type="image/png" href="/favicon.png" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@100;300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Vite Assets -->
        @vite('resources/js/app.jsx')
        @vite('resources/css/app.css')
    </head>
    <body class="h-screen bg-gray-100 dark:bg-gray-900">
        <!-- React App -->
        <div id="app" class="h-full text-gray-800 dark:text-gray-200 text-sm tracking-wide leading-normal flex flex-col"></div>

        <!-- Dark Mode Script -->
        <script>
            const colorDom = document.getElementById('displaycolor');
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                colorDom.className = 'dark';
            }

            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                colorDom.className = e.matches ? "dark" : "light";
            });
        </script>
    </body>
</html>
