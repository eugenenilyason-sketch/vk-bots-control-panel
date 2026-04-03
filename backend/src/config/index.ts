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
  VK_SERVICE_TOKEN: z.string().optional(),
  VK_REDIRECT_URI: z.string(),
  YOOKASSA_SHOP_ID: z.string().optional(),
  YOOKASSA_SECRET_KEY: z.string().optional(),
  YOOMONEY_ACCOUNT_NUMBER: z.string().optional(),
  YOOMONEY_API_KEY: z.string().optional(),
  REDIS_PASSWORD: z.string(),
  FRONTEND_URL: z.string(),
});

export type Config = z.infer<typeof configSchema>;

try {
  configSchema.parse(process.env);
} catch (error) {
  console.error('Invalid environment variables:', error);
  process.exit(1);
}

export const config: Config = {
  NODE_ENV: process.env.NODE_ENV!,
  PORT: process.env.PORT!,
  DATABASE_URL: process.env.DATABASE_URL!,
  JWT_SECRET: process.env.JWT_SECRET!,
  VK_CLIENT_ID: process.env.VK_CLIENT_ID!,
  VK_CLIENT_SECRET: process.env.VK_CLIENT_SECRET!,
  VK_SERVICE_TOKEN: process.env.VK_SERVICE_TOKEN,
  VK_REDIRECT_URI: process.env.VK_REDIRECT_URI!,
  YOOKASSA_SHOP_ID: process.env.YOOKASSA_SHOP_ID,
  YOOKASSA_SECRET_KEY: process.env.YOOKASSA_SECRET_KEY,
  YOOMONEY_ACCOUNT_NUMBER: process.env.YOOMONEY_ACCOUNT_NUMBER,
  YOOMONEY_API_KEY: process.env.YOOMONEY_API_KEY,
  REDIS_PASSWORD: process.env.REDIS_PASSWORD!,
  FRONTEND_URL: process.env.FRONTEND_URL!,
};
