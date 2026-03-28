import { Router } from 'express';
import prisma from '../lib/prisma';
import { logger } from '../utils/logger';

const router = Router();

// POST /webhook/yookassa - YooKassa webhook
router.post('/yookassa', async (req, res, next) => {
  try {
    const { event, object } = req.body;

    logger.info(`YooKassa webhook: ${event}`);

    if (event === 'payment.succeeded') {
      const paymentId = object.id;
      
      // Обновляем платеж
      await prisma.payment.updateMany({
        where: { providerPaymentId: paymentId },
        data: {
          status: 'succeeded',
          paidAt: new Date(),
        },
      });

      // Находим платеж и пополняем баланс
      const payment = await prisma.payment.findFirst({
        where: { providerPaymentId: paymentId },
      });

      if (payment) {
        await prisma.user.update({
          where: { id: payment.userId },
          data: { balance: { increment: payment.amount } },
        });
      }
    }

    res.status(200).send('OK');
  } catch (error) {
    logger.error('YooKassa webhook error:', error);
    res.status(500).send('Error');
  }
});

// POST /webhook/yoomoney - YooMoney P2P webhook
router.post('/yoomoney', async (req, res, next) => {
  try {
    const { notification_type, operation_id, amount, sender, account } = req.body;

    logger.info(`YooMoney webhook: ${notification_type}`);

    if (notification_type === 'p2p-incoming') {
      // Ищем счет получателя
      const yoomoney = await prisma.yoomoneyP2p.findFirst({
        where: { accountNumber: account },
      });

      if (yoomoney) {
        // Создаем или обновляем платеж
        await prisma.payment.create({
          data: {
            userId: '', // Нужно найти по sender
            amount: parseFloat(amount),
            provider: 'yoomoney_p2p',
            status: 'succeeded',
            providerPaymentId: operation_id,
            paidAt: new Date(),
            type: 'deposit',
          },
        });
      }
    }

    res.status(200).send('OK');
  } catch (error) {
    logger.error('YooMoney webhook error:', error);
    res.status(500).send('Error');
  }
});

// POST /webhook/vk - VK Bot webhook
router.post('/vk', async (req, res, next) => {
  try {
    const { type, object, group_id } = req.body;

    logger.info(`VK webhook: ${type}`);

    if (type === 'message_new') {
      // Обработка нового сообщения
      const message = object.message;
      
      // Сохраняем сообщение
      await prisma.message.create({
        data: {
          botId: '', // Нужно найти бота по group_id
          vkMessageId: BigInt(message.id),
          userId: String(message.from_id),
          direction: 'incoming',
          content: message.text,
          attachments: message.attachments || [],
        },
      });
    }

    res.status(200).send('OK');
  } catch (error) {
    logger.error('VK webhook error:', error);
    res.status(500).send('Error');
  }
});

export default router;
