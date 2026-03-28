import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';
import { authAPI } from '../api';
import Container from '@mui/material/Container';
import Box from '@mui/material/Box';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import CircularProgress from '@mui/material/CircularProgress';
import Alert from '@mui/material/Alert';
import VkIcon from '@mui/icons-material/Videocam';

const VK_AUTH_URL = 'https://oauth.vk.com/authorize';
const VK_CLIENT_ID = import.meta.env.VITE.VK_CLIENT_ID || 'your_vk_client_id';
const REDIRECT_URI = import.meta.env.VITE.VK_REDIRECT_URI || 'http://localhost:3000/login';

export default function Login() {
  const navigate = useNavigate();
  const { login } = useAuthStore();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  const handleVKLogin = () => {
    const authUrl = `${VK_AUTH_URL}?client_id=${VK_CLIENT_ID}&redirect_uri=${encodeURIComponent(REDIRECT_URI)}&response_type=code&scope=messages,groups,offline`;
    window.location.href = authUrl;
  };

  // Handle OAuth callback
  const urlParams = new URLSearchParams(window.location.search);
  const code = urlParams.get('code');

  if (code && !loading) {
    setLoading(true);
    authAPI.loginVK(code)
      .then((response) => {
        const { tokens, user } = response.data.data;
        login(tokens, user);
        navigate('/');
      })
      .catch((err) => {
        setError(err.response?.data?.error?.message || 'Ошибка входа');
        setLoading(false);
      });
  }

  return (
    <Container maxWidth="sm">
      <Box
        sx={{
          minHeight: '100vh',
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
        }}
      >
        <Card sx={{ width: '100%', boxShadow: 3 }}>
          <CardContent sx={{ p: 4, textAlign: 'center' }}>
            <Typography variant="h4" component="h1" gutterBottom fontWeight="bold">
              VK Neuro-Agents
            </Typography>
            <Typography variant="body1" color="text.secondary" mb={4}>
              Система управления нейро-агентами ВКонтакте
            </Typography>

            {error && (
              <Alert severity="error" sx={{ mb: 3 }}>
                {error}
              </Alert>
            )}

            <Button
              variant="contained"
              size="large"
              startIcon={loading ? <CircularProgress size={20} /> : <VkIcon />}
              onClick={handleVKLogin}
              disabled={loading}
              sx={{
                bgcolor: '#0077FF',
                '&:hover': { bgcolor: '#0066DD' },
                py: 1.5,
                px: 4,
              }}
            >
              {loading ? 'Вход...' : 'Войти через VK'}
            </Button>

            <Typography variant="caption" color="text.secondary" sx={{ mt: 3, display: 'block' }}>
              Нажимая кнопку, вы соглашаетесь с условиями использования
            </Typography>
          </CardContent>
        </Card>
      </Box>
    </Container>
  );
}
