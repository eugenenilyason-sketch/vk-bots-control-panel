import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:4000';

export const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

// Interceptor для добавления токена
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth-storage');
  if (token) {
    try {
      const { accessToken } = JSON.parse(token).state;
      if (accessToken) {
        config.headers.Authorization = `Bearer ${accessToken}`;
      }
    } catch (e) {
      // Ignore
    }
  }
  return config;
});

// Interceptor для обновления токена
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Try to refresh token
      const token = localStorage.getItem('auth-storage');
      if (token) {
        try {
          const { refreshToken } = JSON.parse(token).state;
          if (refreshToken) {
            const response = await axios.post(`${API_URL}/api/auth/refresh`, {
              refresh_token: refreshToken,
            });
            const { accessToken, refreshToken: newRefreshToken } = response.data.data;
            
            // Update stored tokens
            const stored = JSON.parse(token);
            stored.state.accessToken = accessToken;
            stored.state.refreshToken = newRefreshToken;
            localStorage.setItem('auth-storage', JSON.stringify(stored));
            
            // Retry original request
            error.config.headers.Authorization = `Bearer ${accessToken}`;
            return api(error.config);
          }
        } catch (e) {
          // Clear auth
          localStorage.removeItem('auth-storage');
          window.location.href = '/login';
        }
      }
    }
    return Promise.reject(error);
  }
);

// Auth API
export const authAPI = {
  loginVK: (code: string) => api.post('/api/auth/vk', { code }),
  refresh: (refreshToken: string) => api.post('/api/auth/refresh', { refresh_token: refreshToken }),
  logout: () => api.post('/api/auth/logout'),
  me: () => api.get('/api/auth/me'),
};

// User API
export const userAPI = {
  getProfile: () => api.get('/api/user/profile'),
  updateProfile: (data: { username?: string; email?: string }) =>
    api.put('/api/user/profile', data),
};

// Bots API
export const botsAPI = {
  getAll: () => api.get('/api/bots'),
  getById: (id: string) => api.get(`/api/bots/${id}`),
  create: (data: { name: string; vkGroupId?: string; vkToken?: string }) =>
    api.post('/api/bots', data),
  update: (id: string, data: { name?: string; config?: object }) =>
    api.put(`/api/bots/${id}`, data),
  delete: (id: string) => api.delete(`/api/bots/${id}`),
  start: (id: string) => api.post(`/api/bots/${id}/start`),
  stop: (id: string) => api.post(`/api/bots/${id}/stop`),
};

// Payments API
export const paymentsAPI = {
  getMethods: () => api.get('/api/payments/methods'),
  getAll: (params?: { page?: number; limit?: number; status?: string }) =>
    api.get('/api/payments', { params }),
  create: (data: { amount: number; method: string }) =>
    api.post('/api/payments/create', data),
};

// Admin API
export const adminAPI = {
  getUsers: (params?: { page?: number; limit?: number; role?: string; search?: string }) =>
    api.get('/api/admin/users', { params }),
  updateUser: (id: string, data: { role?: string; balance?: number; isBlocked?: boolean }) =>
    api.put(`/api/admin/users/${id}`, data),
  getPayments: (params?: { page?: number; limit?: number; status?: string }) =>
    api.get('/api/admin/payments', { params }),
  getPaymentMethods: () => api.get('/api/admin/payment-methods'),
  updatePaymentMethod: (id: string, data: { isEnabled?: boolean; config?: object }) =>
    api.put(`/api/admin/payment-methods/${id}`, data),
  getYoomoneyP2p: () => api.get('/api/admin/yoomoney-p2p'),
  addYoomoneyP2p: (data: { accountNumber: string; verifiedUserVkId?: string; verifiedUserName?: string }) =>
    api.post('/api/admin/yoomoney-p2p', data),
  getAnalytics: () => api.get('/api/admin/analytics'),
};
