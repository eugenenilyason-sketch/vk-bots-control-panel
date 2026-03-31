import bcrypt from 'bcryptjs';
import prisma from '../../lib/prisma';
import { generateAccessToken, generateRefreshToken } from '../../lib/jwt';
import { AppError } from '../../middleware/errorHandler';

export interface RegisterInput {
  email: string;
  password: string;
  username?: string;
}

export interface LoginInput {
  email: string;
  password: string;
}

export interface AuthTokens {
  accessToken: string;
  refreshToken: string;
  expiresIn: number;
}

export interface AuthResult {
  user: {
    id: string;
    email: string | null;
    username: string | null;
    role: string;
    avatarUrl: string | null;
  };
  tokens: AuthTokens;
}

export const emailAuthService = {
  async register(input: RegisterInput): Promise<AuthResult> {
    const { email, password, username } = input;

    // Проверка email
    if (!email || !email.includes('@')) {
      throw new AppError('Invalid email', 400);
    }

    // Проверка пароля
    if (!password || password.length < 6) {
      throw new AppError('Password must be at least 6 characters', 400);
    }

    // Проверка существующего пользователя
    const existingUser = await prisma.user.findUnique({
      where: { email },
    });

    if (existingUser) {
      throw new AppError('Email already registered', 400);
    }

    // Хеширование пароля
    const passwordHash = await bcrypt.hash(password, 10);

    // Создание пользователя
    const user = await prisma.user.create({
      data: {
        email,
        username: username || email.split('@')[0],
        passwordHash,
        role: 'user',
      },
    });

    // Генерация токенов
    const tokens = this.generateTokens(user.id, user.role);

    return {
      user: {
        id: user.id,
        email: user.email,
        username: user.username,
        role: user.role,
        avatarUrl: user.avatarUrl,
      },
      tokens,
    };
  },

  async login(input: LoginInput): Promise<AuthResult> {
    const { email, password } = input;

    // Поиск пользователя
    const user = await prisma.user.findUnique({
      where: { email },
    });

    if (!user) {
      throw new AppError('Invalid credentials', 401);
    }

    if (!user.passwordHash) {
      throw new AppError('Please login via VK', 400);
    }

    if (!user.isActive || user.isBlocked) {
      throw new AppError('Account is blocked', 403);
    }

    // Проверка пароля
    const isValid = await bcrypt.compare(password, user.passwordHash);

    if (!isValid) {
      throw new AppError('Invalid credentials', 401);
    }

    // Генерация токенов
    const tokens = this.generateTokens(user.id, user.role);

    return {
      user: {
        id: user.id,
        email: user.email,
        username: user.username,
        role: user.role,
        avatarUrl: user.avatarUrl,
      },
      tokens,
    };
  },

  generateTokens(userId: string, role: string): AuthTokens {
    const accessToken = generateAccessToken({ id: userId, role });
    const refreshToken = generateRefreshToken({ id: userId, role });
    
    return {
      accessToken,
      refreshToken,
      expiresIn: 3600,
    };
  },
};
