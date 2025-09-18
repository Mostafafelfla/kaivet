// --- 6. EXPENSES MODULE ---
bindExpensesEvents() {
    document.getElementById('record-expense-form').addEventListener('submit', async e => {
        e.preventDefault();
        const result = await this.api('transactions', 'POST', { type: 'expense', description: document.getElementById('expense-description').value, amount: document.getElementById('expense-amount').value });
        if (result.success) { await this.loadAllData(); e.target.reset(); }
    });

    ['daily-expenses-tab', 'monthly-expenses-tab', 'all-expenses-tab'].forEach(tabId => {
        document.getElementById(tabId).addEventListener('click', (e) => this.handleTabClick('expenses', e.target));
    });

    document.getElementById('expenses-view').addEventListener('click', e => {
        const editBtn = e.target.closest('.edit-expense-btn');
        if (editBtn) {
            const expense = this.state.expenses.find(ex => ex.id == editBtn.dataset.id);
            if(expense) this.modals.showEditExpense(expense);
        }
        const deleteBtn = e.target.closest('.delete-expense-btn');
        if (deleteBtn) {
            this.modals.showConfirm('تأكيد الحذف', 'هل أنت متأكد من حذف هذا المصروف؟', async () => {
                if ((await this.api('transactions', 'DELETE', { id: deleteBtn.dataset.id, type: 'expense' })).success) {
                    await this.loadAllData();
                }
            });
        }
    });
},