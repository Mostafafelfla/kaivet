// --- 4. SALES MODULE ---
bindSalesEvents() {
    const saleItemNameInput = document.getElementById('sale-item-name');
    const autocompleteResults = document.getElementById('autocomplete-results');
    const saleDetails = document.getElementById('sale-details');
    const saleTypeSelector = document.getElementById('sale-type-selector');

    ['daily-sales-tab', 'monthly-sales-tab', 'all-sales-tab'].forEach(tabId => {
        document.getElementById(tabId).addEventListener('click', (e) => this.handleTabClick('sales', e.target));
    });

    saleItemNameInput.addEventListener('input', () => {
        const searchTerm = saleItemNameInput.value.toLowerCase(); autocompleteResults.innerHTML = '';
        if (searchTerm.length === 0) { autocompleteResults.classList.add('hidden'); return; }
        const matchingItems = this.state.inventory.filter(item => item.name.toLowerCase().includes(searchTerm) && (parseInt(item.quantity) > 0 || parseFloat(item.current_package_volume) > 0));
        if (matchingItems.length > 0) {
            autocompleteResults.classList.remove('hidden');
            matchingItems.forEach(item => {
                const itemEl = document.createElement('div'); itemEl.className = 'autocomplete-item'; 
                let stockInfo = `المتاح: ${item.quantity} عبوة`;
                if(item.current_package_volume > 0) {
                    stockInfo += ` و ${item.current_package_volume} ${item.unit_name || 'وحدة'}`;
                }
                itemEl.textContent = `${item.name} (${stockInfo})`;
                itemEl.addEventListener('click', () => { 
                    saleItemNameInput.value = item.name; 
                    this.state.ui.selectedInventoryItemForSale = item; 
                    autocompleteResults.classList.add('hidden'); 
                    this.render.saleItemDetails(item);
                });
                autocompleteResults.appendChild(itemEl);
            });
        } else { autocompleteResults.classList.add('hidden'); }
    });

    saleTypeSelector.addEventListener('change', () => {
        this.render.saleItemDetails(this.state.ui.selectedInventoryItemForSale);
    });

    document.addEventListener('click', (e) => { if (!e.target.closest('#record-sale-form')) { autocompleteResults.classList.add('hidden'); } });
    
    document.getElementById('record-sale-form').addEventListener('submit', async e => {
        e.preventDefault(); 
        const item = this.state.ui.selectedInventoryItemForSale;
        const quantity = parseFloat(document.getElementById('sale-quantity').value);
        const discount = parseFloat(document.getElementById('sale-discount').value) || 0;
        const sale_type = saleTypeSelector.value;

        if (item && quantity > 0) {
            const result = await this.api('transactions', 'POST', { 
                type: 'sale', 
                inventory_id: item.id, 
                quantity: quantity,
                discount: discount,
                sale_type: sale_type
            });

            if (result.success) { 
                await this.loadAllData(); 
                e.target.reset(); 
                this.state.ui.selectedInventoryItemForSale = null; 
                saleDetails.classList.add('hidden');
            }
        } else { this.utils.showMessageBox('الرجاء اختيار صنف صحيح وإدخال كمية.', 'error'); }
    });
    
    document.getElementById('sales-view')?.addEventListener('click', e => {
        const deleteBtn = e.target.closest('.delete-sale-btn');
        if (deleteBtn) {
            const sale = this.state.sales.find(s => s.id == deleteBtn.dataset.id);
            const message = 'سيتم إلغاء عملية البيع هذه وإرجاع الصنف للمخزون. هل أنت متأكد؟';
            this.modals.showConfirm('تأكيد الإلغاء', message, async () => {
                if ((await this.api('transactions', 'DELETE', { id: deleteBtn.dataset.id, type: 'sale' })).success) {
                    await this.loadAllData();
                }
            });
        }
        const editBtn = e.target.closest('.edit-sale-btn');
        if (editBtn) {
            const sale = this.state.sales.find(s => s.id == editBtn.dataset.id);
            if (sale) this.modals.showEditSale(sale);
        }
    });
},