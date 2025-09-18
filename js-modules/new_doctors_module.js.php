// --- دالة جديدة لإضافتها إلى كائن 'app' ---

bindDoctorsEvents() {
    const form = document.getElementById('add-doctor-form');
    const formBtnText = document.getElementById('doctor-form-btn-text');
    const cancelBtn = document.getElementById('cancel-doctor-edit-btn');
    const doctorIdField = document.getElementById('doctor-id');

    // دالة لإعادة تعيين الفورم
    const resetForm = () => {
        form.reset();
        doctorIdField.value = '';
        formBtnText.textContent = 'إضافة طبيب';
        cancelBtn.classList.add('hidden');
    };

    // عند إرسال الفورم (إضافة أو تعديل)
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const doctor_id = doctorIdField.value;
        
        const data = {
            name: document.getElementById('doctor-name').value,
            specialty: document.getElementById('doctor-specialty').value,
            phone: document.getElementById('doctor-phone').value,
            address: document.getElementById('doctor-address').value,
        };

        let result;
        if (doctor_id) {
            // هذا تعديل (PUT)
            data.doctor_id = doctor_id;
            result = await app.api('doctors', 'PUT', data);
        } else {
            // هذا إضافة جديد (POST)
            result = await app.api('doctors', 'POST', data);
        }

        if (result.success) {
            await app.loadAllData(); // لإعادة تحميل القائمة
            resetForm();
        }
    });

    // زر إلغاء التعديل
    cancelBtn.addEventListener('click', resetForm);

    // ربط الأزرار في القائمة (الحذف والتعديل)
    document.getElementById('doctors-list-container').addEventListener('click', async (e) => {
        const editBtn = e.target.closest('.edit-doctor-btn');
        const deleteBtn = e.target.closest('.delete-doctor-btn');

        if (editBtn) {
            const doctorId = editBtn.dataset.id;
            const doctor = app.state.doctors.find(d => d.id == doctorId);
            if (doctor) {
                // ملء الفورم ببيانات الطبيب
                doctorIdField.value = doctor.id;
                document.getElementById('doctor-name').value = doctor.name;
                document.getElementById('doctor-specialty').value = doctor.specialty;
                document.getElementById('doctor-phone').value = doctor.phone;
                document.getElementById('doctor-address').value = doctor.address;
                
                formBtnText.textContent = 'حفظ التعديلات';
                cancelBtn.classList.remove('hidden');
                window.scrollTo(0, 0); // الصعود لأعلى الصفحة (للفورم)
            }
        }

        if (deleteBtn) {
            const doctorId = deleteBtn.dataset.id;
            app.modals.showConfirm('تأكيد الحذف', 'هل أنت متأكد من حذف هذا الطبيب؟ سيتم حذف جميع المواعيد والتقييمات المرتبطة به.', async () => {
                const result = await app.api('doctors', 'DELETE', { id: doctorId });
                if (result.success) {
                    await app.loadAllData();
                }
            });
        }
    });
},