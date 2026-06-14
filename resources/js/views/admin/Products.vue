<template>
  <!-- Корневой контейнер страницы управления товарами с отступами между секциями -->
  <div class="space-y-6">
    
    <!-- Заголовок страницы и панель действий: кнопки добавления товара и экспорта -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <h1 class="text-3xl font-bold text-gray-900">Товары</h1>
      <div class="flex flex-wrap gap-3">
        
        <!-- Кнопка скачивания каталога товаров в формате Excel -->
        <button 
          @click="downloadProductsExcel" 
          :disabled="loading"
          class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2 transition font-medium text-sm"
          title="Скачать каталог товаров в формате Excel (.xlsx)"
        >
          <!-- Иконка файла (SVG) -->
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Скачать Excel
        </button>
        
        <!-- Кнопка открытия модального окна для добавления нового товара -->
        <button 
          @click="showAddModal = true"
          class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition shadow-md"
        >
          + Добавить товар
        </button>
      </div>
    </div>

    <!-- Блок фильтров и поиска: сетка с полями для фильтрации списка товаров -->
    <div class="bg-white rounded-xl shadow p-6 border border-gray-100">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Поле поиска по названию и описанию товара -->
        <div class="lg:col-span-2">
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Поиск</label>
          <div class="flex gap-2">
            <input 
              v-model="filters.search" 
              type="text" 
              placeholder="Название, описание..." 
              class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition"
              @keyup.enter="applyFilters"
            >
            <!-- Кнопка очистки поиска: показывается только при введённом запросе -->
            <button 
              v-if="filters.search" 
              @click="clearFilter('search')" 
              class="px-3 py-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
              title="Очистить поиск"
            >
              ✕
            </button>
          </div>
        </div>

        <!-- Фильтр по категории товара: выпадающий список -->
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Категория</label>
          <select 
            v-model="filters.category" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition"
            @change="applyFilters"
          >
            <option value="">Все категории</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
          </select>
        </div>

        <!-- Фильтр по материалу: выпадающий список -->
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Материал</label>
          <select 
            v-model="filters.material" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition"
            @change="applyFilters"
          >
            <option value="">Все материалы</option>
            <option v-for="mat in materials" :key="mat.id" :value="mat.id">{{ mat.name }}</option>
          </select>
        </div>
      </div>

      <!-- Второй ряд фильтров: камень, наличие, диапазон цен -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
        
        <!-- Фильтр по камню: показывается только если есть загруженные камни -->
        <div v-if="stones.length > 0">
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Камень</label>
          <select 
            v-model="filters.stone" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition"
            @change="applyFilters"
          >
            <option value="">Все камни</option>
            <option v-for="stone in stones" :key="stone.id" :value="stone.id">{{ stone.name }}</option>
          </select>
        </div>

        <!-- Фильтр по наличию товара на складе -->
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Наличие</label>
          <select 
            v-model="filters.stock" 
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition"
            @change="applyFilters"
          >
            <option value="">Все товары</option>
            <option value="in_stock">В наличии</option>
            <option value="out_of_stock">Нет в наличии</option>
          </select>
        </div>

        <!-- Фильтр: минимальная цена -->
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Цена от (₽)</label>
          <input 
            v-model.number="filters.priceMin" 
            type="number" 
            placeholder="0" 
            min="0"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition"
            @change="applyFilters"
          >
        </div>

        <!-- Фильтр: максимальная цена -->
        <div>
          <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Цена до (₽)</label>
          <input 
            v-model.number="filters.priceMax" 
            type="number" 
            placeholder="100000" 
            min="0"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition"
            @change="applyFilters"
          >
        </div>
      </div>

      <!-- Панель сортировки и отображение активных фильтров -->
      <div class="mt-4 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
          <label class="text-xs font-bold text-gray-500 uppercase tracking-wider">Сортировка:</label>
          <select 
            v-model="filters.sortBy" 
            class="px-7 py-1.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-red-500 outline-none transition"
            @change="applyFilters"
          >
            <option value="name_asc">По названию (А-Я)</option>
            <option value="name_desc">По названию (Я-А)</option>
            <option value="price_asc">По цене (возр.)</option>
            <option value="price_desc">По цене (убыв.)</option>
            <option value="stock_desc">По остатку (убыв.)</option>
            <option value="created_desc">Сначала новые</option>
          </select>
        </div>

        <!-- Чипсы активных фильтров: показывают применённые параметры и позволяют быстро их удалить -->
        <div v-if="hasActiveFilters" class="flex flex-wrap gap-2">
          <span 
            v-for="(label, key) in activeFilterLabels" 
            :key="key"
            class="inline-flex items-center gap-1 px-3 py-1 bg-red-100 text-red-700 text-xs rounded-full font-medium"
          >
            {{ label }}
            <button @click="clearFilter(key)" class="hover:text-red-900 font-bold ml-1">×</button>
          </span>
        </div>

        <!-- Кнопка сброса всех фильтров: показывается при наличии активных фильтров -->
        <button 
          v-if="hasActiveFilters"
          @click="resetFilters" 
          class="text-sm text-gray-500 hover:text-gray-700 underline transition"
        >
          Сбросить все
        </button>
      </div>

      <!-- Статистика: количество найденных товаров после применения фильтров -->
      <p class="text-sm text-gray-500 mt-3">
        Найдено: <span class="font-semibold text-gray-900">{{ products.length }}</span> товаров
      </p>
    </div>

    <!-- Индикатор загрузки: показывается во время выполнения асинхронного запроса -->
    <div v-if="loading" class="text-center py-12">
      <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-red-600"></div>
    </div>

    <!-- Сообщение об ошибке: показывается при неудачном запросе к API -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
      {{ error }}
    </div>

    <!-- Таблица товаров: список с основной информацией и действиями -->
    <div v-else class="bg-white rounded-xl shadow overflow-hidden border border-gray-100">
      <table class="w-full">
        <!-- Заголовки столбцов таблицы -->
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Фото</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Название</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Категория</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Материал</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Камень</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Цена</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Остаток</th>
            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Действия</th>
          </tr>
        </thead>
        
        <!-- Тело таблицы: список товаров с данными -->
        <tbody class="divide-y divide-gray-200">
          <tr v-for="product in products" :key="product.id" class="hover:bg-gray-50 transition-colors">
            
            <!-- Изображение товара: превью с заглушкой при отсутствии фото -->
            <td class="px-6 py-4">
              <img 
                v-if="product.image_url || product.image" 
                :src="product.image_url || `/storage/${product.image}`" 
                class="w-16 h-16 object-cover rounded-lg border border-gray-200"
                alt="Product"
              >
              <div v-else class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center text-gray-400">
                <!-- Иконка заглушки (отсутствующее изображение, SVG) -->
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </td>
            
            <!-- Название товара -->
            <td class="px-6 py-4 font-medium text-gray-900">{{ product.name }}</td>
            
            <!-- Категория товара: поддержка разных форматов данных от сервера -->
            <td class="px-6 py-4 text-gray-600">{{ product.category?.name || product.category_name || '—' }}</td>
            
            <!-- Материал товара -->
            <td class="px-6 py-4 text-gray-600">{{ product.material?.name || '—' }}</td>
            
            <!-- Камень: отображается как цветной бейдж, если указан -->
            <td class="px-6 py-4">
              <span v-if="product.stone" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                {{ product.stone.name }}
              </span>
              <span v-else class="text-gray-400 text-sm">—</span>
            </td>
            
            <!-- Цена товара с форматированием -->
            <td class="px-6 py-4 font-semibold">{{ formatPrice(product.price) }}</td>
            
            <!-- Остаток товара: разная логика для товаров с размерами и без -->
            <td class="px-6 py-4">
              <!-- Вариант А: Товар с размерами (кольца) — отображаем сетку размеров -->
              <div v-if="product.sizes && Object.keys(product.sizes).length > 0">
                <div class="flex flex-wrap gap-1 mb-1">
                  <span 
                    v-for="(qty, size) in product.sizes" 
                    :key="size"
                    :class="qty > 0 ? 'bg-green-100 text-green-800 border-green-200' : 'bg-red-100 text-red-800 border-red-200'"
                    class="px-2 py-0.5 rounded text-xs font-medium border"
                    :title="`Размер ${size}: ${qty} шт.`"
                  >
                    {{ size }}: {{ qty }}
                  </span>
                </div>
                <div class="text-xs text-gray-500 font-medium">
                  Всего: {{ getTotalStock(product.sizes) }} шт.
                </div>
              </div>
              
              <!-- Вариант Б: Товар без размеров — отображаем общее количество -->
              <div v-else-if="product.stock !== undefined">
                <span :class="product.stock > 0 ? 'text-green-600 font-bold' : 'text-red-600 font-bold'">
                  {{ product.stock }} шт.
                </span>
              </div>
              
              <!-- Заглушка при отсутствии данных об остатке -->
              <div v-else class="text-gray-400 text-sm">—</div>
            </td>

            <!-- Кнопки действий: редактирование и удаление -->
            <td class="px-6 py-4">
              <button 
                @click="editProduct(product)"
                class="text-blue-600 hover:text-blue-800 mr-3 font-medium text-sm transition"
              >
                Редактировать
              </button>
              <button 
                @click="deleteProduct(product.id)"
                class="text-red-600 hover:text-red-800 font-medium text-sm transition"
              >
                Удалить
              </button>
            </td>
          </tr>
          
          <!-- Сообщение при пустом результате: разные тексты для фильтров и полного отсутствия данных -->
          <tr v-if="products.length === 0">
            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
              <div v-if="hasActiveFilters">
                Товары не найдены по заданным фильтрам
              </div>
              <div v-else>
                Товары не найдены
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Модальное окно: форма добавления/редактирования товара -->
    <div v-if="showAddModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 backdrop-blur-sm">
      <div class="bg-white rounded-xl p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto shadow-2xl">
        <h2 class="text-2xl font-bold mb-6 text-gray-900">{{ editingProduct ? 'Редактировать товар' : 'Добавить товар' }}</h2>
      
        <!-- Форма товара: отправка предотвращает перезагрузку страницы -->
        <form @submit.prevent="saveProduct" class="space-y-5">
          
          <!-- Поле: Название товара (обязательное) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Название *</label>
            <input v-model="form.name" type="text" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition">
          </div>

          <!-- Сетка: Цена и Остаток -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Цена (₽) *</label>
              <input v-model.number="form.price" type="number" step="0.01" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition">
            </div>
          
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ isRingCategory ? 'Общий остаток (авто)' : 'Количество на складе *' }}
              </label>
            
              <!-- Для колец: поле только для чтения, значение считается автоматически из размеров -->
              <input 
                v-if="isRingCategory"
                v-model.number="form.totalStock" 
                type="number" 
                readonly
                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed"
                title="Сумма всех размеров"
              />

              <!-- Для остальных товаров: обычное поле ввода количества -->
              <input 
                v-else
                v-model.number="form.simpleStock" 
                type="number" 
                min="0"
                required
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition"
                placeholder="Например: 20"
              />
            </div>
          </div>

          <!-- Сетка размеров: показывается только для категории "Кольца" -->
          <div v-if="isRingCategory" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
            <label class="block text-sm font-medium text-gray-700 mb-3">
              Остатки по размерам
              <span class="text-gray-400 font-normal ml-1 text-xs">(Заполните для каждого размера)</span>
            </label>
         
            <!-- Сетка инпутов для каждого размера кольца -->
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-3">
              <div v-for="size in availableSizes" :key="size" class="flex flex-col items-center gap-1">
                <label class="text-xs text-gray-500 font-medium">{{ size }}</label>
                <input 
                  v-model.number="form.sizes[size]"
                  type="number" 
                  min="0"
                  placeholder="0"
                  class="w-full px-2 py-1.5 border border-gray-300 rounded-md focus:ring-2 focus:ring-red-500 outline-none text-center text-sm transition"
                  @input="recalculateTotalStock"
                >
              </div>
            </div>
         
            <p class="mt-3 text-xs text-gray-500 italic">
              Сумма размеров автоматически попадает в "Общий остаток".
            </p>
          </div>

          <!-- Сетка: Категория и Материал -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Категория</label>
              <select v-model.number="form.category_id" @change="onCategoryChange" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition">
                <option :value="null">Без категории</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Материал</label>
              <select v-model.number="form.material_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition">
                <option :value="null">Без материала</option>
                <option v-for="mat in materials" :key="mat.id" :value="mat.id">{{ mat.name }}</option>
              </select>
            </div>
          </div>

          <!-- Поле: Камень (показывается только для колец) -->
          <div v-if="isRingCategory">
            <label class="block text-sm font-medium text-gray-700 mb-1">Камень</label>
            <select v-model.number="form.stone_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition">
              <option :value="null">Без камня</option>
              <option v-for="stone in stones" :key="stone.id" :value="stone.id">
                {{ stone.name }} {{ stone.color ? `(${stone.color})` : '' }}
              </option>
            </select>
          </div>

          <!-- Поле: Описание товара (многострочное) -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Описание</label>
            <textarea v-model="form.description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 outline-none transition"></textarea>
          </div>

          <!-- Секция: Изображения товара -->
          <div class="border-t border-gray-200 pt-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Изображения товара</label>
         
            <!-- Блок: Уже загруженные изображения -->
            <div v-if="existingImages.length > 0" class="mb-4">
              <p class="text-xs text-gray-500 mb-2">Текущие изображения:</p>
              <div class="grid grid-cols-4 gap-3">
                <div 
                  v-for="(img, index) in existingImages" 
                  :key="img.id || index"
                  class="relative aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200 group"
                >
                  <img :src="img.url || `/storage/${img.path}`" class="w-full h-full object-cover">
                  <!-- Кнопка удаления изображения: показывается при наведении -->
                  <button 
                    type="button"
                    @click="removeExistingImage(img.id)"
                    class="absolute top-1 right-1 w-6 h-6 bg-red-600 text-white rounded-full text-xs flex items-center justify-center hover:bg-red-700 shadow opacity-0 group-hover:opacity-100 transition"
                  >
                    ×
                  </button>
                  <!-- Метка главного изображения -->
                  <div v-if="img.is_main" class="absolute bottom-1 left-1 px-2 py-1 bg-black/70 text-white text-[10px] rounded">
                    Главное
                  </div>
                </div>
              </div>
            </div>

            <!-- Блок: Загрузка новых изображений -->
            <div class="border-t border-gray-200 pt-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Добавить новые изображения</label>
              <!-- Input для выбора файлов: множественный выбор, только изображения -->
              <input type="file" @change="handleFile" accept="image/*" multiple class="w-full px-4 py-2 border border-gray-300 rounded-lg file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100">
              <p class="mt-1 text-xs text-gray-500">Можно выбрать до 4 файлов</p>
           
              <!-- Превью выбранных файлов перед отправкой -->
              <div v-if="form.image_previews && form.image_previews.length" class="grid grid-cols-4 gap-3 mt-3">
                <div v-for="(preview, index) in form.image_previews" :key="`new-${index}`" class="relative aspect-square bg-gray-100 rounded-lg overflow-hidden border border-gray-200">
                  <img :src="preview" class="w-full h-full object-cover">
                  <!-- Кнопка удаления превью -->
                  <button type="button" @click="removePreview(index)" class="absolute -top-2 -right-2 w-6 h-6 bg-red-600 text-white rounded-full text-xs flex items-center justify-center hover:bg-red-700 shadow-lg">×</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Кнопки действий формы: Сохранить и Отмена -->
          <div class="flex gap-4 pt-4 border-t border-gray-200">
            <button type="submit" :disabled="saving" class="flex-1 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-md">
              {{ saving ? 'Сохранение...' : 'Сохранить' }}
            </button>
            <button type="button" @click="closeModal" class="flex-1 py-3 border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition">
              Отмена
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
// Импорт реактивных хуков Vue и библиотеки для HTTP-запросов
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// === STATE: Реактивные переменные состояния компонента ===

