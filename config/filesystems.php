<?php

return [


    'default' => env('FILESYSTEM_DRIVER', 'public'),


    'cloud' => env('FILESYSTEM_CLOUD', 's3'),



    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => '/storage'
        ],


        ],
        'gcs' => [
            'driver' => 'gcs',
            'project_id' => env('GOOGLE_CLOUD_PROJECT_ID', 'betterbricks'),
            'key_file' => env('GOOGLE_CLOUD_KEY_FILE', '/app/config/betterbricks.json'), // optional: /path/to/service-account.json
            'bucket' => env('GOOGLE_CLOUD_STORAGE_BUCKET', 'betterbricks.appspot.com'),
            'path_prefix' => env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', 'storage/app/public'), // optional: /default/path/to/apply/in/bucket
            'storage_api_uri' => env('GOOGLE_CLOUD_STORAGE_API_URI', 'https://storage.googleapis.com/betterbricks.appspot.com/'), // see: Public URLs below
            'visibility' => 'public', // optional: public|private
            'url' => env('APP_URL').'/storage',
            'root' => 'https://storage.googleapis.com/betterbricks.appspot.com/app'
            ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],




    'links' => [
        public_path('storage') => 'https://storage.googleapis.com/betterbricks.appspot.com/storage/app/public'

    ],

];
