<?php

return [
    /*
    |--------------------------------------------------------------------------
    | External operational evidence
    |--------------------------------------------------------------------------
    |
    | The evidence pack is generated and stored outside the repository after
    | real staging drills. It must not contain credentials or personal data.
    |
    */

    'evidence_path' => env('EXPLORIA_OPERATIONAL_EVIDENCE_PATH'),

    'evidence_max_age_minutes' => (int) env('EXPLORIA_OPERATIONAL_EVIDENCE_MAX_AGE_MINUTES', 1440),

    /*
    |--------------------------------------------------------------------------
    | Application-managed storage
    |--------------------------------------------------------------------------
    |
    | Advertising uploads currently use the public disk explicitly. The gate
    | probes the disk the application actually uses without selecting a vendor.
    |
    */

    'storage_disks' => ['public'],
];
