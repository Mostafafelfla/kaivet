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
                    
                    <label for="clinicWhatsappInput" class="block text-slate-700 font-semibold mb-2">رقم واتساب العيادة</label>
                    <input type="tel" id="clinicWhatsappInput" name="clinic_whatsapp" placeholder="مثال: +201001234567" class="w-full p-3 rounded-lg border border-slate-300 mb-4 text-right focus:ring-2 focus:ring-sky-400">

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
                        <i class="fas fa-info-circle"></i> اذهب إلى خرائط جوجل، اضغط كليك يمين على موقعك، وانسخ الرقمين هنا. (مهم لظهورك في البحث الجغرافي).
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
        </form> </div>
</div>