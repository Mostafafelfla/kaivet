<div id="expenses-view" class="view space-y-6 hidden">
    <h1 class="text-3xl font-bold text-slate-800 text-center">تسجيل المصروفات</h1>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-6">
        <form id="record-expense-form" class="space-y-3">
            <input type="text" id="expense-description" placeholder="وصف المصروف (فاتورة كهرباء، إيجار...)" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" required />
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="number" id="expense-amount" placeholder="قيمة المصروف" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" required step="any" min="0.01" />
                <button type="submit" class="w-full sm:w-auto bg-rose-600 text-white p-3 rounded-lg font-semibold hover:bg-rose-700 shadow-sm">تسجيل المصروف</button>
            </div>
        </form>
        <div class="mt-8">
            <div class="border-b border-slate-200">
                <nav class="-mb-px flex space-x-4 rtl:space-x-reverse" aria-label="Tabs">
                    <button id="daily-expenses-tab" class="tab-btn active whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">مصروفات اليوم</button>
                    <button id="monthly-expenses-tab" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">مصروفات الشهر</button>
                    <button id="all-expenses-tab" class="tab-btn whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300">السجل الكامل</button>
                </nav>
            </div>
            <div id="daily-expenses-content" class="mt-4 overflow-x-auto tab-content-panel"><div id="daily-expenses-list" class="space-y-2"></div></div>
            <div id="monthly-expenses-content" class="hidden mt-4 overflow-x-auto tab-content-panel"><div id="monthly-expenses-list" class="space-y-2"></div></div>
            <div id="all-expenses-content" class="hidden mt-4 overflow-x-auto tab-content-panel"><div id="all-expenses-list" class="space-y-2"></div></div>
        </div>
    </div>
</div>