import { Router } from 'express';
import { authMiddleware } from '../middleware/authMiddleware';
import { AuthRequest } from '../middleware/authMiddleware';
import { validateRequest } from '../middleware/validateRequest';
import { z } from 'zod';
import prisma from '../lib/prisma';

const router = Router();

const createBotSchema = z.object({
  body: z.object({
    name: z.string().min(1).max(255),
    vkGroupId: z.string().optional(),
    vkToken: z.string().optional(),
  }),
});

const updateBotSchema = z.object({
  body: z.object({
    name: z.string().min(1).max(255).optional(),
    config: z.record(z.any()).optional(),
  }),
  params: z.object({
    id: z.string().uuid(),
  }),
});

// GET /api/bots - Get all user bots
router.get('/', authMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const bots = await prisma.bot.findMany({
      where: { userId: req.user!.id },
      orderBy: { createdAt: 'desc' },
    });

    res.json({
      success: true,
      data: bots.map(bot => ({
        ...bot,
        vkGroupId: bot.vkGroupId?.toString(),
      })),
    });
  } catch (error) {
    next(error);
  }
});

// POST /api/bots - Create new bot
router.post('/', authMiddleware, validateRequest(createBotSchema), async (req: AuthRequest, res, next) => {
  try {
    const { name, vkGroupId, vkToken } = req.body;

    const bot = await prisma.bot.create({
      data: {
        userId: req.user!.id,
        name,
        vkGroupId: vkGroupId ? BigInt(vkGroupId) : null,
        vkToken: vkToken || null,
        status: 'pending',
      },
    });

    res.status(201).json({
      success: true,
      data: {
        ...bot,
        vkGroupId: bot.vkGroupId?.toString(),
      },
    });
  } catch (error) {
    next(error);
  }
});

// GET /api/bots/:id - Get bot by ID
router.get('/:id', authMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const { id } = req.params;

    const bot = await prisma.bot.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
    });

    if (!bot) {
      return res.status(404).json({
        success: false,
        error: { code: 'NOT_FOUND', message: 'Bot not found' },
      });
    }

    res.json({
      success: true,
      data: {
        ...bot,
        vkGroupId: bot.vkGroupId?.toString(),
      },
    });
  } catch (error) {
    next(error);
  }
});

// PUT /api/bots/:id - Update bot
router.put('/:id', authMiddleware, validateRequest(updateBotSchema), async (req: AuthRequest, res, next) => {
  try {
    const { id } = req.params;
    const { name, config } = req.body;

    const bot = await prisma.bot.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
    });

    if (!bot) {
      return res.status(404).json({
        success: false,
        error: { code: 'NOT_FOUND', message: 'Bot not found' },
      });
    }

    const updatedBot = await prisma.bot.update({
      where: { id },
      data: {
        ...(name && { name }),
        ...(config && { config }),
      },
    });

    res.json({
      success: true,
      data: {
        ...updatedBot,
        vkGroupId: updatedBot.vkGroupId?.toString(),
      },
    });
  } catch (error) {
    next(error);
  }
});

// DELETE /api/bots/:id - Delete bot
router.delete('/:id', authMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const { id } = req.params;

    const bot = await prisma.bot.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
    });

    if (!bot) {
      return res.status(404).json({
        success: false,
        error: { code: 'NOT_FOUND', message: 'Bot not found' },
      });
    }

    await prisma.bot.delete({
      where: { id },
    });

    res.json({
      success: true,
      message: 'Bot deleted successfully',
    });
  } catch (error) {
    next(error);
  }
});

// POST /api/bots/:id/start - Start bot
router.post('/:id/start', authMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const { id } = req.params;

    const bot = await prisma.bot.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
    });

    if (!bot) {
      return res.status(404).json({
        success: false,
        error: { code: 'NOT_FOUND', message: 'Bot not found' },
      });
    }

    await prisma.bot.update({
      where: { id },
      data: { status: 'active' },
    });

    res.json({
      success: true,
      message: 'Bot started successfully',
    });
  } catch (error) {
    next(error);
  }
});

// POST /api/bots/:id/stop - Stop bot
router.post('/:id/stop', authMiddleware, async (req: AuthRequest, res, next) => {
  try {
    const { id } = req.params;

    const bot = await prisma.bot.findFirst({
      where: {
        id,
        userId: req.user!.id,
      },
    });

    if (!bot) {
      return res.status(404).json({
        success: false,
        error: { code: 'NOT_FOUND', message: 'Bot not found' },
      });
    }

    await prisma.bot.update({
      where: { id },
      data: { status: 'inactive' },
    });

    res.json({
      success: true,
      message: 'Bot stopped successfully',
    });
  } catch (error) {
    next(error);
  }
});

export default router;
