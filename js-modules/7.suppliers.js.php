// --- 7. SUPPLIERS MODULE ---
bindSuppliersEvents() {
    document.getElementById('add-supplier-form').addEventListener('submit', async e => {
        e.preventDefault();
        const form = e.target;
        const supplierData = {
            name: form.name.value,
            phone: form.phone.value,
            email: form.email.value,
            address: form.address.value,
            notes: form.notes.value
        };
        
        const result = await this.api('suppliers', 'POST', supplierData);
        if (result.success) {
            await this.loadAllData();
            form.reset();
        }
    });
    
    document.getElementById('suppliers-list-table').addEventListener('click', async e => {
        const editBtn = e.target.closest('.edit-supplier-btn');
        if (editBtn) {
            const supplier = this.state.suppliers.find(s => s.id == editBtn.dataset.id);
            if (supplier) this.modals.showEditSupplier(supplier);
        }
        
        const deleteBtn = e.target.closest('.delete-supplier-btn');
        if (deleteBtn) {
            this.modals.showConfirm('تأكيد الحذف', 'هل أنت متأكد من حذف هذا المورد؟', async () => {
                if ((await this.api('suppliers', 'DELETE', { id: deleteBtn.dataset.id })).success) {
                    await this.loadAllData();
                }
            });
        }
    });
},