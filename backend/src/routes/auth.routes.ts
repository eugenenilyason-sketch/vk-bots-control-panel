import { Router } from 'express';
import { authService } from '../services/auth.service';
import { emailAuthService } from '../services/auth/email-auth.service';
import { vkidAuthService } from '../services/auth/vkid-auth.service';
import { authMiddleware } from '../middleware/authMiddleware';
import { validateRequest } from '../middleware/validateRequest';
import { z } from 'zod';
import { AuthRequest } from '../middleware/authMiddleware';

const router = Router();

const loginSchema = z.object({
  body: z.object({
    code: z.string().min(1, 'Code is required'),
  }),
});

const refreshSchema = z.object({
  body: z.object({
    refresh_token: z.string().min(1, 'Refresh token is required'),
  }),
});

const registerSchema = z.object({
  body: z.object({
    email: z.string().email('Invalid email'),
    password: z.string().min(6, 'Password must be at least 6 characters'),
    username: z.string().optional(),
  }),
});

const vkidLoginSchema = z.object({
  body: z.object({
    code: z.string().min(1, 'Code is required'),
    device_id: z.string().min(1, 'Device ID is required'),
    code_verifier: z.string().optional(),
  }),
});

// POST /api/auth/vk - Login via VK
router.post('/vk', validateRequest(loginSchema), async (req, res, next) => {
  try {
    const { code } = req.body;
    const result = await authService.loginWithVK(code);
    res.json(result);
  } catch (error) {
    next(error);
  }
});

// POST /api/auth/vkid - Login via VK ID
router.post('/vkid', validateRequest(vkidLoginSchema), async (req, res, next) => {
  try {
    const { code, device_id, code_verifier } = req.body;
    
    // Обмен кода на токены с PKCE support
    const tokenData = await vkidAuthService.exchangeCode(code, device_id, code_verifier);
    
    // Получаем информацию о пользователе
    const userInfo = await vkidAuthService.getUserInfo(tokenData.access_token);
    
    // Находим или создаём пользователя
    const user = await vkidAuthService.findOrCreateUser(userInfo);
    
    // Генерируем JWT токены
    const accessToken = generateAccessToken({
      userId: user.id,
      email: user.email,
      vkId: user.vk_id,
    });
    
    const refreshToken = generateRefreshToken({
      userId: user.id,
    });
    
    res.json({
      success: true,
      data: {
        user: {
          id: user.id,
          email: user.email,
          username: user.username,
          vk_id: user.vk_id,
          role: user.role,
        },
        access_token: accessToken,
        refresh_token: refreshToken,
      },
    });
  } catch (error) {
    console.error('VK ID Login Error:', error);
    next(error);
  }
});

// POST /api/auth/login - Email/Password login
router.post('/login', validateRequest(loginSchema), async (req, res, next) => {
  try {
    const { code } = req.body;
    const result = await emailAuthService.login(code);
    res.json(result);
  } catch (error) {
    next(error);
  }
});

// POST /api/auth/register - Registration
router.post('/register', validateRequest(registerSchema), async (req, res, next) => {
  try {
    const result = await emailAuthService.register(req.body);
    res.json(result);
  } catch (error) {
    next(error);
  }
});

// POST /api/auth/refresh - Refresh token
router.post('/refresh', validateRequest(refreshSchema), async (req, res, next) => {
  try {
    const { refresh_token } = req.body;
    const result = await authService.refreshToken(refresh_token);
    res.json(result);
  } catch (error) {
    next(error);
  }
});

// POST /api/auth/logout - Logout
router.post('/logout', authMiddleware, async (req: AuthRequest, res, next) => {
  try {
    await authService.logout(req.user!.id);
    res.json({ success: true, message: 'Logged out successfully' });
  } catch (error) {
    next(error);
  }
});

// GET /api/auth/me - Get current user
router.get('/me', authMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const user = await authService.getCurrentUser(req.user!.id);
    res.json({ success: true, data: user });
  } catch (error) {
    next(error);
  }
});

export default router;
