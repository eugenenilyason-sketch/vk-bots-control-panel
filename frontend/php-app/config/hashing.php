<?php

return [
    'driver' => 'bcrypt',

    'bcrypt' => [
        'rounds' => env('BCRYPT_ROUNDS', 10),
        'verify' => false,  // Не проверять алгоритм - принимать любой хэш
    ],

    'argon' => [
        'memory' => 65536,
        'threads' => 1,
        'time' => 4,
        'verify' => false,  // Не проверять алгоритм
    ],
];
