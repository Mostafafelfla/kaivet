<div id="cases-view" class="view space-y-6 hidden">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-4">
           <h1 class="text-3xl font-bold text-slate-800">سجل الحالات الطبية</h1>
           <button id="add-case-btn" class="bg-sky-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-sky-700 shadow-sm flex items-center gap-2"><i class="fas fa-plus"></i> إضافة حالة جديدة</button>
        </div>
        <div class="relative"><input type="text" id="case-search-input" placeholder="ابحث باسم المالك, الهاتف, أو الحيوان..." class="w-full p-3 border border-slate-300 rounded-lg pr-10 text-right focus:ring-2 focus:ring-sky-400" /><i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i></div>
        <div id="cases-list" class="space-y-4"></div>
</div>