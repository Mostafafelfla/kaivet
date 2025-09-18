// --- 8. TODO MODULE ---
bindTodoEvents() {
    document.getElementById('add-todo-form').addEventListener('submit', async e => {
        e.preventDefault(); const input = document.getElementById('todo-input');
        if (input.value.trim()) { if ((await this.api('todos', 'POST', { text: input.value.trim() })).success) { await this.loadAllData(); input.value = ''; } }
    });
    document.getElementById('todo-list-container').addEventListener('click', async e => {
        const checkbox = e.target.closest('input[type="checkbox"]');
        if (checkbox) {
            await this.api('todos', 'PUT', { id: checkbox.dataset.id, completed: checkbox.checked ? 1 : 0 });
            const todo = this.state.todos.find(t => t.id == checkbox.dataset.id); if (todo) todo.completed = checkbox.checked ? 1 : 0;
            this.render.todoList(); this.render.dashboardSummary();
        }
        const deleteBtn = e.target.closest('.delete-todo-btn');
        if (deleteBtn) { this.modals.showConfirm('تأكيد الحذف', 'هل أنت متأكد من حذف المهمة؟', async () => { if ((await this.api('todos', 'DELETE', { id: deleteBtn.dataset.id })).success) await this.loadAllData(); }); }
    });
},