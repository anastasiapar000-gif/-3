<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Category;
use App\Models\Material;
use App\Models\Stone;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Address;

class DatabaseSeeder extends Seeder
{
    /**
     * Запуск всех сидеров
     */
    public function run(): void
    {
        $this->command->info('🚀 Запуск заполнения базы данных ZIMA...');

        // === 1. Создаём пользователей (Админ + Покупатели) ===
        $admin = $this->seedAdmin();
        $buyers = $this->seedBuyers();

        // === 2. Создаём справочники ===
        $categories = $this->seedCategories();
        $materials = $this->seedMaterials();
        $stones = $this->seedStones();

        // === 3. Создаём товары ===
        $this->seedProducts($categories, $materials, $stones);

        $this->command->info('✅ База данных успешно заполнена!');
        $this->command->info('📊 Итог:');
        $this->command->info('   • Админ: admin@zimajewelry.ru / password');
        $this->command->info('   • Покупателей: ' . User::where('role', 'buyer')->count());
        $this->command->info('   • Товаров: ' . Product::count());
    }

    // ========================================================================
    // ПОЛЬЗОВАТЕЛИ
    // ========================================================================

    private function seedAdmin(): User
    {
        $this->command->info('👮 Создание администратора...');
        
        return User::updateOrCreate(
            ['email' => 'admin@zimajewelry.ru'],
            [
                'full_name' => 'Главный Администратор',
                'password' => Hash::make('password'),
                'phone' => '+79990000000',
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }

    private function seedBuyers(): array
    {
        $this->command->info('👥 Создание покупателей...');

        $buyersData = [
            ['name' => 'Иван Иванов', 'email' => 'ivan@example.com', 'phone' => '+79991112233'],
            ['name' => 'Мария Петрова', 'email' => 'maria@example.com', 'phone' => '+79992223344'],
            ['name' => 'Алексей Сидоров', 'email' => 'alex@example.com', 'phone' => '+79993334455'],
            ['name' => 'Елена Смирнова', 'email' => 'elena@example.com', 'phone' => '+79994445566'],
            ['name' => 'Дмитрий Козлов', 'email' => 'dmitry@example.com', 'phone' => '+79995556677'],
        ];

        $buyers = [];
        foreach ($buyersData as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'full_name' => $data['name'],
                    'password' => Hash::make('password'),
                    'phone' => $data['phone'],
                    'role' => 'buyer',
                    'email_verified_at' => now(),
                ]
            );

            // Создаем адрес по умолчанию для каждого покупателя
            Address::updateOrCreate(
                ['user_id' => $user->id, 'is_default' => true],
                [
                    'city' => 'Иркутск',
                    'street' => 'ул. Ленина',
                    'building' => rand(1, 50) . 'А',
                    'apartment' => rand(1, 100),
                    'zip_code' => '664000',
                    'phone' => $data['phone'],
                    'delivery_comment' => 'Домофон не работает, звоните в дверь',
                ]
            );

            $buyers[] = $user;
            $this->command->info("   ✓ {$data['name']}");
        }

        return $buyers;
    }


    // ========================================================================
    // СПРАВОЧНИКИ (Категории, Материалы, Камни) - Без изменений
    // ========================================================================

    private function seedCategories(): array
    {
        $this->command->info('📂 Создание категорий...');
        $data = [
            ['name' => 'Кольца', 'slug' => 'kolca', 'description' => 'Авторские кольца ручной работы'],
            ['name' => 'Подвески', 'slug' => 'podeski', 'description' => 'Уникальные подвески и кулоны'],
            ['name' => 'Серьги', 'slug' => 'sergi', 'description' => 'Элегантные серьги на любой случай'],
            ['name' => 'Броши', 'slug' => 'broshi', 'description' => 'Изысканные броши ручной работы'],
        ];
        $map = [];
        foreach ($data as $item) {
            $category = Category::firstOrCreate(['slug' => $item['slug']], ['name' => $item['name'], 'description' => $item['description']]);
            $map[$item['name']] = $category->id;
        }
        return $map;
    }

