<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    /**
     * История платежей
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Payment::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');
        
        // Фильтры
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        $payments = $query->paginate(20)->withQueryString();
        
        // Статистика
        $totalSpent = Payment::where('user_id', $user->id)
            ->where('status', 'succeeded')
            ->sum('amount');
        
        $pendingCount = Payment::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();
        
        return view('payments.index', compact('payments', 'totalSpent', 'pendingCount'));
    }
    
    /**
     * Форма создания платежа (пополнение)
     */
    public function create()
    {
        $methods = PaymentMethod::getActiveMethods();
        
        return view('payments.create', compact('methods'));
    }
    
    /**
     * Создание платежа (инициализация оплаты)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100|max:100000',
            'method' => 'required|exists:payment_methods,name',
        ], [
            'amount.min' => 'Минимальная сумма 100₽',
            'amount.max' => 'Максимальная сумма 100000₽',
        ]);
        
        $user = auth()->user();
        
        // Получаем платёжный метод
        $paymentMethod = PaymentMethod::find($validated['method']);
        
        if (!$paymentMethod || !$paymentMethod->is_enabled) {
            return back()->withErrors(['method' => 'Выбранный метод оплаты недоступен']);
        }
        
        // Создаём платёж в БД
        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'status' => 'pending',
            'provider' => $paymentMethod->display_name,
            'type' => 'deposit',
            'currency' => 'RUB',
        ]);
        
        // Здесь должна быть интеграция с платёжной системой
        // Для примера сразу помечаем как успешный
        $payment->update(['status' => 'succeeded']);
        
        // Зачисляем на баланс
        $user->balance = $user->balance + $validated['amount'];
        $user->save();
        
        return redirect()->route('payments.index')
            ->with('success', 'Платёж успешно проведён!');
    }
    
    /**
     * Детали платежа
     */
    public function show(Payment $payment)
    {
        // Проверка прав
        if ($payment->user_id !== auth()->id()) {
            abort(403);
        }
        
        return view('payments.show', compact('payment'));
    }
}
