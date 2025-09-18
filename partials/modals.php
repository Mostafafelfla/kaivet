<div id="message-box" class="fixed top-4 right-4 p-4 rounded-lg text-center hidden w-full max-w-sm z-[9999] shadow-lg text-white"></div>
<div id="custom-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-[10000]"><div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg mx-4 space-y-4"><h3 id="modal-title" class="text-2xl font-bold text-slate-800 mb-2 text-center"></h3><div class="space-y-4"><div id="modal-inventory-fields" class="space-y-2 hidden"><input type="text" id="modal-input-name" placeholder="أدخل الاسم الجديد" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" /><input type="number" step="any" id="modal-input-purchase-price" placeholder="سعر الشراء" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" /><input type="number" step="any" id="modal-input-price" placeholder="سعر البيع" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" /><input type="number" id="modal-input-quantity" placeholder="أدخل الكمية الجديدة" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" /><input type="number" step="any" id="modal-input-package-size" placeholder="حجم العبوة" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" /><input type="text" id="modal-input-unit-name" placeholder="اسم الوحدة" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" /><input type="number" step="any" id="modal-input-unit-sale-price" placeholder="سعر بيع الوحدة" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" /></div><div id="modal-expense-fields" class="space-y-2 hidden"><input type="text" id="modal-expense-description" placeholder="وصف المصروف" class="w-full p-3 border border-slate-300 rounded-lg text-right" /><input type="number" step="any" id="modal-expense-amount" placeholder="قيمة المصروف" class="w-full p-3 border border-slate-300 rounded-lg text-right" /></div>
    <div id="modal-service-fields" class="space-y-2 hidden">
         <input type="text" id="modal-service-description" placeholder="وصف الخدمة" class="w-full p-3 border border-slate-300 rounded-lg text-right" />
         <input type="number" step="any" id="modal-service-price" placeholder="سعر الخدمة" class="w-full p-3 border border-slate-300 rounded-lg text-right" />
    </div>
    <div id="modal-sale-fields" class="space-y-2 hidden">
        <p>الصنف: <strong id="modal-sale-item-name"></strong></p>
        <label for="modal-sale-quantity" class="block text-sm font-medium text-slate-700">الكمية الجديدة</label>
        <input type="number" step="any" id="modal-sale-quantity" placeholder="الكمية الجديدة" class="w-full p-3 border border-slate-300 rounded-lg text-right" min="1" />
        <label for="modal-sale-discount" class="block text-sm font-medium text-slate-700 mt-2">الخصم الجديد على الربح (%)</label>
        <input type="number" step="any" id="modal-sale-discount" placeholder="0-100" class="w-full p-3 border border-slate-300 rounded-lg text-right" min="0" max="100" />
    </div>
    <div id="modal-rename-fields" class="space-y-2 hidden"><input type="text" id="modal-rename-input" placeholder="أدخل الاسم الجديد" class="w-full p-3 border border-slate-300 rounded-lg text-right" /></div>
    
    <div id="modal-supplier-fields" class="space-y-2 hidden">
        <input type="text" id="modal-supplier-name" placeholder="اسم المورد" class="w-full p-3 border border-slate-300 rounded-lg text-right" />
        <input type="tel" id="modal-supplier-phone" placeholder="رقم الهاتف" class="w-full p-3 border border-slate-300 rounded-lg text-right" />
        <input type="email" id="modal-supplier-email" placeholder="البريد الإلكتروني (اختياري)" class="w-full p-3 border border-slate-300 rounded-lg text-right" />
        <textarea id="modal-supplier-address" placeholder="العنوان (اختياري)" rows="2" class="w-full p-3 border border-slate-300 rounded-lg text-right"></textarea>
        <textarea id="modal-supplier-notes" placeholder="ملاحظات (اختياري)" rows="2" class="w-full p-3 border border-slate-300 rounded-lg text-right"></textarea>
    </div>
    </div><div class="flex justify-between space-x-4 rtl:space-x-reverse mt-4"><button id="modal-confirm-btn" class="flex-1 bg-emerald-600 text-white p-3 rounded-lg font-semibold hover:bg-emerald-700">تأكيد</button><button id="modal-cancel-btn" class="flex-1 bg-rose-600 text-white p-3 rounded-lg font-semibold hover:bg-rose-700">إلغاء</button></div></div></div>
    
    <div id="confirm-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-[10000]"><div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-sm mx-4 space-y-4"><h3 id="confirm-title" class="text-2xl font-bold text-slate-800 mb-2 text-center">تأكيد الإجراء</h3><p id="confirm-message" class="text-slate-600 text-center">هل أنت متأكد من حذف هذا العنصر؟</p><div class="flex justify-between space-x-4 rtl:space-x-reverse mt-4"><button id="confirm-yes-btn" class="flex-1 bg-rose-600 text-white p-3 rounded-lg font-semibold hover:bg-rose-700">نعم</button><button id="confirm-no-btn" class="flex-1 bg-slate-300 text-slate-800 p-3 rounded-lg font-semibold hover:bg-slate-400">إلغاء</button></div></div></div>
    
    <div id="case-modal" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-[10000] overflow-y-auto p-4 hidden">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-auto custom-scrollbar animate__animated animate__fadeInDown">
            <div class="bg-sky-600 text-white p-4 rounded-t-2xl flex justify-between items-center">
                <h3 id="case-modal-title" class="text-2xl font-bold">➕ إضافة حالة</h3>
                <button id="case-modal-cancel-btn-header" class="text-2xl hover:text-gray-200">&times;</button>
            </div>
    
            <form id="case-form" class="space-y-6 p-8">
                <input type="hidden" id="case-id" name="id">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div><label for="owner-name" class="block text-sm font-semibold mb-1 text-slate-700">اسم صاحب الحالة</label><input type="text" id="owner-name" name="owner_name" placeholder="أدخل اسم المالك" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-500" required></div>
                    <div><label for="animal-name" class="block text-sm font-semibold mb-1 text-slate-700">اسم الحيوان</label><input type="text" id="animal-name" name="animal_name" placeholder="أدخل اسم الحيوان" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-500" required></div>
                    <div><label for="animal-type" class="block text-sm font-semibold mb-1 text-slate-700">نوع الحيوان</label><input type="text" id="animal-type" name="animal_type" placeholder="قط / كلب / حصان ..." class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-500"></div>
                    
                    <div>
                        <label class="block text-sm font-semibold mb-1 text-slate-700">رقم واتساب المالك</label>
                        <div class="flex items-center gap-2">
                            <input type="text" id="owner-phone" name="owner_phone" placeholder="1012345678" class="w-full p-3 border border-slate-300 rounded-lg text-left" dir="ltr" required>
                            <input type="text" id="owner-phone-code" name="owner_phone_code" value="20" class="w-24 p-3 border border-slate-300 rounded-lg text-left" dir="ltr" required>
                        </div>
                         <p class="text-xs text-slate-500 mt-1">الرقم بدون الصفر الأول، والكود الدولي لمصر هو 20.</p>
                    </div>
                    </div>
                <div><h4 class="text-xl font-bold mb-3 flex items-center gap-2 text-slate-700">💉 التطعيمات</h4><div id="vaccinations-container" class="space-y-3"></div><button type="button" id="add-vaccination-btn" class="mt-2 text-sm text-sky-600 font-semibold hover:underline">+ إضافة تطعيم</button></div>
                <div><h4 class="text-xl font-bold mb-3 flex items-center gap-2 text-slate-700">💊 العلاجات والزيارات</h4><div id="treatments-container" class="space-y-3"></div><button type="button" id="add-treatment-btn" class="mt-2 text-sm text-sky-600 font-semibold hover:underline">+ إضافة علاج/زيارة</button></div>
                <div><label for="case-notes" class="block text-sm font-semibold mb-2 text-slate-700">📝 ملاحظات</label><textarea id="case-notes" name="notes" rows="4" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-500"></textarea></div>
                <div class="flex justify-end gap-4 mt-6"><button type="submit" class="bg-emerald-600 text-white py-2 px-6 rounded-lg font-semibold hover:bg-emerald-700 shadow-sm transition">✅ حفظ الحالة</button><button type="button" id="case-modal-cancel-btn" class="bg-slate-300 text-slate-800 py-2 px-6 rounded-lg font-semibold hover:bg-slate-400 transition">❌ إلغاء</button></div>
            </form>
        </div>
    </div><div id="doctor-crop-modal" class="fixed inset-0 bg-black bg-opacity-60 hidden items-center justify-center z-[10001] p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg mx-auto animate__animated animate__fadeInDown">
        <div class="p-6">
            <h3 class="text-xl font-bold text-center mb-4 text-slate-800">اضبط الصورة</h3>
            
            <div class="max-w-full h-80 bg-slate-100 flex items-center justify-center">
                <img id="doctor-cropper-image" src="" alt="صورة للقص" style="max-width: 100%; max-height: 320px;">
            </div>
            
            <div class="flex gap-4 mt-6">
                <button id="cancel-crop-btn" type="button" class="flex-1 bg-slate-200 text-slate-800 p-3 rounded-lg font-semibold hover:bg-slate-300 transition-colors">إلغاء</button>
                <button id="save-crop-btn" type="button" class="flex-1 bg-emerald-600 text-white p-3 rounded-lg font-semibold hover:bg-emerald-700 transition-colors">قص وحفظ الصورة</button>
            </div>
        </div>
    </div>
