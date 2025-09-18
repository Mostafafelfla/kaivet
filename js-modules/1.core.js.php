<?php
// js-modules/1.core.js.php (الإصدار الماستر - شامل كل التحديثات)
?>
// --- 1. CORE STATE & INIT ---
state: {
    inventory: [], sales: [], expenses: [], todos: [], cases: [], settings: {}, doctors: [], 
    suppliers: [], 
    services: [],
    vaccinationReminders: [],    // <-- يتضمن موديول الخدمات
    promotions: [],  // <-- يتضمن موديول العروض
    chat: {
        sessions: [],
        activeSessionId: null,
        messages: []
    },
    ui: { 
        isGeneratingResponse: false, 
        selectedInventoryItemForSale: null, 
        mapInitialized: false, 
        chatImageBase64: null, 
        chatAbortController: null, 
        isRecording: false,
        mediaRecorder: null,
        audioChunks: [],
        recordingTimerInterval: null,
        currentAudio: null, 
        currentReadAloudButton: null,
        croppieInstance: null,
        isTTSLoading: false
    },
    charts: { line: null, pie: null, reportsData: null },
},
elements: {},
init() {
    document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
    this.cacheElements(); 
    this.bindEvents(); 
    this.loadAllData(); 
    
    const lastView = localStorage.getItem('lastActiveView') || 'dashboard';
    this.switchView(lastView, true);

    this.converter = new showdown.Converter({tables: true, simplifiedAutoLink: true, strikethrough: true, tasklists: true});
    document.dispatchEvent(new Event('app-ready'));
},
cacheElements() {
    this.elements.loadingOverlay = document.getElementById('loading-overlay');
    this.elements.navLinks = document.querySelectorAll('.nav-link');
    this.elements.views = document.querySelectorAll('.view');
    this.elements.messageBox = document.getElementById('message-box');
    this.elements.sidebarProfilePic = document.getElementById('sidebar-profile-pic');
    this.elements.sidebarUserName = document.getElementById('sidebar-user-name');
    this.elements.hamburgerBtn = document.getElementById('hamburger-btn');
    this.elements.closeSidebarBtn = document.getElementById('close-sidebar-btn');
    this.elements.sidebar = document.getElementById('sidebar');
    this.elements.sidebarOverlay = document.getElementById('sidebar-overlay');
    this.elements.chatInput = document.getElementById('chat-input');
    this.elements.sendBtn = document.getElementById('send-btn');
    this.elements.sendBtnContent = document.getElementById('send-btn-content');
    this.elements.voiceInputBtn = document.getElementById('voice-input-btn');
    this.elements.imageInput = document.getElementById('image-input');
    this.elements.imagePreviewContainer = document.getElementById('image-preview-container');
    this.elements.imagePreview = document.getElementById('image-preview');
    this.elements.removeImageBtn = document.getElementById('remove-image-btn');
    this.elements.chatWindow = document.getElementById('chat-window');
    this.elements.chatHistorySidebar = document.getElementById('chat-history-sidebar');
    this.elements.chatHistoryList = document.getElementById('chat-history-list');
    this.elements.chatSidebarToggle = document.getElementById('chat-sidebar-toggle');
    this.elements.chatSidebarOverlay = document.getElementById('chat-sidebar-overlay');
    this.elements.newChatBtn = document.getElementById('new-chat-btn');
    this.elements.chatWelcome = document.getElementById('chat-welcome');
    this.elements.chatInputContainer = document.getElementById('chat-input-container');
    this.elements.recordingContainer = document.getElementById('recording-container');
    this.elements.cancelRecordingBtn = document.getElementById('cancel-recording-btn');
    this.elements.sendRecordingBtn = document.getElementById('send-recording-btn');
    this.elements.recordingTimer = document.getElementById('recording-timer');
},
bindEvents() {
    this.elements.navLinks.forEach(link => { 
        link.addEventListener('click', (e) => { 
            e.preventDefault(); 
            const view = e.currentTarget.dataset.view;
            this.switchView(view);
            if(window.innerWidth < 768) {
                this.toggleSidebar();
            }
        });
    });
    
    // (شامل كل الإضافات الجديدة)
    this.bindChatEvents();
    this.bindInventoryEvents(); 
    this.bindSalesEvents(); 
    this.bindServicesEvents();       
    this.bindExpensesEvents();
    this.bindTodoEvents(); 
    this.bindCasesEvents(); 
    this.bindSettingsEvents(); 
    this.bindReportsEvents();
    this.bindSuppliersEvents();
    this.bindDoctorsEvents(); 
    this.bindClinicServicesEvents(); // <-- إضافة "إدارة الخدمات"
    this.bindPromotionsEvents();     // <-- إضافة "إدارة العروض" (هذه هي التي تشغل الزر)

    if (this.elements.hamburgerBtn) {
        this.elements.hamburgerBtn.addEventListener('click', () => this.toggleSidebar());
    }
    if (this.elements.closeSidebarBtn) {
        this.elements.closeSidebarBtn.addEventListener('click', () => this.toggleSidebar());
    }
    if (this.elements.sidebarOverlay) {
        this.elements.sidebarOverlay.addEventListener('click', () => this.toggleSidebar());
    }

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.session-menu-btn')) {
            document.querySelectorAll('.session-menu').forEach(menu => menu.classList.add('hidden'));
        }
    });
},
toggleSidebar() {
    this.elements.sidebar.classList.toggle('translate-x-full');
    this.elements.sidebarOverlay.classList.toggle('hidden');
},
handleTabClick(view, button) {
    document.querySelectorAll(`#${view}-view .tab-btn`).forEach(b => b.classList.remove('active'));
    button.classList.add('active');
    document.querySelectorAll(`#${view}-view .tab-content-panel`).forEach(c => c.classList.add('hidden'));
    const contentId = button.id.replace('-tab', '-content');
    document.getElementById(contentId).classList.remove('hidden');
},
async api(endpoint, method = 'GET', body = null, showGlobalLoader = true) {
    if (showGlobalLoader) this.utils.showLoader(true);
    
    this.state.ui.chatAbortController = new AbortController();
    const options = { method, headers: {}, signal: this.state.ui.chatAbortController.signal };

    let url = `api/${endpoint}.php`;
    if (method === 'GET' && body) {
        url += `?${new URLSearchParams(body)}`;
    } else if (body instanceof FormData) {
        options.body = body;
    } else if (method !== 'GET' && body) {
        options.headers['Content-Type'] = 'application/json'; 
        options.body = JSON.stringify(body); 
    }
    
    try {
        const response = await fetch(url, options);
        if (response.status === 401) { 
             throw new Error('User not authenticated.');
        }
        const resultText = await response.text();
         try {
             const result = JSON.parse(resultText);
            if (!result.success) throw new Error(result.error || 'Unknown server error');
            if (result.message && method !== 'GET' && endpoint !== 'chat') this.utils.showMessageBox(result.message, 'success');
            return result;
         } catch (jsonError) {
            console.error("JSON Parsing Error:", jsonError, "Response Text:", resultText);
            throw new Error("استجابة غير صالحة من الخادم. قد يكون هناك خطأ في PHP.");
         }
    } catch (error) {
        if (error.name === 'AbortError') {
             console.log('Fetch aborted.');
             return { success: false, error: 'Aborted' }; 
        }
        console.error('API Error:', error); 
        if (error.message === 'User not authenticated.') {
            this.utils.showMessageBox('انتهت جلستك. يرجى تسجيل الدخول مرة أخرى.', 'error');
            setTimeout(() => { window.location.href = 'login.php'; }, 2000);
        } else {
             this.utils.showMessageBox(error.message, 'error'); 
        }
        return { success: false, error: error.message };
    } finally {
        if (showGlobalLoader) this.utils.showLoader(false);
    }
},
async loadAllData() {
    const result = await this.api('data', 'GET');
    if (result.success) { 
        this.state.inventory = result.data.inventory || [];
        this.state.sales = result.data.sales || [];
        this.state.expenses = result.data.expenses || [];
        this.state.todos = result.data.todos || [];
        this.state.vaccinationReminders = result.data.vaccination_reminders || [];
        this.state.cases = result.data.cases || [];
        this.state.settings = result.data.settings || {};
        this.state.doctors = result.data.doctors || []; 
        this.state.suppliers = result.data.suppliers || [];
        this.state.services = result.data.services || [];     // <-- محدث
        this.state.promotions = result.data.promotions || []; // <-- محدث
        await this.loadChatSessions();
        this.renderAll();
    } 
},
renderAll() {
    this.render.settings();
    this.render.inventory();
    
    const isToday = (dateString) => {
        if (!dateString) return false;
        const serverDatePart = dateString.substring(0, 10);
        const today = new Date();
        const clientTodayDatePart = today.getFullYear() + '-' + 
                                  String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                                  String(today.getDate()).padStart(2, '0');
        return serverDatePart === clientTodayDatePart;
    };
    
    const thisMonth = new Date().toLocaleDateString('en-CA').substring(0, 7);
    
    const productSales = this.state.sales.filter(s => s.sale_type !== 'service');
    const serviceSales = this.state.sales.filter(s => s.sale_type === 'service');

    this.render.productSalesList(document.getElementById('daily-sales-list'), productSales.filter(s => isToday(s.created_at)));
    this.render.productSalesList(document.getElementById('monthly-sales-list'), productSales.filter(s => s.sale_date && s.sale_date.startsWith(thisMonth)), true);
    this.render.productSalesList(document.getElementById('all-sales-list'), productSales, true);
    
    this.render.servicesList(document.getElementById('daily-services-list'), serviceSales.filter(s => isToday(s.created_at)));
    this.render.servicesList(document.getElementById('monthly-services-list'), serviceSales.filter(s => s.sale_date && s.sale_date.startsWith(thisMonth)), true);
    this.render.servicesList(document.getElementById('all-services-list'), serviceSales, true);
    
    this.render.expensesList(document.getElementById('daily-expenses-list'), this.state.expenses.filter(e => isToday(e.created_at)));
    this.render.expensesList(document.getElementById('monthly-expenses-list'), this.state.expenses.filter(e => e.expense_date && e.expense_date.startsWith(thisMonth)), true);
    this.render.expensesList(document.getElementById('all-expenses-list'), this.state.expenses, true);

    this.render.todoList();
    this.render.casesList();
    this.render.doctorsList(); 
    this.render.suppliersList();
    this.render.clinicServicesList(); // <-- محدث
    this.render.promotionsList();     // <-- محدث
    this.render.dashboardSummary();
    if(this.elements.chatHistoryList) this.render.chatSessionsList();

    if (document.getElementById('reports-view').classList.contains('hidden') === false) {
       this.loadReportsData();
    }
},
switchView(viewId, isInitialLoad = false) {
    if (viewId) {
        localStorage.setItem('lastActiveView', viewId);
    }
    
    const viewEl = document.getElementById(`${viewId}-view`);
    
    this.elements.views.forEach(v => {
        if (v !== viewEl) {
            v.classList.add('hidden');
        }
    });

    if (viewEl) {
        viewEl.classList.remove('hidden');
    }

    this.elements.navLinks.forEach(l => l.classList.remove('active'));
    const activeLink = document.querySelector(`.nav-link[data-view="${viewId}"]`); if (activeLink) activeLink.classList.add('active');
    
    if (viewId === 'dashboard') {
        this.render.dashboardSummary();
    }
    if (viewId === 'reports') this.loadReportsData();
    if (viewId === 'settings-profile' && !this.state.ui.mapInitialized) if (typeof google !== 'undefined' && google.maps) this.initMap();
},

