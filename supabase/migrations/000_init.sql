-- Базовая схема для быстрого старта
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Users
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    vk_id BIGINT UNIQUE,
    email VARCHAR(255) UNIQUE,
    username VARCHAR(100),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    avatar_url TEXT,
    role VARCHAR(50) DEFAULT 'user',
    balance DECIMAL(10, 2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT true,
    is_blocked BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Bots
CREATE TABLE IF NOT EXISTS bots (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    vk_group_id BIGINT,
    vk_token TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    config JSONB DEFAULT '{}',
    messages_sent INTEGER DEFAULT 0,
    messages_received INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Payments
CREATE TABLE IF NOT EXISTS payments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'RUB',
    status VARCHAR(50) DEFAULT 'pending',
    provider VARCHAR(50) DEFAULT 'yookassa',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Payment Methods
CREATE TABLE IF NOT EXISTS payment_methods (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    is_enabled BOOLEAN DEFAULT false,
    config JSONB DEFAULT '{}',
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Settings
CREATE TABLE IF NOT EXISTS settings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    key VARCHAR(255) UNIQUE NOT NULL,
    value JSONB NOT NULL,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Индексы
CREATE INDEX IF NOT EXISTS idx_users_vk_id ON users(vk_id);
CREATE INDEX IF NOT EXISTS idx_bots_user_id ON bots(user_id);
CREATE INDEX IF NOT EXISTS idx_payments_user_id ON payments(user_id);

-- Seed данные
INSERT INTO payment_methods (name, display_name, is_enabled, config, sort_order) VALUES
    ('yoomoney_p2p', 'ЮMoney P2P', true, '{"min_amount":100,"max_amount":50000,"commission":0}', 1),
    ('yookassa', 'ЮKassa', false, '{"min_amount":100,"max_amount":100000,"commission":0.028}', 2),
    ('cards', 'Банковские карты', false, '{"min_amount":100,"max_amount":100000,"commission":0.025}', 3)
ON CONFLICT (name) DO NOTHING;

INSERT INTO settings (key, value) VALUES
    ('system.maintenance_mode', 'false'),
    ('system.registration_enabled', 'true')
ON CONFLICT (key) DO NOTHING;