</div>
    <div id="whatsapp-blast-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center z-[10000] p-4">
        <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-lg mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold text-slate-800">إرسال حملة واتساب</h3>
                <button id="close-wa-modal" class="text-3xl text-slate-400 hover:text-slate-700">&times;</button>
            </div>
    
            <div class="space-y-4">
                <p>العرض: <strong id="wa-promo-title" class="text-sky-700"></strong></p>
                <p>تم العثور على <strong id="wa-client-count" class="text-emerald-600">0</strong> عميل (متبقي) لإرسال العرض له. (<span id="wa-sent-count">0</span> تم الإرسال إليهم سابقاً).</p>
                <hr>
                <div id="wa-step-1-select">
                    <p class="text-sm text-slate-600 mb-2">اختر العملاء الذين تريد إرسال الدفعة لهم:</p>
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="wa-select-all" class="ml-2 rtl:mr-0 rtl:ml-2">
                        <label for="wa-select-all" class="font-semibold text-slate-700">تحديد الكل (<span id="wa-total-count-selected">0</span>)</label>
                    </div>
                    <div id="wa-client-list" class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar border p-3 rounded-lg bg-slate-50"></div>
                    <button id="wa-prepare-batch-btn" class="w-full bg-sky-600 text-white font-semibold py-3 rounded-lg hover:bg-sky-700 mt-4" disabled>
                        تجهيز دفعة للإرسال (0)
                    </button>
                </div>
                <div id="wa-step-2-send" class="hidden space-y-3">
                    <p class="font-semibold text-center">الدفعة جاهزة (<span id="wa-batch-total-count">0</span> رسالة). يرجى الضغط على الروابط تباعاً.</p>
                    <div id="wa-batch-links-list" class="space-y-2 max-h-48 overflow-y-auto custom-scrollbar border p-3 rounded-lg bg-slate-50"></div>
                    <button id="wa-log-batch-btn" class="w-full bg-emerald-600 text-white font-semibold py-3 rounded-lg hover:bg-emerald-700">
                        لقد انتهيت، قم بتسجيل هذه الدفعة كـ "تم الإرسال"
                    </button>
                    <button id="wa-back-btn" type="button" class="w-full text-slate-600 text-sm hover:underline">العودة لاختيار العملاء</button>
                </div>
            </div>
        </div>
    </div>
