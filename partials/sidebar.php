<aside id="sidebar" class="bg-white flex flex-col p-4 h-screen fixed inset-y-0 right-0 z-50 w-72 transform translate-x-full transition-transform duration-300 ease-in-out md:relative md:translate-x-0 md:border-l border-slate-200">
    <button id="close-sidebar-btn" class="md:hidden absolute top-5 left-5 text-slate-500 hover:text-slate-800 text-2xl">&times;</button>
    <div class="flex items-center space-x-4 rtl:space-x-reverse p-4 mb-6 border-b border-slate-200">
        <div class="flex-shrink-0">
            <img id="sidebar-profile-pic" src="uploads/default.png" alt="صورة الملف الشخصي" class="w-12 h-12 rounded-full object-cover border-2 border-sky-500" onerror="this.onerror=null; this.src='uploads/default.png';"/>
        </div>
        <div>
            <h3 id="sidebar-user-name" class="text-lg font-bold text-slate-800 whitespace-nowrap"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
            <p class="text-sm text-slate-600 max-w-full">طبيب بيطري</p>
        </div>
    </div>
    
    <nav class="flex flex-col flex-grow space-y-2 overflow-y-auto custom-scrollbar px-2">
        
     <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="dashboard"><i class="fas fa-th-large ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> الرئيسية</a>
     <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="ai-chat"><i class="fas fa-robot ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> المساعد الذكي</a>   
         <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="doctors"><i class="fas fa-user-md ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> إدارة الأطباء</a>
        <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="services"><i class="fas fa-concierge-bell ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> الخدمات</a>
        <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="clinic_services"><i class="fas fa-medkit ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> إدارة الخدمات المعروضة</a>
        <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="promotions"><i class="fas fa-bullhorn ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> العروض والحملات</a>
        <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="cases"><i class="fas fa-heart-pulse ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> سجل الحالات</a>
       <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="inventory"><i class="fas fa-boxes-stacked ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> إدارة المخزون</a>
       <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="sales"><i class="fas fa-cash-register ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> نقطة البيع</a>
        <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="expenses"><i class="fas fa-receipt ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> المصروفات</a>
        <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="suppliers"><i class="fas fa-truck-loading ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> إدارة الموردين</a>
        <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="reports"><i class="fas fa-chart-pie ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> التقارير المالية</a>
        <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="todo"><i class="fas fa-clipboard-list ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> قائمة المهام</a>
        <a href="#" class="nav-link flex items-center p-3 rounded-lg text-slate-700 hover:bg-slate-100" data-view="settings-profile"><i class="fas fa-user-cog ml-3 rtl:mr-3 w-5 text-center text-slate-400"></i> الإعدادات</a>
    </nav>
    <div class="mt-auto pt-4 px-2"><a href="logout.php" class="w-full text-center bg-rose-600 hover:bg-rose-700 text-white font-bold py-3 px-6 rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center justify-center gap-2"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></div>
</aside>