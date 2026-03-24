<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Livewire Temporary Uploads
    |--------------------------------------------------------------------------
    |
    | Keep Livewire's temporary upload files on local disk even when the
    | application's default filesystem disk is S3. This avoids temp-file
    | warnings on constrained container environments.
    |
    */
    'temporary_file_upload' => [
        'disk' => env('LIVEWIRE_TEMP_DISK', 'local'),
        'directory' => env('LIVEWIRE_TEMP_DIR', 'livewire-tmp'),
    ],
];