// Массив товаров для отображения в таблице
const products = ref([]);

// Справочники: категории, материалы, камни (для выпадающих списков фильтров и форм)
const categories = ref([]);
const materials = ref([]);
const stones = ref([]);

// Флаги состояния загрузки и сохранения
const loading = ref(true);
const saving = ref(false);

// Текст ошибки для отображения пользователю
const error = ref('');

// Управление модальным окном и режимом редактирования
const showAddModal = ref(false);
const editingProduct = ref(null);

// Выбранные файлы изображений и существующие изображения товара
const selectedFiles = ref([]);
const existingImages = ref([]);

// Стандартные размеры колец для генерации сетки инпутов
const availableSizes = ['16', '16.5', '17', '17.5', '18', '18.5', '19', '19.5', '20', '20.5', '21', '21.5', '22', '22.5', '23', '23.5', '24'];

// Объект формы для добавления/редактирования товара
const form = ref({
  name: '',
  price: 0,
  totalStock: 0,      // Используется для колец: сумма всех размеров
  simpleStock: 0,     // Используется для остальных товаров: прямое количество
  sizes: {},          // JSON-объект с остатками по размерам (для колец)
  description: '',
  category_id: null,
  material_id: null,
  stone_id: null,
  image_previews: [], // Массив URL для превью выбранных файлов
});

