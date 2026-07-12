<?php

// 1. Boot up Composer's autoloader and load the Laravel application instance
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 2. Handle the HTTP request through the kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// 3. CAPTURE & RE-WRITE THE URI FOR LARAVEL'S INTERNAL ROUTER
$request = Illuminate\Http\Request::capture();

// If the incoming URI doesn't start with /api (e.g. your deploy tools),
// but Vercel routed it here anyway, we force Laravel to see the raw path.
$uri = $request->server->get('REQUEST_URI');

// Clean up duplicate slashes or potential Vercel path mutations
if (strpos($uri, '/api/') === false && $uri !== '/api' && strpos($uri, 'deploy') !== false) {
    // This allows /deploy/routes and your migration tools to function cleanly
    $request->server->set('REQUEST_URI', '/' . ltrim($uri, '/'));
}

// 4. Send the request into Laravel
$response = $kernel->handle($request);

$response->send();

$kernel->terminate($request, $response);
