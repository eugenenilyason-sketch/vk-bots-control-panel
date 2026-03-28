import dotenv from 'dotenv';
import { z } from 'zod';

dotenv.config();

const configSchema = z.object({
  NODE_ENV: z.string().default('development'),
  PORT: z.string().default('4000'),
  DATABASE_URL: z.string(),
  JWT_SECRET: z.string(),
  VK_CLIENT_ID: z.string(),
  VK_CLIENT_SECRET: z.string(),
  VK_REDIRECT_URI: z.string(),
  YOOKASSA_SHOP_ID: z.string().optional(),
  YOOKASSA_SECRET_KEY: z.string().optional(),
  YOOMONEY_ACCOUNT_NUMBER: z.string().optional(),
  YOOMONEY_API_KEY: z.string().optional(),
  N8N_API_URL: z.string().optional(),
  N8N_API_KEY: z.string().optional(),
  NOCODB_API_URL: z.string().optional(),
  NOCODB_API_KEY: z.string().optional(),
  FRONTEND_URL: z.string().default('http://localhost:3000'),
});

export type Config = z.infer<typeof configSchema>;

try {
  configSchema.parse(process.env);
} catch (error) {
  console.error('Invalid environment variables:', error);
  process.exit(1);
}

export const config: Config = {
  NODE_ENV: process.env.NODE_ENV || 'development',
  PORT: process.env.PORT || '4000',
  DATABASE_URL: process.env.DATABASE_URL!,
  JWT_SECRET: process.env.JWT_SECRET!,
  VK_CLIENT_ID: process.env.VK_CLIENT_ID!,
  VK_CLIENT_SECRET: process.env.VK_CLIENT_SECRET!,
  VK_REDIRECT_URI: process.env.VK_REDIRECT_URI!,
  YOOKASSA_SHOP_ID: process.env.YOOKASSA_SHOP_ID,
  YOOKASSA_SECRET_KEY: process.env.YOOKASSA_SECRET_KEY,
  YOOMONEY_ACCOUNT_NUMBER: process.env.YOOMONEY_ACCOUNT_NUMBER,
  YOOMONEY_API_KEY: process.env.YOOMONEY_API_KEY,
  N8N_API_URL: process.env.N8N_API_URL,
  N8N_API_KEY: process.env.N8N_API_KEY,
  NOCODB_API_URL: process.env.NOCODB_API_URL,
  NOCODB_API_KEY: process.env.NOCODB_API_KEY,
  FRONTEND_URL: process.env.FRONTEND_URL || 'http://localhost:3000',
};
