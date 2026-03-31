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

export interface VKIDAuthRequest {
  code: string;
  device_id: string;
  code_verifier?: string;
}

export const vkidAuthService = {
  async exchangeCode(code: string, device_id: string, code_verifier?: string): Promise<VKIDTokenResponse> {
    // Для бизнес-аккаунтов используем Service Token
    const headers: any = {
      'Content-Type': 'application/x-www-form-urlencoded',
    };

    // Если есть Service Token (для бизнес-аккаунтов)
    if (config.VK_SERVICE_TOKEN) {
      headers['Authorization'] = `Bearer ${config.VK_SERVICE_TOKEN}`;
    }

    const params: any = {
      client_id: config.VK_CLIENT_ID,
      redirect_uri: config.VK_REDIRECT_URI,
      code,
      device_id,
      grant_type: 'authorization_code',
    };

    // Добавляем code_verifier если есть (PKCE)
    if (code_verifier) {
      params.code_verifier = code_verifier;
    }

    // Если нет Service Token, используем client_secret (для обычных аккаунтов)
    if (!config.VK_SERVICE_TOKEN && config.VK_CLIENT_SECRET) {
      params.client_secret = config.VK_CLIENT_SECRET;
    }

    const response = await axios.post<VKIDTokenResponse>(
      'https://id.vk.com/oauth2/auth',
      new URLSearchParams(params),
      { headers }
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

  async findOrCreateUser(vkUserInfo: VKIDUserInfo) {
    let user = await prisma.user.findUnique({
      where: { vk_id: vkUserInfo.id },
    });

    if (!user) {
      user = await prisma.user.create({
        data: {
          vk_id: vkUserInfo.id,
          email: vkUserInfo.email || `${vkUserInfo.id}@vkid.local`,
          username: `${vkUserInfo.first_name || ''} ${vkUserInfo.last_name || ''}`.trim() || `vk_user_${vkUserInfo.id}`,
          first_name: vkUserInfo.first_name,
          last_name: vkUserInfo.last_name,
          avatar_url: vkUserInfo.avatar,
          role: 'user',
          is_active: true,
          is_blocked: false,
        },
      });
    }

    return user;
  },
};
