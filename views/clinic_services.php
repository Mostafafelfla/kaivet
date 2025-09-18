<?php // views/clinic_services.php (الإصدار المبسط) ?>
<div id="clinic_services-view" class="view space-y-6 hidden">
    <h1 class="text-3xl font-bold text-slate-800 text-center">إدارة الخدمات المعروضة</h1>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-6">
        <h2 class="text-2xl font-bold text-slate-700">إضافة / تعديل خدمة</h2>
        <form id="add-service-admin-form" class="space-y-4">
            <input type="hidden" name="service_id" id="service-id">
            <div>
                <label for="service-name" class="block text-sm font-medium text-slate-700 mb-1">اسم الخدمة</label>
                <input type="text" id="service-name" name="service_name" placeholder="مثال: كشف، تطعيم رباعي..." class="w-full p-3 border border-slate-300 rounded-lg text-right" required>
            </div>

            <div>
                <label for="service-price-note" class="block text-sm font-medium text-slate-700 mb-1">السعر أو ملاحظة السعر</label>
                <input type="text" id="service-price-note" name="price_note" value="" placeholder="مثال: 150 جنيه، أو 'السعر حسب الحالة'، أو 'مجاناً'" class="w-full p-3 border border-slate-300 rounded-lg text-right">
            </div>
            <div>
                <label for="service-desc" class="block text-sm font-medium text-slate-700 mb-1">وصف الخدمة (اختياري)</label>
                <textarea id="service-desc" name="description" rows="3" placeholder="تفاصيل سريعة عن الخدمة..." class="w-full p-3 border border-slate-300 rounded-lg text-right"></textarea>
            </div>
            
            <button type="submit" class="w-full bg-sky-600 text-white p-3 rounded-lg font-semibold hover:bg-sky-700 shadow-sm transition-all duration-200">
                <span id="service-form-btn-text">إضافة الخدمة</span>
            </button>
            <button type="button" id="cancel-service-edit-btn" class="w-full bg-slate-200 text-slate-700 p-3 rounded-lg font-semibold hover:bg-slate-300 hidden">إلغاء التعديل</button>
        </form>
        
        <div class="mt-8 border-t pt-6">
            <h2 class="text-2xl font-bold text-slate-700 mb-4">قائمة خدمات العيادة</h2>
            <div id="clinic-services-list-container" class="space-y-3">
                </div>
        </div>
    </div>
</div>