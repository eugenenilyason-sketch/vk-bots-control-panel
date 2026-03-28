import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { botsAPI } from '../api';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import CardActions from '@mui/material/CardActions';
import Grid from '@mui/material/Grid';
import Chip from '@mui/material/Chip';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import TextField from '@mui/material/TextField';
import IconButton from '@mui/material/IconButton';
import Tooltip from '@mui/material/Tooltip';
import PlayArrowIcon from '@mui/icons-material/PlayArrow';
import StopIcon from '@mui/icons-material/Stop';
import DeleteIcon from '@mui/icons-material/Delete';
import AddIcon from '@mui/icons-material/Add';
import SmartToyIcon from '@mui/icons-material/SmartToy';

export default function Bots() {
  const [open, setOpen] = useState(false);
  const [newBotName, setNewBotName] = useState('');
  const queryClient = useQueryClient();

  const { data: botsData } = useQuery({
    queryKey: ['bots'],
    queryFn: botsAPI.getAll,
  });

  const createBot = useMutation({
    mutationFn: botsAPI.create,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bots'] });
      setOpen(false);
      setNewBotName('');
    },
  });

  const startBot = useMutation({
    mutationFn: botsAPI.start,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bots'] });
    },
  });

  const stopBot = useMutation({
    mutationFn: botsAPI.stop,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bots'] });
    },
  });

  const deleteBot = useMutation({
    mutationFn: botsAPI.delete,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['bots'] });
    },
  });

  const handleCreate = () => {
    if (newBotName.trim()) {
      createBot.mutate({ name: newBotName.trim() });
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'active': return 'success';
      case 'inactive': return 'warning';
      case 'blocked': return 'error';
      default: return 'default';
    }
  };

  const bots = botsData?.data.data || [];

  return (
    <Box>
      <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', mb: 3 }}>
        <Typography variant="h4">Мои боты</Typography>
        <Button
          variant="contained"
          startIcon={<AddIcon />}
          onClick={() => setOpen(true)}
        >
          Создать бота
        </Button>
      </Box>

      {bots.length === 0 ? (
        <Card>
          <CardContent sx={{ textAlign: 'center', py: 6 }}>
            <SmartToyIcon sx={{ fontSize: 64, color: 'text.secondary', mb: 2 }} />
            <Typography variant="h6" color="text.secondary">
              У вас ещё нет ботов
            </Typography>
            <Typography color="text.secondary" sx={{ mb: 3 }}>
              Создайте своего первого бота для управления сообщениями ВКонтакте
            </Typography>
            <Button variant="contained" onClick={() => setOpen(true)}>
              Создать бота
            </Button>
          </CardContent>
        </Card>
      ) : (
        <Grid container spacing={3}>
          {bots.map((bot: any) => (
            <Grid size={{ xs: 12, md: 6, lg: 4 }} key={bot.id}>
              <Card>
                <CardContent>
                  <Box sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                    <Typography variant="h6" gutterBottom>
                      {bot.name}
                    </Typography>
                    <Chip
                      label={bot.status}
                      color={getStatusColor(bot.status) as any}
                      size="small"
                    />
                  </Box>
                  <Typography color="text.secondary" variant="body2">
                    ID: {bot.id.slice(0, 8)}...
                  </Typography>
                  <Typography color="text.secondary" variant="body2" sx={{ mt: 1 }}>
                    Сообщений отправлено: {bot.messagesSent}
                  </Typography>
                  <Typography color="text.secondary" variant="body2">
                    Сообщений получено: {bot.messagesReceived}
                  </Typography>
                </CardContent>
                <CardActions>
                  {bot.status === 'active' ? (
                    <Tooltip title="Остановить">
                      <IconButton onClick={() => stopBot.mutate(bot.id)} color="warning">
                        <StopIcon />
                      </IconButton>
                    </Tooltip>
                  ) : (
                    <Tooltip title="Запустить">
                      <IconButton onClick={() => startBot.mutate(bot.id)} color="success">
                        <PlayArrowIcon />
                      </IconButton>
                    </Tooltip>
                  )}
                  <Tooltip title="Удалить">
                    <IconButton onClick={() => deleteBot.mutate(bot.id)} color="error">
                      <DeleteIcon />
                    </IconButton>
                  </Tooltip>
                </CardActions>
              </Card>
            </Grid>
          ))}
        </Grid>
      )}

      {/* Create Bot Dialog */}
      <Dialog open={open} onClose={() => setOpen(false)}>
        <DialogTitle>Создать бота</DialogTitle>
        <DialogContent>
          <TextField
            autoFocus
            margin="dense"
            label="Название бота"
            fullWidth
            value={newBotName}
            onChange={(e) => setNewBotName(e.target.value)}
          />
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setOpen(false)}>Отмена</Button>
          <Button onClick={handleCreate} variant="contained" disabled={!newBotName.trim()}>
            Создать
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
