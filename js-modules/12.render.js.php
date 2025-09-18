// --- RENDER FUNCTIONS ---
// (داخل js-modules/12.render.js.php)

dashboardSummary() {
    const currency = app.state.settings.currency || 'EGP';
    const isTodayFunc = (dateString) => {
       if (!dateString) return false;
       const serverDatePart = dateString.substring(0, 10);
       const today = new Date();
       const clientTodayDatePart = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
       return serverDatePart === clientTodayDatePart;
    };

    // 1. حساب الإحصائيات (يبقى كما هو)
    const dailySales = app.state.sales.filter(s => isTodayFunc(s.created_at));
    const dailyExpenses = app.state.expenses.filter(e => isTodayFunc(e.created_at));
    const totalDailyProfit = dailySales.reduce((s, sale) => s + parseFloat(sale.profit), 0);
    const totalDailyExpenses = dailyExpenses.reduce((s, exp) => s + parseFloat(exp.amount), 0);
    const netDailyProfit = totalDailyProfit - totalDailyExpenses;
    const totalInventoryValue = app.state.inventory.reduce((s, item) => s + (parseFloat(item.purchase_price) * item.quantity), 0);
    document.getElementById('daily-profit-stat').textContent = `${app.utils.formatCurrency(netDailyProfit)} ${currency}`;
    document.getElementById('total-inventory-value').textContent = `${app.utils.formatCurrency(totalInventoryValue)} ${currency}`;
    document.getElementById('daily-tasks').textContent = `${app.state.todos.filter(t => !t.completed).length} مهمة`;
    const lowStockItems = app.state.inventory.filter(item => item.quantity <= 5);
    const lowStockAlerts = document.getElementById('low-stock-alerts');
    lowStockAlerts.querySelector('span').textContent = `${lowStockItems.length} أصناف`;
    lowStockAlerts.className = `text-lg font-bold mt-1 ${lowStockItems.length > 0 ? 'text-red-600' : 'text-emerald-600'}`;

    // 2. ملء قسم العروض النشطة (يبقى كما هو)
    const promoContainer = document.getElementById('dashboard-promo-container');
    if (promoContainer) {
        const todayStr = new Date().toISOString().split('T')[0];
        const activePromos = (app.state.promotions || []).filter(p => {
            const hasStarted = !p.start_date || p.start_date <= todayStr;
            const hasNotExpired = p.expiry_date >= todayStr;
            return p.is_active == 1 && hasStarted && hasNotExpired;
        });
        if (activePromos.length === 0) {
            promoContainer.innerHTML = '<p class="text-slate-500 text-center">لا توجد عروض نشطة حالياً.</p>';
        } else {
            promoContainer.innerHTML = activePromos.map(promo => {
                return `
                <div class="border-b border-slate-100 pb-2 mb-2 last:border-b-0 flex flex-col sm:flex-row justify-between sm:items-center">
                    <span class="font-semibold text-slate-700 mb-1 sm:mb-0">${promo.title}</span>
                    <span class="text-xs text-rose-600 font-semibold">ينتهي في: ${new Date(promo.expiry_date).toLocaleDateString('ar-EG')}</span>
                </div>`;
            }).join('');
        }
    }

   // ========== تحديث تصميم تنبيهات التطعيمات (ليحتوي على زرين) ==========
    const remindersContainer = document.getElementById('dashboard-vaccine-reminders');
    if (remindersContainer) {
        const reminders = app.state.vaccinationReminders || [];
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (reminders.length === 0) {
            remindersContainer.innerHTML = '<p class="text-slate-500 text-center py-4">لا توجد تطعيمات قادمة أو متأخرة.</p>';
        } else {
            remindersContainer.innerHTML = reminders.map(reminder => {
                const dueDate = new Date(reminder.next_due_date);
                let dateText, dateClass;
                // (منطق التاريخ يبقى كما هو)
                if (dueDate < today) { dateText = 'متأخر!'; dateClass = 'bg-rose-100 text-rose-700'; } 
                else if (dueDate.getTime() === today.getTime()) { dateText = 'اليوم'; dateClass = 'bg-amber-100 text-amber-700'; } 
                else { dateText = `قادم: ${dueDate.toLocaleDateString('ar-EG')}`; dateClass = 'bg-blue-100 text-blue-700'; }

                // (منطق تجهيز الرسالة يبقى كما هو)
                const clinicName = app.state.settings.clinic_name || 'عيادتنا';
                let messageText = `مرحباً، ${reminder.owner_name}.\n\n`;
                messageText += `نود تذكيركم بموعد تطعيم "${reminder.vaccination_name}" الخاص بـ "${reminder.animal_name}"، والمقرر بتاريخ ${new Date(reminder.next_due_date).toLocaleDateString('ar-EG')}.\n\n`;
                messageText += `برجاء تأكيد الموعد أو التواصل معنا لإعادة جدولته.\n\nمع تحيات،\n${clinicName}`;
                const encodedMessage = encodeURIComponent(messageText);
                
                const fullPhone = (reminder.owner_phone_code || '20') + String(reminder.owner_phone || '').replace(/\D/g, '');
                
                // ==========  هذا هو التحديث: إنشاء رابطين مختلفين  ==========
                const waLinkApp = `whatsapp://send?phone=${fullPhone}&text=${encodedMessage}`;
                const waLinkWeb = `https://web.whatsapp.com/send?phone=${fullPhone}&text=${encodedMessage}`;
                // ==================================================

                return `
                <div class="border-b border-slate-100 pb-2 mb-2 last:border-b-0 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <div class="w-full sm:w-auto">
                        <p class="font-semibold text-slate-700">
                            <span class="text-sky-600">${reminder.animal_name}</span> (لـ ${reminder.owner_name})
                        </p>
                        <p class="text-sm text-slate-500">تطعيم: ${reminder.vaccination_name}</p>
                    </div>
                    <div class="flex items-center gap-2 w-full sm:w-auto justify-between mt-2 sm:mt-0">
                         <span class="text-xs font-bold px-2 py-1 rounded-full ${dateClass}">${dateText}</span>
                         <a href="${waLinkApp}" target="_blank" title="إرسال عبر التطبيق" 
                            class="send-reminder-btn flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-emerald-600 bg-emerald-100 hover:bg-emerald-200">
                            <i class="fab fa-whatsapp"></i>
                         </a>
                         <a href="${waLinkWeb}" target="_blank" title="إرسال عبر الويب" 
                            class="send-reminder-btn flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-slate-600 bg-slate-100 hover:bg-slate-200">
                            <i class="fas fa-globe"></i>
                         </a>
                    </div>
                </div>
                `;
            }).join('');
        }
    }
    // ==============================================================
},
// (داخل ملف js-modules/12.render.js.php)
promotionsList() {
    const container = document.getElementById('promotions-list-container');
    if (!container) return;
    
    const items = app.state.promotions || []; 
    const today = new Date().toISOString().split('T')[0]; 

    if (items.length === 0) {
        container.innerHTML = `<p class="text-center text-slate-500 py-4">لا توجد أي عروض مسجلة حالياً.</p>`;
        return;
    }

    container.innerHTML = items.map(p => {
        const isExpired = p.expiry_date < today;
        const isPending = p.start_date && p.start_date > today; 
        
        let statusClass = 'bg-emerald-100 text-emerald-700';
        let statusText = 'عرض نشط';

        if (isExpired) {
            statusClass = 'bg-rose-100 text-rose-700';
            statusText = 'عرض منتهي';
        } else if (isPending) {
            statusClass = 'bg-amber-100 text-amber-700';
            statusText = 'عرض قادم (لم يبدأ بعد)';
        }

        // ==========  هذا هو الإصلاح: نسمح بإرسال العروض القادمة (Pending) ==========
        // الكود القديم كان: const sendBtnDisabled = isExpired || isPending;
        const sendBtnDisabled = isExpired; // الكود الجديد: فقط امنع إرسال المنتهي
        // ==============================================================

        const startDateText = p.start_date ? new Date(p.start_date).toLocaleDateString('ar-EG') : 'فوراً';
        const expiryDateText = new Date(p.expiry_date).toLocaleDateString('ar-EG');

        return `
        <div class="p-4 rounded-lg flex justify-between items-start gap-4 border ${isExpired ? 'border-slate-200 bg-slate-50' : 'border-slate-300 bg-white shadow-sm'}">
            <div>
                <div class="flex items-center gap-3 mb-2 flex-wrap">
                    <h4 class="text-lg font-bold ${isExpired ? 'text-slate-500' : 'text-slate-800'}">${p.title}</h4>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full ${statusClass}">${statusText}</span>
                </div>
                <p class="text-sm text-slate-600">${p.description}</p>
                <p class="text-sm text-slate-500 font-semibold mt-2">
                    <i class="fas fa-calendar-check text-emerald-600"></i> يبدأ: ${startDateText} | 
                    <i class="fas fa-calendar-times text-rose-600"></i> ينتهي: ${expiryDateText}
                </p>
            </div>
            <div class="flex-shrink-0 flex items-center gap-2">
                
                <button title="إرسال الحملة عبر واتساب" class="send-promo-wa-btn p-2 rounded-full text-emerald-600 hover:bg-emerald-100 ${sendBtnDisabled ? 'opacity-40 cursor-not-allowed' : ''}" 
                        data-id="${p.id}" ${sendBtnDisabled ? 'disabled' : ''}>
                    <i class="fab fa-whatsapp pointer-events-none text-lg"></i>
                </button>

                <button title="تعديل" class="edit-promo-btn p-2 rounded-full text-sky-600 hover:bg-sky-100" data-id="${p.id}">
                    <i class="fas fa-edit pointer-events-none"></i>
                </button>
                <button title="حذف" class="delete-promo-btn p-2 rounded-full text-rose-600 hover:bg-rose-100" data-id="${p.id}">
                    <i class="fas fa-trash pointer-events-none"></i>
                </button>
            </div>
        </div>
        `;
    }).join('');
},
inventory() {
    const searchFilter = document.getElementById('search-input').value.toLowerCase();
    const filterItems = (items) => searchFilter ? items.filter(i => i.name.toLowerCase().includes(searchFilter)) : items;
    const renderList = (el, items) => { el.innerHTML = items.length === 0 ? `<p class="text-center text-slate-500 py-4">لا توجد عناصر.</p>` : items.map(item => `<div class="bg-white p-4 rounded-lg shadow-sm flex justify-between items-center border border-slate-200"><div><p class="text-lg font-semibold text-slate-800">${item.name}</p><p class="text-sm text-slate-600">شراء: ${app.utils.formatCurrency(item.purchase_price)} | بيع: ${app.utils.formatCurrency(item.price)} | كمية: ${item.quantity}</p></div><div class="flex space-x-2 rtl:space-x-reverse"><button class="edit-btn p-2 rounded-full text-sky-600 hover:bg-sky-100" data-id="${item.id}"><i class="fas fa-edit pointer-events-none"></i></button><button class="delete-btn p-2 rounded-full text-rose-600 hover:bg-rose-100" data-id="${item.id}"><i class="fas fa-trash pointer-events-none"></i></button></div></div>`).join(''); };
    renderList(document.getElementById('medication-list'), filterItems(app.state.inventory.filter(item => item.type === 'medication')));
    renderList(document.getElementById('feeds-list'), filterItems(app.state.inventory.filter(item => item.type === 'feed')));
    renderList(document.getElementById('supplies-list'), filterItems(app.state.inventory.filter(item => item.type === 'supply')));
},
productSalesList(listElement, items, showDate = false) { 
    if (!listElement) return;
    const currency = app.state.settings.currency || 'EGP';
    
    const dateHeader = showDate ? '<th scope="col" class="px-6 py-3">التاريخ</th>' : '';
    const tableHeaders = `<thead class="text-xs text-slate-700 uppercase bg-slate-100"><tr>${dateHeader}<th scope="col" class="px-6 py-3">الصنف</th><th scope="col" class="px-6 py-3">الكمية</th><th scope="col" class="px-6 py-3">الإجمالي</th><th scope="col" class="px-6 py-3">الخصم</th><th scope="col" class="px-6 py-3">النهائي</th><th scope="col" class="px-6 py-3">إجراءات</th></tr></thead>`;
    
    listElement.innerHTML = items.length === 0 
        ? `<p class="text-center text-slate-500 py-4">لا توجد مبيعات مسجلة.</p>` 
        : `<table class="w-full text-sm text-right text-slate-500">${tableHeaders}<tbody>${items.map(item => {
            let quantityDisplay = item.sale_type === 'unit' ? `${item.quantity} ${item.unit_name_at_sale || 'وحدة'}` : `${item.quantity} عبوة`;
            const finalPrice = (item.final_price !== null && parseFloat(item.final_price) >= 0) ? item.final_price : item.total_price;
            const discountDisplay = item.discount > 0 ? `${parseFloat(item.discount).toFixed(2)}%` : '-';
            const dateCell = showDate ? `<td class="px-6 py-4">${new Date(item.created_at).toLocaleString('ar-EG')}</td>` : '';

            return `<tr class="bg-white border-b border-slate-200">
                        ${dateCell}
                        <td class="px-6 py-4">${item.item_name}</td>
                        <td class="px-6 py-4">${quantityDisplay}</td>
                        <td class="px-6 py-4">${app.utils.formatCurrency(item.total_price)} ${currency}</td>
                        <td class="px-6 py-4 text-center">${discountDisplay}</td>
                        <td class="px-6 py-4 font-bold">${app.utils.formatCurrency(finalPrice)} ${currency}</td>
                        <td class="px-6 py-4 flex items-center gap-2">
                            <button title="تعديل" class="edit-sale-btn p-2 rounded-full text-sky-600 hover:bg-sky-100" data-id="${item.id}"><i class="fas fa-pencil-alt pointer-events-none"></i></button>
                            <button title="إلغاء" class="delete-sale-btn p-2 rounded-full text-rose-600 hover:bg-rose-100" data-id="${item.id}"><i class="fas fa-trash pointer-events-none"></i></button>
                        </td>
                    </tr>`;
        }).join('')}</tbody></table>`; 
},
servicesList(listElement, items, showDate = false) {
    if (!listElement) return;
    const currency = app.state.settings.currency || 'EGP';
    const dateHeader = showDate ? '<th scope="col" class="px-6 py-3">التاريخ</th>' : '';
    const tableHeaders = `<thead class="text-xs text-slate-700 uppercase bg-slate-100"><tr>${dateHeader}<th scope="col" class="px-6 py-3">وصف الخدمة</th><th scope="col" class="px-6 py-3">السعر</th><th scope="col" class="px-6 py-3">إجراءات</th></tr></thead>`;
    
    listElement.innerHTML = items.length === 0 
        ? `<p class="text-center text-slate-500 py-4">لا توجد خدمات مسجلة.</p>` 
        : `<table class="w-full text-sm text-right text-slate-500">${tableHeaders}<tbody>${items.map(item => {
            const dateCell = showDate ? `<td class="px-6 py-4">${new Date(item.created_at).toLocaleString('ar-EG')}</td>` : '';
            return `<tr class="bg-white border-b border-slate-200">
                        ${dateCell}
                        <td class="px-6 py-4">${item.item_name}</td>
                        <td class="px-6 py-4 font-bold">${app.utils.formatCurrency(item.final_price)} ${currency}</td>
                        <td class="px-6 py-4 flex items-center gap-2">
                            <button title="تعديل" class="edit-service-btn p-2 rounded-full text-sky-600 hover:bg-sky-100" data-id="${item.id}"><i class="fas fa-pencil-alt pointer-events-none"></i></button>
                            <button title="إلغاء" class="delete-service-btn p-2 rounded-full text-rose-600 hover:bg-rose-100" data-id="${item.id}"><i class="fas fa-trash pointer-events-none"></i></button>
                        </td>
                    </tr>`;
        }).join('')}</tbody></table>`;
},
expensesList(listElement, items, showDate = false) { 
    const currency = app.state.settings.currency || 'EGP';
    const dateHeader = showDate ? '<th scope="col" class="px-6 py-3">التاريخ</th>' : '';
    const tableHeaders = `<thead class="text-xs text-slate-700 uppercase bg-slate-100"><tr>${dateHeader}<th scope="col" class="px-6 py-3">الوصف</th><th scope="col" class="px-6 py-3">المبلغ</th><th scope="col" class="px-6 py-3">إجراءات</th></tr></thead>`;

    listElement.innerHTML = items.length === 0 ? `<p class="text-center text-slate-500 py-4">لا توجد مصروفات.</p>` : `<table class="w-full text-sm text-right text-slate-500">${tableHeaders}<tbody>${items.map(item => {
        const dateCell = showDate ? `<td class="px-6 py-4">${new Date(item.created_at).toLocaleString('ar-EG')}</td>` : '';
        return `<tr class="bg-white border-b border-slate-200">
                    ${dateCell}
                    <td class="px-6 py-4">${item.description}</td>
                    <td class="px-6 py-4">${app.utils.formatCurrency(item.amount)} ${currency}</td>
                    <td class="px-6 py-4 flex items-center gap-2">
                        <button class="edit-expense-btn p-2 rounded-full text-sky-600 hover:bg-sky-100" data-id="${item.id}"><i class="fas fa-edit pointer-events-none"></i></button>
                        <button class="delete-expense-btn p-2 rounded-full text-rose-600 hover:bg-rose-100" data-id="${item.id}"><i class="fas fa-trash pointer-events-none"></i></button>
                    </td>
                </tr>`;
    }).join('')}</tbody></table>`; 
},
suppliersList() {
    const listElement = document.getElementById('suppliers-list-table');
    if (!listElement) return;
    
    const tableBody = listElement.querySelector('tbody');
    const items = app.state.suppliers;
    
    if (items.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="5" class="text-center p-4 text-slate-500">لا يوجد موردين مسجلين.</td></tr>`;
    } else {
        tableBody.innerHTML = items.map(item => `
            <tr class="bg-white border-b border-slate-200">
                <td class="px-6 py-4">${item.name}</td>
                <td class="px-6 py-4">${item.phone || '-'}</td>
                <td class="px-6 py-4">${item.email || '-'}</td>
                <td class="px-6 py-4 truncate max-w-xs">${item.notes || '-'}</td>
                <td class="px-6 py-4 flex items-center gap-2">
                    <button class="edit-supplier-btn p-2 rounded-full text-sky-600 hover:bg-sky-100" data-id="${item.id}"><i class="fas fa-edit pointer-events-none"></i></button>
                    <button class="delete-supplier-btn p-2 rounded-full text-rose-600 hover:bg-rose-100" data-id="${item.id}"><i class="fas fa-trash pointer-events-none"></i></button>
                </td>
            </tr>
        `).join('');
    }
},
// --- دالة جديدة لإضافتها إلى كائن 'render' ---
// (داخل ملف js-modules/12.render.js.php)

// (داخل ملف js-modules/12.render.js.php)

clinicServicesList() {
    const container = document.getElementById('clinic-services-list-container');
    if (!container) return;
    
    const items = app.state.services || []; 
    
    if (items.length === 0) {
        container.innerHTML = `<p class="text-center text-slate-500 py-4">لم تقم بإضافة أي خدمات.</p>`;
        return;
    }

    container.innerHTML = items.map(s => {
        let priceDisplay = `<span class="font-semibold text-sky-600">${s.price_note || 'السعر غير محدد'}</span>`;

        // ==========  هذا هو التعديل  ==========
        // سنقوم بإنشاء وسم الـ <p> فقط إذا كان هناك وصف موجود بالفعل
        const descriptionHtml = s.description 
            ? `<p class="text-sm text-slate-500 mt-1">${s.description}</p>` 
            : ''; // إذا كان فارغاً، لا تقم بإنشاء أي كود HTML
        // ======================================

        return `
        <div class="bg-slate-50 p-4 rounded-lg flex justify-between items-start gap-4 border border-slate-200">
            <div>
                <h4 class="text-lg font-bold text-slate-800">${s.service_name}</h4>
                <p class="text-sm">${priceDisplay}</p>
                ${descriptionHtml} </div>
            <div class="flex-shrink-0 flex items-center gap-2">
                <button title="تعديل" class="edit-service-btn p-2 rounded-full text-sky-600 hover:bg-sky-100" data-id="${s.id}">
                    <i class="fas fa-edit pointer-events-none"></i>
                </button>
                <button title="حذف" class="delete-service-btn p-2 rounded-full text-rose-600 hover:bg-rose-100" data-id="${s.id}">
                    <i class="fas fa-trash pointer-events-none"></i>
                </button>
            </div>
        </div>
        `;
    }).join('');
},
doctorsList() {
    const container = document.getElementById('doctors-list-container');
    if (!container) return;
    
    const items = app.state.doctors;
    
    if (items.length === 0) {
        container.innerHTML = `<p class="text-center text-slate-500 py-4">لم تقم بإضافة أي أطباء أو مساعدين بعد.</p>`;
        return;
    }

    container.innerHTML = items.map(doc => {
        return `
        <div class="bg-slate-50 p-4 rounded-lg shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border border-slate-200">
            <div class="flex items-center gap-3">
                <img src="${doc.profile_pic || 'uploads/default.png'}" class="w-16 h-16 rounded-full object-cover border-2 border-sky-200" onerror="this.onerror=null; this.src='uploads/default.png';">
                <div>
                    <h4 class="text-lg font-bold text-slate-800">${doc.name}</h4>
                    <p class="text-sm text-sky-600 font-semibold">${doc.specialty || 'طبيب عام'}</p>
                    <p class="text-sm text-slate-500">${doc.phone || 'لا يوجد هاتف'}</p>
                    <p class="text-xs text-slate-500 mt-1">${doc.address || 'لا يوجد عنوان مسجل'}</p>
                </div>
            </div>
            <div class="flex-shrink-0 flex items-center gap-2">
                <button title="تعديل" class="edit-doctor-btn p-2 rounded-full text-sky-600 hover:bg-sky-100" data-id="${doc.id}">
                    <i class="fas fa-edit pointer-events-none"></i>
                </button>
                <button title="حذف" class="delete-doctor-btn p-2 rounded-full text-rose-600 hover:bg-rose-100" data-id="${doc.id}">
                    <i class="fas fa-trash pointer-events-none"></i>
                </button>
            </div>
        </div>
        `;
    }).join('');
},
todoList() { document.getElementById('todo-list-container').innerHTML = app.state.todos.length === 0 ? '<p class="text-center text-slate-500 py-4">لا توجد مهام حالياً.</p>' : app.state.todos.map(todo => `<div class="p-4 rounded-lg shadow-sm flex items-center justify-between border border-slate-200 ${todo.completed == 1 ? 'bg-emerald-50' : 'bg-white'}"><div class="flex items-center space-x-3 rtl:space-x-reverse"><input type="checkbox" data-id="${todo.id}" class="form-checkbox h-5 w-5 text-sky-600 rounded focus:ring-sky-500" ${todo.completed == 1 ? 'checked' : ''}><label class="text-lg font-medium ${todo.completed == 1 ? 'line-through text-slate-500' : 'text-slate-800'}">${todo.text}</label></div><button class="delete-todo-btn p-2 rounded-full text-rose-600 hover:bg-rose-100" data-id="${todo.id}"><i class="fas fa-times pointer-events-none"></i></button></div>`).join(''); },
casesList() {
    const container = document.getElementById('cases-list');
    const searchTerm = document.getElementById('case-search-input').value.toLowerCase();

    const filteredCases = !app.state.cases ? [] : app.state.cases.filter(c => 
        (c.owner_name && c.owner_name.toLowerCase().includes(searchTerm)) || 
        (c.owner_phone && c.owner_phone.includes(searchTerm)) ||
        (c.animal_name && c.animal_name.toLowerCase().includes(searchTerm))
    );

    const groupByOwner = (cases) => {
        if (!cases || !Array.isArray(cases)) return {};
        return cases.reduce((acc, caseData) => {
            const ownerKey = `${caseData.owner_name.trim()}|${caseData.owner_phone.trim()}`;
            if (!acc[ownerKey]) {
                acc[ownerKey] = {
                    owner_name: caseData.owner_name,
                    owner_phone: caseData.owner_phone,
                    animals: []
                };
            }
            acc[ownerKey].animals.push(caseData);
            return acc;
        }, {});
    };

    const owners = groupByOwner(filteredCases);
    const ownersKeys = Object.keys(owners);

    if (ownersKeys.length === 0) {
        container.innerHTML = '<p class="text-center text-slate-500 py-4">لا توجد حالات مطابقة.</p>';
        return;
    }

    container.innerHTML = ownersKeys.map(ownerKey => {
        const ownerData = owners[ownerKey];
        const animalsHTML = ownerData.animals.map(animal => {
            const formatDt = (d) => d ? new Date(d).toLocaleDateString('ar-EG', { year: 'numeric', month: 'long', day: 'numeric' }) : 'غير محدد';
            
            const vaccinations = Array.isArray(animal.vaccinations) ? animal.vaccinations : [];
            const treatments = Array.isArray(animal.treatments) ? animal.treatments : [];

            const appointments = [...vaccinations, ...treatments].filter(v => v && v.next_due_date);
            
            let appointmentsHTML = appointments.length > 0 ? '<ul class="mt-2 pt-2 border-t border-slate-200">' + appointments.map(v => `<li class="flex justify-between items-center p-1 text-sm"><span>${v.date ? '💉' : '✚'} ${v.name} (التالي: ${formatDt(v.next_due_date)})</span><a href="https://wa.me/${animal.owner_phone.replace(/\D/g, '')}?text=${encodeURIComponent(`مرحباً ${animal.owner_name}، تذكير بموعد ${animal.animal_name} بتاريخ ${formatDt(v.next_due_date)}`)}" target="_blank" class="text-emerald-500 hover:text-emerald-700"><i class="fab fa-whatsapp"></i> تذكير</a></li>`).join('') + '</ul>' : '<p class="text-sm text-slate-500 mt-2 pt-2 border-t border-slate-200">لا توجد مواعيد قادمة.</p>';

            return `
            <div class="bg-slate-50 p-3 rounded-lg mt-2 border border-slate-200">
                <div class="flex justify-between items-center">
                    <div>
                        <p class="font-bold text-slate-800">${animal.animal_name} <span class="text-sm font-normal text-slate-600">(${animal.animal_type || 'غير محدد'})</span></p>
                    </div>
                    <div class="flex items-center gap-2">
                         <button title="عرض المواعيد" class="expand-animal-details-btn p-2 rounded-full text-slate-500 hover:bg-slate-200" data-animal-id="${animal.id}"><i class="fas fa-calendar-alt pointer-events-none"></i></button>
                         <button title="تعديل الحالة" class="edit-case-btn p-2 rounded-full text-sky-600 hover:bg-sky-100" data-id="${animal.id}"><i class="fas fa-edit pointer-events-none"></i></button>
                         <button title="حذف الحالة" class="delete-case-btn p-2 rounded-full text-rose-600 hover:bg-rose-100" data-id="${animal.id}"><i class="fas fa-trash pointer-events-none"></i></button>
                    </div>
                </div>
                <div class="animal-card-details case-card-details">
                    ${appointmentsHTML}
                </div>
            </div>
            `;
        }).join('');

        return `
        <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-xl font-bold text-sky-700">${ownerData.owner_name}</h3>
                    <p class="text-sm text-slate-600">${ownerData.owner_phone}</p>
                </div>
                <button class="add-animal-to-owner-btn bg-sky-100 text-sky-700 hover:bg-sky-200 font-semibold py-1 px-3 rounded-full flex items-center gap-2" data-owner-name="${ownerData.owner_name}" data-owner-phone="${ownerData.owner_phone}">
                    <i class="fas fa-plus"></i> إضافة حيوان
                </button>
            </div>
            <div class="mt-2 border-t border-slate-200 pt-2">
                ${animalsHTML}
            </div>
        </div>
        `;
    }).join('');
},
// (ابحث عن دالة settings داخل ملف 12.render.js.php وقم بتحديثها)
settings() {
    const { settings } = app.state;
    app.elements.sidebarUserName.textContent = settings.name || 'مستخدم جديد';
    
    // ملء الحقول النصية
    document.getElementById('nameInput').value = settings.name || ''; 
    document.getElementById('clinicNameInput').value = settings.clinic_name || ''; 
    document.getElementById('clinicAddressInput').value = settings.clinic_address || ''; 
    document.getElementById('currencySelect').value = settings.currency || 'EGP';
    
    // --- الإضافة الجديدة ---
    document.getElementById('clinicWhatsappInput').value = settings.clinic_whatsapp || '';
    document.getElementById('clinic_lat').value = settings.clinic_lat || '';
    document.getElementById('clinic_lng').value = settings.clinic_lng || '';
    // --------------------

    const profilePic = settings.profile_pic ? settings.profile_pic : 'uploads/default.png';
    const timestamp = new Date().getTime();
    app.elements.sidebarProfilePic.src = `${profilePic}?t=${timestamp}`; 
    document.getElementById('profilePicPreview').src = `${profilePic}?t=${timestamp}`;
    
    // تحديث الخريطة إذا كانت محملة
    if (app.state.ui.mapInitialized && settings.clinic_lat && settings.clinic_lng) {
        const savedPos = { lat: parseFloat(settings.clinic_lat), lng: parseFloat(settings.clinic_lng) };
        app.map.setCenter(savedPos);
        app.marker.setPosition(savedPos);
    } else if (app.state.ui.mapInitialized && settings.clinic_address) {
         app.geocodeAddress(settings.clinic_address);
    }
},
reports() {
    const reportsData = app.state.charts.reportsData; if (!reportsData) return;
    const currency = app.state.settings.currency || 'EGP';
    document.getElementById('report-today-profit').textContent = `${app.utils.formatCurrency(reportsData.today.net_profit)} ${currency}`;
    document.getElementById('report-month-profit').textContent = `${app.utils.formatCurrency(reportsData.month.net_profit)} ${currency}`;
    document.getElementById('report-year-profit').textContent = `${app.utils.formatCurrency(reportsData.year.net_profit)} ${currency}`;
    document.getElementById('report-profit-margin').textContent = `${reportsData.month.profit_margin}%`;
    const days = Number(document.getElementById('line-chart-interval').value || 30);
    const startDate = new Date(); startDate.setDate(startDate.getDate() - days);
    const filteredTimeseries = (reportsData.timeseries.daily || []).filter(d => new Date(d.date) >= startDate);
    const labels = filteredTimeseries.map(d => d.date);
    const revenueData = filteredTimeseries.map(d => d.gross_revenue);
    const profitData = filteredTimeseries.map(d => d.net_profit);
    const expenseData = filteredTimeseries.map(d => d.total_expenses);
    this.lineChart(labels, revenueData, profitData, expenseData);
    const categoryLabels = reportsData.category_breakdown.map(c => c.category);
    const categoryValues = reportsData.category_breakdown.map(c => c.category_revenue);
    this.pieChart(categoryLabels, categoryValues)
    const topProducts = reportsData.top_products;
    const tableBody = document.getElementById('top-products-table');
    tableBody.innerHTML = topProducts.length > 0 ? topProducts.map((p, i) => `<tr class="border-b border-slate-200"><td class="p-3">${i + 1}</td><td class="p-3">${p.item_name}</td><td class="p-3">${p.total_qty}</td><td class="p-3">${app.utils.formatCurrency(p.revenue)} ${currency}</td></tr>`).join('') : `<tr><td colspan="4" class="text-center p-4">لا توجد بيانات كافية.</td></tr>`;
},
lineChart(labels, revenueData, profitData, expenseData) {
    const ctx = document.getElementById('finance-line-chart').getContext('2d');
    if (app.state.charts.line) app.state.charts.line.destroy();
    
    app.state.charts.line = new Chart(ctx, { 
        type: 'line', 
        data: { 
            labels, 
            datasets: [
                { label: 'إجمالي الإيرادات', data: revenueData, borderColor: '#10B981', backgroundColor: 'rgba(16, 185, 129, 0.1)', fill: false, tension: 0.4 },
                { label: 'إجمالي المصروفات', data: expenseData, borderColor: '#ef4444', backgroundColor: 'rgba(239, 68, 68, 0.1)', fill: false, tension: 0.4 },
                { label: 'صافي الربح', data: profitData, borderColor: '#0ea5e9', backgroundColor: 'rgba(14, 165, 233, 0.1)', fill: true, tension: 0.4 }
            ] 
        }, 
        options: { 
            responsive: true, maintainAspectRatio: false, 
            scales: { x: { grid: { color: '#f3f4f6' } }, y: { beginAtZero: true, grid: { color: '#f3f4f6' } } }, 
            plugins: { 
                legend: { display: true, position: 'top' },
                tooltip: { backgroundColor: '#1f2937', titleColor: '#fff', bodyColor: '#d1d5db', padding: 10, borderColor: '#4f46e5', borderWidth: 1 }
            } 
        } 
    });
},
pieChart(labels, data) {
    const ctx = document.getElementById('finance-pie-chart').getContext('2d');
    const typeTranslations = { 'medication': 'أدوية', 'feed': 'أعلاف', 'supply': 'مستلزمات', 'service': 'خدمات'};
    const translatedLabels = labels.map(l => typeTranslations[l] || 'أصناف أخرى');
    if (app.state.charts.pie) app.state.charts.pie.destroy();
    app.state.charts.pie = new Chart(ctx, { type: 'doughnut', data: { labels: translatedLabels, datasets: [{ data, backgroundColor: ['#10B981','#0ea5e9','#F59E0B', '#EF4444', '#8B5CF6'] }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: (ctx) => `${ctx.label}: ${app.utils.formatCurrency(ctx.raw)}` } } } } });
},
chatSessionsList() {
    const listEl = app.elements.chatHistoryList;
    if (!listEl) return;
    listEl.innerHTML = ''; 
    if (app.state.chat.sessions.length > 0) {
        app.state.chat.sessions.forEach(session => {
           const sessionEl = document.createElement('div');
           sessionEl.className = `chat-session-item p-3 my-1 cursor-pointer rounded-lg hover:bg-slate-200 relative ${session.id == app.state.chat.activeSessionId ? 'active' : ''}`;
           sessionEl.dataset.sessionId = session.id;
           sessionEl.innerHTML = `
                <p class="font-semibold text-slate-700 truncate pr-8">${session.title || 'محادثة جديدة'}</p>
                <div class="session-actions-wrapper">
                    <div class="session-actions">
                        <button class="session-menu-btn text-slate-500 hover:text-slate-800 p-1"><i class="fas fa-ellipsis-h pointer-events-none"></i></button>
                        <div class="session-menu hidden py-1">
                            <a href="#" class="rename-session-btn flex items-center gap-3 px-4 py-2 text-sm text-slate-100 hover:bg-slate-700" data-session-id="${session.id}"><i class="fas fa-pencil-alt w-4 text-center"></i><span>إعادة تسمية</span></a>
                            <a href="#" class="delete-session-btn flex items-center gap-3 px-4 py-2 text-sm text-rose-400 hover:bg-slate-700 hover:text-rose-300" data-session-id="${session.id}"><i class="fas fa-trash w-4 text-center"></i><span>حذف</span></a>
                        </div>
                    </div>
                </div>
           `;
           listEl.appendChild(sessionEl);
        });
    } else {
         listEl.innerHTML = '<p class="text-center text-slate-500 p-4">لا توجد محادثات سابقة.</p>';
    }
},
activeChatMessages() {
    const windowEl = app.elements.chatWindow;
    if (!windowEl) return;
    windowEl.innerHTML = '';
    if (app.state.chat.messages.length === 0) {
        if (app.elements.chatWelcome) app.elements.chatWelcome.classList.remove('hidden');
        return;
    }
    if (app.elements.chatWelcome) app.elements.chatWelcome.classList.add('hidden');
    app.state.chat.messages.forEach((msg, index) => {
        const isUser = msg.role === 'user';
        const imageSrc = msg.image_base64 || msg.image_path;
        const imageHTML = imageSrc ? `<img src="${imageSrc}" alt="Uploaded image" class="mt-2 rounded-lg max-w-full h-auto">` : '';
        const audioHTML = msg.audio_path ? `<audio controls class="w-full mt-2" src="${msg.audio_path}"></audio>` : '';
        let contentHTML;
         if (msg.isTyping) {
             contentHTML = '<span class="typing-cursor"></span>';
         } else if (msg.content) {
             contentHTML = app.converter.makeHtml(msg.content);
         } else {
             contentHTML = '';
         }

        const userActions = isUser ? `
            <div class="message-actions mr-2">
                <button title="تعديل" class="edit-user-msg-btn text-slate-400 hover:text-white" data-message-index="${index}"><i class="fas fa-pencil-alt"></i></button>
            </div>` : '';
        
        const aiActions = !isUser && !msg.isTyping ? `
            <div class="message-actions ml-2">
                 <button title="قراءة الرسالة" class="read-aloud-btn text-slate-500 hover:text-slate-800" data-message-index="${index}"><i class="fas fa-volume-up"></i></button>
                 <button title="إعادة إنشاء الرد" class="regenerate-ai-msg-btn text-slate-500 hover:text-slate-800" data-message-index="${index}"><i class="fas fa-sync-alt"></i></button>
                 <button title="نسخ" class="copy-ai-msg-btn text-slate-500 hover:text-slate-800" data-message-index="${index}"><i class="fas fa-copy"></i></button>
            </div>` : '';

        const messageEl = document.createElement('div');
        messageEl.className = `flex items-end gap-2 ${isUser ? 'justify-end' : 'justify-start'}`;
        messageEl.dataset.messageIndex = index;
        
        const messageBubble = `
            <div class="${isUser ? 'bg-sky-500 text-white' : 'bg-slate-200 text-slate-800'} p-3 rounded-lg max-w-xs md:max-w-md shadow-sm">
                <div class="message-content">${contentHTML}</div>
                ${imageHTML}
                ${audioHTML}
            </div>`;
        
        messageEl.innerHTML = isUser ? `${userActions}${messageBubble}` : `${messageBubble}${aiActions}`;
        
        windowEl.appendChild(messageEl);
    });
    windowEl.scrollTop = windowEl.scrollHeight;
},
 saleItemDetails(item) {
    const detailsEl = document.getElementById('sale-details');
    const saleTypeSelector = document.getElementById('sale-type-selector');
    if (!item) {
        detailsEl.classList.add('hidden');
        return;
    }

    detailsEl.classList.remove('hidden');
    const saleType = saleTypeSelector.value;
    let detailsText = '';

    if (saleType === 'package') {
        detailsText = `سعر الشراء: <strong>${app.utils.formatCurrency(item.purchase_price)}</strong> | سعر البيع: <strong>${app.utils.formatCurrency(item.price)} ${app.state.settings.currency || 'EGP'}</strong>`;
    } else { // unit
        const unitPurchasePrice = (item.purchase_price / item.package_size).toFixed(2);
        if (item.unit_sale_price && item.unit_name) {
            detailsText = `شراء الوحدة: <strong>${app.utils.formatCurrency(unitPurchasePrice)}</strong> | بيع الوحدة: <strong>${app.utils.formatCurrency(item.unit_sale_price)} ${app.state.settings.currency || 'EGP'}</strong>`;
        } else {
            detailsText = `<span class="text-rose-500">هذا الصنف غير متاح للبيع بالفرط. يرجى تحديد سعر الوحدة أولاً.</span>`;
        }
    }
     detailsEl.innerHTML = detailsText;
}