<div id="sales-view" class="view space-y-6 hidden">
     <h1 class="text-3xl font-bold text-slate-800 text-center">نقطة البيع وسجل المبيعات</h1>
     <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-6">
         <h2 class="text-2xl font-bold text-slate-700">تسجيل عملية بيع جديدة</h2>
        <div id="sale-item-content">
             <form id="record-sale-form" class="space-y-4">
                 <div class="relative">
                     <input type="text" id="sale-item-name" placeholder="ابحث عن الصنف بالاسم..." class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" required />
                     <div id="autocomplete-results" class="autocomplete-list hidden custom-scrollbar"></div>
                 </div>
                 <div id="sale-details" class="p-3 bg-slate-50 rounded-lg text-sm text-slate-600 hidden"></div>
                 <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-center">
                     <div class="lg:col-span-2">
                         <label for="sale-type-selector" class="block text-sm font-medium text-slate-700 mb-1">نوع البيع</label>
                         <select id="sale-type-selector" class="w-full p-3 border border-slate-300 rounded-lg bg-white">
                             <option value="package">بيع بالعبوة</option>
                             <option value="unit">بيع بالفرط (وحدة)</option>
                         </select>
                     </div>
                     <div>
                         <label for="sale-quantity" class="block text-sm font-medium text-slate-700 mb-1">الكمية</label>
                         <input type="number" id="sale-quantity" placeholder="الكمية" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" required min="1" step="any">
                     </div>
                     <div>
                         <label for="sale-discount" class="block text-sm font-medium text-slate-700 mb-1">خصم على الربح (%)</label>
                         <input type="number" id="sale-discount" placeholder="0-100" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" min="0" max="100" value="0" step="any">
                     </div>
                 </div>
                 <button type="submit" class="w-full bg-emerald-600 text-white p-3 rounded-lg font-semibold hover:bg-emerald-700 shadow-sm transition-all duration-200">تسجيل البيع</button>
             </form>
           </div>

         <div class="mt-8 border-t pt-6">
             <div class="border-b border-slate-200">
                 <nav class="-mb-px flex space-x-4 rtl:space-x-reverse" aria-label="Tabs">
                     <button id="daily-sales-tab" class="tab-btn active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">مبيعات اليوم</button>
                     <button id="monthly-sales-tab" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">مبيعات الشهر</button>
                     <button id="all-sales-tab" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">السجل الكامل</button>
                 </nav>
             </div>
             <div id="daily-sales-content" class="mt-4 overflow-x-auto tab-content-panel"><div id="daily-sales-list"></div></div>
             <div id="monthly-sales-content" class="hidden mt-4 overflow-x-auto tab-content-panel"><div id="monthly-sales-list"></div></div>
             <div id="all-sales-content" class="hidden mt-4 overflow-x-auto tab-content-panel"><div id="all-sales-list"></div></div>
         </div>
     </div>
</div>