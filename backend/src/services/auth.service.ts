import prisma from '../lib/prisma';
import jwt from 'jsonwebtoken';
import { config } from '../config/index';
import { generateAccessToken, generateRefreshToken } from '../lib/jwt';
import { vkAuthService, VKUser } from './vk/auth.service';
import { AppError } from '../middleware/errorHandler';

export interface AuthTokens {
  accessToken: string;
  refreshToken: string;
  expiresIn: number;
}

export interface AuthResult {
  user: {
    id: string;
    vkId: bigint;
    username: string;
    email: string | null;
    role: string;
    avatarUrl: string | null;
  };
  tokens: AuthTokens;
}

export const authService = {
  async loginWithVK(code: string): Promise<AuthResult> {
    // Получаем токен от VK
    const tokenResponse = await vkAuthService.getAccessToken(code);
    
    // Получаем информацию о пользователе
    const vkUser = await vkAuthService.getUserInfo(tokenResponse.access_token);

    // Ищем или создаем пользователя в БД
    let user = await prisma.user.findUnique({
      where: { vkId: BigInt(vkUser.id) },
    });

    if (!user) {
      // Создаем нового пользователя
      user = await prisma.user.create({
        data: {
          vkId: BigInt(vkUser.id),
          username: `${vkUser.first_name} ${vkUser.last_name}`,
          firstName: vkUser.first_name,
          lastName: vkUser.last_name,
          avatarUrl: vkUser.photo_200 || null,
          email: vkUser.email || null,
          role: 'user',
        },
      });
    }

    if (user.isBlocked) {
      throw new AppError('User is blocked', 403);
    }

    if (!user.isActive) {
      throw new AppError('User is not active', 403);
    }

    // Генерируем токены
    const tokens = this.generateTokens(user.id, BigInt(vkUser.id), user.role);

    // Сохраняем сессию
    await prisma.userSession.create({
      data: {
        userId: user.id,
        refreshToken: tokens.refreshToken,
        accessToken: tokens.accessToken,
        expiresAt: new Date(Date.now() + tokens.expiresIn * 1000),
      },
    });

    return {
      user: {
        id: user.id,
        vkId: user.vkId!,
        username: user.username!,
        email: user.email,
        role: user.role,
        avatarUrl: user.avatarUrl,
      },
      tokens,
    };
  },

  async refreshToken(refreshToken: string): Promise<AuthTokens> {
    // Проверяем токен
    let payload;
    try {
      payload = jwt.verify(refreshToken, config.JWT_SECRET);
    } catch {
      throw new AppError('Invalid refresh token', 401);
    }

    // Ищем сессию
    const session = await prisma.userSession.findUnique({
      where: { refreshToken },
      include: { user: true },
    });

    if (!session || session.expiresAt < new Date()) {
      throw new AppError('Session expired', 401);
    }

    if (!session.user.isActive || session.user.isBlocked) {
      throw new AppError('User is blocked or not active', 403);
    }

    // Генерируем новые токены
    const tokens = this.generateTokens(session.user.id, session.user.vkId!, session.user.role);

    // Обновляем сессию
    await prisma.userSession.update({
      where: { id: session.id },
      data: {
        refreshToken: tokens.refreshToken,
        accessToken: tokens.accessToken,
        expiresAt: new Date(Date.now() + tokens.expiresIn * 1000),
        lastActive: new Date(),
      },
    });

    return tokens;
  },

  async logout(accessToken: string): Promise<void> {
    await prisma.userSession.deleteMany({
      where: { accessToken },
    });
  },

  generateTokens(userId: string, vkId: bigint, role: string): AuthTokens {
    const accessToken = generateAccessToken({ id: userId, vkId: String(vkId), role });
    const refreshToken = generateRefreshToken({ id: userId, vkId: String(vkId), role });
    
    return {
      accessToken,
      refreshToken,
      expiresIn: 3600,
    };
  },
};
