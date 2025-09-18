<div id="doctors-view" class="view space-y-6 hidden">
    <h1 class="text-3xl font-bold text-slate-800 text-center">إدارة الفريق الطبي</h1>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-6">
        <h2 class="text-2xl font-bold text-slate-700">إضافة / تعديل طبيب</h2>
        
        <form id="add-doctor-form" class="space-y-4">
            
            <input type="hidden" name="doctor_id" id="doctor-id">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="doctor-name" class="block text-sm font-medium text-slate-700 mb-1">اسم الطبيب</label>
                    <input type="text" id="doctor-name" name="name" class="w-full p-3 border border-slate-300 rounded-lg text-right" required>
                </div>
                <div>
                    <label for="doctor-specialty" class="block text-sm font-medium text-slate-700 mb-1">التخصص</label>
                    <input type="text" id="doctor-specialty" name="specialty" placeholder="مثال: جراحة، باطنة..." class="w-full p-3 border border-slate-300 rounded-lg text-right">
                </div>
                <div>
                    <label for="doctor-phone" class="block text-sm font-medium text-slate-700 mb-1">رقم الهاتف</label>
                    <input type="tel" id="doctor-phone" name="phone" class="w-full p-3 border border-slate-300 rounded-lg text-right">
                </div>
                <div>
                    <label for="doctor-address" class="block text-sm font-medium text-slate-700 mb-1">العنوان (اختياري)</label>
                    <input type="text" id="doctor-address" name="address" class="w-full p-3 border border-slate-300 rounded-lg text-right">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">صورة الطبيب (اختياري)</label>
                <input type="file" id="doctor-profile-pic-input" class="hidden" accept="image/*"> 
                <label for="doctor-profile-pic-input" class="w-full cursor-pointer flex items-center justify-center p-3 border-2 border-dashed border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                    <i class="fas fa-camera mr-2 text-slate-500"></i> 
                    <span id="upload-pic-text" class="text-slate-600 font-semibold">اختر صورة للطبيب</span>
                </label>
            </div>
            <button type="submit" class="w-full bg-sky-600 text-white p-3 rounded-lg font-semibold hover:bg-sky-700 shadow-sm transition-all duration-200">
                <span id="doctor-form-btn-text">إضافة طبيب</span>
            </button>
            <button type="button" id="cancel-doctor-edit-btn" class="w-full bg-slate-200 text-slate-700 p-3 rounded-lg font-semibold hover:bg-slate-300 hidden">إلغاء التعديل</button>
        </form>

        <div class="mt-8 border-t pt-6">
            <h2 class="text-2xl font-bold text-slate-700 mb-4">قائمة الأطباء</h2>
            <div id="doctors-list-container" class="space-y-3">
                </div>
        </div>
    </div>
</div>