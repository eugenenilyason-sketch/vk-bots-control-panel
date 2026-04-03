import { Router } from 'express';
import { generateAccessToken, generateRefreshToken } from '../lib/jwt';
import { authService } from '../services/auth.service';
import { emailAuthService } from '../services/auth/email-auth.service';
import { vkidAuthService } from '../services/auth/vkid-auth.service';
import { authMiddleware } from '../middleware/authMiddleware';
import { validateRequest } from '../middleware/validateRequest';
import { AppError } from '../middleware/errorHandler';
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
    access_token: z.string().optional(),
    user_id: z.string().optional(),
    code: z.string().optional(),
    device_id: z.string().optional(),
    code_verifier: z.string().optional(),
  }).refine(data => {
    // Требуется либо access_token+user_id, либо code+device_id
    return (data.access_token && data.user_id) || (data.code && data.device_id);
  }, {
    message: 'Either access_token+user_id or code+device_id must be provided',
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
    const { code, device_id, code_verifier, access_token, user_id } = req.body;

    let userInfo;

    // Если есть access_token - используем его (frontend уже обменял код)
    if (access_token && user_id) {
      console.log('🔑 Using access_token from frontend');
      userInfo = {
        id: String(user_id),
        email: '',
        name: '',
        avatar: '',
      };
    }
    // Если есть code - обмениваем на токены
    else if (code && device_id) {
      console.log('🔄 Exchanging code for tokens');
      const tokenData = await vkidAuthService.exchangeCode(code, device_id, code_verifier);
      userInfo = await vkidAuthService.getUserInfo(tokenData.access_token);
    }
    else {
      throw new AppError('No access_token or code provided', 400);
    }

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

    console.log('✅ VK ID login successful:', { userId: user.id, vk_id: user.vk_id });

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
    console.error('❌ VK ID Login Error:', error);
    if (error instanceof AppError) {
      res.status(error.statusCode).json({
        success: false,
        message: error.message,
      });
    } else {
      next(error);
    }
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
