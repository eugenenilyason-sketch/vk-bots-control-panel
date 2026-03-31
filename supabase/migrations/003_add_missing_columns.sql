-- Add missing columns for Prisma schema compatibility

-- Add webhook_url to bots table
ALTER TABLE bots ADD COLUMN IF NOT EXISTS webhook_url VARCHAR(255);

-- Add type to payments table  
ALTER TABLE payments ADD COLUMN IF NOT EXISTS type VARCHAR(50) DEFAULT 'deposit';

-- Add config to bots table
ALTER TABLE bots ADD COLUMN IF NOT EXISTS config JSONB DEFAULT '{}';

-- Add last_activity to bots table
ALTER TABLE bots ADD COLUMN IF NOT EXISTS last_activity TIMESTAMP WITH TIME ZONE;

-- Add messages_sent to bots table
ALTER TABLE bots ADD COLUMN IF NOT EXISTS messages_sent INTEGER DEFAULT 0;

-- Add messages_received to bots table
ALTER TABLE bots ADD COLUMN IF NOT EXISTS messages_received INTEGER DEFAULT 0;