<div id="settings-profile-view" class="view space-y-6 hidden">
    <h1 class="text-3xl font-bold text-slate-800 text-center">الملف الشخصي والإعدادات</h1>
    
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8">
        
        <form id="settings-profile-form">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div> <h3 class="text-xl font-bold mb-4 text-slate-700">الملف الشخصي</h3>
                    <div class="flex flex-col items-center mb-6">
                        <img id="profilePicPreview" class="w-32 h-32 rounded-full object-cover border-4 border-sky-500 shadow-md mb-4" src="uploads/default.png" alt="صورة البروفايل" onerror="this.onerror=null; this.src='uploads/default.png';">
                        <label for="profilePicInput" class="cursor-pointer bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2 px-4 rounded-full shadow-sm">تغيير الصورة</label>
                        <input type="file" id="profilePicInput" accept="image/*" class="hidden">
                    </div>
                    
                    <label for="nameInput" class="block text-slate-700 font-semibold mb-2">الاسم الشخصي</label>
                    <input type="text" id="nameInput" name="name" class="w-full p-3 rounded-lg border border-slate-300 mb-4 text-right focus:ring-2 focus:ring-sky-400">
                    
                    <label for="clinicNameInput" class="block text-slate-700 font-semibold mb-2">اسم العيادة</label>
                    <input type="text" id="clinicNameInput" name="clinic_name" class="w-full p-3 rounded-lg border border-slate-300 mb-4 text-right focus:ring-2 focus:ring-sky-400">
                    
                    <div>
                         <label class="block text-sm font-semibold mb-1 text-slate-700">رقم واتساب العيادة الأساسي (للإرسال)</label>
                        <div class="flex items-center gap-2">
                            <input type="tel" id="clinic_whatsapp" name="clinic_whatsapp" placeholder="1012345678" class="w-full p-3 border border-slate-300 rounded-lg text-left" dir="ltr">
                            <input type="text" id="clinic_country_code" name="clinic_country_code" value="20" placeholder="20" class="w-24 p-3 border border-slate-300 rounded-lg text-left" dir="ltr">
                        </div>
                        <p class="text-xs text-slate-500 mt-1">هام: هذا هو الرقم الذي سترسل منه الحملات. الرقم بدون الصفر الأول.</p>
                    </div>

                    <div class="mt-4">
                        <label for="clinic_phone_2" class="block text-slate-700 font-semibold mb-2">رقم هاتف إضافي (للعرض فقط)</label>
                        <input type="text" id="clinic_phone_2" name="clinic_phone_2" placeholder="رقم أرضي أو موبايل آخر" class="w-full p-3 rounded-lg border border-slate-300 mb-4 text-right">
                    </div>
                    <label for="clinicAddressInput" class="block text-slate-700 font-semibold mb-2">عنوان العيادة</label>
                    <input type="text" id="clinicAddressInput" name="clinic_address" class="w-full p-3 rounded-lg border border-slate-300 mb-4 text-right focus:ring-2 focus:ring-sky-400">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="clinic_lat" class="block text-slate-700 font-semibold mb-2">Latitude (خط العرض)</label>
                            <input type="text" id="clinic_lat" name="clinic_lat" placeholder="مثال: 30.0444" class="w-full p-3 rounded-lg border border-slate-300 text-left" dir="ltr">
                        </div>
                        <div>
                            <label for="clinic_lng" class="block text-slate-700 font-semibold mb-2">Longitude (خط الطول)</label>
                            <input type="text" id="clinic_lng" name="clinic_lng" placeholder="مثال: 31.2357" class="w-full p-3 rounded-lg border border-slate-300 text-left" dir="ltr">
                        </div>
                    </div>
                    <p class="text-sm text-slate-500 -mt-2 mb-4">
                        <i class="fas fa-info-circle"></i> انسخ الإحداثيات من خرائط جوجل. (مهم لظهورك في البحث الجغرافي).
                    </p>

                    <div id="map" class="w-full h-64 rounded-xl shadow-inner border border-slate-300 mt-4"></div>
                </div>
                
                <div> <h3 class="text-xl font-bold mb-4 text-slate-700">الإعدادات</h3>
                    <label for="currencySelect" class="block text-slate-700 font-semibold mb-2">نوع العملة</label>
                    <select id="currencySelect" name="currency" class="w-full p-3 rounded-lg border border-slate-300 mb-4 focus:ring-2 focus:ring-sky-400 bg-white">
                        <option value="EGP">جنيه مصري (EGP)</option>
                        <option value="USD">دولار أمريكي (USD)</option>
                        <option value="EUR">يورو (EUR)</option>
                    </select>
                    
                    <button id="saveSettingsBtn" type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg shadow-sm mt-6">حفظ التعديلات</button>
                </div>
            </div>
        </form>
    </div>
</div>