// js-modules/2.events.js.php

bindDoctorsEvents() {
    const form = document.getElementById('add-doctor-form');
    if (!form) return;

    // --- تعريف العناصر ---
    const formBtnText = document.getElementById('doctor-form-btn-text');
    const cancelBtn = document.getElementById('cancel-doctor-edit-btn');
    const doctorIdField = document.getElementById('doctor-id');
    const fileInput = document.getElementById('doctor-profile-pic-input');
    const uploadPicText = document.getElementById('upload-pic-text');
    const cropModal = document.getElementById('doctor-crop-modal');
    const cropperImage = document.getElementById('doctor-cropper-image');
    const saveCropBtn = document.getElementById('save-crop-btn');
    const cancelCropBtn = document.getElementById('cancel-crop-btn');
    const doctorsList = document.getElementById('doctors-list-container');
    
    let cropperInstance = null;
    let croppedImageData = null;

    // --- دوال مساعدة ---
    const resetForm = () => {
        form.reset();
        doctorIdField.value = '';
        croppedImageData = null;
        fileInput.value = '';
        uploadPicText.textContent = 'اختر صورة للطبيب';
        uploadPicText.classList.remove('text-emerald-600');
        formBtnText.textContent = 'إضافة طبيب';
        cancelBtn.classList.add('hidden');
    };

    const cleanupCropper = () => {
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
        cropModal.classList.add('hidden');
        cropModal.classList.remove('flex');
    };

    // --- ربط الأحداث ---
    
    // عند اختيار ملف
    if (!fileInput.dataset.listenerAttached) {
        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    cropperImage.src = event.target.result;
                    cropModal.classList.remove('hidden');
                    cropModal.classList.add('flex');
                    
                    if (cropperInstance) cropperInstance.destroy();

                    cropperInstance = new Cropper(cropperImage, {
                        aspectRatio: 1,
                        viewMode: 1,
                        autoCropArea: 0.9,
                        background: false
                    });
                };
                reader.readAsDataURL(file);
            }
        });
        fileInput.dataset.listenerAttached = 'true';
    }

    // عند حفظ القص
    if (!saveCropBtn.dataset.listenerAttached) {
        saveCropBtn.addEventListener('click', () => {
            if (!cropperInstance) return;
            const canvas = cropperInstance.getCroppedCanvas({
                width: 400,
                height: 400,
                imageSmoothingQuality: 'high',
            });
            
            croppedImageData = canvas.toDataURL('image/jpeg', 0.9);
            uploadPicText.textContent = 'تم اختيار الصورة وجاهزة للحفظ';
            uploadPicText.classList.add('text-emerald-600');
            cleanupCropper();
        });
        saveCropBtn.dataset.listenerAttached = 'true';
    }

    // عند إلغاء القص
    if (!cancelCropBtn.dataset.listenerAttached) {
        cancelCropBtn.addEventListener('click', () => {
            fileInput.value = '';
            cleanupCropper();
        });
        cancelCropBtn.dataset.listenerAttached = 'true';
    }
    
    // عند إرسال الفورم الرئيسي
    if (!form.dataset.listenerAttached) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData();
            formData.append('doctor_id', doctorIdField.value);
            formData.append('name', document.getElementById('doctor-name').value);
            formData.append('specialty', document.getElementById('doctor-specialty').value);
            formData.append('phone', document.getElementById('doctor-phone').value);
            formData.append('address', document.getElementById('doctor-address').value);
            
            // إضافة الصورة المقصوصة (Base64) إلى الفورم
            if (croppedImageData) {
                formData.append('profile_pic_cropped', croppedImageData);
            }
            
            const result = await app.api('doctors', 'POST', formData);
            if (result.success) {
                await app.loadAllData();
                resetForm();
            }
        });
        form.dataset.listenerAttached = 'true';
    }
    
    if (!cancelBtn.dataset.listenerAttached) {
        cancelBtn.addEventListener('click', resetForm);
        cancelBtn.dataset.listenerAttached = 'true';
    }

    if (!doctorsList.dataset.listenerAttached) {
        doctorsList.addEventListener('click', async (e) => {
            const editBtn = e.target.closest('.edit-doctor-btn');
            if (editBtn) {
                const doctorId = editBtn.dataset.id;
                const doctor = app.state.doctors.find(d => d.id == doctorId);
                if (doctor) {
                    resetForm();
                    doctorIdField.value = doctor.id;
                    document.getElementById('doctor-name').value = doctor.name;
                    document.getElementById('doctor-specialty').value = doctor.specialty || '';
                    document.getElementById('doctor-phone').value = doctor.phone || '';
                    document.getElementById('doctor-address').value = doctor.address || '';
                    uploadPicText.textContent = 'يمكنك رفع صورة جديدة لتغيير الحالية';
                    formBtnText.textContent = 'حفظ التعديلات';
                    cancelBtn.classList.remove('hidden');
                    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }

            const deleteBtn = e.target.closest('.delete-doctor-btn');
            if (deleteBtn) {
                const doctorId = deleteBtn.dataset.id;
                app.modals.showConfirm('تأكيد الحذف', 'هل أنت متأكد من حذف هذا الطبيب؟', async () => {
                    const formData = new FormData();
                    formData.append('_method', 'DELETE');
                    formData.append('id', doctorId);
                    const result = await app.api('doctors', 'POST', formData);
                    if (result.success) {
                        await app.loadAllData();
                    }
                });
            }
        });
        doctorsList.dataset.listenerAttached = 'true';
    }
},
// (داخل ملف js-modules/1.core.js.php)

