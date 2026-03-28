import { useQuery } from '@tanstack/react-query';
import { useAuthStore } from '../store/authStore';
import { adminAPI } from '../api';
import Grid from '@mui/material/Grid';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import PeopleIcon from '@mui/icons-material/People';
import SmartToyIcon from '@mui/icons-material/SmartToy';
import AttachMoneyIcon from '@mui/icons-material/AttachMoney';
import TrendingUpIcon from '@mui/icons-material/TrendingUp';

export default function Dashboard() {
  const { user } = useAuthStore();
  const isAdmin = user?.role === 'admin' || user?.role === 'superadmin';

  const { data: analytics } = useQuery({
    queryKey: ['analytics'],
    queryFn: adminAPI.getAnalytics,
    enabled: isAdmin,
  });

  const stats = isAdmin
    ? [
        { label: 'Пользователей', value: analytics?.data.data.totalUsers || 0, icon: <PeopleIcon /> },
        { label: 'Ботов', value: analytics?.data.data.activeBots || 0, icon: <SmartToyIcon /> },
        { label: 'Доход', value: `${analytics?.data.data.totalRevenue || 0}₽`, icon: <AttachMoneyIcon /> },
        { label: 'Платежей', value: analytics?.data.data.totalPayments || 0, icon: <TrendingUpIcon /> },
      ]
    : [
        { label: 'Баланс', value: `${user?.balance || 0}₽`, icon: <AttachMoneyIcon /> },
        { label: 'Статус', value: user?.role === 'superadmin' ? 'Суперадмин' : 'Пользователь', icon: <PeopleIcon /> },
      ];

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Dashboard
      </Typography>

      <Grid container spacing={3}>
        {stats.map((stat, index) => (
          <Grid size={{ xs: 12, sm: 6, md: 3 }} key={index}>
            <Card sx={{ height: '100%' }}>
              <CardContent>
                <Box sx={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
                  <Box>
                    <Typography color="text.secondary" variant="body2">
                      {stat.label}
                    </Typography>
                    <Typography variant="h4" sx={{ mt: 1 }}>
                      {stat.value}
                    </Typography>
                  </Box>
                  <Box sx={{ color: 'primary.main', fontSize: 40 }}>{stat.icon}</Box>
                </Box>
              </CardContent>
            </Card>
          </Grid>
        ))}
      </Grid>

      <Grid container spacing={3} sx={{ mt: 2 }}>
        <Grid size={{ xs: 12, md: 6 }}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Активность ботов
              </Typography>
              <Typography color="text.secondary">
                График активности будет здесь
              </Typography>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12, md: 6 }}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Последние платежи
              </Typography>
              <Typography color="text.secondary">
                Список последних платежей будет здесь
              </Typography>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
}
