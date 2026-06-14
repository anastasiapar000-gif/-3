<template>
  <!-- Корневой контейнер главной страницы: минимальная высота на весь экран, белый фон -->
  <div class="min-h-screen bg-white">
    
    <!-- Секция Hero: главный баннер с фоновым изображением и призывом к действию -->
    <section class="relative bg-gray-900 text-white h-[600px]">
      <!-- Контейнер фонового изображения с затемнением -->
      <div class="absolute inset-0 overflow-hidden">
        <!-- Фоновое изображение: затемнено для читаемости текста -->
        <img :src="heroBg" 
          alt="ZIMA Jewelry" 
          class="w-full h-full object-cover opacity-150"
        >
        <!-- Градиентное затемнение: плавный переход от полупрозрачного к более тёмному -->
        <div class="absolute inset-0 bg-gradient-to-b from-black/60 to-black/80"></div>
      </div>
      
      <!-- Контент секции: центрирование, отступы, выравнивание по вертикали -->
      <div class="relative max-w-7xl mx-auto px-4 py-32 sm:px-6 lg:px-8 h-full flex items-center">
        <div>
          <!-- Заголовок бренда: адаптивный размер шрифта, жирное начертание -->
          <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold mb-6 tracking-tight leading-tight">
            ZIMA Jewelry | Russian<br>Doomer Collection
          </h1>
          <!-- Подзаголовок: описание бренда, светло-серый цвет для контраста -->
          <p class="text-xl sm:text-2xl mb-10 max-w-2xl text-gray-300 font-light">
            Ювелирный бренд для тех, кто часто задумчиво смотрит вдаль
          </p>
          <!-- Кнопки призыва к действию: переход в каталог и раздел "О нас" -->
          <div class="flex flex-wrap gap-4">
            <router-link 
              to="/catalog" 
              class="px-10 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-gray-900 transition-all duration-300"
            >
              КАТАЛОГ
            </router-link>
            <router-link 
              to="/about" 
              class="px-10 py-4 border-2 border-white text-white font-semibold rounded-lg hover:bg-white hover:text-gray-900 transition-all duration-300"
            >
              О НАС
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Секция продукции: сетка категорий товаров с изображениями -->
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Заголовок секции -->
        <h2 class="text-4xl sm:text-5xl font-bold mb-4 text-gray-900">Наша продукция</h2>
        <!-- Описание секции: эмоциональный текст бренда -->
        <p class="text-lg text-gray-600 mb-12 max-w-3xl">
          Здесь вас ждет серебряное счастье, которое вы обронили где-то в детстве среди панельных дворов!
        </p>
        
        <!-- Сетка карточек категорий: адаптивная разметка (1 колонка на мобильных, 2 на десктопе) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Карточка категории "Кольца": занимает всю ширину на десктопе, соотношение сторон 16:9 -->
          <router-link 
            to="/catalog" 
            class="group cursor-pointer relative overflow-hidden rounded-lg aspect-[16/9] md:col-span-2 block"
          >
            <!-- Изображение категории с эффектом увеличения при наведении -->
            <img 
              :src="rings" 
              alt="Кольца" 
              class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
            >
            <!-- Текстовый оверлей: градиент снизу для читаемости текста -->
            <div class="absolute bottom-0 left-0 p-6 bg-gradient-to-t from-black/80 to-transparent w-full">
              <h3 class="text-3xl font-bold text-white uppercase tracking-wide">КОЛЬЦА</h3>
            </div>
          </router-link>
          
          <!-- Карточка категории "Подвески": квадратное соотношение сторон -->
          <router-link 
            to="/catalog" 
            class="group cursor-pointer relative overflow-hidden rounded-lg aspect-square block"
          >
            <img 
              :src="pendants" 
              alt="Подвески" 
              class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
            >
            <div class="absolute bottom-0 left-0 p-4 bg-gradient-to-t from-black/80 to-transparent w-full">
              <h3 class="text-2xl font-bold text-white uppercase tracking-wide">ПОДВЕСКИ</h3>
            </div>
          </router-link>
          
          <!-- Карточка категории "Броши": квадратное соотношение сторон -->
          <router-link 
            to="/catalog" 
            class="group cursor-pointer relative overflow-hidden rounded-lg aspect-square block"
          >
            <img 
              :src="brooches" 
              alt="Броши" 
              class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
            >
            <div class="absolute bottom-0 left-0 p-4 bg-gradient-to-t from-black/80 to-transparent w-full">
              <h3 class="text-2xl font-bold text-white uppercase tracking-wide">БРОШИ</h3>
            </div>
          </router-link>
          
          <!-- Карточка категории "Серьги": занимает всю ширину на десктопе, соотношение сторон 16:9 -->
          <router-link 
            to="/catalog" 
            class="group cursor-pointer relative overflow-hidden rounded-lg aspect-[16/9] md:col-span-2 block"
          >
            <img 
              :src="earrings" 
              alt="Серьги" 
              class="w-full h-full object-cover group-hover:scale-105 transition duration-700"
            >
            <div class="absolute bottom-0 left-0 p-6 bg-gradient-to-t from-black/80 to-transparent w-full">
              <h3 class="text-3xl font-bold text-white uppercase tracking-wide">СЕРЬГИ</h3>
            </div>
          </router-link>
          
        </div>
      </div>
    </section>
    
    <!-- Секция "О нас": тёмный фон с фоновым изображением и текстом бренда -->
    <section class="relative py-24 bg-gray-900 overflow-hidden">
      <!-- Фоновое изображение с затемнением -->
      <div class="absolute inset-0">
        <img 
          :src="aboutBg" 
          alt="Фон" 
          class="w-full h-full object-cover opacity-80"
        >
        <!-- Полупрозрачный чёрный оверлей для контраста текста -->
        <div class="absolute inset-0 bg-black/80"></div>
      </div>

      <!-- Контент секции: центрирование, максимальная ширина -->
      <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl sm:text-5xl font-bold text-white mb-10">О нас</h2>

        <!-- Текстовый блок с историей бренда: отступы, цвет текста, межстрочный интервал -->
        <div class="space-y-6 text-gray-300 text-lg leading-relaxed">
          <p>
            ZIMA Jewelry — бренд о настоящем и искреннем, о том, что затерялось в далеком-далеком детстве, и сейчас отражается в наших ювелирных изделиях. Мы вложили в каждое украшение частичку собственных теплых воспоминаний, а их отражения прячутся за окошками домов, в ностальгических образах и формах.
          </p>
          <p>
            Изделия создаются под влиянием индустриального стиля с большой любовью к промышленной эстетике. Любимая тема — высоко детализированные архитектурно-ностальгические ювелирные украшения. Здесь вас ждет серебряное счастье, которое вы обронили где-то в детстве среди панельных дворов!
          </p>
          <p class="text-white font-medium">
            Сейчас наша мастерская понемногу расширяется и у нас появляется много планов и целей по развитию на ближайшие годы. Спасибо, что вы с нами!
          </p>
        </div>

        <!-- Дополнительное изображение: деталь изделия, центрирование, тень -->
        <div class="mt-16 flex justify-center">
          <img
            :src="aboutDetail"
            alt="ZIMA Jewelry Detail"
            class="rounded-xl shadow-2xl w-full max-w-md object-cover border border-gray-800/50"
          >
        </div>
      </div>
    </section>

    <!-- Секция преимуществ: сетка карточек с иконками и описанием -->
    <section class="py-20 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl sm:text-5xl font-bold mb-16 text-center text-gray-900">Наши преимущества</h2>
        
        <!-- Сетка преимуществ: 2 колонки на десктопе, центрирование -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
          
          <!-- Преимущество 1: Уникальный дизайн -->
          <div class="flex gap-5 items-start">
            <!-- Иконка преимущества (SVG) -->
            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center">
              <svg class="w-8 h-8 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
              </svg>
            </div>
            <!-- Текстовое описание преимущества -->
            <div>
              <h3 class="font-bold text-gray-900 mb-2 text-lg">Уникальный дизайн</h3>
              <p class="text-gray-600 text-sm leading-relaxed">вдохновленный индустриальной эстетикой и постсоветской романтикой, передающий тепло и уют родного дома</p>
            </div>
          </div>
          
          <!-- Преимущество 2: Высокая детализация -->
          <div class="flex gap-5 items-start">
            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center">
              <svg class="w-8 h-8 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-gray-900 mb-2 text-lg">Высокая детализация</h3>
              <p class="text-gray-600 text-sm leading-relaxed">и тщательная проработка мельчайших элементов в каждом изделии</p>
            </div>
          </div>
          
          <!-- Преимущество 3: Собственное производство -->
          <div class="flex gap-5 items-start">
            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center">
              <svg class="w-8 h-8 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-gray-900 mb-2 text-lg">Своё ювелирное производство</h3>
              <p class="text-gray-600 text-sm leading-relaxed">гарантирующее контроль на всех этапах создания украшений</p>
            </div>
          </div>
          
          <!-- Преимущество 4: Сроки изготовления -->
          <div class="flex gap-5 items-start">
            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center">
              <svg class="w-8 h-8 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-gray-900 mb-2 text-lg">Изготовление — 10-14 рабочих дней</h3>
              <p class="text-gray-600 text-sm leading-relaxed">также регулярное пополнение ассортимента готовых изделий</p>
            </div>
          </div>
          
          <!-- Преимущество 5: Персонализация -->
          <div class="flex gap-5 items-start">
            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center">
              <svg class="w-8 h-8 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-gray-900 mb-2 text-lg">Возможность персонализации</h3>
              <p class="text-gray-600 text-sm leading-relaxed">нанесение индивидуальной гравировки для создания уникальных украшений</p>
            </div>
          </div>
          
          <!-- Преимущество 6: Дополнительные покрытия -->
          <div class="flex gap-5 items-start">
            <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center">
              <svg class="w-8 h-8 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
              </svg>
            </div>
            <div>
              <h3 class="font-bold text-gray-900 mb-2 text-lg">Дополнительные опции покрытия</h3>
              <p class="text-gray-600 text-sm leading-relaxed">нанокерамика, позолота, белый и чёрный родий</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Секция услуг: список дополнительных сервисов бренда -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl sm:text-5xl font-bold mb-16 text-center text-gray-900">Наши услуги</h2>
        
        <!-- Список услуг: вертикальные отступы между блоками -->
        <div class="space-y-16">
          
          <!-- Услуга 1: Индивидуальная гравировка -->
          <div class="text-center max-w-3xl mx-auto">
            <!-- Иконка услуги (SVG) -->
            <div class="flex justify-center mb-6">
              <svg class="w-12 h-12 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-6 uppercase tracking-wide">ИНДИВИДУАЛЬНАЯ ГРАВИРОВКА</h3>
            <!-- Описание услуги: текст с отступами, выравнивание по левому краю -->
            <div class="text-gray-600 space-y-4 text-left">
              <p>Сделайте ваше украшение по-настоящему уникальным! Мы предлагаем нанести гравировку.</p>
              <p>Вашим почерком — просто пришлите нам изображение или скан надписи. Стандартным или любым другим шрифтом — выберите понравившийся стиль, и мы воплотим вашу идею.</p>
              <p>Гравировка может быть как лаконичной, так и с особым смыслом — инициалы, важная дата, короткое послание или символ.</p>
            </div>
          </div>
          
          <!-- Услуга 2: Гальванические покрытия -->
          <div class="text-center max-w-3xl mx-auto">
            <div class="flex justify-center mb-6">
              <svg class="w-12 h-12 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-6 uppercase tracking-wide">ГАЛЬВАНИЧЕСКИЕ ПОКРЫТИЯ</h3>
            <div class="text-gray-600 space-y-4 text-left">
              <p>Мы используем современные технологии для создания долговечных и эффектных покрытий:</p>
              <!-- Маркированный список вариантов покрытий -->
              <ul class="list-disc list-inside space-y-2 ml-4">
                <li>Золото желтого и красного оттенков</li>
                <li>Родий: белый и черный</li>
                <li>Нанокерамика</li>
              </ul>
              <p>Выберите покрытие по вашему вкусу — оно подчеркнет индивидуальность украшения и обеспечит дополнительную защиту.</p>
            </div>
          </div>
          
          <!-- Услуга 3: Бесплатная чистка -->
          <div class="text-center max-w-3xl mx-auto">
            <div class="flex justify-center mb-6">
              <svg class="w-12 h-12 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-6 uppercase tracking-wide">БЕСПЛАТНАЯ ЧИСТКА УКРАШЕНИЙ</h3>
            <div class="text-gray-600 space-y-4 text-left">
              <p>Мы заботимся о ваших изделиях даже после покупки!</p>
              <p>Если со временем ваше украшение потеряет блеск или потускнеет, вы всегда можете обратиться к нам за бесплатной профессиональной чисткой изделия, приобретенного у нас.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Секция контактов: ссылки на соцсети и физический адрес -->
    <section class="py-20 bg-white border-t border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-4xl sm:text-5xl font-bold mb-12 text-center text-gray-900">Наши контакты</h2>
        
        <!-- Блок контактов: центрирование, максимальная ширина, вертикальные отступы -->
        <div class="max-w-2xl mx-auto space-y-8 text-center">
          <!-- Ссылка на Telegram -->
          <div>
            <h3 class="font-bold text-gray-900 mb-3 text-lg">Наш телеграм канал</h3>
            <a href="https://t.me/zimajewelrybrand" target="_blank" class="text-gray-600 hover:text-gray-900 underline break-all">
              https://t.me/zimajewelrybrand
            </a>
          </div>
          
          <!-- Ссылка на ВКонтакте -->
          <div>
            <h3 class="font-bold text-gray-900 mb-3 text-lg">Наш ВК</h3>
            <a href="https://vk.com/zimajewelry" target="_blank" class="text-gray-600 hover:text-gray-900 underline break-all">
              https://vk.com/zimajewelry
            </a>
          </div>
          
          <!-- Физический адрес мастерской -->
          <div>
            <h3 class="font-bold text-gray-900 mb-3 text-lg">Адрес</h3>
            <p class="text-gray-600 text-lg">Иркутск, Ленина, 5А</p>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
// Импорт изображений для секции Hero и карточек категорий
// Пути начинаются с /public/, так как Vite обрабатывает папку public как статическую
import heroBg from '/public/images/hero-bg.jpg'
import rings from '/public/images/rings.jpg'
import pendants from '/public/images/pendants.jpg'
import brooches from '/public/images/brooches.jpg'
import earrings from '/public/images/earrings.jpg'

// Импорт изображений для секции "О нас"
// Фоновое изображение с индустриальной эстетикой
import aboutBg from '/public/images/panelka-bg.jpg' 
// Детальное фото изделия для акцента в секции
import aboutDetail from '/public/images/ring-detail.jpg' 
</script>

<style scoped>
/* Глобальная настройка анимаций: плавная функция времени для всех переходов */
* {
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>