import { useState } from 'react';
import { useAuthStore } from '../store/authStore';
import { useThemeStore } from '../store/themeStore';
import { userAPI } from '../api';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Button from '@mui/material/Button';
import TextField from '@mui/material/TextField';
import Avatar from '@mui/material/Avatar';
import Grid from '@mui/material/Grid';
import Alert from '@mui/material/Alert';
import SaveIcon from '@mui/icons-material/Save';

export default function Settings() {
  const { user, updateUser } = useAuthStore();
  const { mode, toggleTheme } = useThemeStore();
  const [formData, setFormData] = useState({
    username: user?.username || '',
    email: user?.email || '',
  });
  const [success, setSuccess] = useState('');
  const [error, setError] = useState('');

  const handleSave = async () => {
    try {
      const response = await userAPI.updateProfile(formData);
      updateUser(response.data.data);
      setSuccess('Профиль обновлен');
      setError('');
    } catch (err: any) {
      setError(err.response?.data?.error?.message || 'Ошибка обновления');
      setSuccess('');
    }
  };

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Настройки
      </Typography>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, md: 6 }}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Профиль
              </Typography>
              
              <Box sx={{ display: 'flex', alignItems: 'center', mb: 3 }}>
                <Avatar src={user?.avatarUrl} sx={{ width: 80, height: 80, mr: 2 }}>
                  {user?.username?.[0]}
                </Avatar>
                <Box>
                  <Typography variant="h6">{user?.username}</Typography>
                  <Typography color="text.secondary">ID: {user?.id.slice(0, 8)}...</Typography>
                </Box>
              </Box>

              {success && (
                <Alert severity="success" sx={{ mb: 2 }}>
                  {success}
                </Alert>
              )}

              {error && (
                <Alert severity="error" sx={{ mb: 2 }}>
                  {error}
                </Alert>
              )}

              <TextField
                label="Имя пользователя"
                fullWidth
                margin="normal"
                value={formData.username}
                onChange={(e) => setFormData({ ...formData, username: e.target.value })}
              />

              <TextField
                label="Email"
                fullWidth
                margin="normal"
                value={formData.email || ''}
                onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              />

              <Button
                variant="contained"
                startIcon={<SaveIcon />}
                onClick={handleSave}
                sx={{ mt: 2 }}
              >
                Сохранить
              </Button>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12, md: 6 }}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Внешний вид
              </Typography>

              <Box sx={{ mt: 2 }}>
                <Typography variant="subtitle2" gutterBottom>
                  Тема оформления
                </Typography>
                <Button
                  variant={mode === 'light' ? 'contained' : 'outlined'}
                  onClick={() => mode === 'dark' && toggleTheme()}
                  sx={{ mr: 1 }}
                >
                  Светлая
                </Button>
                <Button
                  variant={mode === 'dark' ? 'contained' : 'outlined'}
                  onClick={() => mode === 'light' && toggleTheme()}
                >
                  Тёмная
                </Button>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12 }}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Информация об аккаунте
              </Typography>
              <Grid container spacing={2} sx={{ mt: 1 }}>
                <Grid size={{ xs: 6, md: 3 }}>
                  <Typography color="text.secondary" variant="body2">Роль</Typography>
                  <Typography>{user?.role}</Typography>
                </Grid>
                <Grid size={{ xs: 6, md: 3 }}>
                  <Typography color="text.secondary" variant="body2">Баланс</Typography>
                  <Typography>{user?.balance}₽</Typography>
                </Grid>
                <Grid size={{ xs: 6, md: 3 }}>
                  <Typography color="text.secondary" variant="body2">VK ID</Typography>
                  <Typography>{user?.vkId}</Typography>
                </Grid>
                <Grid size={{ xs: 6, md: 3 }}>
                  <Typography color="text.secondary" variant="body2">Дата регистрации</Typography>
                  <Typography>{user?.createdAt ? new Date(user.createdAt).toLocaleDateString('ru-RU') : '-'}</Typography>
                </Grid>
              </Grid>
            </CardContent>
          </Card>
        </Grid>
      </Grid>
    </Box>
  );
}
