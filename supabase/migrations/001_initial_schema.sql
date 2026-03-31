-- VK Neuro-Agents Control Panel
-- Database Schema Migration
-- PostgreSQL (Supabase)

-- ============= EXTENSIONS =============
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- ============= ENUMS =============
CREATE TYPE user_role AS ENUM ('superadmin', 'admin', 'user');
CREATE TYPE bot_status AS ENUM ('active', 'inactive', 'blocked', 'pending');
CREATE TYPE payment_status AS ENUM ('pending', 'succeeded', 'failed', 'refunded');
CREATE TYPE payment_type AS ENUM ('deposit', 'subscription', 'one_time');

-- ============= USERS =============
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    vk_id BIGINT UNIQUE,
    ok_id BIGINT UNIQUE,
    email VARCHAR(255) UNIQUE,
    username VARCHAR(100),
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    avatar_url TEXT,
    role user_role DEFAULT 'user',
    balance DECIMAL(10, 2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT true,
    is_blocked BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_users_vk_id ON users(vk_id);
CREATE INDEX idx_users_ok_id ON users(ok_id);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);

-- ============= USER SESSIONS =============
CREATE TABLE IF NOT EXISTS user_sessions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    refresh_token TEXT NOT NULL,
    access_token TEXT NOT NULL,
    expires_at TIMESTAMP WITH TIME ZONE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    last_active TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_sessions_user_id ON user_sessions(user_id);
CREATE INDEX idx_sessions_refresh_token ON user_sessions(refresh_token);

