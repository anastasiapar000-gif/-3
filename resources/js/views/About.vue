<template>
  <div class="min-h-screen bg-white font-sans"> <!-- Добавил font-sans на корневой элемент -->
    
    <!-- Заголовок с фоном -->
    <div class="relative border-b border-gray-800 overflow-hidden">
      <!-- Фоновое изображение -->
      <div 
        class="absolute inset-0 bg-cover bg-center bg-no-repeat"
        style="background-image: url('https://i.pinimg.com/736x/df/62/1e/df621eb33fbe80474164151ab0e02d79.jpg');"
      >
        <!-- Тёмный оверлей -->
        <div class="absolute inset-0 bg-black/50"></div>
      </div>

      <!-- Контент заголовка -->
      <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
        <div class="text-center">
         
          <h1 class="text-4xl md:text-5xl font-bold text-red-600 mb-4 tracking-[0.2em] font-sans">
            О НАС
          </h1>
          <p class="text-lg text-gray-300 tracking-wide font-sans"></p>
        </div>
      </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 space-y-16">
      
      <!-- Секция 1: История -->
      <section class="space-y-6">
      
        <h2 class="text-2xl font-bold text-gray-900 tracking-wide font-sans">
          Наша история
        </h2>
        <div class="prose prose-sm text-gray-600 leading-relaxed font-sans">
          <p>
            ZIMA Jewelry — это бренд украшений, вдохновлённых эстетикой советского и постсоветского пространства. 
            Мы создаём кольца, подвески и серьги, которые становятся мостом между урбанистической архитектурой и личным стилем.
          </p>
          <p>
            Каждое изделие — это миниатюрная скульптура, воспроизводящая детали панельных домов, заводских труб, трамвайных депо 
            и других символов городской среды. Мы верим, что красота скрыта в повседневности, и наша задача — показать её.
          </p>
        </div>
      </section>

      <!-- Секция 2: Философия -->
      <section class="space-y-6">
       
        <h2 class="text-2xl font-bold text-gray-900 tracking-wide font-sans">
          Философия
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="space-y-3">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-1 4h1m-1 4h1m-1 4h1m-1 4h1" />
              </svg>
            </div>
           
            <h3 class="font-semibold text-gray-900 font-sans">
              Архитектура
            </h3>
            <p class="text-sm text-gray-600 leading-relaxed font-sans">
              Мы находим вдохновение в бетонных фасадах, геометрических формах и индустриальных деталях городов.
            </p>
          </div>
          
          <div class="space-y-3">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
           
            <h3 class="font-semibold text-gray-900 font-sans">
              Время
            </h3>
            <p class="text-sm text-gray-600 leading-relaxed font-sans">
              Наши украшения сохраняют память об эпохе, превращая ностальгию в современный аксессуар.
            </p>
          </div>
          
          <div class="space-y-3">
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
              <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
              </svg>
            </div>
            
            <h3 class="font-semibold text-gray-900 font-sans">
              Ручная работа
            </h3>
            <p class="text-sm text-gray-600 leading-relaxed font-sans">
              Каждое изделие создаётся вручную из серебра 925 пробы (и не только) с вниманием к каждой детали.
            </p>
          </div>
        </div>
      </section>

      <!-- Секция 3: Контакты -->
      <section class="space-y-6">
        
        <h2 class="text-2xl font-bold text-gray-900 tracking-wide font-sans">
          Контакты
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 font-sans">
          <div class="space-y-4">
            <div>
             
              <h3 class="font-semibold text-gray-900 mb-2 font-sans">
                Email
              </h3>
              <a href="mailto:info@zimajewelry.ru" class="text-red-600 hover:text-red-700 transition-colors font-sans">
                info@zimajewelry.ru
              </a>
            </div>
            <div>
             
              <h3 class="font-semibold text-gray-900 mb-2 font-sans">
                Телефон
              </h3>
              <a href="tel:+79242703153" class="text-red-600 hover:text-red-700 transition-colors font-sans">
                +7 (924) 270-31-53
              </a>
            </div>
          </div>
          <div class="space-y-4">
            <div>
             
              <h3 class="font-semibold text-gray-900 mb-2 font-sans">
                Социальные сети
              </h3>
              <div class="flex gap-4">
                <a href="#" class="text-gray-600 hover:text-red-600 transition-colors font-sans">Telegram</a>
                <a href="#" class="text-gray-600 hover:text-red-600 transition-colors font-sans">VK</a>
              </div>
            </div>
            <div>
              
              <h3 class="font-semibold text-gray-900 mb-2 font-sans">
                Адрес
              </h3>
              <p class="text-gray-600 font-sans">г. Иркутск, ул. Ленина, 5А</p>
            </div>
          </div>
        </div>
      </section>

    </div>
  </div>
</template>

<script setup>
// Страница статическая, логика не требуется
</script>

<style scoped>
* {
  transition: color 0.2s;
}

.prose p {
  margin-bottom: 1rem;
}

.prose p:last-child {
  margin-bottom: 0;
}
</style>