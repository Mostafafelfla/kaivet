<div id="suppliers-view" class="view space-y-6 hidden">
    <h1 class="text-3xl font-bold text-slate-800 text-center">إدارة الموردين</h1>
    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-6">
        <h2 class="text-2xl font-bold text-slate-700">إضافة مورد جديد</h2>
        <form id="add-supplier-form" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="supplier-name" class="block text-sm font-medium text-slate-700 mb-1">اسم المورد</label>
                    <input type="text" id="supplier-name" name="name" placeholder="اسم الشركة أو المورد" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" required />
                </div>
                <div>
                    <label for="supplier-phone" class="block text-sm font-medium text-slate-700 mb-1">رقم الهاتف</label>
                    <input type="tel" id="supplier-phone" name="phone" placeholder="رقم الهاتف" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" />
                </div>
            </div>
            <div>
                <label for="supplier-email" class="block text-sm font-medium text-slate-700 mb-1">البريد الإلكتروني (اختياري)</label>
                <input type="email" id="supplier-email" name="email" placeholder="example@company.com" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400" />
            </div>
            <div>
                <label for="supplier-address" class="block text-sm font-medium text-slate-700 mb-1">العنوان (اختياري)</label>
                <textarea id="supplier-address" name="address" rows="2" placeholder="تفاصيل العنوان" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400"></textarea>
            </div>
            <div>
                <label for="supplier-notes" class="block text-sm font-medium text-slate-700 mb-1">ملاحظات (اختياري)</label>
                <textarea id="supplier-notes" name="notes" rows="2" placeholder="ملاحظات إضافية عن المورد أو المنتجات" class="w-full p-3 border border-slate-300 rounded-lg text-right focus:ring-2 focus:ring-sky-400"></textarea>
            </div>
            <button type="submit" class="w-full bg-sky-600 text-white p-3 rounded-lg font-semibold hover:bg-sky-700 shadow-sm transition-all duration-200">إضافة المورد</button>
        </form>
        
        <div class="mt-8 border-t pt-6">
            <h2 class="text-2xl font-bold text-slate-700 mb-4">قائمة الموردين</h2>
            <div class="overflow-x-auto">
                <table id="suppliers-list-table" class="w-full text-sm text-right text-slate-500">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-100">
                        <tr>
                            <th scope="col" class="px-6 py-3">الاسم</th>
                            <th scope="col" class="px-6 py-3">الهاتف</th>
                            <th scope="col" class="px-6 py-3">البريد الإلكتروني</th>
                            <th scope="col" class="px-6 py-3">ملاحظات</th>
                            <th scope="col" class="px-6 py-3">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="5" class="text-center p-4 text-slate-500">جاري تحميل الموردين...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>