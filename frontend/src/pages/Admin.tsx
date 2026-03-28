import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { adminAPI } from '../api';
import Box from '@mui/material/Box';
import Typography from '@mui/material/Typography';
import Card from '@mui/material/Card';
import CardContent from '@mui/material/CardContent';
import Grid from '@mui/material/Grid';
import Table from '@mui/material/Table';
import TableBody from '@mui/material/TableBody';
import TableCell from '@mui/material/TableCell';
import TableContainer from '@mui/material/TableContainer';
import TableHead from '@mui/material/TableHead';
import TableRow from '@mui/material/TableRow';
import Button from '@mui/material/Button';
import Chip from '@mui/material/Chip';
import IconButton from '@mui/material/IconButton';
import Dialog from '@mui/material/Dialog';
import DialogTitle from '@mui/material/DialogTitle';
import DialogContent from '@mui/material/DialogContent';
import DialogActions from '@mui/material/DialogActions';
import TextField from '@mui/material/TextField';
import Switch from '@mui/material/Switch';
import FormControlLabel from '@mui/material/FormControlLabel';
import PeopleIcon from '@mui/icons-material/People';
import PaymentIcon from '@mui/icons-material/Payment';
import SettingsIcon from '@mui/icons-material/Settings';

export default function Admin() {
  const [selectedTab, setSelectedTab] = useState<'users' | 'payments' | 'settings'>('users');
  const [selectedUser, setSelectedUser] = useState(null);
  const [openUserDialog, setOpenUserDialog] = useState(false);
  const queryClient = useQueryClient();

  const { data: usersData } = useQuery({
    queryKey: ['adminUsers'],
    queryFn: () => adminAPI.getUsers({ limit: 50 }),
  });

  const { data: paymentsData } = useQuery({
    queryKey: ['adminPayments'],
    queryFn: () => adminAPI.getPayments({ limit: 50 }),
  });

  const { data: methodsData } = useQuery({
    queryKey: ['adminPaymentMethods'],
    queryFn: adminAPI.getPaymentMethods,
  });

  const { data: yoomoneyData } = useQuery({
    queryKey: ['adminYoomoney'],
    queryFn: adminAPI.getYoomoneyP2p,
  });

  const updateUser = useMutation({
    mutationFn: ({ id, data }: { id: string; data: any }) => adminAPI.updateUser(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['adminUsers'] });
      setOpenUserDialog(false);
    },
  });

  const updatePaymentMethod = useMutation({
    mutationFn: ({ id, data }: { id: string; data: any }) => adminAPI.updatePaymentMethod(id, data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['adminPaymentMethods'] });
    },
  });

  const users = usersData?.data.data || [];
  const payments = paymentsData?.data.data || [];
  const methods = methodsData?.data.data || [];
  const yoomoney = yoomoneyData?.data.data || [];

  return (
    <Box>
      <Typography variant="h4" gutterBottom>
        Админ-панель
      </Typography>

      <Grid container spacing={3} sx={{ mb: 3 }}>
        <Grid size={{ xs: 12, md: 4 }}>
          <Card onClick={() => setSelectedTab('users')} sx={{ cursor: 'pointer' }}>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <PeopleIcon sx={{ fontSize: 40, mr: 2, color: 'primary.main' }} />
                <Box>
                  <Typography variant="h6">Пользователи</Typography>
                  <Typography color="text.secondary">{users.length} чел.</Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12, md: 4 }}>
          <Card onClick={() => setSelectedTab('payments')} sx={{ cursor: 'pointer' }}>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <PaymentIcon sx={{ fontSize: 40, mr: 2, color: 'primary.main' }} />
                <Box>
                  <Typography variant="h6">Платежи</Typography>
                  <Typography color="text.secondary">{payments.length} записей</Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid size={{ xs: 12, md: 4 }}>
          <Card onClick={() => setSelectedTab('settings')} sx={{ cursor: 'pointer' }}>
            <CardContent>
              <Box sx={{ display: 'flex', alignItems: 'center' }}>
                <SettingsIcon sx={{ fontSize: 40, mr: 2, color: 'primary.main' }} />
                <Box>
                  <Typography variant="h6">Настройки</Typography>
                  <Typography color="text.secondary">Методы оплаты</Typography>
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>
      </Grid>

      {/* Users Tab */}
      {selectedTab === 'users' && (
        <Card>
          <CardContent>
            <Typography variant="h6" gutterBottom>
              Пользователи
            </Typography>
            <TableContainer>
              <Table>
                <TableHead>
                  <TableRow>
                    <TableCell>ID</TableCell>
                    <TableCell>Имя</TableCell>
                    <TableCell>Email</TableCell>
                    <TableCell>Роль</TableCell>
                    <TableCell>Баланс</TableCell>
                    <TableCell>Статус</TableCell>
                    <TableCell>Действия</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {users.map((user: any) => (
                    <TableRow key={user.id}>
                      <TableCell>{user.id.slice(0, 8)}...</TableCell>
                      <TableCell>{user.username}</TableCell>
                      <TableCell>{user.email || '-'}</TableCell>
                      <TableCell>{user.role}</TableCell>
                      <TableCell>{user.balance}₽</TableCell>
                      <TableCell>
                        <Chip
                          label={user.isBlocked ? 'Заблокирован' : 'Активен'}
                          color={user.isBlocked ? 'error' : 'success'}
                          size="small"
                        />
                      </TableCell>
                      <TableCell>
                        <Button
                          size="small"
                          onClick={() => {
                            setSelectedUser(user);
                            setOpenUserDialog(true);
                          }}
                        >
                          Редактировать
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
          </CardContent>
        </Card>
      )}

      {/* Payment Methods Tab */}
      {selectedTab === 'settings' && (
        <Card>
          <CardContent>
            <Typography variant="h6" gutterBottom>
              Методы оплаты
            </Typography>
            {methods.map((method: any) => (
              <Box key={method.id} sx={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', py: 2, borderBottom: 1, borderColor: 'divider' }}>
                <Box>
                  <Typography variant="subtitle1">{method.displayName}</Typography>
                  <Typography color="text.secondary" variant="body2">{method.description}</Typography>
                </Box>
                <FormControlLabel
                  control={
                    <Switch
                      checked={method.isEnabled}
                      onChange={(e) => updatePaymentMethod.mutate({ id: method.id, data: { isEnabled: e.target.checked } })}
                    />
                  }
                  label="Включен"
                />
              </Box>
            ))}
          </CardContent>
        </Card>
      )}

      {/* Edit User Dialog */}
      <Dialog open={openUserDialog} onClose={() => setOpenUserDialog(false)}>
        <DialogTitle>Редактировать пользователя</DialogTitle>
        <DialogContent sx={{ minWidth: 400 }}>
          {selectedUser && (
            <Box sx={{ display: 'flex', flexDirection: 'column', gap: 2 }}>
              <TextField
                label="Роль"
                select
                SelectProps={{ native: true }}
                value={selectedUser.role}
                onChange={(e) => setSelectedUser({ ...selectedUser, role: e.target.value })}
              >
                <option value="user">user</option>
                <option value="admin">admin</option>
                <option value="superadmin">superadmin</option>
              </TextField>
              <TextField
                label="Баланс"
                type="number"
                value={selectedUser.balance}
                onChange={(e) => setSelectedUser({ ...selectedUser, balance: parseFloat(e.target.value) })}
              />
              <FormControlLabel
                control={
                  <Switch
                    checked={!selectedUser.isBlocked}
                    onChange={(e) => setSelectedUser({ ...selectedUser, isBlocked: !e.target.checked })}
                  />
                }
                label="Активен"
              />
            </Box>
          )}
        </DialogContent>
        <DialogActions>
          <Button onClick={() => setOpenUserDialog(false)}>Отмена</Button>
          <Button
            onClick={() => selectedUser && updateUser.mutate({ id: selectedUser.id, data: selectedUser })}
            variant="contained"
          >
            Сохранить
          </Button>
        </DialogActions>
      </Dialog>
    </Box>
  );
}

import { useState } from 'react';
