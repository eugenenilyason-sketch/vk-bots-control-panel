<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Payment Methods Configuration
    |--------------------------------------------------------------------------
    |
    | Настройки платёжных методов для всех пользователей
    | Админ может изменять эти настройки через админ-панель
    |
    */
    
    'methods' => [
        'yoomoney' => [
            'id' => 'yoomoney',
            'name' => 'YooMoney',
            'title' => 'YooMoney P2P',
            'enabled' => true,
            'type' => 'p2p',
            'icon' => '💰',
            'description' => 'P2P перевод',
            'min_amount' => 100,
            'max_amount' => 100000,
            'commission' => 0,
        ],
        'card' => [
            'id' => 'card',
            'name' => 'Банковская карта',
            'title' => 'Банковская карта',
            'enabled' => true,
            'type' => 'card',
            'icon' => '💳',
            'description' => 'Visa, MasterCard, MIR',
            'min_amount' => 100,
            'max_amount' => 100000,
            'commission' => 0,
        ],
        'sbp' => [
            'id' => 'sbp',
            'name' => 'СБП',
            'title' => 'СБП (QR)',
            'enabled' => false,
            'type' => 'qr',
            'icon' => '📱',
            'description' => 'Система Быстрых Платежей',
            'min_amount' => 100,
            'max_amount' => 100000,
            'commission' => 0,
        ],
        'crypto' => [
            'id' => 'crypto',
            'name' => 'Криптовалюта',
            'title' => 'Криптовалюта',
            'enabled' => false,
            'type' => 'crypto',
            'icon' => '₿',
            'description' => 'USDT, BTC, ETH',
            'min_amount' => 100,
            'max_amount' => 100000,
            'commission' => 0,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Default Payment Method
    |--------------------------------------------------------------------------
    */
    'default' => 'yoomoney',
];
