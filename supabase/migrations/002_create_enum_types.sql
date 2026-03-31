-- Create enum types for the application
DO $$ BEGIN
    CREATE TYPE "UserRole" AS ENUM ('superadmin', 'admin', 'user');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE "BotStatus" AS ENUM ('active', 'inactive', 'blocked', 'pending');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE "PaymentStatus" AS ENUM ('pending', 'succeeded', 'failed', 'refunded');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;

DO $$ BEGIN
    CREATE TYPE "PaymentType" AS ENUM ('deposit', 'subscription', 'one_time');
EXCEPTION
    WHEN duplicate_object THEN null;
END $$;
