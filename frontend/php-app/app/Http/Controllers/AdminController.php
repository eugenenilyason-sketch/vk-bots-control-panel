<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Главная страница админки - статистика
     */
    public function index()
    {
        // Проверка прав
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403, 'Доступ запрещён. Требуются права администратора.');
        }
        
        $stats = [
            'totalUsers' => \App\Models\User::count(),
            'activeUsers' => \App\Models\User::where('is_active', true)->count(),
            'blockedUsers' => \App\Models\User::where('is_blocked', true)->count(),
            'totalBots' => \App\Models\Bot::count(),
            'activeBots' => \App\Models\Bot::where('status', 'active')->count(),
            'totalRevenue' => \App\Models\Payment::where('status', 'succeeded')->sum('amount'),
            'totalPayments' => \App\Models\Payment::count(),
            'pendingPayments' => \App\Models\Payment::where('status', 'pending')->count(),
        ];
        
        $recentUsers = \App\Models\User::orderBy('created_at', 'desc')->limit(5)->get();
        $recentPayments = \App\Models\Payment::with('user')->orderBy('created_at', 'desc')->limit(5)->get();
        
        return view('admin', compact('stats', 'recentUsers', 'recentPayments'));
    }
    
    /**
     * Список пользователей
     */
    public function users(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $query = \App\Models\User::query();
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        if ($request->filled('status')) {
            if ($request->status === 'blocked') {
                $query->where('is_blocked', true);
            } elseif ($request->status === 'active') {
                $query->where('is_blocked', false)->where('is_active', true);
            }
        }
        
        $users = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        return view('admin.users', compact('users'));
    }
    
    /**
     * Редактирование пользователя
     */
    public function editUser(\App\Models\User $user)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        return view('admin.user-edit', compact('user'));
    }
    
    /**
     * Обновление пользователя
     */
    public function updateUser(Request $request, \App\Models\User $user)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'role' => 'required|in:user,admin,superadmin',
            'balance' => 'numeric|min:0',
            'is_active' => 'boolean',
            'is_blocked' => 'boolean',
            'password' => 'nullable|min:6|confirmed',
        ]);
        
        // Обновляем основные поля
        $updateData = [
            'role' => $validated['role'],
            'balance' => $validated['balance'] ?? $user->balance,
            'is_active' => $request->has('is_active'),
            'is_blocked' => $request->has('is_blocked'),
        ];
        
        // Если введён новый пароль - хэшируем и сохраняем
        if (!empty($validated['password'])) {
            $updateData['password_hash'] = \Hash::make($validated['password']);
        }
        
        $user->update($updateData);
        
        return back()->with('success', 'Пользователь обновлён!');
    }
    
    /**
     * Блокировка пользователя
     */
    public function blockUser(\App\Models\User $user)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Нельзя заблокировать себя');
        }
        
        $user->update(['is_blocked' => true]);
        
        return back()->with('success', 'Пользователь заблокирован');
    }
    
    /**
     * Разблокировка пользователя
     */
    public function unblockUser(\App\Models\User $user)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $user->update(['is_blocked' => false]);
        
        return back()->with('success', 'Пользователь разблокирован');
    }
    
    /**
     * Управление платёжными методами (только для суперадмина)
     */
    public function paymentMethods()
    {
        // Проверка на суперадмина
        if (!Auth::check() || Auth::user()->role !== 'superadmin') {
            abort(403, 'Доступ запрещён. Требуются права суперадминистратора.');
        }
        
        // Получаем методы из БД
        $methods = PaymentMethod::orderBy('sort_order')->orderBy('name')->get();
        
        return view('admin.payment-methods', compact('methods'));
    }
    
    /**
     * Обновление платёжного метода (только для суперадмина)
     */
    public function updatePaymentMethod(Request $request, $id)
    {
        // Проверка на суперадмина
        if (!Auth::check() || Auth::user()->role !== 'superadmin') {
            abort(403, 'Доступ запрещён. Требуются права суперадминистратора.');
        }
        
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'type' => 'required|in:p2p,card,qr,crypto',
            'icon' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'is_enabled' => 'boolean',
            'min_amount' => 'numeric|min:0',
            'max_amount' => 'numeric|min:0',
            'commission' => 'numeric|min:0',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'merchant_id' => 'nullable|string',
        ]);
        
        // Находим метод
        $method = PaymentMethod::find($id);
        
        if (!$method) {
            return back()->with('error', 'Платёжный метод не найден');
        }
        
        // Обновляем поля
        $updateData = [
            'display_name' => $validated['display_name'],
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? $method->icon,
            'description' => $validated['description'] ?? $method->description,
            'is_enabled' => $request->has('is_enabled'),
            'min_amount' => $validated['min_amount'] ?? $method->min_amount,
            'max_amount' => $validated['max_amount'] ?? $method->max_amount,
            'commission' => $validated['commission'] ?? $method->commission,
        ];
        
        // Сохраняем настройки в JSONB
        $settings = $method->settings ?? [];
        if ($request->filled('settings')) {
            $settings = array_merge($settings, $request->input('settings'));
        }
        $updateData['settings'] = $settings;
        
        $method->update($updateData);
        
        // API ключи обновляем только если они были введены
        if (!empty($validated['api_key'])) {
            $method->api_key = $validated['api_key'];
        }
        
        if (!empty($validated['api_secret'])) {
            $method->api_secret = $validated['api_secret'];
        }
        
        if (!empty($validated['merchant_id'])) {
            $method->merchant_id = $validated['merchant_id'];
        }
        
        $method->save();
        
        return back()->with('success', 'Платёжный метод обновлён!');
    }
    
    /**
     * Удаление платёжного метода (только для суперадмина)
     */
    public function deletePaymentMethod($id)
    {
        // Проверка на суперадмина
        if (!Auth::check() || Auth::user()->role !== 'superadmin') {
            abort(403, 'Доступ запрещён. Требуются права суперадминистратора.');
        }
        
        $method = PaymentMethod::find($id);
        
        if (!$method) {
            return back()->with('error', 'Платёжный метод не найден');
        }
        
        // Нельзя удалить если есть платежи с этим методом
        $paymentsCount = \App\Models\Payment::where('provider', $method->display_name)->count();
        if ($paymentsCount > 0) {
            return back()->with('error', 'Нельзя удалить метод с существующими платежами (' . $paymentsCount . ')');
        }
        
        $method->delete();
        
        return back()->with('success', 'Платёжный метод удалён!');
    }
    
    /**
     * Форма добавления нового метода (только для суперадмина)
     */
    public function createPaymentMethod()
    {
        // Проверка на суперадмина
        if (!Auth::check() || Auth::user()->role !== 'superadmin') {
            abort(403, 'Доступ запрещён. Требуются права суперадминистратора.');
        }
        
        return view('admin.payment-methods-create');
    }
    
    /**
     * Создание нового платёжного метода (только для суперадмина)
     */
    public function storePaymentMethod(Request $request)
    {
        // Проверка на суперадмина
        if (!Auth::check() || Auth::user()->role !== 'superadmin') {
            abort(403, 'Доступ запрещён. Требуются права суперадминистратора.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:payment_methods,name',
            'display_name' => 'required|string|max:255',
            'type' => 'required|in:p2p,card,qr,crypto',
            'icon' => 'nullable|string|max:10',
            'description' => 'nullable|string',
            'is_enabled' => 'boolean',
            'min_amount' => 'numeric|min:0',
            'max_amount' => 'numeric|min:0',
            'commission' => 'numeric|min:0',
            'api_key' => 'nullable|string',
            'api_secret' => 'nullable|string',
            'merchant_id' => 'nullable|string',
        ]);
        
        // Создаём метод
        $method = PaymentMethod::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'type' => $validated['type'],
            'icon' => $validated['icon'] ?? '💳',
            'description' => $validated['description'] ?? '',
            'is_enabled' => $request->has('is_enabled'),
            'min_amount' => $validated['min_amount'] ?? 100,
            'max_amount' => $validated['max_amount'] ?? 100000,
            'commission' => $validated['commission'] ?? 0,
        ]);
        
        // API ключи
        if (!empty($validated['api_key'])) {
            $method->api_key = $validated['api_key'];
        }
        
        if (!empty($validated['api_secret'])) {
            $method->api_secret = $validated['api_secret'];
        }
        
        if (!empty($validated['merchant_id'])) {
            $method->merchant_id = $validated['merchant_id'];
        }
        
        $method->save();

        return redirect()->route('admin.payment-methods')->with('success', 'Платёжный метод создан!');
    }
    
    /**
     * Настройки системы (только для суперадмина)
     */
    public function systemSettings()
    {
        // Проверка на суперадмина
        if (!Auth::check() || Auth::user()->role !== 'superadmin') {
            abort(403, 'Доступ запрещён. Требуются права суперадминистратора.');
        }
        
        $settings = [
            'registration_enabled' => Setting::isRegistrationEnabled(),
        ];
        
        return view('admin.settings', compact('settings'));
    }
    
    /**
     * Обновление настроек системы (только для суперадмина)
     */
    public function updateSystemSettings(Request $request)
    {
        // Проверка на суперадмина
        if (!Auth::check() || Auth::user()->role !== 'superadmin') {
            abort(403, 'Доступ запрещён. Требуются права суперадминистратора.');
        }
        
        // Обновляем настройки
        Setting::set('registration_enabled', $request->has('registration_enabled'));
        
        return back()->with('success', 'Настройки системы обновлены!');
    }
}
