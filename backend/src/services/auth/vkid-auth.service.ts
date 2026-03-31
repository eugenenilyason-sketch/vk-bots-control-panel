import axios from 'axios';
import { config } from '../../config/index';
import prisma from '../../lib/prisma';
import { generateAccessToken, generateRefreshToken } from '../../lib/jwt';
import { AppError } from '../../middleware/errorHandler';

export interface VKIDTokenResponse {
  access_token: string;
  expires_in: number;
  user_id: number;
  email?: string;
}

export interface VKIDUserInfo {
  id: number;
  name?: string;
  first_name?: string;
  last_name?: string;
  avatar?: string;
  email?: string;
}

export const vkidAuthService = {
  async exchangeCode(code: string, device_id: string): Promise<VKIDTokenResponse> {
    const response = await axios.post<VKIDTokenResponse>(
      'https://id.vk.com/oauth2/auth',
      null,
      {
        params: {
          client_id: config.VK_CLIENT_ID,
          client_secret: config.VK_CLIENT_SECRET,
          redirect_uri: config.VK_REDIRECT_URI,
          code,
          device_id,
          grant_type: 'authorization_code',
        },
      }
    );
    return response.data;
  },

  async getUserInfo(accessToken: string): Promise<VKIDUserInfo> {
    const response = await axios.get('https://id.vk.com/oauth2/user_info', {
      headers: {
        Authorization: `Bearer ${accessToken}`,
      },
      params: {
        fields: 'id,name,first_name,last_name,avatar,email',
      },
    });
    return response.data;
  },

  async loginWithVKID(code: string, device_id: string) {
    // Обмен кода на токен
    const tokenResponse = await this.exchangeCode(code, device_id);
    
    // Получение информации о пользователе
    const userInfo = await this.getUserInfo(tokenResponse.access_token);
    
    // VK ID может вернуть id как number или string
    const vkId = userInfo.id || tokenResponse.user_id;
    
    if (!vkId) {
      throw new AppError('Failed to get user ID from VK', 400);
    }
    
    // Поиск или создание пользователя
    let user = await prisma.user.findUnique({
      where: { vkId: BigInt(vkId) },
    });

    if (!user) {
      // Создание нового пользователя
      user = await prisma.user.create({
        data: {
          vkId: BigInt(vkId),
          username: userInfo.name || `${userInfo.first_name || ''} ${userInfo.last_name || ''}`.trim() || `user_${vkId}`,
          firstName: userInfo.first_name,
          lastName: userInfo.last_name,
          avatarUrl: userInfo.avatar,
          email: userInfo.email || tokenResponse.email,
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

    // Генерация токенов
    const tokens = this.generateTokens(user.id, user.role);

    return {
      user: {
        id: user.id,
        vkId: user.vkId!.toString(),
        username: user.username!,
        email: user.email,
        role: user.role,
        avatarUrl: user.avatarUrl,
      },
      tokens,
    };
  },

  generateTokens(userId: string, role: string) {
    const accessToken = generateAccessToken({ id: userId, role });
    const refreshToken = generateRefreshToken({ id: userId, role });
    
    return {
      accessToken,
      refreshToken,
      expiresIn: 3600,
    };
  },
};
