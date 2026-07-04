<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Low Stock Threshold
    |--------------------------------------------------------------------------
    |
    | This value is the threshold under which a product will be flagged
    | as having "low stock". It will trigger alerts on the dashboard
    | and database notifications for Admin/Staff roles.
    |
    */
    'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 5),
];
