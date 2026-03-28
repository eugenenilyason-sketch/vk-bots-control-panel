import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  console.log('🌱 Seeding database...');

  // Payment methods
  await prisma.paymentMethod.createMany({
    data: [
      {
        name: 'yookassa',
        displayName: 'ЮKassa',
        description: 'Банковские карты, СБП, ЮMoney (юрлица)',
        isEnabled: true,
        config: { min_amount: 100, max_amount: 100000, commission: 0.028 },
        icon: 'yookassa',
        sortOrder: 1,
      },
      {
        name: 'yoomoney_p2p',
        displayName: 'ЮMoney P2P',
        description: 'Перевод на счёт физлица (проверенный пользователь)',
        isEnabled: false,
        config: { min_amount: 100, max_amount: 50000, commission: 0 },
        icon: 'yoomoney',
        sortOrder: 2,
      },
      {
        name: 'cards',
        displayName: 'Банковские карты',
        description: 'Visa, Mastercard, МИР',
        isEnabled: true,
        config: { min_amount: 100, max_amount: 100000, commission: 0.025 },
        icon: 'cards',
        sortOrder: 3,
      },
    ],
    skipDuplicates: true,
  });

  // Settings
  await prisma.setting.createMany({
    data: [
      { key: 'system.maintenance_mode', value: false, description: 'Режим обслуживания' },
      { key: 'system.registration_enabled', value: true, description: 'Регистрация новых пользователей' },
      { key: 'payments.min_deposit', value: 100, description: 'Минимальная сумма пополнения' },
      { key: 'payments.max_deposit', value: 100000, description: 'Максимальная сумма пополнения' },
      { key: 'bots.max_per_user', value: 5, description: 'Максимум ботов на пользователя' },
      {
        key: 'tariffs.plans',
        value: [
          { id: 'free', name: 'Free', price: 0, limits: { bots: 1, messages: 100 } },
          { id: 'pro', name: 'Pro', price: 990, limits: { bots: 5, messages: 10000 } },
          { id: 'business', name: 'Business', price: 2990, limits: { bots: 20, messages: 100000 } },
        ],
        description: 'Тарифные планы',
      },
    ],
    skipDuplicates: true,
  });

  console.log('✅ Seeding completed!');
}

main()
  .catch((e) => {
    console.error('❌ Seeding error:', e);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
