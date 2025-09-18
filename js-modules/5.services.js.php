// --- 5. SERVICES MODULE ---
bindServicesEvents() {
    document.getElementById('record-service-form').addEventListener('submit', async e => {
        e.preventDefault();
        const description = document.getElementById('service-description').value;
        const price = parseFloat(document.getElementById('service-price').value);

        if (description.trim() && price > 0) {
             const result = await this.api('transactions', 'POST', { 
                type: 'service', 
                description: description, 
                price: price
            });
             if (result.success) { 
                await this.loadAllData(); 
                e.target.reset(); 
            }
        } else {
            this.utils.showMessageBox('يرجى إدخال وصف وسعر صحيح للخدمة.', 'error');
        }
    });

     ['daily-services-tab', 'monthly-services-tab', 'all-services-tab'].forEach(tabId => {
        document.getElementById(tabId).addEventListener('click', (e) => this.handleTabClick('services', e.target));
    }); 

    document.getElementById('services-view').addEventListener('click', e => {
        const editBtn = e.target.closest('.edit-service-btn');
        if (editBtn) {
            const service = this.state.sales.find(s => s.id == editBtn.dataset.id);
            if(service) this.modals.showEditService(service);
        }
        const deleteBtn = e.target.closest('.delete-service-btn');
        if (deleteBtn) {
            this.modals.showConfirm('تأكيد الحذف', 'هل أنت متأكد من حذف هذه الخدمة؟', async () => {
                if ((await this.api('transactions', 'DELETE', { id: deleteBtn.dataset.id, type: 'service' })).success) {
                    await this.loadAllData();
                }
            });
        }
    });
},