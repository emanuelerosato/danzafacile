<?php

return [

    /*
    |--------------------------------------------------------------------------
    | QR Code Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for QR code generation used in event check-in system.
    | Uses simplesoftwareio/simple-qrcode package.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | QR Code Format
    |--------------------------------------------------------------------------
    |
    | The format for generated QR codes.
    | Supported formats: png, svg, eps
    | Default: svg (scalable and smaller file size)
    |
    */
    'format' => env('QRCODE_FORMAT', 'svg'),

    /*
    |--------------------------------------------------------------------------
    | QR Code Size
    |--------------------------------------------------------------------------
    |
    | The size of the QR code in pixels.
    | Recommended: 200-400 for print, 300 is a good balance
    |
    */
    'size' => env('QRCODE_SIZE', 300),

    /*
    |--------------------------------------------------------------------------
    | Error Correction Level
    |--------------------------------------------------------------------------
    |
    | The error correction level for QR codes.
    | Options: L (7%), M (15%), Q (25%), H (30%)
    | Higher levels allow QR codes to be read even if partially damaged
    | Default: M (15%) - good balance between size and error correction
    |
    */
    'error_correction' => env('QRCODE_ERROR_CORRECTION', 'M'),

    /*
    |--------------------------------------------------------------------------
    | QR Code Storage Path
    |--------------------------------------------------------------------------
    |
    | The directory where generated QR codes will be stored.
    | Relative to the storage/app/public directory
    |
    */
    'storage_path' => env('QRCODE_STORAGE_PATH', 'qr_codes'),

    /*
    |--------------------------------------------------------------------------
    | QR Code Auto-Generation
    |--------------------------------------------------------------------------
    |
    | Automatically generate QR codes when event registration is created.
    | If false, QR codes must be generated manually via admin panel.
    |
    */
    'auto_generate' => env('QRCODE_AUTO_GENERATE', true),

    /*
    |--------------------------------------------------------------------------
    | QR Code Expiration
    |--------------------------------------------------------------------------
    |
    | Number of days after event end date when QR codes should be deleted.
    | Set to null to keep QR codes indefinitely.
    |
    */
    'cleanup_after_days' => env('QRCODE_CLEANUP_DAYS', 30),

];
