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

const emailLoginSchema = z.object({
  body: z.object({
    email: z.string().email('Invalid email'),
    password: z.string().min(1, 'Password is required'),
  }),
});

const vkidLoginSchema = z.object({
  body: z.object({
    code: z.string().min(1, 'Code is required'),
    device_id: z.string().min(1, 'Device ID is required'),
  }),
});

// POST /api/auth/vk - Login via VK
router.post('/vk', validateRequest(loginSchema), async (req, res, next) => {
  try {
    const { code } = req.body;
    const result = await authService.loginWithVK(code);
    
    res.json({
      success: true,
      data: result,
    });
  } catch (error) {
    next(error);
  }
});

// POST /api/auth/register - Register with email
router.post('/register', validateRequest(registerSchema), async (req, res, next) => {
  try {
    const { email, password, username } = req.body;
    const result = await emailAuthService.register({ email, password, username });
    
    res.status(201).json({
      success: true,
      data: result,
    });
  } catch (error) {
    next(error);
  }
});

// POST /api/auth/login - Login with email
router.post('/login', validateRequest(emailLoginSchema), async (req, res, next) => {
  try {
    const { email, password } = req.body;
    const result = await emailAuthService.login({ email, password });
    
    res.json({
      success: true,
      data: result,
    });
  } catch (error) {
    next(error);
  }
});

// POST /api/auth/refresh - Refresh token
router.post('/refresh', validateRequest(refreshSchema), async (req, res, next) => {
  try {
    const { refresh_token } = req.body;
    const tokens = await authService.refreshToken(refresh_token);
    
    res.json({
      success: true,
      data: tokens,
    });
  } catch (error) {
    next(error);
  }
});

// POST /api/auth/logout - Logout
router.post('/logout', authMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const authHeader = req.headers.authorization;
    const token = authHeader?.split(' ')[1];
    
    if (token) {
      await authService.logout(token);
    }
    
    res.json({
      success: true,
      message: 'Logged out successfully',
    });
  } catch (error) {
    next(error);
  }
});

// GET /api/auth/me - Get current user
router.get('/me', authMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const prisma = (await import('../../lib/prisma.js')).default;
    
    const user = await prisma.user.findUnique({
      where: { id: req.user!.id },
      select: {
        id: true,
        vkId: true,
        username: true,
        email: true,
        firstName: true,
        lastName: true,
        avatarUrl: true,
        role: true,
        balance: true,
        isActive: true,
        createdAt: true,
      },
    });

    if (!user) {
      return res.status(404).json({
        success: false,
        error: { code: 'NOT_FOUND', message: 'User not found' },
      });
    }

    res.json({
      success: true,
      data: {
        ...user,
        vkId: user.vkId?.toString(),
      },
    });
  } catch (error) {
    next(error);
  }
});

// POST /api/auth/vkid - Login via VK ID
router.post('/vkid', validateRequest(vkidLoginSchema), async (req, res, next) => {
  try {
    const { code, device_id } = req.body;
    const result = await vkidAuthService.loginWithVKID(code, device_id);
    
    res.json({
      success: true,
      data: result,
    });
  } catch (error) {
    next(error);
  }
});

export default router;
