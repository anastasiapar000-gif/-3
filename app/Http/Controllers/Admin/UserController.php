<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Получение списка всех пользователей с пагинацией
     * Возвращает данные с количеством заказов каждого пользователя
     * 
     *
     */
    public function index()
    {
        try {
            // Проверка существования таблицы users перед выполнением запроса
            if (!DB::getSchemaBuilder()->hasTable('users')) {
                return response()->json([
                    'message' => 'Таблица пользователей не существует',
                    'data' => [],
                    'total' => 0
                ], 200);
            }

            // Проверка наличия таблицы заказов для подгрузки счётчика
            $hasOrdersTable = DB::getSchemaBuilder()->hasTable('orders');

            // Определение доступного поля имени в таблице users
            // Поддержка разных схем БД: full_name, login или name
            $columns = DB::getSchemaBuilder()->getColumnListing('users');
            $nameField = in_array('full_name', $columns) ? 'users.full_name' 
                : (in_array('login', $columns) ? 'users.login' : 'users.name');

            // Формирование базового запроса с выбором необходимых полей
            $query = DB::table('users')
                ->select(
                    'users.id',
                    DB::raw("$nameField as name"),
                    'users.email',
                    'users.role',
                    'users.phone',
                    'users.created_at',
                    'users.updated_at'
                );

            // Добавление подзапроса для подсчёта количества заказов пользователя
            if ($hasOrdersTable) {
                $query->selectRaw('(SELECT COUNT(*) FROM orders WHERE orders.user_id = users.id) as orders_count');
            } else {
                $query->selectRaw('0 as orders_count');
            }

            // Выполнение запроса с сортировкой и пагинацией
            $users = $query
                ->orderBy('users.created_at', 'desc')
                ->paginate(20);

            return response()->json($users);

        } catch (\Exception $e) {
            // Логирование ошибки и возврат ответа с кодом 500
            \Log::error('Admin Users index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка загрузки пользователей: ' . $e->getMessage(),
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Обновление роли пользователя (buyer/admin)
     * 
     *
     */
    public function updateRole(Request $request, $id)
    {
        try {
            // Валидация входящего значения роли
            $validated = $request->validate([
                'role' => 'required|in:buyer,admin',
            ]);

            // Поиск пользователя по ID
            $user = DB::table('users')->where('id', $id)->first();
            if (!$user) {
                return response()->json(['message' => 'Пользователь не найден'], 404);
            }

            // Защита: запрет на понижение роли последнего администратора
            if ($user->role === 'admin' && $validated['role'] === 'buyer') {
                $adminCount = DB::table('users')->where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    return response()->json([
                        'message' => 'Нельзя изменить роль последнего администратора'
                    ], 422);
                }
            }

            // Обновление поля role в базе данных
            $updated = DB::table('users')
                ->where('id', $id)
                ->update(['role' => $validated['role']]);

            if (!$updated) {
                return response()->json(['message' => 'Не удалось обновить роль'], 500);
            }

            return response()->json([
                'message' => 'Роль успешно обновлена',
                'role' => $validated['role']
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Возврат ошибок валидации для отображения на фронтенде
            return response()->json([
                'message' => 'Ошибка валидации',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Admin Users updateRole error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка обновления роли: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Удаление пользователя по ID

     */
    public function destroy($id)
    {
        try {
            $user = DB::table('users')->where('id', $id)->first();
            
            if (!$user) {
                return response()->json(['message' => 'Пользователь не найден'], 404);
            }

            // Защита: запрет на удаление собственной учётной записи
            $currentUserId = auth()->id();
            if ($user->id == $currentUserId) {
                return response()->json([
                    'message' => 'Нельзя удалить самого себя'
                ], 422);
            }

            // Защита: запрет на удаление последнего администратора
            if ($user->role === 'admin') {
                $adminCount = DB::table('users')->where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    return response()->json([
                        'message' => 'Нельзя удалить последнего администратора'
                    ], 422);
                }
            }

            // Удаление записи пользователя из базы данных
            DB::table('users')->where('id', $id)->delete();

            return response()->json(['message' => 'Пользователь успешно удалён']);

        } catch (\Exception $e) {
            \Log::error('Admin Users destroy error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Ошибка удаления: ' . $e->getMessage()
            ], 500);
        }
    }
}