-- Создание таблицы payment_methods
CREATE TABLE IF NOT EXISTS payment_methods (
    id VARCHAR(50) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    description TEXT,
    icon VARCHAR(10) DEFAULT '💳',
    enabled BOOLEAN DEFAULT true,
    min_amount DECIMAL(10,2) DEFAULT 100,
    max_amount DECIMAL(10,2) DEFAULT 100000,
    commission DECIMAL(5,2) DEFAULT 0,
    
    -- API ключи (будут хэшироваться)
    api_key TEXT,
    api_secret TEXT,
    merchant_id TEXT,
    
    -- Дополнительные настройки
    settings JSONB,
    
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Индекс для enabled
CREATE INDEX IF NOT EXISTS idx_payment_methods_enabled ON payment_methods(enabled);

-- Вставка данных по умолчанию
INSERT INTO payment_methods (id, name, title, type, description, icon, enabled, min_amount, max_amount, commission) VALUES
    ('yoomoney', 'YooMoney', 'YooMoney P2P', 'p2p', 'P2P перевод', '💰', true, 100, 100000, 0),
    ('card', 'Банковская карта', 'Банковская карта', 'card', 'Visa, MasterCard, MIR', '💳', true, 100, 100000, 0),
    ('sbp', 'СБП', 'СБП (QR)', 'qr', 'Система Быстрых Платежей', '📱', false, 100, 100000, 0),
    ('crypto', 'Криптовалюта', 'Криптовалюта', 'crypto', 'USDT, BTC, ETH', '₿', false, 100, 100000, 0)
ON CONFLICT (id) DO NOTHING;
