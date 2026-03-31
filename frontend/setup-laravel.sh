#!/bin/bash
# Скрипт настройки Laravel внутри контейнера
# Запуск: docker exec vk-php bash /tmp/setup-laravel.sh

cd /var/www/html

echo "=== Создание моделей ==="

# User модель
cat > app/Models/User.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'vk_id',
        'email',
        'username',
        'password_hash',
        'role',
        'balance',
        'is_active',
        'is_blocked',
    ];

    protected $hidden = ['password_hash'];

    protected $casts = [
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getAuthPassword() {
        return $this->password_hash;
    }

    public function bots() {
        return $this->hasMany(Bot::class, 'user_id');
    }

    public function payments() {
        return $this->hasMany(Payment::class);
    }

    public function isAdmin() {
        return in_array($this->role, ['admin', 'superadmin']);
    }
}
EOF

# Bot модель
cat > app/Models/Bot.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    protected $table = 'bots';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'name',
        'status',
        'config',
        'webhook_url',
        'messages_sent',
        'messages_received',
    ];

    protected $casts = [
        'config' => 'array',
        'messages_sent' => 'integer',
        'messages_received' => 'integer',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
EOF

# Payment модель
cat > app/Models/Payment.php << 'EOF'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'amount',
        'provider',
        'status',
        'type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
EOF

echo "✅ Модели созданы!"

echo "=== Создание контроллеров ==="

mkdir -p app/Http/Controllers

# Dashboard контроллер
cat > app/Http/Controllers/DashboardController.php << 'EOF'
<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        $botsCount = Bot::where('user_id', $user->id)->count();
        $activeBots = Bot::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();
        
        $payments = [];
        try {
            $response = Http::withToken($user->api_token ?? '')
                ->get(env('BACKEND_URL') . '/api/payments?limit=5');
            $payments = $response->json()['data'] ?? [];
        } catch (\Exception $e) {
            \Log::error('Failed to load payments: ' . $e->getMessage());
        }
        
        return view('dashboard', compact('botsCount', 'activeBots', 'payments'));
    }
}
EOF

# Bot контроллер
cat > app/Http/Controllers/BotController.php << 'EOF'
<?php

namespace App\Http\Controllers;

use App\Models\Bot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BotController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $bots = Bot::where('user_id', $user->id)->get();
        
        return view('bots.index', compact('bots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $user = auth()->user();
        
        $response = Http::withToken($user->api_token ?? '')
            ->post(env('BACKEND_URL') . '/api/bots', [
                'name' => $request->name
            ]);

        if ($response->successful()) {
            return redirect()->route('bots.index')
                ->with('success', 'Бот создан!');
        }

        return back()->withErrors(['name' => 'Ошибка создания бота']);
    }

    public function destroy(Bot $bot)
    {
        $user = auth()->user();
        
        if ($bot->user_id !== $user->id) {
            abort(403);
        }

        Http::withToken($user->api_token ?? '')
            ->delete(env('BACKEND_URL') . '/api/bots/' . $bot->id);

        return redirect()->route('bots.index')
            ->with('success', 'Бот удалён!');
    }
}
EOF

echo "✅ Контроллеры созданы!"

echo "=== Настройка роутов ==="

cat > routes/web.php << 'EOF'
<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() 
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/bots', [BotController::class, 'index'])->name('bots.index');
    Route::post('/bots', [BotController::class, 'store'])->name('bots.store');
    Route::delete('/bots/{bot}', [BotController::class, 'destroy'])->name('bots.destroy');
    
    Route::get('/payments', function() {
        return view('payments');
    })->name('payments');
    
    Route::get('/settings', function() {
        return view('settings');
    })->name('settings');
    
    Route::get('/admin', function() {
        return view('admin');
    })->name('admin');
});
EOF

echo "✅ Роуты настроены!"

echo "=== Очистка кэша ==="
php artisan cache:clear
php artisan config:clear
php artisan view:clear

echo "✅ Настройка завершена!"
