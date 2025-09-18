<div id="services-view" class="view space-y-6 hidden">
    <h1 class="text-3xl font-bold text-slate-800 text-center">إدارة الخدمات</h1>
     <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-6">
         <h2 class="text-2xl font-bold text-slate-700">تسجيل خدمة جديدة</h2>
         <form id="record-service-form" class="space-y-4">
             <div>
                 <label for="service-description" class="block text-sm font-medium text-slate-700 mb-1">وصف الخدمة</label>
                 <input type="text" id="service-description" placeholder="كشف، عملية جراحية..." class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" required />
             </div>
              <div>
                 <label for="service-price" class="block text-sm font-medium text-slate-700 mb-1">سعر الخدمة</label>
                 <input type="number" id="service-price" placeholder="0.00" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" required min="0" step="any">
             </div>
             <button type="submit" class="w-full bg-sky-600 text-white p-3 rounded-lg font-semibold hover:bg-sky-700 shadow-sm transition-all duration-200">تسجيل الخدمة</button>
         </form>
         <div class="mt-8">
             <div class="border-b border-slate-200">
                 <nav class="-mb-px flex space-x-4 rtl:space-x-reverse" aria-label="Tabs">
                     <button id="daily-services-tab" class="tab-btn active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">خدمات اليوم</button>
                     <button id="monthly-services-tab" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">خدمات الشهر</button>
                     <button id="all-services-tab" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">السجل الكامل</button>
                 </nav>
             </div>
             <div id="daily-services-content" class="mt-4 overflow-x-auto tab-content-panel"><div id="daily-services-list"></div></div>
             <div id="monthly-services-content" class="hidden mt-4 overflow-x-auto tab-content-panel"><div id="monthly-services-list"></div></div>
             <div id="all-services-content" class="hidden mt-4 overflow-x-auto tab-content-panel"><div id="all-services-list"></div></div>
         </div>
     </div>
</div>