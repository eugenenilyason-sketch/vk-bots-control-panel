import axios from 'axios';
import { config } from '../../config/index';

export interface VKUser {
  id: number;
  first_name: string;
  last_name: string;
  photo_200?: string;
  email?: string;
}

export interface VKTokenResponse {
  access_token: string;
  expires_in: number;
  user_id: number;
  email?: string;
}

export const vkAuthService = {
  async getAccessToken(code: string): Promise<VKTokenResponse> {
    const response = await axios.post<VKTokenResponse>(
      'https://oauth.vk.com/access_token',
      null,
      {
        params: {
          client_id: config.VK_CLIENT_ID,
          client_secret: config.VK_CLIENT_SECRET,
          redirect_uri: config.VK_REDIRECT_URI,
          code,
          grant_type: 'authorization_code',
        },
      }
    );
    return response.data;
  },

  async getUserInfo(accessToken: string): Promise<VKUser> {
    const response = await axios.get('https://api.vk.com/method/users.get', {
      params: {
        access_token: accessToken,
        v: '5.214',
        fields: 'photo_200,email',
      },
    });

    if (response.data.response && response.data.response.length > 0) {
      return response.data.response[0];
    }

    throw new Error('Failed to get VK user info');
  },
};
