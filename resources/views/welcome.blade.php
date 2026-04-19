<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ secure_asset('app.css') }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

</head>
<body>
<div class="background">
    <div class="container">
        <h1></h1>
        <div class="holographic-card">
            <h2>Enaya: Medical Center Management System</h2>
        </div>
        <!-- Using common classes to minimize redundancy -->
        <span class="ball"></span>
        <span class="ball"></span>
        <span class="ball"></span>
        <span class="ball"></span>
        <span class="ball"></span>
        <span class="ball"></span>
    </div>
</div>
<div class="loader_container">
    <div class="loader">
        <svg width="100" height="100" viewBox="0 0 100 100">
            <defs>
                <mask id="clipping">
                    <polygon points="0,0 100,0 100,100 0,100" fill="black"></polygon>
                    <polygon points="25,25 75,25 50,75" fill="white"></polygon>
                    <polygon points="50,25 75,75 25,75" fill="white"></polygon>
                    <polygon points="35,35 65,35 50,65" fill="white"></polygon>
                    <polygon points="35,35 65,35 50,65" fill="white"></polygon>
                    <polygon points="35,35 65,35 50,65" fill="white"></polygon>
                    <polygon points="35,35 65,35 50,65" fill="white"></polygon>
                </mask>
            </defs>
        </svg>
        <div class="box"></div>
    </div>

</div>
</body>
</html>
