import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { paymentsAPI } from '../api';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Button from '@mui/material/Button';
import Grid from '@mui/material/Grid';
import TextField from '@mui/material/TextField';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import ListItemText from '@mui/material/ListItemText';
import Chip from '@mui/material/Chip';
import Radio from '@mui/material/Radio';
import RadioGroup from '@mui/material/RadioGroup';
import FormControlLabel from '@mui/material/FormControlLabel';
import PaymentIcon from '@mui/icons-material/Payment';

export default function Payments() {
  const [open, setOpen] = useState(false);
  const [amount, setAmount] = useState('');
  const [method, setMethod] = useState('yookassa');
  const queryClient = useQueryClient();

  const { data: methodsData } = useQuery({
    queryKey: ['paymentMethods'],
    queryFn: paymentsAPI.getMethods,
  });

  const { data: paymentsData } = useQuery({
    queryKey: ['payments'],
    queryFn: () => paymentsAPI.getAll({ limit: 10 }),
  });

  const createPayment = useMutation({
    mutationFn: paymentsAPI.create,
    onSuccess: (data) => {
      queryClient.invalidateQueries({ queryKey: ['payments'] });
      setOpen(false);
      setAmount('');
      
      // Redirect to payment gateway
      if (data.data.data.confirmationUrl) {
        window.open(data.data.data.confirmationUrl, '_blank');
      }
    },
  });

  const handleCreate = () => {
    if (amount && parseInt(amount) >= 100) {
      createPayment.mutate({ amount: parseInt(amount), method });
    }
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'succeeded': return 'success';
      case 'pending': return 'warning';
      case 'failed': return 'error';
      case 'refunded': return 'default';
      default: return 'default';
    }
  };

  const methods = methodsData?.data.data || [];
  const payments = paymentsData?.data.data || [];

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Оплата
      </Typography>

      <Grid container spacing={3}>
        <Grid size={{ xs: 12, md: 6 }}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Пополнить баланс
              </Typography>
              <Button
                variant="contained"
                size="large"
                startIcon={<PaymentIcon />}
                onClick={() => setOpen(true)}
                sx={{ mt: 2 }}
              >
                Пополнить
              </Button>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12, md: 6 }}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                Доступные методы
              </Typography>
              <List>
                {methods.map((m: any) => (
                  <ListItem key={m.id}>
                    <ListItemText
                      primary={m.displayName}
                      secondary={m.description}
                    />
                    <Chip label={m.isEnabled ? 'Активен' : 'Неактивен'} size="small" />
                  </ListItem>
                ))}
              </List>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12 }}>
          <Card>
            <CardContent>
              <Typography variant="h6" gutterBottom>
                История платежей
              </Typography>
              {payments.length === 0 ? (
                <Typography color="text.secondary">
                  У вас пока нет платежей
                </Typography>
              ) : (
                <List>
                  {payments.map((payment: any) => (
                    <ListItem key={payment.id} divider>
                      <ListItemText
                        primary={`${payment.amount}₽ - ${payment.provider}`}
                        secondary={new Date(payment.createdAt).toLocaleDateString('ru-RU')}
                      />
                      <Chip
                        label={payment.status}
                        color={getStatusColor(payment.status) as any}
                        size="small"
                      />
                    </ListItem>
                  ))}
                </List>
              )}
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Payment Dialog */}
      <Dialog open={open} onClose={() => setOpen(false)}>
        <DialogTitle>Пополнение баланса</DialogTitle>
        <DialogContent sx={{ minWidth: 400 }}>
          <TextField
            autoFocus
            margin="dense"
            label="Сумма (₽)"
            type="number"
            fullWidth
            value={amount}
            onChange={(e) => setAmount(e.target.value)}
            inputProps={{ min: 100, max: 100000 }}
            sx={{ mb: 2 }}
          />
          <RadioGroup value={method} onChange={(e) => setMethod(e.target.value)}>
            {methods.map((m: any) => (
              <FormControlLabel
                key={m.id}
                value={m.name}
                control={<Radio />}
                label={`${m.displayName} (${m.description})`}
              />
            ))}
          </RadioGroup>
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setOpen(false)}>Отмена</Button>
          <Button
            onClick={handleCreate}
            variant="contained"
            disabled={!amount || parseInt(amount) < 100}
          >
            Оплатить
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}
