<div id="dashboard-view" class="view hidden space-y-6">
    <h1 class="text-3xl md:text-4xl font-bold text-slate-800">ملخص اليوم</h1>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-base text-slate-500 font-semibold">صافي أرباح اليوم</p>
                    <p id="daily-profit-stat" class="text-3xl font-bold text-slate-800 mt-1">0.00</p>
                </div>
                <div class="bg-emerald-100 text-emerald-600 rounded-full p-3"><i class="fas fa-wallet text-xl"></i></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-base text-slate-500 font-semibold">قيمة المخزون</p>
                    <p id="total-inventory-value" class="text-3xl font-bold text-slate-800 mt-1">0.00</p>
                </div>
                <div class="bg-sky-100 text-sky-600 rounded-full p-3"><i class="fas fa-warehouse text-xl"></i></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-base text-slate-500 font-semibold">مهام غير منجزة</p>
                    <p id="daily-tasks" class="text-3xl font-bold text-slate-800 mt-1">0 مهمة</p>
                </div>
                <div class="bg-violet-100 text-violet-600 rounded-full p-3"><i class="fas fa-tasks text-xl"></i></div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-base text-slate-500 font-semibold">تنبيهات المخزون</p>
                    <div id="low-stock-alerts" class="text-3xl font-bold mt-1"><span>0 أصناف</span></div>
                </div>
                <div class="bg-amber-100 text-amber-600 rounded-full p-3"><i class="fas fa-exclamation-triangle text-xl"></i></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fas fa-syringe text-blue-500"></i> تنبيهات التطعيمات
            </h2>
            <div id="dashboard-vaccine-reminders" class="space-y-3 max-h-52 overflow-y-auto custom-scrollbar pr-2">
                </div>
        </div>
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
            <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-2">
                <i class="fas fa-bullhorn text-sky-600"></i> العروض النشطة
            </h2>
            <div id="dashboard-promo-container" class="space-y-3 max-h-52 overflow-y-auto custom-scrollbar pr-2">
                </div>
        </div>
    </div>
    
    <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <h2 class="text-3xl font-bold text-slate-800 text-center mb-4">مرحباً بك في kaivet!</h2>
        <p class="text-center text-slate-600 text-lg max-w-2xl mx-auto">
            نظامك المتكامل لإدارة عيادتك البيطرية بكفاءة.
        </p>
        <div class="mt-8 text-center">
            <a href="#" data-view="ai-chat" class="nav-link inline-block bg-gradient-to-r from-sky-500 to-sky-600 text-white py-3 px-8 rounded-lg font-semibold shadow-md hover:shadow-xl transform hover:-translate-y-1 transition-all duration-300">
                <i class="fas fa-robot mr-2"></i>ابدأ محادثة مع المساعد الذكي
            </a>
        </div>
    </div>
</div>