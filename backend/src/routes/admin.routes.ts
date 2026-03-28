import { Router } from 'express';
import { authMiddleware, adminMiddleware, superAdminMiddleware } from '../middleware/authMiddleware';
import { AuthRequest } from '../middleware/authMiddleware';
import { validateRequest } from '../middleware/validateRequest';
import { z } from 'zod';
import prisma from '../lib/prisma';

const router = Router();

// GET /api/admin/users - Get all users
router.get('/users', authMiddleware, adminMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const { page = '1', limit = '20', role, search } = req.query;
    
    const users = await prisma.user.findMany({
      where: {
        ...(role && { role: role as any }),
        ...(search && {
          OR: [
            { username: { contains: search as string } },
            { email: { contains: search as string } },
          ],
        }),
      },
      orderBy: { createdAt: 'desc' },
      skip: (parseInt(page as string) - 1) * parseInt(limit as string),
      take: parseInt(limit as string),
    });

    const total = await prisma.user.count({
      where: {
        ...(role && { role: role as any }),
        ...(search && {
          OR: [
            { username: { contains: search as string } },
            { email: { contains: search as string } },
          ],
        }),
      },
    });

    res.json({
      success: true,
      data: users.map(u => ({ ...u, vkId: u.vkId?.toString() })),
      pagination: { page: parseInt(page as string), limit: parseInt(limit as string), total },
    });
  } catch (error) {
    next(error);
  }
});

// PUT /api/admin/users/:id - Update user
router.put('/users/:id', authMiddleware, adminMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const { id } = req.params;
    const { role, balance, isBlocked } = req.body;

    const user = await prisma.user.update({
      where: { id },
      data: {
        ...(role && { role }),
        ...(typeof balance === 'number' && { balance }),
        ...(typeof isBlocked === 'boolean' && { isBlocked }),
      },
    });

    res.json({
      success: true,
      data: { ...user, vkId: user.vkId?.toString() },
    });
  } catch (error) {
    next(error);
  }
});

// GET /api/admin/payments - Get all payments
router.get('/payments', authMiddleware, adminMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const { page = '1', limit = '20', status } = req.query;
    
    const payments = await prisma.payment.findMany({
      where: {
        ...(status && { status: status as any }),
      },
      include: {
        user: {
          select: { id: true, username: true, email: true },
        },
      },
      orderBy: { createdAt: 'desc' },
      skip: (parseInt(page as string) - 1) * parseInt(limit as string),
      take: parseInt(limit as string),
    });

    const total = await prisma.payment.count({
      where: { ...(status && { status: status as any }) },
    });

    res.json({
      success: true,
      data: payments,
      pagination: { page: parseInt(page as string), limit: parseInt(limit as string), total },
    });
  } catch (error) {
    next(error);
  }
});

// GET /api/admin/payment-methods - Get payment methods
router.get('/payment-methods', authMiddleware, adminMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const methods = await prisma.paymentMethod.findMany({
      orderBy: { sortOrder: 'asc' },
    });

    res.json({
      success: true,
      data: methods,
    });
  } catch (error) {
    next(error);
  }
});

// PUT /api/admin/payment-methods/:id - Update payment method
router.put('/payment-methods/:id', authMiddleware, adminMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const { id } = req.params;
    const { isEnabled, config } = req.body;

    const method = await prisma.paymentMethod.update({
      where: { id },
      data: {
        ...(typeof isEnabled === 'boolean' && { isEnabled }),
        ...(config && { config }),
      },
    });

    res.json({
      success: true,
      data: method,
    });
  } catch (error) {
    next(error);
  }
});

// GET /api/admin/yoomoney-p2p - Get YooMoney P2P accounts
router.get('/yoomoney-p2p', authMiddleware, adminMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const accounts = await prisma.yoomoneyP2p.findMany({
      orderBy: { createdAt: 'desc' },
    });

    res.json({
      success: true,
      data: accounts.map(a => ({
        ...a,
        verifiedUserVkId: a.verifiedUserVkId?.toString(),
      })),
    });
  } catch (error) {
    next(error);
  }
});

// POST /api/admin/yoomoney-p2p - Add YooMoney P2P account
router.post('/yoomoney-p2p', authMiddleware, adminMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const { accountNumber, verifiedUserVkId, verifiedUserName, isVerified, isActive } = req.body;

    const account = await prisma.yoomoneyP2p.create({
      data: {
        accountNumber,
        verifiedUserVkId: verifiedUserVkId ? BigInt(verifiedUserVkId) : null,
        verifiedUserName,
        isVerified: isVerified || false,
        isActive: isActive !== undefined ? isActive : true,
      },
    });

    res.status(201).json({
      success: true,
      data: { ...account, verifiedUserVkId: account.verifiedUserVkId?.toString() },
    });
  } catch (error) {
    next(error);
  }
});

// GET /api/admin/analytics - Get system analytics
router.get('/analytics', authMiddleware, adminMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const totalUsers = await prisma.user.count();
    const activeUsers = await prisma.user.count({ where: { isActive: true } });
    const totalBots = await prisma.bot.count();
    const activeBots = await prisma.bot.count({ where: { status: 'active' } });
    
    const payments = await prisma.payment.aggregate({
      where: { status: 'succeeded' },
      _sum: { amount: true },
      _count: true,
    });

    res.json({
      success: true,
      data: {
        totalUsers,
        activeUsers,
        totalBots,
        activeBots,
        totalRevenue: payments._sum.amount || 0,
        totalPayments: payments._count,
      },
    });
  } catch (error) {
    next(error);
  }
});

export default router;