// --- ⭐⭐⭐ دالة سجل الحالات (مكتملة ومحدثة) ⭐⭐⭐ ---
bindCasesEvents() {
    // الأزرار الرئيسية لفتح وإغلاق النافذة
    document.getElementById('add-case-btn').addEventListener('click', () => app.modals.showCase());
    document.getElementById('case-modal-cancel-btn').addEventListener('click', () => document.getElementById('case-modal').classList.add('hidden'));
    document.getElementById('case-modal-cancel-btn-header').addEventListener('click', () => document.getElementById('case-modal').classList.add('hidden'));
    
    // ==========  هذا هو الكود الذي كان مفقوداً  ==========
    // ربط زر "إضافة تطعيم"
    document.getElementById('add-vaccination-btn').addEventListener('click', () => app.modals.addDynamicInput('vaccinations-container', 'اسم التطعيم/الدواء'));
    
    // ربط زر "إضافة علاج"
    document.getElementById('add-treatment-btn').addEventListener('click', () => app.modals.addDynamicInput('treatments-container', 'وصف العلاج/الزيارة'));
    // ===================================================

    // ربط زر حذف الحقول الديناميكية (التطعيمات والعلاجات)
    document.getElementById('case-modal').addEventListener('click', e => { 
        if (e.target.classList.contains('remove-dynamic-input-btn')) {
            e.target.closest('.dynamic-input-group').remove();
        }
    });

    // عند إرسال الفورم (حفظ الحالة)
    document.getElementById('case-form').addEventListener('submit', async e => {
        e.preventDefault();
        const getDynamicData = cId => Array.from(document.getElementById(cId).children).map(d => ({ name: d.querySelector('input[type="text"]').value, date: d.querySelector('input[title="تاريخ الجرعة الحالية"]').value, next_due_date: d.querySelector('input[title="تاريخ الجرعة التالية"]').value }));
        
        const caseData = { 
            id: document.getElementById('case-id').value || null, 
            owner_name: document.getElementById('owner-name').value, 
            animal_name: document.getElementById('animal-name').value, 
            animal_type: document.getElementById('animal-type').value, 
            owner_phone: document.getElementById('owner-phone').value,
            owner_phone_code: document.getElementById('owner-phone-code').value,
            notes: document.getElementById('case-notes').value, 
            vaccinations: getDynamicData('vaccinations-container'), 
            treatments: getDynamicData('treatments-container') 
        };

        if ((await app.api('cases', caseData.id ? 'PUT' : 'POST', caseData)).success) { 
            document.getElementById('case-modal').classList.add('hidden'); 
            await app.loadAllData(); 
        }
    });

    // ربط أزرار التعديل والحذف في قائمة الحالات الرئيسية
    document.getElementById('cases-list').addEventListener('click', e => {
        const editBtn = e.target.closest('.edit-case-btn');
        if (editBtn) {
            const caseId = parseInt(editBtn.dataset.id, 10);
            const caseToEdit = app.state.cases.find(c => c.id === caseId);
            if (caseToEdit) {
                app.modals.showCase(caseToEdit);
            }
            return;
        }

        const deleteBtn = e.target.closest('.delete-case-btn');
        if (deleteBtn) {
            const caseId = deleteBtn.dataset.id;
            app.modals.showConfirm('تأكيد الحذف', 'سيتم حذف سجلات الحيوان بالكامل.', async () => {
                if ((await app.api('cases', 'DELETE', { id: caseId })).success) {
                    await app.loadAllData();
                }
            });
            return;
        }

        const addAnimalBtn = e.target.closest('.add-animal-to-owner-btn');
        if(addAnimalBtn) {
            const ownerData = { owner_name: addAnimalBtn.dataset.ownerName, owner_phone: addAnimalBtn.dataset.ownerPhone, owner_phone_code: addAnimalBtn.dataset.ownerPhoneCode };
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

    // ربط البحث في سجل الحالات
    document.getElementById('case-search-input').addEventListener('input', () => app.render.casesList());
},

// --- ⭐⭐⭐ دالة إدارة الخدمات (محدثة بإضافة سطر طباعة للتشخيص) ⭐⭐⭐ ---
// --- ⭐⭐⭐ دالة إدارة الخدمات المعروضة (إصلاح خطأ ملء حقل التعديل) ⭐⭐⭐ ---
// --- ⭐⭐⭐ دالة إدارة الخدمات (الإصدار المبسط: نص فقط) ⭐⭐⭐ ---
bindClinicServicesEvents() {
    const form = document.getElementById('add-service-admin-form');
    if (!form) return; 

    const formBtnText = document.getElementById('service-form-btn-text');
    const cancelBtn = document.getElementById('cancel-service-edit-btn');
    const serviceIdField = document.getElementById('service-id');

    const resetForm = () => {
        form.reset();
        serviceIdField.value = '';
        formBtnText.textContent = 'إضافة الخدمة';
        cancelBtn.classList.add('hidden');
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const service_id = serviceIdField.value;
        
        // جلب البيانات (تم حذف حقل السعر الرقمي)
        const data = {
            service_name: document.getElementById('service-name').value,
            price_note: document.getElementById('service-price-note').value,
            description: document.getElementById('service-desc').value,
        };

        let result;
        if (service_id) {
            data.service_id = service_id;
            result = await app.api('clinic_services', 'PUT', data);
        } else {
            result = await app.api('clinic_services', 'POST', data);
        }

        if (result.success) {
            await app.loadAllData();
            resetForm();
        }
    });

    cancelBtn.addEventListener('click', resetForm);

    document.getElementById('clinic-services-list-container').addEventListener('click', async (e) => {
        const editBtn = e.target.closest('.edit-service-btn');
        const deleteBtn = e.target.closest('.delete-service-btn');

        if (editBtn) {
            const serviceId = editBtn.dataset.id;
            const service = app.state.services.find(s => s.id == serviceId);
            if (service) {
                serviceIdField.value = service.id;
                document.getElementById('service-name').value = service.service_name;
                // (تم حذف حقل السعر الرقمي)
                document.getElementById('service-price-note').value = service.price_note;
                document.getElementById('service-desc').value = service.description;
                
                formBtnText.textContent = 'حفظ التعديلات';
                cancelBtn.classList.remove('hidden');
                form.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        if (deleteBtn) {
            // (الحذف يبقى كما هو)
            const serviceId = deleteBtn.dataset.id;
            app.modals.showConfirm('تأكيد الحذف', 'هل أنت متأكد من حذف هذه الخدمة؟', async () => {
                const result = await app.api('clinic_services', 'DELETE', { id: parseInt(serviceId) });
                if (result.success) {
                    await app.loadAllData();
                }
            });
        }
    });
},

// --- ⭐⭐⭐ دالة إدارة العروض (هذه هي الدالة المطلوبة) ⭐⭐⭐ ---
// (داخل ملف js-modules/1.core.js.php)

// --- ⭐⭐⭐ دالة إدارة العروض (محدثة: تخفي التفاصيل الفارغة) ⭐⭐⭐ ---
// (داخل ملف js-modules/1.core.js.php)

bindPromotionsEvents() {
    const form = document.getElementById('add-promotion-form');
    if (!form) return; 

    // --- عناصر الفورم ---
    const formBtnText = document.getElementById('promo-form-btn-text');
    const cancelBtn = document.getElementById('cancel-promo-edit-btn');
    const promoIdField = document.getElementById('promo-id');

    // --- عناصر النافذة المنبثقة ---
    const promoListContainer = document.getElementById('promotions-list-container');
    const waModal = document.getElementById('whatsapp-blast-modal');
    const closeWaModalBtn = document.getElementById('close-wa-modal');
    const step1 = document.getElementById('wa-step-1-select');
    const step2 = document.getElementById('wa-step-2-send');
    const clientListDiv = document.getElementById('wa-client-list');
    const selectAllCheckbox = document.getElementById('wa-select-all');
    const prepareBatchBtn = document.getElementById('wa-prepare-batch-btn');
    const batchLinksList = document.getElementById('wa-batch-links-list');
    const logBatchBtn = document.getElementById('wa-log-batch-btn');
    const backBtn = document.getElementById('wa-back-btn');
    
    // --- متغيرات الحالة ---
    let currentPromo = null; 
    let clientsToSend = []; 

    // --- دوال مساعدة ---
    const resetForm = () => {
        form.reset(); 
        promoIdField.value = '';
        formBtnText.textContent = 'حفظ العرض';
        cancelBtn.classList.add('hidden');
    };
    const updateSelectedCount = () => {
        if(!clientListDiv) return;
        const selectedCount = clientListDiv.querySelectorAll('input[type="checkbox"]:checked').length;
        if (document.getElementById('wa-total-count-selected')) {
             document.getElementById('wa-total-count-selected').textContent = selectedCount;
             prepareBatchBtn.textContent = `تجهيز دفعة للإرسال (${selectedCount})`;
             prepareBatchBtn.disabled = selectedCount === 0;
        }
    };
    
    // --- ربط أحداث الفورم الأساسي (مع تاريخ البدء) ---
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const promo_id = promoIdField.value;
        const data = {
            title: document.getElementById('promo-title').value,
            description: document.getElementById('promo-desc').value,
            start_date: document.getElementById('promo-start-date').value, 
            expiry_date: document.getElementById('promo-expiry').value,
        };
        let result;
        if (promo_id) {
            data.promo_id = promo_id;
            result = await app.api('promotions', 'PUT', data);
        } else {
            result = await app.api('promotions', 'POST', data);
        }
        if (result.success) {
            await app.loadAllData();
            resetForm();
        }
    });

    cancelBtn.addEventListener('click', resetForm);

    // --- ربط أحداث القائمة الرئيسية (لفتح النافذة) ---
    if (promoListContainer) {
        promoListContainer.addEventListener('click', async (e) => {
            const editBtn = e.target.closest('.edit-promo-btn');
            const deleteBtn = e.target.closest('.delete-promo-btn');
            const sendWaBtn = e.target.closest('.send-promo-wa-btn'); 

            if (editBtn) {
                const promoId = editBtn.dataset.id;
                const promo = app.state.promotions.find(p => p.id == promoId);
                if (promo) {
                    promoIdField.value = promo.id;
                    document.getElementById('promo-title').value = promo.title;
                    document.getElementById('promo-desc').value = promo.description;
                    document.getElementById('promo-start-date').value = promo.start_date;
                    document.getElementById('promo-expiry').value = promo.expiry_date;
                    formBtnText.textContent = 'حفظ التعديلات';
                    cancelBtn.classList.remove('hidden');
                    form.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }
            if (deleteBtn) {
                const promoId = deleteBtn.dataset.id;
                app.modals.showConfirm('تأكيد الحذف', 'هل أنت متأكد من حذف هذا العرض؟', async () => {
                    const result = await app.api('promotions', 'DELETE', { id: parseInt(promoId) });
                    if (result.success) await app.loadAllData();
                });
                return;
            }

            if (sendWaBtn) {
                app.utils.showLoader(true);
                const promoId = sendWaBtn.dataset.id;
                currentPromo = app.state.promotions.find(p => p.id == promoId);
                const clientResult = await app.api('promotions', 'GET', { action: 'get_clients', promo_id: promoId }, false);
                
                if (!clientResult.success) { app.utils.showLoader(false); app.utils.showMessageBox(clientResult.error || 'خطأ.', 'error'); return; }
                
                clientsToSend = clientResult.data; 
                document.getElementById('wa-promo-title').textContent = currentPromo.title;
                document.getElementById('wa-client-count').textContent = clientsToSend.length;
                document.getElementById('wa-sent-count').textContent = clientResult.sent_count || 0;
                clientListDiv.innerHTML = '';
                 if (clientsToSend.length === 0) {
                     clientListDiv.innerHTML = '<p class="text-slate-500 text-center">رائع! لقد قمت بإرسال هذا العرض لجميع عملائك.</p>';
                    if (selectAllCheckbox) selectAllCheckbox.disabled = true;
                    if (prepareBatchBtn) prepareBatchBtn.disabled = true;
                } else {
                    clientsToSend.forEach((client, index) => {
                        const phoneCode = client.owner_phone_code || '20';
                        const localPhone = String(client.owner_phone || '').replace(/\D/g, '');
                        const fullPhoneNumber = phoneCode + localPhone;
                         clientListDiv.innerHTML += `<label for="client_${index}" class="flex items-center p-2 bg-white rounded-md border border-slate-200 cursor-pointer hover:bg-slate-50"><input type="checkbox" id="client_${index}" data-phone="${fullPhoneNumber}" data-name="${client.owner_name}" class="ml-3"><span class="font-semibold text-slate-700">${client.owner_name || client.owner_phone}</span></label>`;
                    });
                    if (selectAllCheckbox) { selectAllCheckbox.disabled = false; selectAllCheckbox.checked = false; }
                }
                updateSelectedCount(); 
                if(step1) step1.classList.remove('hidden');
                if(step2) step2.classList.add('hidden');
                app.utils.showLoader(false);
                if(waModal) { waModal.classList.remove('hidden'); waModal.classList.add('flex'); }
            }
        });
    }
    
    // --- ربط أحداث أزرار النافذة المنبثقة (Modal) ---
    if (waModal) {
        closeWaModalBtn.addEventListener('click', () => { waModal.classList.add('hidden'); waModal.classList.remove('flex'); });
        backBtn.addEventListener('click', () => { step1.classList.remove('hidden'); step2.classList.add('hidden'); });
        
        waModal.addEventListener('change', (e) => {
            if (e.target.matches('input[type="checkbox"]')) {
                if (e.target.id === 'wa-select-all') {
                    clientListDiv.querySelectorAll('input[type="checkbox"]').forEach(cb => { cb.checked = e.target.checked; });
                }
                updateSelectedCount();
            }
        });

        prepareBatchBtn.addEventListener('click', () => {
            const selectedCheckboxes = clientListDiv.querySelectorAll('input[type="checkbox"]:checked');
            let linksHtml = '';
            let batchToLog = []; 
            const clinicWhatsapp = app.state.settings.clinic_whatsapp || '';
            const clinicCountryCode = app.state.settings.clinic_country_code || '20';
            
            let messageText = `عرض خاص من (${app.state.settings.clinic_name || 'العيادة'})\n\nالعرض: ${currentPromo.title}\n\n`;
            if(currentPromo.description && currentPromo.description.trim() !== '') { messageText += `التفاصيل: ${currentPromo.description}\n\n`; }
            if(currentPromo.start_date) { messageText += `يبدأ العرض من: ${new Date(currentPromo.start_date).toLocaleDateString('ar-EG')}\n`; }
            messageText += `يسري العرض حتى: ${new Date(currentPromo.expiry_date).toLocaleDateString('ar-EG')}\n`;
             messageText += `للحجز أو الاستفسار: ${clinicWhatsapp}`;
            const encodedMessage = encodeURIComponent(messageText);

            selectedCheckboxes.forEach(cb => {
                const phone = cb.dataset.phone;
                const name = cb.dataset.name;
                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                const waLink = `whatsapp://send?phone=${phone}&text=${encodedMessage}`;
window.location.href = waLink;
                
                linksHtml += `<a href="${waLink}" target="_blank" class="wa-link-item block w-full p-3 bg-white border border-slate-300 rounded-lg text-emerald-700 font-semibold text-center hover:bg-emerald-50"><i class="fab fa-whatsapp ml-2"></i> أرسل إلى: ${name || phone}</a>`;
                batchToLog.push({ phone: phone, name: name }); 
            });

            batchLinksList.innerHTML = linksHtml;
            document.getElementById('wa-batch-total-count').textContent = batchToLog.length;
            logBatchBtn.dataset.batchToLog = JSON.stringify(batchToLog); 
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
        });
        
        batchLinksList.addEventListener('click', (e) => {
            const link = e.target.closest('.wa-link-item');
            if (link && !link.classList.contains('clicked')) {
                link.classList.add('opacity-40', 'line-through', 'bg-slate-100', 'pointer-events-none'); 
                link.innerHTML = `<i class="fas fa-check ml-2"></i> ${link.textContent.replace('أرسل إلى:', 'تم فتح رابط:').trim()}`;
                link.classList.remove('text-emerald-700');
                link.classList.add('text-slate-500');
            }
        });

        logBatchBtn.addEventListener('click', async () => {
            const batchData = JSON.parse(logBatchBtn.dataset.batchToLog || '[]');
            if (batchData.length === 0) { waModal.classList.add('hidden'); waModal.classList.remove('flex'); return; }
            app.utils.showLoader(true);
            const result = await app.api('log_promo', 'POST', {
                promo_id: currentPromo.id,
                clients_to_log: batchData
            });
            app.utils.showLoader(false);
            if (result.success) {
                app.utils.showMessageBox(`تم تسجيل ${batchData.length} عملاء بنجاح!`, 'success');
                waModal.classList.add('hidden');
                waModal.classList.remove('flex');
            } else {
                 app.utils.showMessageBox(result.error, 'error');
            }
        });
    }
},
// ------------------------------------------