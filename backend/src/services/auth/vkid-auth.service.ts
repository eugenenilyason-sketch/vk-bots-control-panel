import axios from 'axios';
import { config } from '../../config/index';
import prisma from '../../lib/prisma';
import { generateAccessToken, generateRefreshToken } from '../../lib/jwt';
import { AppError } from '../../middleware/errorHandler';
import { z } from 'zod';

export interface VKIDUserInfo {
  id?: string | number;
  user_id?: string | number;
  email?: string;
  name?: string;
  first_name?: string;
  last_name?: string;
  avatar?: string;
}

export const vkidAuthService = {
  /**
   * Обменять code на токены (OAuth 2.0)
   */
  async exchangeCode(code: string, deviceId: string, codeVerifier?: string) {
    try {
      const response = await axios.post('https://id.vk.com/oauth2/token', {
        client_id: config.VK_CLIENT_ID,
        client_secret: config.VK_CLIENT_SECRET,
        grant_type: 'authorization_code',
        code: code,
        device_id: deviceId,
        code_verifier: codeVerifier,
        redirect_uri: config.VK_REDIRECT_URI,
      });

      console.log('📊 VK ID Token Response:', response.data);

      return {
        access_token: response.data.access_token,
        expires_in: response.data.expires_in,
        token_type: response.data.token_type,
        id_token: response.data.id_token,
        user_id: response.data.user_id,
        email: response.data.email,
      };
    } catch (error: any) {
      console.error('❌ VK ID Exchange Code Error:', error.response?.data || error.message);
      throw new AppError(
        'Failed to exchange code for tokens',
        400,
        error.response?.data || error.message
      );
    }
  },

  /**
   * Получить информацию о пользователе из VK ID
   */
  async getUserInfo(accessToken: string): Promise<VKIDUserInfo> {
    try {
      const response = await axios.get('https://id.vk.com/oauth2/user_info', {
        headers: {
          Authorization: `Bearer ${accessToken}`,
        },
        params: {
          client_id: config.VK_CLIENT_ID,
          fields: 'id,name,first_name,last_name,avatar,email',
        },
      });

      const data = response.data;
      console.log('📊 VK ID User Info Response:', data);

      // Поддержка разных форматов ответа
      const userData = data.user || data;

      return {
        id: String(userData.user_id || userData.id || data.user_id || data.id),
        email: userData.email || data.email,
        name: userData.name || data.name || `${userData.first_name || ''} ${userData.last_name || ''}`.trim(),
        first_name: userData.first_name || data.first_name,
        last_name: userData.last_name || data.last_name,
        avatar: userData.avatar || data.avatar,
      };
    } catch (error: any) {
      console.error('❌ VK ID UserInfo Error:', error.response?.data || error.message);
      throw new AppError(
        'Failed to get user info from VK ID',
        400,
        error.response?.data || error.message
      );
    }
  },

  /**
   * Найти или создать пользователя
   */
  async findOrCreateUser(vkUserInfo: VKIDUserInfo) {
    const userId = String(vkUserInfo.id);

    if (!userId) {
      throw new AppError('User ID not found in VK ID response', 500);
    }

    const email = vkUserInfo.email || `${userId}@vkid.local`;
    const username = vkUserInfo.name || `VK User ${userId}`;
    const avatar = vkUserInfo.avatar;

    let user = await prisma.user.findUnique({
      where: { vkId: userId },
    });

    if (user) {
      // Обновляем существующего пользователя
      user = await prisma.user.update({
        where: { vkId: userId },
        data: {
          avatarUrl: avatar,
          username: user.username || username,
        },
      });
      return user;
    }

    // Создаём нового пользователя
    return await prisma.user.create({
      data: {
        vkId: userId,
        email,
        username,
        role: 'user',
        isActive: true,
        isBlocked: false,
      },
    });
  },
};