-- ============= BOTS =============
CREATE TABLE IF NOT EXISTS bots (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    vk_group_id BIGINT,
    vk_token TEXT,
    status bot_status DEFAULT 'pending',
    config JSONB DEFAULT '{}',
    webhook_url TEXT,
    last_activity TIMESTAMP WITH TIME ZONE,
    messages_sent INTEGER DEFAULT 0,
    messages_received INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_bots_user_id ON bots(user_id);
CREATE INDEX idx_bots_status ON bots(status);
CREATE INDEX idx_bots_vk_group_id ON bots(vk_group_id);

-- ============= TARGET AUDIENCES =============
CREATE TABLE IF NOT EXISTS target_audiences (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    bot_id UUID REFERENCES bots(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    data JSONB DEFAULT '[]',
    size INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_target_audiences_user_id ON target_audiences(user_id);
CREATE INDEX idx_target_audiences_bot_id ON target_audiences(bot_id);

-- ============= PAYMENTS =============
CREATE TABLE IF NOT EXISTS payments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'RUB',
    status payment_status DEFAULT 'pending',
    type payment_type DEFAULT 'deposit',
    provider VARCHAR(50) DEFAULT 'yookassa',
    provider_payment_id VARCHAR(255),
    description TEXT,
    metadata JSONB DEFAULT '{}',
    paid_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_payments_user_id ON payments(user_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_payments_provider_id ON payments(provider_payment_id);

-- ============= SUBSCRIPTIONS =============
CREATE TABLE IF NOT EXISTS subscriptions (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    plan VARCHAR(100) NOT NULL,
    status VARCHAR(50) DEFAULT 'active',
    start_date TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    end_date TIMESTAMP WITH TIME ZONE,
    auto_renew BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_subscriptions_user_id ON subscriptions(user_id);
CREATE INDEX idx_subscriptions_status ON subscriptions(status);

-- ============= MESSAGES =============
CREATE TABLE IF NOT EXISTS messages (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    bot_id UUID REFERENCES bots(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    vk_message_id BIGINT,
    direction VARCHAR(10) CHECK (direction IN ('incoming', 'outgoing')),
    content TEXT,
    attachments JSONB DEFAULT '[]',
    is_processed BOOLEAN DEFAULT false,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_messages_bot_id ON messages(bot_id);
CREATE INDEX idx_messages_user_id ON messages(user_id);
CREATE INDEX idx_messages_vk_id ON messages(vk_message_id);
CREATE INDEX idx_messages_created_at ON messages(created_at);

-- ============= ANALYTICS =============
CREATE TABLE IF NOT EXISTS analytics (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    bot_id UUID REFERENCES bots(id) ON DELETE CASCADE,
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    metric_type VARCHAR(50) NOT NULL,
    metric_value DECIMAL(15, 2) DEFAULT 0,
    metric_data JSONB DEFAULT '{}',
    recorded_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_analytics_bot_id ON analytics(bot_id);
CREATE INDEX idx_analytics_user_id ON analytics(user_id);
CREATE INDEX idx_analytics_type ON analytics(metric_type);
CREATE INDEX idx_analytics_recorded_at ON analytics(recorded_at);

-- ============= API KEYS =============
CREATE TABLE IF NOT EXISTS api_keys (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    key_hash TEXT NOT NULL,
    key_prefix VARCHAR(20) NOT NULL,
    permissions JSONB DEFAULT '{}',
    expires_at TIMESTAMP WITH TIME ZONE,
    last_used_at TIMESTAMP WITH TIME ZONE,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_api_keys_user_id ON api_keys(user_id);
CREATE INDEX idx_api_keys_prefix ON api_keys(key_prefix);

-- ============= LOGS =============
CREATE TABLE IF NOT EXISTS system_logs (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    user_id UUID REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(100) NOT NULL,
    resource VARCHAR(255),
    resource_id UUID,
    ip_address INET,
    user_agent TEXT,
    status_code INTEGER,
    error_message TEXT,
    metadata JSONB DEFAULT '{}',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_logs_user_id ON system_logs(user_id);
CREATE INDEX idx_logs_action ON system_logs(action);
CREATE INDEX idx_logs_created_at ON system_logs(created_at);

-- ============= SETTINGS =============
CREATE TABLE IF NOT EXISTS settings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    key VARCHAR(255) UNIQUE NOT NULL,
    value JSONB NOT NULL,
    description TEXT,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_settings_key ON settings(key);

-- ============= PAYMENT METHODS =============
CREATE TABLE IF NOT EXISTS payment_methods (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    description TEXT,
    is_enabled BOOLEAN DEFAULT false,
    is_admin_only BOOLEAN DEFAULT false,
    config JSONB DEFAULT '{}',
    icon VARCHAR(255),
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_payment_methods_name ON payment_methods(name);
CREATE INDEX idx_payment_methods_enabled ON payment_methods(is_enabled);

-- ============= YOOMONEY P2P =============
CREATE TABLE IF NOT EXISTS yoomoney_p2p (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    account_number VARCHAR(50) UNIQUE NOT NULL,
    verified_user_vk_id BIGINT,
    verified_user_name VARCHAR(255),
    is_verified BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    api_key_hash TEXT,
    webhook_secret TEXT,
    last_payment_check TIMESTAMP WITH TIME ZONE,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_yoomoney_p2p_account ON yoomoney_p2p(account_number);
CREATE INDEX idx_yoomoney_p2p_verified ON yoomoney_p2p(is_verified);

-- ============= TRIGGERS =============
-- Auto-update updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_bots_updated_at BEFORE UPDATE ON bots
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_target_audiences_updated_at BEFORE UPDATE ON target_audiences
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_payments_updated_at BEFORE UPDATE ON payments
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_subscriptions_updated_at BEFORE UPDATE ON subscriptions
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_settings_updated_at BEFORE UPDATE ON settings
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_payment_methods_updated_at BEFORE UPDATE ON payment_methods
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_yoomoney_p2p_updated_at BEFORE UPDATE ON yoomoney_p2p
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- ============= SEED DATA =============
-- Default settings
INSERT INTO settings (key, value, description) VALUES
    ('system.maintenance_mode', 'false', 'Режим обслуживания'),
    ('system.registration_enabled', 'true', 'Регистрация новых пользователей'),
    ('payments.min_deposit', '100', 'Минимальная сумма пополнения'),
    ('payments.max_deposit', '100000', 'Максимальная сумма пополнения'),
    ('bots.max_per_user', '5', 'Максимум ботов на пользователя'),
    ('tariffs.plans', '[{"id":"free","name":"Free","price":0,"limits":{"bots":1,"messages":100}},{"id":"pro","name":"Pro","price":990,"limits":{"bots":5,"messages":10000}},{"id":"business","name":"Business","price":2990,"limits":{"bots":20,"messages":100000}}]', 'Тарифные планы');

-- Payment methods (admin может включать/выключать)
INSERT INTO payment_methods (name, display_name, description, is_enabled, config, icon, sort_order) VALUES
    ('yookassa', 'ЮKassa', 'Банковские карты, СБП, ЮMoney (юридические лица)', true, '{"min_amount":100,"max_amount":100000,"commission":0.028}', 'yookassa', 1),
    ('yoomoney_p2p', 'ЮMoney P2P', 'Перевод на счёт физлица (проверенный пользователь)', false, '{"min_amount":100,"max_amount":50000,"commission":0}', 'yoomoney', 2),
    ('cards', 'Банковские карты', 'Visa, Mastercard, МИР', true, '{"min_amount":100,"max_amount":100000,"commission":0.025}', 'cards', 3);

-- ============= COMMENTS =============
COMMENT ON TABLE users IS 'Пользователи системы (VK, OK OAuth)';
COMMENT ON TABLE bots IS 'Конфигурации VK ботов';
COMMENT ON TABLE payments IS 'Платежи и транзакции';
COMMENT ON TABLE messages IS 'История сообщений ботов';
COMMENT ON TABLE analytics IS 'Метрики и статистика';
COMMENT ON TABLE api_keys IS 'API ключи для интеграций';
COMMENT ON TABLE system_logs IS 'Системные логи действий';
COMMENT ON TABLE payment_methods IS 'Методы оплаты (вкл/выкл админом)';
COMMENT ON TABLE yoomoney_p2p IS 'Настройки ЮMoney P2P (проверенные пользователи)';
