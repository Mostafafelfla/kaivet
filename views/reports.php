<div id="reports-view" class="view space-y-6 hidden">
        <div class="flex flex-wrap justify-between items-center gap-4 no-print">
           <h1 class="text-3xl font-bold text-slate-800">التقارير المالية</h1>
           <div class="flex items-center gap-3">
               <button id="refresh-report-btn" title="تحديث" class="bg-sky-600 text-white py-2 px-3 rounded-lg hover:bg-sky-700 shadow-sm"><i class="fas fa-sync-alt"></i></button>
               <button id="print-report-btn" title="طباعة التقرير" class="bg-slate-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-slate-700 shadow-sm flex items-center gap-2"><i class="fas fa-print"></i> طباعة</button>
           </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 printable-area">
           <div class="bg-white p-5 rounded-xl text-center shadow-sm border border-slate-200"><p class="font-semibold text-slate-600">أرباح اليوم</p><p id="report-today-profit" class="text-2xl font-bold text-emerald-700 mt-2">0.00</p></div>
           <div class="bg-white p-5 rounded-xl text-center shadow-sm border border-slate-200"><p class="font-semibold text-slate-600">أرباح الشهر</p><p id="report-month-profit" class="text-2xl font-bold text-emerald-700 mt-2">0.00</p></div>
           <div class="bg-white p-5 rounded-xl text-center shadow-sm border border-slate-200"><p class="font-semibold text-slate-600">أرباح السنة</p><p id="report-year-profit" class="text-2xl font-bold text-emerald-700 mt-2">0.00</p></div>
            <div class="bg-white p-5 rounded-xl text-center shadow-sm border border-slate-200"><p class="font-semibold text-slate-600">هامش الربح (الشهر)</p><p id="report-profit-margin" class="text-2xl font-bold text-sky-700 mt-2">0.00%</p></div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 printable-area">
             <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                 <div class="flex justify-between items-center mb-4 no-print">
                     <h3 class="text-lg font-bold text-slate-700">الأداء المالي اليومي</h3>
                     <select id="line-chart-interval" class="text-sm border rounded px-2 py-1 bg-slate-50 border-slate-300 focus:ring-sky-400 focus:border-sky-400"><option value="30">آخر 30 يوم</option><option value="90">آخر 90 يوم</option></select>
                 </div>
                 <div id="finance-line-chart-container">
                     <canvas id="finance-line-chart"></canvas>
                 </div>
             </div>
             <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                 <h3 class="text-lg font-bold text-slate-700 mb-4">توزيع الإيرادات حسب التصنيف</h3>
                 <div id="finance-pie-chart-container">
                     <canvas id="finance-pie-chart"></canvas>
                 </div>
             </div>
        </div>
        
        <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mt-6 printable-area">
           <h3 class="text-xl font-bold text-slate-700 mb-4">أعلى 5 أصناف مبيعاً (هذا الشهر)</h3>
           <div class="overflow-x-auto">
               <table class="w-full text-right table-auto border-collapse">
                   <thead><tr class="bg-slate-100 text-slate-700 text-sm"><th class="p-3 border-b font-semibold">#</th><th class="p-3 border-b font-semibold text-right">الصنف</th><th class="p-3 border-b font-semibold">الكمية المباعة</th><th class="p-3 border-b font-semibold">إجمالي الإيراد</th></tr></thead>
                   <tbody id="top-products-table"><tr><td colspan="4" class="text-center p-4">لا توجد بيانات كافية.</td></tr></tbody>
               </table>
           </div>
        </div>
</div>