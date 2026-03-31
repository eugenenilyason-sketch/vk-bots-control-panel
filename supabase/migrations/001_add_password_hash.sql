-- Add password_hash column for email authentication
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255);
