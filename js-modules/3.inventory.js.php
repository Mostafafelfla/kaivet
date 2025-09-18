// --- 3. INVENTORY MODULE ---
bindInventoryEvents() {
    ['medications-tab', 'feeds-tab', 'supplies-tab'].forEach(tabId => {
        document.getElementById(tabId).addEventListener('click', (e) => this.handleTabClick('inventory', e.target));
    });
    document.getElementById('inventory-view').addEventListener('click', async (e) => {
        const editBtn = e.target.closest('.edit-btn');
        if (editBtn) { this.modals.showEditInventory(this.state.inventory.find(i => i.id == editBtn.dataset.id)); }
        const deleteBtn = e.target.closest('.delete-btn');
        if (deleteBtn) { this.modals.showConfirm('تأكيد الحذف', 'سيتم حذف الصنف من المخزون مع الاحتفاظ به في التقارير القديمة. هل أنت متأكد؟', async () => { if ((await this.api('inventory', 'DELETE', { id: deleteBtn.dataset.id, soft: true })).success) await this.loadAllData(); }); }
    });
    ['medication', 'feed', 'supply'].forEach(type => {
        document.getElementById(`add-${type}-form`).addEventListener('submit', async (e) => {
            e.preventDefault(); const form = e.target;
            const formData = {
                name: form.name.value,
                purchase_price: form.purchase_price.value,
                price: form.price.value,
                quantity: form.quantity.value,
                package_size: form.package_size.value,
                unit_name: form.unit_name.value,
                unit_sale_price: form.unit_sale_price.value,
                type: type
            };
            const result = await this.api('inventory', 'POST', formData);
            if (result.success) { await this.loadAllData(); form.reset(); }
        });
    });
    document.getElementById('search-input').addEventListener('input', () => this.render.inventory());
},