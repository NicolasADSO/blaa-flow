<?php

return [

    'broadcasting' => [
        // configuración opcional...
    ],  

    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

    'assets_path' => 'css/filament',

    'cache_path' => base_path('bootstrap/cache/filament'),

    'livewire_loading_delay' => 'default',

    'system_route_prefix' => 'filament',

    /*
    |--------------------------------------------------------------------------
    | Panel config (branding y tema)
    |--------------------------------------------------------------------------
    */

    'brand' => [
        'name' => 'BlaaFlow',
        'logo' => 'https://filamentphp.com/images/logo.svg', // aquí puedes poner ruta a un logo en public/logo.png
        'favicon' => '/images/favicon-16x16.png', // aquí puedes poner ruta a un favicon en public/favicon-16x16.png
    ],

    /*'theme' => [
        'default' => 'light', // 👈 fuerza tema claro
        'toggle' => true,     // permite cambiar claro/oscuro en el panel
    ],*/

    /*
    |--------------------------------------------------------------------------
    | Colors globales
    |--------------------------------------------------------------------------
    */

    'colors' => [
        'primary' => 'gadier.red',
        'secondary' => 'gadier.gray',
        'danger' => '#ef4444',   // rojo
        'success' => '#22c55e',  // verde
    ],
];
