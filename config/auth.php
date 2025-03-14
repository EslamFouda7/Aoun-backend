<?php

return [

    'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],

    'api' => [
        'driver' => 'sanctum',
        'provider' => 'donors', // أو 'foundations' إذا كنت تريد استخدام Foundation كمزود افتراضي
    ],

    'donor' => [
        'driver' => 'session', // استخدام session للتحقق من تسجيل الدخول
        'provider' => 'donors', // يجب أن يكون موجودًا في القسم 'providers'
    ],

    'foundation' => [
        'driver' => 'session', // استخدام session للتحقق من تسجيل الدخول
        'provider' => 'foundations', // يجب أن يكون موجودًا في القسم 'providers'
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],

    'donors' => [
        'driver' => 'eloquent',
        'model' => App\Models\Donor::class, // تأكد من أن النموذج موجود
    ],

    'foundations' => [
        'driver' => 'eloquent',
        'model' => App\Models\Foundation::class, // تأكد من أن النموذج موجود
    ],
],
];
