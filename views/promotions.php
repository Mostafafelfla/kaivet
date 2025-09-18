<?php // views/promotions.php (محدث بالتواريخ) ?>
<div id="promotions-view" class="view space-y-6 hidden">
    <h1 class="text-3xl font-bold text-slate-800 text-center">إدارة العروض والحملات التسويقية</h1>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-6">
        <h2 class="text-2xl font-bold text-slate-700">إنشاء عرض جديد</h2>
        
        <form id="add-promotion-form" class="space-y-4">
            <input type="hidden" name="promo_id" id="promo-id">
            <div>
                <label for="promo-title" class="block text-sm font-medium text-slate-700 mb-1">عنوان العرض (رسالة قصيرة جذابة)</label>
                <input type="text" id="promo-title" name="title" placeholder="مثال: خصم 20% على تطعيمات القطط" class="w-full p-3 border border-slate-300 rounded-lg text-right" required>
            </div>
            <div>
                <label for="promo-desc" class="block text-sm font-medium text-slate-700 mb-1">تفاصيل العرض (سيتم إرساله للعملاء)</label>
                <textarea id="promo-desc" name="description" rows="4" placeholder="اكتب تفاصيل العرض الكاملة هنا..." class="w-full p-3 border border-slate-300 rounded-lg text-right"></textarea>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="promo-start-date" class="block text-sm font-medium text-slate-700 mb-1">تاريخ بدء العرض (اختياري)</label>
                    <input type="date" id="promo-start-date" name="start_date" class="w-full p-3 border border-slate-300 rounded-lg text-right">
                    <p class="text-xs text-slate-500 mt-1">اتركه فارغاً ليبدأ العرض فوراً.</p>
                </div>
                <div>
                    <label for="promo-expiry" class="block text-sm font-medium text-slate-700 mb-1">تاريخ انتهاء العرض (إجباري)</label>
                    <input type="date" id="promo-expiry" name="expiry_date" class="w-full p-3 border border-slate-300 rounded-lg text-right" required>
                </div>
            </div>
            <button type="submit" class="w-full bg-sky-600 text-white p-3 rounded-lg font-semibold hover:bg-sky-700 shadow-sm transition-all duration-200">
                <span id="promo-form-btn-text">حفظ العرض (لعرضه في البروفايل)</span>
            </button>
            <button type="button" id="cancel-promo-edit-btn" class="w-full bg-slate-200 text-slate-700 p-3 rounded-lg font-semibold hover:bg-slate-300 hidden">إلغاء التعديل</button>
        </form>
        
        <div class="mt-8 border-t pt-6">
            <h2 class="text-2xl font-bold text-slate-700 mb-4">العروض الحالية والسابقة</h2>
            <div id="promotions-list-container" class="space-y-3">
                </div>
        </div>
    </div>
</div>