    private function seedMaterials(): array
    {
        $this->command->info('🧱 Создание материалов...');
        $data = [
            ['name' => 'Серебро 925', 'slug' => 'serebro-925', 'description' => 'Высококачественное серебро пробы 925'],
            ['name' => 'Золото 585', 'slug' => 'zoloto-585', 'description' => 'Классическое желтое золото 585 пробы'],
            ['name' => 'Латунь', 'slug' => 'latun', 'description' => 'Прочный и доступный сплав'],
        ];
        $map = [];
        foreach ($data as $item) {
            $material = Material::firstOrCreate(['slug' => $item['slug']], ['name' => $item['name'], 'description' => $item['description']]);
            $map[$item['name']] = $material->id;
        }
        return $map;
    }

    private function seedStones(): array
    {
        $this->command->info('💎 Создание камней...');
        $data = [
            ['name' => 'Фианит', 'slug' => 'fianit', 'color' => 'Прозрачный', 'description' => 'Искусственный кристалл с бриллиантовым блеском'],
            ['name' => 'Горный хрусталь', 'slug' => 'gorniy-hrustal', 'color' => 'Прозрачный', 'description' => 'Природный кварц'],
            ['name' => 'Голубой топаз', 'slug' => 'goluboy-topaz', 'color' => 'Голубой', 'description' => 'Полудрагоценный камень небесного цвета'],
            ['name' => 'Янтарь', 'slug' => 'yantar', 'color' => 'Жёлто-оранжевый', 'description' => 'Окаменевшая смола'],
            ['name' => 'Гранат', 'slug' => 'granat', 'color' => 'Красный', 'description' => 'Благородный камень насыщенного цвета'],
            ['name' => 'Лунный камень', 'slug' => 'lunnyy-kamen', 'color' => 'Белый с голубым отливом', 'description' => 'Полевой шпат с эффектом адуляризации'],
            ['name' => 'Зелёный агат', 'slug' => 'zelenyy-agat', 'color' => 'Зелёный', 'description' => 'Разновидность халцедона'],
            ['name' => 'Цитрин', 'slug' => 'citrin', 'color' => 'Жёлтый', 'description' => 'Золотистый кварц'],
            ['name' => 'Кошачий глаз', 'slug' => 'koshachiy-glaz', 'color' => 'Зелёно-золотистый', 'description' => 'Хризоберилл с эффектом кошачьего глаза'],
            ['name' => 'Розовый кварц', 'slug' => 'rozovyy-kvarc', 'color' => 'Розовый', 'description' => 'Камень любви и нежности'],
        ];
        $map = [];
        foreach ($data as $item) {
            $stone = Stone::firstOrCreate(['slug' => $item['slug']], ['name' => $item['name'], 'color' => $item['color'], 'description' => $item['description']]);
            $map[$item['name']] = $stone->id;
        }
        return $map;
    }

    // ========================================================================
    // ТОВАРЫ (Без изменений, кроме удаления image)
    // ========================================================================

