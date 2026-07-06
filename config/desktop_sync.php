<?php

/**
 * SQL Server → Web DB sync mapping.
 *
 * Adjust table/column names here if the desktop schema differs per deployment.
 * SQL Server is read-only; credentials come from DESKTOP_DB_* env vars.
 */
return [

    'enabled' => env('DESKTOP_SYNC_ENABLED', env('DESKTOP_DB_ENABLED', true)),

    // How often the scheduler runs test/particular sync (minutes).
    'interval_minutes' => (int) env('DESKTOP_SYNC_INTERVAL_MINUTES', 3),

    // Catalog sync on login — disabled by default (tests/particulars live in web DB).
    // Set DESKTOP_SYNC_ON_LOGIN=true only when you need to refresh from SQL Server.
    'sync_on_login' => env('DESKTOP_SYNC_ON_LOGIN', false),

    // Lab bookings/patients: read live from Leb_reg_test_info view (not synced to web DB).
    'bookings_read_from_view' => true,

    'sources' => [

        'tests' => [
            'connection' => 'desktop',
            'table' => env('DESKTOP_SYNC_TESTS_TABLE', 'test'),
            'columns' => [
                'id' => 'id',
                'name' => 'name',
                'price' => 'price',
                'type' => 'type',
                'carry_out' => 'carry_out',
                'report' => 'report',
            ],
        ],

        'test_particulars' => [
            'connection' => 'desktop',
            'table' => env('DESKTOP_SYNC_PARTICULARS_TABLE', 'test_particulars'),
            'columns' => [
                'id' => 'id',
                'test_id' => 'test_id',
                'name' => 'particulars',
                'unit' => 'unit',
                'male_range' => 'male',
                'female_range' => 'female',
                'child_range' => 'Child',
            ],
        ],

        'bookings' => [
            // View used for live lab reg lookup (read-only, not synced to web DB).
            'connection' => 'desktop',
            'table' => env('DESKTOP_SYNC_BOOKINGS_TABLE', 'Leb_reg_test_info'),
            'columns' => [
                'line_id' => 'TestNO',
                'booking_id' => 'id',
                'mr_no' => 'Mr_No',
                'file_no' => 'Fil_No',
                'patient_name' => 'PatientName',
                'gender' => 'Gender',
                'mobile' => 'MobileNo',
                'age' => 'Age',
                'system_age' => 'Sytem_Age',
                'doctor' => 'doctor',
                'test_id' => 'test_id',
                'test_name' => 'name',
                'test_price' => 'test_p',
                'discount' => 'dis',
                'total' => 'total',
                'paid' => 'paid',
                'due' => 'due',
                'lab_share' => 'Lab_Share',
                'hosp_share' => 'Hosp_share',
                'booking_date' => 'date',
                'priority' => 'type',
                'is_cancelled' => 'Is_Cancel',
            ],
        ],

        'invoice' => [
            'connection' => 'desktop',
            'table' => env('DESKTOP_INVOICE_TABLE', 'invoice'),
            'columns' => [
                'id' => 'id',
                'is_sample_collected' => 'IsSimpleCollect',
            ],
        ],

    ],

];
