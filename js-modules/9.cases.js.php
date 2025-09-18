// --- 9. CASES MODULE ---
bindCasesEvents() {
    document.getElementById('add-case-btn').addEventListener('click', () => this.modals.showCase());
    document.getElementById('case-modal-cancel-btn').addEventListener('click', () => document.getElementById('case-modal').classList.add('hidden'));
    document.getElementById('case-modal-cancel-btn-header').addEventListener('click', () => document.getElementById('case-modal').classList.add('hidden'));
    document.getElementById('case-form').addEventListener('submit', async e => {
        e.preventDefault();
        const getDynamicData = cId => Array.from(document.getElementById(cId).children).map(d => ({ name: d.querySelector('input[type="text"]').value, date: d.querySelector('input[title="تاريخ الجرعة الحالية"]').value, next_due_date: d.querySelector('input[title="تاريخ الجرعة التالية"]').value }));
        const caseData = { id: document.getElementById('case-id').value || null, owner_name: document.getElementById('owner-name').value, animal_name: document.getElementById('animal-name').value, animal_type: document.getElementById('animal-type').value, owner_phone: document.getElementById('owner-phone').value, notes: document.getElementById('case-notes').value, vaccinations: getDynamicData('vaccinations-container'), treatments: getDynamicData('treatments-container') };
        if ((await this.api('cases', caseData.id ? 'PUT' : 'POST', caseData)).success) { document.getElementById('case-modal').classList.add('hidden'); await this.loadAllData(); }
    });
    document.getElementById('cases-list').addEventListener('click', e => {
        const editBtn = e.target.closest('.edit-case-btn');
        if (editBtn) {
            const caseId = parseInt(editBtn.dataset.id, 10);
            const caseToEdit = app.state.cases.find(c => c.id === caseId);
            if (caseToEdit) {
                app.modals.showCase(caseToEdit);
            } else {
                console.error(`Case with ID ${caseId} not found.`);
            }
            return;
        }
        const deleteBtn = e.target.closest('.delete-case-btn');
        if (deleteBtn) {
            const caseId = deleteBtn.dataset.id;
            app.modals.showConfirm('تأكيد الحذف', 'هل أنت متأكد من حذف هذه الحالة؟ سيتم حذف سجلات الحيوان بالكامل.', async () => {
                if ((await app.api('cases', 'DELETE', { id: caseId })).success) {
                    await app.loadAllData();
                }
            });
            return;
        }
        const addAnimalBtn = e.target.closest('.add-animal-to-owner-btn');
        if(addAnimalBtn) {
            const ownerData = { owner_name: addAnimalBtn.dataset.ownerName, owner_phone: addAnimalBtn.dataset.ownerPhone };
            app.modals.showCase(ownerData, true);
            return;
        }
        const expandBtn = e.target.closest('.expand-animal-details-btn');
          if (expandBtn) {
              const detailsEl = expandBtn.closest('.flex').nextElementSibling;
              if (detailsEl) {
                  detailsEl.style.maxHeight = detailsEl.style.maxHeight ? null : `${detailsEl.scrollHeight}px`;
              }
              return;
          }
    });
    document.getElementById('add-vaccination-btn').addEventListener('click', () => this.modals.addDynamicInput('vaccinations-container', 'اسم التطعيم/الدواء'));
    document.getElementById('add-treatment-btn').addEventListener('click', () => this.modals.addDynamicInput('treatments-container', 'وصف العلاج/الزيارة'));
    document.getElementById('case-modal').addEventListener('click', e => { if (e.target.classList.contains('remove-dynamic-input-btn')) e.target.closest('.dynamic-input-group').remove(); });
    document.getElementById('case-search-input').addEventListener('input', () => this.render.casesList());
},