-- Обновление таблицы payment_methods
ALTER TABLE payment_methods 
ADD COLUMN IF NOT EXISTS type VARCHAR(50) DEFAULT 'p2p',
ADD COLUMN IF NOT EXISTS icon VARCHAR(10) DEFAULT '💳',
ADD COLUMN IF NOT EXISTS description TEXT,
ADD COLUMN IF NOT EXISTS min_amount DECIMAL(10,2) DEFAULT 100,
ADD COLUMN IF NOT EXISTS max_amount DECIMAL(10,2) DEFAULT 100000,
ADD COLUMN IF NOT EXISTS commission DECIMAL(5,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS api_key_encrypted TEXT,
ADD COLUMN IF NOT EXISTS api_secret_encrypted TEXT,
ADD COLUMN IF NOT EXISTS merchant_id_encrypted TEXT,
ADD COLUMN IF NOT EXISTS settings JSONB;

-- Индекс для is_enabled
CREATE INDEX IF NOT EXISTS idx_payment_methods_enabled ON payment_methods(is_enabled);

-- Вставка данных по умолчанию
INSERT INTO payment_methods (name, display_name, type, icon, description, is_enabled, min_amount, max_amount) VALUES
    ('yoomoney', 'YooMoney P2P', 'p2p', '💰', 'P2P перевод', true, 100, 100000),
    ('card', 'Банковская карта', 'card', '💳', 'Visa, MasterCard, MIR', true, 100, 100000),
    ('sbp', 'СБП (QR)', 'qr', '📱', 'Система Быстрых Платежей', false, 100, 100000),
    ('crypto', 'Криптовалюта', 'crypto', '₿', 'USDT, BTC, ETH', false, 100, 100000)
ON CONFLICT (name) DO UPDATE SET 
    display_name = EXCLUDED.display_name,
    type = EXCLUDED.type,
    icon = EXCLUDED.icon,
    description = EXCLUDED.description;