// Объект фильтров для серверной фильтрации списка товаров
const filters = ref({
  search: '',
  category: '',
  material: '',
  stone: '',
  stock: '',
  priceMin: null,
  priceMax: null,
  sortBy: 'created_desc', // По умолчанию: новые товары первыми
});

// === ВЫЧИСЛЯЕМЫЕ СВОЙСТВА И ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ ===

// Пересчёт общего остатка для колец: сумма всех значений в объекте sizes
const recalculateTotalStock = () => {
  let total = 0;
  for (const size in form.value.sizes) {
    const qty = parseInt(form.value.sizes[size]) || 0;
    total += qty;
  }
  form.value.totalStock = total;
};

// Подсчёт общего количества из объекта размеров (для отображения в таблице)
const getTotalStock = (sizes) => {
  if (!sizes) return 0;
  let total = 0;
  for (const size in sizes) {
    total += parseInt(sizes[size]) || 0;
  }
  return total;
};

// Проверка: является ли выбранная категорией "Кольца" (по названию)
const isRingCategory = computed(() => {
  const cat = categories.value.find(c => c.id === form.value.category_id);
  return cat?.name?.toLowerCase().includes('кольц'); 
});

// Обработчик смены категории: сбрасывает камень, если категория не "Кольца"
const onCategoryChange = () => {
  if (!isRingCategory.value) {
    form.value.stone_id = null;
  }
};

