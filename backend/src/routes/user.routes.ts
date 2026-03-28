import { Router } from 'express';
import { authMiddleware } from '../middleware/authMiddleware';
import { AuthRequest } from '../middleware/authMiddleware';
import prisma from '../lib/prisma';

const router = Router();

// GET /api/user/profile - Get user profile
router.get('/profile', authMiddleware, async (req: AuthRequest, res, next) => {
  try {
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

// PUT /api/user/profile - Update user profile
router.put('/profile', authMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const { username, email } = req.body;

    const user = await prisma.user.update({
      where: { id: req.user!.id },
      data: {
        ...(username && { username }),
        ...(email && { email }),
      },
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
      },
    });

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
