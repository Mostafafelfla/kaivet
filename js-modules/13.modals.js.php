// --- MODAL FUNCTIONS ---
showConfirm(title, message, callback) {
    const modal = document.getElementById('confirm-modal');
    modal.querySelector('#confirm-title').textContent = title; modal.querySelector('#confirm-message').textContent = message; modal.classList.remove('hidden');
    const yesBtn = modal.querySelector('#confirm-yes-btn'), noBtn = modal.querySelector('#confirm-no-btn');
    const cleanup = () => { modal.classList.add('hidden'); yesBtn.removeEventListener('click', onConfirm); noBtn.removeEventListener('click', onCancel); };
    const onConfirm = () => { callback(); cleanup(); };
    const onCancel = () => cleanup();
    yesBtn.addEventListener('click', onConfirm, {once: true}); noBtn.addEventListener('click', onCancel, {once: true});
},
showEditInventory(item) {
    if (!item) return; 
    const modal = document.getElementById('custom-modal');
    
    document.getElementById('modal-expense-fields').classList.add('hidden');
    document.getElementById('modal-service-fields').classList.add('hidden');
    document.getElementById('modal-sale-fields').classList.add('hidden');
    document.getElementById('modal-rename-fields').classList.add('hidden');
    document.getElementById('modal-supplier-fields').classList.add('hidden');
    document.getElementById('modal-inventory-fields').classList.remove('hidden');

    modal.querySelector('#modal-title').textContent = `تعديل ${item.name}`; 
    modal.querySelector('#modal-input-name').value = item.name;
    modal.querySelector('#modal-input-price').value = item.price;
    modal.querySelector('#modal-input-purchase-price').value = item.purchase_price;
    modal.querySelector('#modal-input-quantity').value = item.quantity;
    modal.querySelector('#modal-input-package-size').value = item.package_size || '';
    modal.querySelector('#modal-input-unit-name').value = item.unit_name || '';
    modal.querySelector('#modal-input-unit-sale-price').value = item.unit_sale_price || '';
    modal.classList.remove('hidden');
    
    const confirmBtn = modal.querySelector('#modal-confirm-btn'), cancelBtn = modal.querySelector('#modal-cancel-btn');
    const cleanup = () => { modal.classList.add('hidden'); confirmBtn.removeEventListener('click', onConfirm); };
    const onConfirm = async () => { 
        const data = {
            id: item.id, 
            name: modal.querySelector('#modal-input-name').value, 
            price: modal.querySelector('#modal-input-price').value, 
            purchase_price: modal.querySelector('#modal-input-purchase-price').value, 
            quantity: modal.querySelector('#modal-input-quantity').value,
            package_size: modal.querySelector('#modal-input-package-size').value,
            unit_name: modal.querySelector('#modal-input-unit-name').value,
            unit_sale_price: modal.querySelector('#modal-input-unit-sale-price').value
        };
        if ((await app.api('inventory', 'PUT', data)).success) { 
            await app.loadAllData(); 
            cleanup(); 
        } 
    };
    confirmBtn.addEventListener('click', onConfirm, {once: true}); cancelBtn.addEventListener('click', cleanup, {once: true});
},
showEditExpense(item) {
    if (!item) return;
    const modal = document.getElementById('custom-modal');

    document.getElementById('modal-inventory-fields').classList.add('hidden');
    document.getElementById('modal-service-fields').classList.add('hidden');
    document.getElementById('modal-sale-fields').classList.add('hidden');
    document.getElementById('modal-rename-fields').classList.add('hidden');
    document.getElementById('modal-supplier-fields').classList.add('hidden');
    const expenseFields = document.getElementById('modal-expense-fields');
    expenseFields.classList.remove('hidden');

    const descInput = document.getElementById('modal-expense-description');
    const amountInput = document.getElementById('modal-expense-amount');

    descInput.value = item.description;
    amountInput.value = item.amount;
    modal.querySelector('#modal-title').textContent = `تعديل مصروف`;
    modal.classList.remove('hidden');

    const confirmBtn = modal.querySelector('#modal-confirm-btn'), cancelBtn = modal.querySelector('#modal-cancel-btn');
    const cleanup = () => {
        modal.classList.add('hidden');
        expenseFields.classList.add('hidden');
        confirmBtn.removeEventListener('click', onConfirm);
    };
    const onConfirm = async () => {
        const result = await app.api('transactions', 'PUT', { id: item.id, description: descInput.value, amount: amountInput.value, type: 'expense' });
        if (result.success) { await app.loadAllData(); cleanup(); }
    };
    confirmBtn.addEventListener('click', onConfirm, { once: true });
    cancelBtn.addEventListener('click', cleanup, { once: true });
},
 showEditService(item) {
    if (!item) return;
    const modal = document.getElementById('custom-modal');

    document.getElementById('modal-inventory-fields').classList.add('hidden');
    document.getElementById('modal-expense-fields').classList.add('hidden');
    document.getElementById('modal-sale-fields').classList.add('hidden');
    document.getElementById('modal-rename-fields').classList.add('hidden');
    document.getElementById('modal-supplier-fields').classList.add('hidden');
    const serviceFields = document.getElementById('modal-service-fields');
    serviceFields.classList.remove('hidden');

    const descInput = document.getElementById('modal-service-description');
    const priceInput = document.getElementById('modal-service-price');

    descInput.value = item.item_name;
    priceInput.value = item.final_price;
    modal.querySelector('#modal-title').textContent = `تعديل خدمة`;
    modal.classList.remove('hidden');

    const confirmBtn = modal.querySelector('#modal-confirm-btn'), cancelBtn = modal.querySelector('#modal-cancel-btn');
    const cleanup = () => {
        modal.classList.add('hidden');
        serviceFields.classList.add('hidden');
        confirmBtn.removeEventListener('click', onConfirm);
    };
    const onConfirm = async () => {
        const result = await app.api('transactions', 'PUT', { id: item.id, description: descInput.value, price: priceInput.value, type: 'service' });
        if (result.success) { await app.loadAllData(); cleanup(); }
    };
    confirmBtn.addEventListener('click', onConfirm, { once: true });
    cancelBtn.addEventListener('click', cleanup, { once: true });
},
showEditSale(item) {
    if (!item) return;

    if (item.sale_type === 'service') {
        app.utils.showMessageBox('لا يمكن تعديل الخدمات. يرجى إلغاء العملية وتسجيلها من جديد.', 'error');
        return;
    }

    const modal = document.getElementById('custom-modal');

    document.getElementById('modal-inventory-fields').classList.add('hidden');
    document.getElementById('modal-service-fields').classList.add('hidden');
    document.getElementById('modal-expense-fields').classList.add('hidden');
    document.getElementById('modal-rename-fields').classList.add('hidden');
    document.getElementById('modal-supplier-fields').classList.add('hidden');
    const saleFields = document.getElementById('modal-sale-fields');
    saleFields.classList.remove('hidden');

    const nameEl = document.getElementById('modal-sale-item-name');
    const quantityInput = document.getElementById('modal-sale-quantity');
    const discountInput = document.getElementById('modal-sale-discount');


    nameEl.textContent = item.item_name;
    quantityInput.value = item.quantity;
    discountInput.value = item.discount;
    modal.querySelector('#modal-title').textContent = `تعديل عملية بيع`;
    modal.classList.remove('hidden');

    const confirmBtn = modal.querySelector('#modal-confirm-btn');
    const cancelBtn = modal.querySelector('#modal-cancel-btn');
    
    const cleanup = () => {
        modal.classList.add('hidden');
        saleFields.classList.add('hidden');
        confirmBtn.removeEventListener('click', onConfirm);
    };

    const onConfirm = async () => {
        const newQuantity = quantityInput.value;
        const newDiscount = discountInput.value;
        if (newQuantity && newQuantity > 0) {
            const result = await app.api('transactions', 'PUT', { 
                id: item.id, 
                quantity: newQuantity, 
                discount: newDiscount,
                type: 'sale' 
            });
            if (result.success) {
                await app.loadAllData();
                cleanup();
            }
        } else {
            app.utils.showMessageBox('الرجاء إدخال كمية صحيحة.', 'error');
        }
    };

    confirmBtn.addEventListener('click', onConfirm, { once: true });
    cancelBtn.addEventListener('click', cleanup, { once: true });
},
showEditSupplier(item) {
    if (!item) return;
    const modal = document.getElementById('custom-modal');
    
    document.getElementById('modal-inventory-fields').classList.add('hidden');
    document.getElementById('modal-expense-fields').classList.add('hidden');
    document.getElementById('modal-service-fields').classList.add('hidden');
    document.getElementById('modal-sale-fields').classList.add('hidden');
    document.getElementById('modal-rename-fields').classList.add('hidden');
    
    const supplierFields = document.getElementById('modal-supplier-fields');
    supplierFields.classList.remove('hidden');

    modal.querySelector('#modal-title').textContent = `تعديل المورد: ${item.name}`;
    document.getElementById('modal-supplier-name').value = item.name;
    document.getElementById('modal-supplier-phone').value = item.phone || '';
    document.getElementById('modal-supplier-email').value = item.email || '';
    document.getElementById('modal-supplier-address').value = item.address || '';
    document.getElementById('modal-supplier-notes').value = item.notes || '';
    
    modal.classList.remove('hidden');

    const confirmBtn = modal.querySelector('#modal-confirm-btn');
    const cancelBtn = modal.querySelector('#modal-cancel-btn');
    
    const cleanup = () => {
        modal.classList.add('hidden');
        supplierFields.classList.add('hidden');
        confirmBtn.removeEventListener('click', onConfirm);
    };

    const onConfirm = async () => {
        const data = {
            id: item.id,
            name: document.getElementById('modal-supplier-name').value,
            phone: document.getElementById('modal-supplier-phone').value,
            email: document.getElementById('modal-supplier-email').value,
            address: document.getElementById('modal-supplier-address').value,
            notes: document.getElementById('modal-supplier-notes').value,
        };
        if ((await app.api('suppliers', 'PUT', data)).success) {
            await app.loadAllData();
            cleanup();
        }
    };
    
    confirmBtn.addEventListener('click', onConfirm, { once: true });
    cancelBtn.addEventListener('click', cleanup, { once: true });
},
showRenameSession(session) {
    if (!session) return;
    const modal = document.getElementById('custom-modal');
    const renameFields = document.getElementById('modal-rename-fields');
    const renameInput = document.getElementById('modal-rename-input');
    
    document.getElementById('modal-inventory-fields').classList.add('hidden');
    document.getElementById('modal-expense-fields').classList.add('hidden');
    document.getElementById('modal-service-fields').classList.add('hidden');
    document.getElementById('modal-sale-fields').classList.add('hidden');
    document.getElementById('modal-supplier-fields').classList.add('hidden');
    renameFields.classList.remove('hidden');
    
    modal.querySelector('#modal-title').textContent = 'إعادة تسمية المحادثة';
    renameInput.value = session.title;
    modal.classList.remove('hidden');
    
    const confirmBtn = modal.querySelector('#modal-confirm-btn');
    const cancelBtn = modal.querySelector('#modal-cancel-btn');

    const cleanup = () => {
        modal.classList.add('hidden');
        renameFields.classList.add('hidden');
        confirmBtn.removeEventListener('click', onConfirm);
    };

    const onConfirm = async () => {
        const newTitle = renameInput.value.trim();
        if (newTitle) {
             const result = await app.api('chat', 'POST', { action: 'rename_session', session_id: session.id, title: newTitle });
             if (result.success) {
                await app.loadChatSessions();
                cleanup();
             }
        }
    };
    
    confirmBtn.addEventListener('click', onConfirm, { once: true });
    cancelBtn.addEventListener('click', cleanup, { once: true });
},
showCase(data = null, isNewAnimalForOwner = false) {
    const modal = document.getElementById('case-modal');
    const form = document.getElementById('case-form');
    form.reset();
    
    const ownerNameInput = form.elements['owner_name'];
    // لم نعد بحاجة لتعريف حقل الهاتف الواحد القديم

    ownerNameInput.disabled = isNewAnimalForOwner;
    // حقول الهاتف لن يتم تعطيلها الآن

    form.elements['id'].value = data && !isNewAnimalForOwner ? data.id : '';
    modal.querySelector('#case-modal-title').textContent = isNewAnimalForOwner ? `إضافة حيوان لـ ${data.owner_name}` : (data && data.id ? `تعديل: ${data.animal_name}` : '➕ إضافة حالة');
    
    const vacC = document.getElementById('vaccinations-container');
    const treatC = document.getElementById('treatments-container');
    vacC.innerHTML = '';
    treatC.innerHTML = '';

    if (data) {
        ownerNameInput.value = data.owner_name || '';
        
        // ==========  هذا هو التحديث المطلوب  ==========
        // يقوم بملء حقلي الهاتف المنفصلين من البيانات القادمة
        document.getElementById('owner-phone').value = data.owner_phone || '';
        document.getElementById('owner-phone-code').value = data.owner_phone_code || '20'; // القيمة الافتراضية 20 إذا لم تكن موجودة
        // ===============================================
        
        if (!isNewAnimalForOwner) {
            form.elements['animal_name'].value = data.animal_name || '';
            form.elements['animal_type'].value = data.animal_type || '';
            form.elements['notes'].value = data.notes || '';
            (data.vaccinations || []).forEach(v => this.addDynamicInput('vaccinations-container', 'اسم التطعيم', v));
            (data.treatments || []).forEach(t => this.addDynamicInput('treatments-container', 'وصف العلاج', t));
        }
    } else {
        // عند إضافة حالة جديدة تماماً، تأكد أن القيمة الافتراضية لكود الدولة هي 20
        document.getElementById('owner-phone-code').value = '20';
    }
    modal.classList.remove('hidden');
},

addDynamicInput(containerId, placeholder, data = {}) {
    const div = document.createElement('div');
    div.className = 'dynamic-input-group p-3 border rounded-lg bg-slate-50 space-y-2 border-slate-200';
    div.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-500">اسم الإجراء</label>
                <input type="text" placeholder="${placeholder}" class="w-full p-2 border-slate-300 rounded-md text-right focus:ring-2 focus:ring-sky-400" required value="${data.name || ''}">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500">تاريخ الجرعة الحالية</label>
                <input type="date" title="تاريخ الجرعة الحالية" class="w-full p-2 border-slate-300 rounded-md" value="${app.utils.formatDateForInput(data.date)}">
            </div>
        </div>
        <div class="relative">
            <label class="block text-xs font-medium text-slate-500">تاريخ الجرعة التالية (للتذكير)</label>
            <div class="flex items-center gap-2">
                <input type="date" title="تاريخ الجرعة التالية" class="w-full p-2 border-slate-300 rounded-md" value="${app.utils.formatDateForInput(data.next_due_date)}">
                <button type="button" class="remove-dynamic-input-btn text-rose-500 font-bold text-2xl hover:text-rose-700">&times;</button>
            </div>
        </div>`;
    document.getElementById(containerId).appendChild(div);
}