// Форматирование цены в строку с валютой и разделителями тысяч
const formatPrice = (value) => {
  try {
    if (value === undefined || value === null || value === '') return '0 ₽';
    const num = typeof value === 'number' ? value : parseFloat(value);
    if (isNaN(num)) return '0 ₽';
    return new Intl.NumberFormat('ru-RU', {
      style: 'currency',
      currency: 'RUB',
      minimumFractionDigits: 0,
      maximumFractionDigits: 2
    }).format(num);
  } catch (e) {
    return '0 ₽';
  }
};

// === API-МЕТОДЫ: Асинхронные запросы к серверу ===

// Загрузка полных данных товара по ID (для редактирования)
const loadFullProduct = async (productId) => {
  try {
    const token = localStorage.getItem('api_token');
    const res = await axios.get(`http://127.0.0.1:8000/api/admin/products/${productId}`, {
      headers: { 
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });
    return res.data;
  } catch (err) {
    console.error('Ошибка загрузки товара:', err);
    throw err;
  }
};

// Загрузка справочников (категории, материалы, камни) — выполняется один раз при старте
const loadMeta = async () => {
  try {
    const token = localStorage.getItem('api_token');
    const [cats, mats, stn] = await Promise.all([
      axios.get('http://127.0.0.1:8000/api/admin/categories', { headers: { 'Authorization': `Bearer ${token}` } }),
      axios.get('http://127.0.0.1:8000/api/admin/materials', { headers: { 'Authorization': `Bearer ${token}` } }),
      axios.get('http://127.0.0.1:8000/api/admin/stones', { headers: { 'Authorization': `Bearer ${token}` } }),
    ]);
    // Нормализация ответов: поддержка разных форматов данных от сервера
    categories.value = cats.data.data || cats.data || [];
    materials.value = mats.data.data || mats.data || [];
    stones.value = stn.data.data || stn.data || [];
  } catch (e) { console.error('Ошибка загрузки справочников', e); }
};

// Загрузка списка товаров с применением текущих фильтров и пагинации
const loadProducts = async () => {
  loading.value = true;
  error.value = '';
  try {
    const token = localStorage.getItem('api_token');
    
    // Формирование параметров запроса: убираем неопределённые значения
    const params = {
      page: 1, per_page: 50,
      search: filters.value.search || undefined,
      category_id: filters.value.category || undefined,
      material_id: filters.value.material || undefined,
      stone_id: filters.value.stone || undefined,
      stock: filters.value.stock || undefined,
      price_min: filters.value.priceMin || undefined,
      price_max: filters.value.priceMax || undefined,
      sort_by: filters.value.sortBy || 'created_desc',
    };
    Object.keys(params).forEach(key => params[key] === undefined && delete params[key]);

    const res = await axios.get('http://127.0.0.1:8000/api/admin/products', {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      params
    });
    products.value = res.data.data || res.data || [];
  } catch (err) {
    console.error('Ошибка загрузки товаров:', err);
    // Обработка ошибок авторизации
    if (err.response?.status === 401) { error.value = 'Сессия истекла'; localStorage.removeItem('api_token'); }
    else error.value = 'Ошибка загрузки';
  } finally { loading.value = false; }
};

// Инициализация: параллельная загрузка справочников и списка товаров
const loadData = async () => {
  await Promise.all([loadMeta(), loadProducts()]);
};

// Применение фильтров: перезагружает только список товаров (не справочники)
const applyFilters = () => { loadProducts(); };

// Очистка отдельного фильтра по ключу
const clearFilter = (key) => {
  if (key === 'search') filters.value.search = '';
  else if (key === 'category') filters.value.category = '';
  else if (key === 'material') filters.value.material = '';
  else if (key === 'stone') filters.value.stone = '';
  else if (key === 'stock') filters.value.stock = '';
  else if (key === 'priceMin') filters.value.priceMin = null;
  else if (key === 'priceMax') filters.value.priceMax = null;
  else if (key === 'sortBy') filters.value.sortBy = 'created_desc';
  
  applyFilters(); 
};

// Сброс всех фильтров к значениям по умолчанию
const resetFilters = () => {
  filters.value = { search: '', category: '', material: '', stone: '', stock: '', priceMin: null, priceMax: null, sortBy: 'created_desc' };
  applyFilters();
};

// Проверка: есть ли активные фильтры (для отображения чипсов и кнопки сброса)
const hasActiveFilters = computed(() => {
  return filters.value.search || filters.value.category || filters.value.material || filters.value.stone || filters.value.stock || filters.value.priceMin !== null || filters.value.priceMax !== null || filters.value.sortBy !== 'created_desc';
});

// Формирование читаемых меток для активных фильтров (для чипсов)
const activeFilterLabels = computed(() => {
  const labels = {};
  if (filters.value.search) labels.search = `Поиск: "${filters.value.search}"`;
  if (filters.value.category) { const cat = categories.value.find(c => c.id == filters.value.category); labels.category = `Категория: ${cat?.name}`; }
  if (filters.value.material) { const mat = materials.value.find(m => m.id == filters.value.material); labels.material = `Материал: ${mat?.name}`; }
  if (filters.value.stone) { const stone = stones.value.find(s => s.id == filters.value.stone); labels.stone = `Камень: ${stone?.name}`; }
  if (filters.value.stock === 'in_stock') labels.stock = 'В наличии';
  else if (filters.value.stock === 'out_of_stock') labels.stock = 'Нет в наличии';
  if (filters.value.priceMin) labels.priceMin = `От ${filters.value.priceMin} ₽`;
  if (filters.value.priceMax) labels.priceMax = `До ${filters.value.priceMax} ₽`;
  return labels;
});

// === ЛОГИКА МОДАЛЬНОГО ОКНА И РАБОТЫ С ИЗОБРАЖЕНИЯМИ ===

// Загрузка списка изображений товара по ID
const loadProductImages = async (productId) => {
  try {
    const token = localStorage.getItem('api_token');
    const res = await axios.get(`http://127.0.0.1:8000/api/admin/products/${productId}/images`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    if (res.data.success && res.data.images) {
      existingImages.value = res.data.images;
    }
  } catch (err) {
    console.error('Ошибка загрузки изображений:', err);
    existingImages.value = [];
  }
};

// Обработчик выбора файлов: создаёт превью и ограничивает количество до 4
const handleFile = (e) => {
  const files = Array.from(e.target.files);
  if (files.length > 0) {
    const limitedFiles = files.slice(0, 4);
    selectedFiles.value = limitedFiles;
    form.value.image_previews = limitedFiles.map(file => URL.createObjectURL(file));
  }
};

// Удаление превью: освобождает память и обновляет массивы
const removePreview = (index) => {
  URL.revokeObjectURL(form.value.image_previews[index]);
  form.value.image_previews.splice(index, 1);
  selectedFiles.value.splice(index, 1);
};

// Удаление существующего изображения через API
const removeExistingImage = async (imageId) => {
  if (!confirm('Удалить это изображение?')) return;
  try {
    const token = localStorage.getItem('api_token');
    await axios.delete(`http://127.0.0.1:8000/api/admin/images/${imageId}`, {
      headers: { 'Authorization': `Bearer ${token}` }
    });
    existingImages.value = existingImages.value.filter(img => img.id !== imageId);
  } catch (err) {
    console.error('Ошибка удаления изображения:', err);
    alert('Не удалось удалить изображение');
  }
};

// Открытие модального окна в режиме редактирования: загрузка полных данных товара
const editProduct = async (product) => {
  try {
    editingProduct.value = product;
    const fullProduct = await loadFullProduct(product.id);
    
    // Парсинг поля sizes: поддержка строки JSON и объекта
    let sizes = {};
    if (fullProduct.sizes) {
      if (typeof fullProduct.sizes === 'object' && !Array.isArray(fullProduct.sizes)) {
        sizes = { ...fullProduct.sizes };
      } else if (typeof fullProduct.sizes === 'string') {
        try { sizes = JSON.parse(fullProduct.sizes); } catch (e) { console.error('Ошибка парсинга sizes:', e); sizes = {}; }
      }
    } 
    
    // Заполнение формы данными товара
    form.value = {
      name: fullProduct.name || '',
      price: fullProduct.price || 0,
      totalStock: getTotalStock(sizes),
      simpleStock: fullProduct.stock || 0,
      sizes: sizes,
      description: fullProduct.description || '',
      category_id: fullProduct.category_id || null,
      material_id: fullProduct.material_id || null,
      stone_id: fullProduct.stone_id || null,
      image_previews: [],
    };
    selectedFiles.value = [];
    existingImages.value = [];
    showAddModal.value = true;
    await loadProductImages(product.id);
  } catch (err) {
    console.error('Ошибка при редактировании товара:', err);
    alert('Не удалось загрузить данные товара');
  }
}; 

// Закрытие модального окна: очистка формы и освобождение ресурсов превью
const closeModal = () => {
  if (form.value.image_previews) {
    form.value.image_previews.forEach(url => { URL.revokeObjectURL(url); });
  }
  showAddModal.value = false;
  editingProduct.value = null;
  form.value = { name: '', price: 0, totalStock: 0, simpleStock: 0, sizes: {}, description: '', category_id: null, material_id: null, stone_id: null, image_previews: [] };
  selectedFiles.value = [];
  existingImages.value = [];
};

// Сохранение товара: создание или обновление через API с поддержкой загрузки файлов
const saveProduct = async () => {
  saving.value = true;
  const formData = new FormData();
  formData.append('name', form.value.name);
  formData.append('price', form.value.price);

  // Логика остатков: для колец — размеры + сумма, для остальных — прямое значение
  if (isRingCategory.value) {
    formData.append('sizes', JSON.stringify(form.value.sizes));
    formData.append('stock', form.value.totalStock);
  } else {
    formData.append('stock', form.value.simpleStock);
    formData.append('sizes', JSON.stringify({})); 
  }

  if (form.value.description) formData.append('description', form.value.description);
  if (form.value.category_id) formData.append('category_id', form.value.category_id);
  if (form.value.material_id) formData.append('material_id', form.value.material_id);
  if (isRingCategory.value && form.value.stone_id) {
    formData.append('stone_id', form.value.stone_id);
  }

  // Добавление выбранных файлов в FormData (максимум 4)
  if (selectedFiles.value.length > 0) {
    const filesToSend = selectedFiles.value.slice(0, 4);
    filesToSend.forEach(file => { formData.append('images[]', file); });
  }

  try {
    const token = localStorage.getItem('api_token');
    // Формирование URL и метода запроса в зависимости от режима
    const url = editingProduct.value 
      ? `http://127.0.0.1:8000/api/admin/products/${editingProduct.value.id}`
      : 'http://127.0.0.1:8000/api/admin/products';

    const config = {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'multipart/form-data'
      }
    };

    // Для обновления используем POST с _method=PUT (поддержка Laravel)
    if (editingProduct.value) {
      formData.append('_method', 'PUT');
      await axios.post(url, formData, config);
    } else {
      await axios.post(url, formData, config);
    }
    closeModal();
    await loadProducts(); // Перезагружаем только список товаров
  } catch (err) {
    console.error('Ошибка сохранения товара:', err);
    alert('Ошибка сохранения: ' + (err.response?.data?.message || 'Неизвестная ошибка'));
  } finally {
    saving.value = false;
  }
};

// Удаление товара: запрос подтверждения и отправка DELETE-запроса
const deleteProduct = async (id) => {
  if (!confirm('Удалить товар?')) return;
  try {
    const token = localStorage.getItem('api_token');
    await axios.delete(`http://127.0.0.1:8000/api/admin/products/${id}`, {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' }
    });
    await loadProducts();
  } catch (err) {
    console.error('Ошибка удаления товара:', err);
    alert('Ошибка удаления');
  }
};

// Скачивание каталога товаров в формате Excel
const downloadProductsExcel = async () => {
  try {
    const token = localStorage.getItem('api_token');
    const response = await axios.get('http://127.0.0.1:8000/api/admin/reports/products/excel', {
      headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
      responseType: 'blob'
    });
    const blob = new Blob([response.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    const date = new Date().toISOString().slice(0, 10).replace(/-/g, '');
    link.href = url;
    link.setAttribute('download', `products_catalog_${date}.xlsx`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    window.URL.revokeObjectURL(url);
  } catch (err) {
    console.error('Ошибка скачивания Excel-отчёта:', err);
    let message = 'Не удалось скачать Excel-отчёт';
    if (err.response?.status === 401 || err.response?.status === 403) {
      message = 'Нет доступа. Перезайдите в систему.';
      localStorage.removeItem('api_token');
    } else if (err.response?.data?.message) {
      message = err.response.data.message;
    }
    alert(message);
  }
};

// === ЖИЗНЕННЫЙ ЦИКЛ ===

// Инициализация компонента: загрузка справочников и списка товаров
onMounted(() => {
  loadData();
});
</script>

<style scoped>
/* Утилитарный класс для обрезки текста до 2 строк с многоточием */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Глобальный плавный переход для основных свойств: улучшает визуальную отзывчивость интерфейса */
* {
  transition: border-color 0.2s, color 0.2s, background-color 0.2s;
}
</style>