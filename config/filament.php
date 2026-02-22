<?php

use App\Filament\Widgets\CalendarWidget;
use Filament\Facades\Filament;

return [

    'widgets' => [
        CalendarWidget::class,
    ],

    'cache_path' => base_path('bootstrap/cache/filament'),

    'livewire_loading_delay' => 'default',

    'auth' => [
        'guard' => 'web',
        'user' => App\Models\User::class,
        'logout_redirect' => '/',
    ],

    'panels' => [
        'admin' => App\Providers\Filament\AdminPanelProvider::class,
    ],

    'default_filesystem_disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

    'assets_path' => 'assets/filament',

    'rendering' => [
        'page' => [
            'chunk' => [
                'enabled' => true,
                'size' => 25000,
            ],
        ],
    ],

];

Filament::getUserAvatarProvider()->setNameUsing(function ($user) {
    return $user->name.' '.$user->apellido;
});