    private function seedProducts(array $categories, array $materials, array $stones): void
    {
        $this->command->info('🛍️ Создание товаров...');

        $ringSizes = ['15.5', '16', '16.5', '17', '17.5', '18', '18.5', '19', '19.5', '20'];
        $generateSizes = function(int $min, int $max) use ($ringSizes): array {
            $result = [];
            foreach ($ringSizes as $size) {
                $result[$size] = rand($min, $max);
            }
            return $result;
        };

        $products = [
            // === КОЛЬЦА С КАМНЯМИ (10 шт.) ===
            [
                'name' => 'Кольцо "Северное сияние"', 'slug' => 'koltso-severnoe-siyanie',
                'description' => 'Серебряное кольцо с фианитом, напоминающим звёзды над панельным районом.',
                'price' => 6800.00, 'sizes' => $generateSizes(1, 4), 'stock' => 12,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Серебро 925'], 'stone_id' => $stones['Фианит'],
            ],
            [
                'name' => 'Кольцо "Иней на бетоне"', 'slug' => 'koltso-iney-na-betone',
                'description' => 'Минималистичное кольцо с прозрачным кристаллом, имитирующим утренний иней.',
                'price' => 7200.00, 'sizes' => $generateSizes(0, 5), 'stock' => 8,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Золото 585'], 'stone_id' => $stones['Горный хрусталь'],
            ],
            [
                'name' => 'Кольцо "Окно в детство"', 'slug' => 'koltso-okno-v-detstvo',
                'description' => 'Кольцо в виде оконной рамы с голубым камнем — как небо за стеклом хрущёвки.',
                'price' => 8500.00, 'sizes' => $generateSizes(2, 4), 'stock' => 6,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Серебро 925'], 'stone_id' => $stones['Голубой топаз'],
            ],
            [
                'name' => 'Кольцо "Трамвайный звонок"', 'slug' => 'koltso-tramvainyi-zvonok',
                'description' => 'Винтажное кольцо с янтарной вставкой, напоминающей свет в вагоне.',
                'price' => 9200.00, 'sizes' => $generateSizes(1, 3), 'stock' => 5,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Латунь'], 'stone_id' => $stones['Янтарь'],
            ],
            [
                'name' => 'Кольцо "Заводская искра"', 'slug' => 'koltso-zavodskaia-iskra',
                'description' => 'Брутальное кольцо с красным гранатом — как искра от сварки.',
                'price' => 10500.00, 'sizes' => $generateSizes(0, 3), 'stock' => 4,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Серебро 925'], 'stone_id' => $stones['Гранат'],
            ],
            [
                'name' => 'Кольцо "Лунная панелька"', 'slug' => 'koltso-lunnaia-panelka',
                'description' => 'Кольцо с лунным камнем, отражающим свет фонарей во дворе.',
                'price' => 8900.00, 'sizes' => $generateSizes(1, 4), 'stock' => 7,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Золото 585'], 'stone_id' => $stones['Лунный камень'],
            ],
            [
                'name' => 'Кольцо "Электричка"', 'slug' => 'koltso-elektrichka',
                'description' => 'Кольцо с зелёным камнем — цвет вагонов пригородных поездов.',
                'price' => 7800.00, 'sizes' => $generateSizes(2, 5), 'stock' => 9,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Серебро 925'], 'stone_id' => $stones['Зелёный агат'],
            ],
            [
                'name' => 'Кольцо "Подъездный свет"', 'slug' => 'koltso-podezdnyi-svet',
                'description' => 'Кольцо с жёлтым цитрином — как лампочка в подъезде.',
                'price' => 6500.00, 'sizes' => $generateSizes(1, 4), 'stock' => 11,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Серебро 925'], 'stone_id' => $stones['Цитрин'],
            ],
            [
                'name' => 'Кольцо "Дворовый кот"', 'slug' => 'koltso-dvorovyi-kot',
                'description' => 'Игривое кольцо с кошачьим глазом — талисман дворовых котов.',
                'price' => 7500.00, 'sizes' => $generateSizes(0, 4), 'stock' => 10,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Золото 585'], 'stone_id' => $stones['Кошачий глаз'],
            ],
            [
                'name' => 'Кольцо "Вечный рассвет"', 'slug' => 'koltso-vechnyi-rassvet',
                'description' => 'Романтичное кольцо с розовым кварцем — цвет неба на рассвете над спальным районом.',
                'price' => 9800.00, 'sizes' => $generateSizes(1, 3), 'stock' => 6,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Латунь'], 'stone_id' => $stones['Розовый кварц'],
            ],

            // === КОЛЬЦА БЕЗ КАМНЕЙ (10 шт.) ===
            [
                'name' => 'Кольцо "Панелька"', 'slug' => 'koltso-panelka',
                'description' => 'Кольцо в виде типовой панельной многоэтажки.',
                'price' => 4500.00, 'sizes' => $generateSizes(2, 6), 'stock' => 15,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Кольцо "РАССВЕТ"', 'slug' => 'koltso-rassvet',
                'description' => 'Кольцо с надписью "РАССВЕТ" в стиле советских вывесок.',
                'price' => 5200.00, 'sizes' => $generateSizes(1, 4), 'stock' => 12,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Золото 585'], 'stone_id' => null,
            ],
            [
                'name' => 'Кольцо "Электросила"', 'slug' => 'koltso-elektrosila',
                'description' => 'Индустриальное кольцо с элементами электрических столбов.',
                'price' => 6800.00, 'sizes' => $generateSizes(0, 3), 'stock' => 8,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Кольцо "Окна во двор"', 'slug' => 'koltso-okna-vo-dvor',
                'description' => 'Минималистичное кольцо с силуэтами окон хрущёвки.',
                'price' => 3900.00, 'sizes' => $generateSizes(3, 7), 'stock' => 20,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Кольцо "Труба ЦТП"', 'slug' => 'koltso-truba-tstp',
                'description' => 'Брутальное кольцо в виде трубы теплопункта.',
                'price' => 7500.00, 'sizes' => $generateSizes(1, 3), 'stock' => 5,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Золото 585'], 'stone_id' => null,
            ],
            [
                'name' => 'Кольцо "Подъезд 4"', 'slug' => 'koltso-podezd-4',
                'description' => 'Кольцо с табличкой "ПОДЪЕЗД 4".',
                'price' => 4800.00, 'sizes' => $generateSizes(2, 5), 'stock' => 10,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Кольцо "Заводской двор"', 'slug' => 'koltso-zavodskoi-dvor',
                'description' => 'Кольцо с элементами заводских ворот.',
                'price' => 5900.00, 'sizes' => $generateSizes(0, 4), 'stock' => 7,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Латунь'], 'stone_id' => null,
            ],
            [
                'name' => 'Кольцо "Лестничная клетка"', 'slug' => 'koltso-lestnichnaia-kletka',
                'description' => 'Геометрическое кольцо с паттерном лестниц.',
                'price' => 4200.00, 'sizes' => $generateSizes(2, 6), 'stock' => 18,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Кольцо "Спутник"', 'slug' => 'koltso-sputnik',
                'description' => 'Ретро-кольцо в виде спутника связи.',
                'price' => 5500.00, 'sizes' => $generateSizes(1, 4), 'stock' => 9,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Золото 585'], 'stone_id' => null,
            ],
            [
                'name' => 'Кольцо "Трамвайное депо"', 'slug' => 'koltso-tramvainoe-depo',
                'description' => 'Кольцо с силуэтом трамвайного депо.',
                'price' => 6200.00, 'sizes' => $generateSizes(0, 3), 'stock' => 6,
                'category_id' => $categories['Кольца'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],

            // === ПОДВЕСКИ (8 шт.) ===
            [
                'name' => 'Подвеска "Ключ от квартиры"', 'slug' => 'podeska-kliuch-ot-kvartiry',
                'description' => 'Подвеска в виде старого советского ключа.',
                'price' => 3800.00, 'sizes' => null, 'stock' => 25,
                'category_id' => $categories['Подвески'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Подвеска "ЭЛЕКТРОСНАБЖЕНИЕ"', 'slug' => 'podeska-elektrosnabzhenie',
                'description' => 'Подвеска с табличкой "ЭЛЕКТРОСНАБЖЕНИЕ".',
                'price' => 4500.00, 'sizes' => null, 'stock' => 14,
                'category_id' => $categories['Подвески'], 'material_id' => $materials['Золото 585'], 'stone_id' => null,
            ],
            [
                'name' => 'Подвеска "Кирпичная кладка"', 'slug' => 'podeska-kirpichnaia-kladka',
                'description' => 'Подвеска с текстурой кирпичной кладки.',
                'price' => 4200.00, 'sizes' => null, 'stock' => 11,
                'category_id' => $categories['Подвески'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Подвеска "Теплосеть"', 'slug' => 'podeska-teploset',
                'description' => 'Индустриальная подвеска с элементами теплотрассы.',
                'price' => 3500.00, 'sizes' => null, 'stock' => 22,
                'category_id' => $categories['Подвески'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Подвеска "Номер дома"', 'slug' => 'podeska-nomer-doma',
                'description' => 'Подвеска в виде эмалевой таблички с номером дома.',
                'price' => 4900.00, 'sizes' => null, 'stock' => 16,
                'category_id' => $categories['Подвески'], 'material_id' => $materials['Латунь'], 'stone_id' => null,
            ],
            [
                'name' => 'Подвеска "Крыша пятиэтажки"', 'slug' => 'podeska-krysha-piatietazhki',
                'description' => 'Минималистичная подвеска с силуэтом крыши.',
                'price' => 3200.00, 'sizes' => null, 'stock' => 30,
                'category_id' => $categories['Подвески'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Подвеска "Железнодорожный мост"', 'slug' => 'podeska-zheleznodorozhnyi-most',
                'description' => 'Подвеска с элементами железнодорожного моста.',
                'price' => 5800.00, 'sizes' => null, 'stock' => 8,
                'category_id' => $categories['Подвески'], 'material_id' => $materials['Золото 585'], 'stone_id' => null,
            ],
            [
                'name' => 'Подвеска "Вентиляционная труба"', 'slug' => 'podeska-ventiliatsionnaia-truba',
                'description' => 'Брутальная подвеска в виде вентиляционной трубы.',
                'price' => 4100.00, 'sizes' => null, 'stock' => 13,
                'category_id' => $categories['Подвески'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],

            // === СЕРЬГИ (7 шт.) ===
            [
                'name' => 'Серьги "РАССВЕТ"', 'slug' => 'sergi-rassvet',
                'description' => 'Серьги в виде миниатюрных вывесок "РАССВЕТ".',
                'price' => 5500.00, 'sizes' => null, 'stock' => 10,
                'category_id' => $categories['Серьги'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Серьги "Окна"', 'slug' => 'sergi-okna',
                'description' => 'Серьги в виде оконных рам с переплётами.',
                'price' => 4800.00, 'sizes' => null, 'stock' => 15,
                'category_id' => $categories['Серьги'], 'material_id' => $materials['Золото 585'], 'stone_id' => null,
            ],
            [
                'name' => 'Серьги "Трамвайные рельсы"', 'slug' => 'sergi-tramvainye-relsy',
                'description' => 'Лёгкие серьги в виде трамвайных рельсов.',
                'price' => 4200.00, 'sizes' => null, 'stock' => 18,
                'category_id' => $categories['Серьги'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Серьги "Кирпичи"', 'slug' => 'sergi-kirpichi',
                'description' => 'Серьги с текстурой кирпичной кладки.',
                'price' => 3900.00, 'sizes' => null, 'stock' => 20,
                'category_id' => $categories['Серьги'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Серьги "Антенна"', 'slug' => 'sergi-antenna',
                'description' => 'Серьги в виде телевизионных антенн.',
                'price' => 4600.00, 'sizes' => null, 'stock' => 12,
                'category_id' => $categories['Серьги'], 'material_id' => $materials['Латунь'], 'stone_id' => null,
            ],
            [
                'name' => 'Серьги "Решётка балкона"', 'slug' => 'sergi-reshetka-balkona',
                'description' => 'Серьги с узором кованых балконных решёток.',
                'price' => 5200.00, 'sizes' => null, 'stock' => 9,
                'category_id' => $categories['Серьги'], 'material_id' => $materials['Золото 585'], 'stone_id' => null,
            ],
            [
                'name' => 'Серьги "Светофор"', 'slug' => 'sergi-svetofor',
                'description' => 'Серьги в виде миниатюрных светофоров.',
                'price' => 6100.00, 'sizes' => null, 'stock' => 7,
                'category_id' => $categories['Серьги'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],

            // === БРОШИ (5 шт.) ===
            [
                'name' => 'Брошь "ЭЛЕКТРОЦЕХ"', 'slug' => 'brosh-elektrotsekh',
                'description' => 'Брошь с красной табличкой "ЭЛЕКТРОЦЕХ".',
                'price' => 3500.00, 'sizes' => null, 'stock' => 25,
                'category_id' => $categories['Броши'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Брошь "Проходная"', 'slug' => 'brosh-prokhodnaia',
                'description' => 'Брошь в виде проходной завода.',
                'price' => 4200.00, 'sizes' => null, 'stock' => 15,
                'category_id' => $categories['Броши'], 'material_id' => $materials['Золото 585'], 'stone_id' => null,
            ],
            [
                'name' => 'Брошь "Табличка подъезда"', 'slug' => 'brosh-tablichka-podezda',
                'description' => 'Брошь с номером подъезда.',
                'price' => 3800.00, 'sizes' => null, 'stock' => 0,
                'category_id' => $categories['Броши'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
            [
                'name' => 'Брошь "Заводской знак"', 'slug' => 'brosh-zavodskoi-znak',
                'description' => 'Брошь в виде заводского знака.',
                'price' => 4500.00, 'sizes' => null, 'stock' => 0,
                'category_id' => $categories['Броши'], 'material_id' => $materials['Латунь'], 'stone_id' => null,
            ],
            [
                'name' => 'Брошь "Остановка"', 'slug' => 'brosh-ostanovka',
                'description' => 'Брошь с силуэтом автобусной остановки.',
                'price' => 3200.00, 'sizes' => null, 'stock' => 0,
                'category_id' => $categories['Броши'], 'material_id' => $materials['Серебро 925'], 'stone_id' => null,
            ],
        ];

        foreach ($products as $productData) {
            Product::updateOrCreate(['slug' => $productData['slug']], $productData);
        }
        $this->command->info("   ✓ Создано " . count($products) . " товаров");
    }
}