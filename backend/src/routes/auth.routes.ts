import { Router } from 'express';
import { authService } from '../services/auth.service';
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

export default router;
