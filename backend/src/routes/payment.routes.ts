import { Router } from 'express';
import { authMiddleware } from '../middleware/authMiddleware';
import { AuthRequest } from '../middleware/authMiddleware';
import { validateRequest } from '../middleware/validateRequest';
import { z } from 'zod';
import prisma from '../lib/prisma';

const router = Router();

const createPaymentSchema = z.object({
  body: z.object({
    amount: z.number().min(100).max(100000),
    method: z.enum(['yookassa', 'yoomoney_p2p', 'cards']),
  }),
});

// GET /api/payments/methods - Get available payment methods
router.get('/methods', async (req, res, next) => {
  try {
    const methods = await prisma.paymentMethod.findMany({
      where: { isEnabled: true },
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

// GET /api/payments - Get user payments
router.get('/', authMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const { page = '1', limit = '20', status } = req.query;
    
    const payments = await prisma.payment.findMany({
      where: {
        userId: req.user!.id,
        ...(status && { status: status as any }),
      },
      orderBy: { createdAt: 'desc' },
      skip: (parseInt(page as string) - 1) * parseInt(limit as string),
      take: parseInt(limit as string),
    });

    const total = await prisma.payment.count({
      where: {
        userId: req.user!.id,
        ...(status && { status: status as any }),
      },
    });

    res.json({
      success: true,
      data: payments,
      pagination: {
        page: parseInt(page as string),
        limit: parseInt(limit as string),
        total,
      },
    });
  } catch (error) {
    next(error);
  }
});

// POST /api/payments/create - Create payment
router.post('/create', authMiddleware, validateRequest(createPaymentSchema), async (req: AuthRequest, res, next) => {
  try {
    const { amount, method } = req.body;

    const payment = await prisma.payment.create({
      data: {
        userId: req.user!.id,
        amount,
        type: 'deposit',
        provider: method,
        status: 'pending',
        description: `Пополнение баланса через ${method}`,
      },
    });

    // Для ЮKassa - создаем платеж
    if (method === 'yookassa') {
      // Здесь будет интеграция с ЮKassa API
      res.json({
        success: true,
        data: {
          paymentId: payment.id,
          amount,
          status: 'pending',
          confirmationUrl: `https://yookassa.ru/confirm/${payment.id}`, // Заглушка
        },
      });
    }
    
    // Для ЮMoney P2P
    else if (method === 'yoomoney_p2p') {
      const yoomoney = await prisma.yoomoneyP2p.findFirst({
        where: { isActive: true, isVerified: true },
      });

      res.json({
        success: true,
        data: {
          paymentId: payment.id,
          accountNumber: yoomoney?.accountNumber || null,
          amount,
          status: 'pending',
          instruction: yoomoney?.accountNumber
            ? `Переведите ${amount}₽ на счёт ЮMoney: ${yoomoney.accountNumber}`
            : 'Платёжный метод не настроен. Обратитесь к администратору.',
        },
      });
    }
    
    // Для карт
    else {
      res.json({
        success: true,
        data: {
          paymentId: payment.id,
          amount,
          status: 'pending',
          confirmationUrl: `https://payment.gateway.com/${payment.id}`, // Заглушка
        },
      });
    }
  } catch (error) {
    next(error);
  }
});

export default router;
