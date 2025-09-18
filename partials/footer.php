<script async defer
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAOVYRIgupAurZup5y1PRh8Ismb1A3lLao&libraries=places&language=ar&callback=initMap">
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>

<script>
// هذه الدالة يجب أن تبقى في النطاق العام (Global) ليراها Google Maps
function initMap() {
    if (window.app && typeof window.app.initMap === 'function') {
        window.app.initMap();
    } else {
        document.addEventListener('app-ready', () => {
            if (window.app) window.app.initMap();
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    
    // هذا هو الهيكل العظمي للتطبيق. 
    // يتم ملء باقي الدوال من الملفات المضمنة بالأسفل.
    const app = {

        // ===================================
        // 1. الملفات الأساسية (Core)
        // ===================================
        <?php include 'js-modules/1.core.js.php'; ?>
        <?php include 'js-modules/2.events.js.php'; ?> 

        // ===================================
        // 2. دوال ربط الأحداث (Event Binders) ووظائف الأقسام
        // ===================================
        <?php include 'js-modules/2.chat.js.php'; ?>
        <?php include 'js-modules/3.inventory.js.php'; ?>
        <?php include 'js-modules/4.sales.js.php'; ?>
        <?php include 'js-modules/5.services.js.php'; ?>
        <?php include 'js-modules/6.expenses.js.php'; ?>
        <?php include 'js-modules/7.suppliers.js.php'; ?>
        <?php include 'js-modules/8.todo.js.php'; ?>
        <?php include 'js-modules/9.cases.js.php'; ?>
        <?php include 'js-modules/10.settings.js.php'; ?>
        <?php include 'js-modules/11.reports.js.php'; ?>

        // ===================================
        // 3. كائنات الدوال المساعدة (Helper Objects)
        // ===================================

        // كل دوال العرض (Render Functions)
        render: {
            <?php include 'js-modules/12.render.js.php'; ?>
        },

        // كل دوال النوافذ المنبثقة (Modals)
        modals: {
            <?php include 'js-modules/13.modals.js.php'; ?>
        },

        // كل الأدوات المساعدة (Utilities)
        utils: {
            <?php include 'js-modules/14.utils.js.php'; ?>
        }
    };
    
    window.app = app; // اجعل التطبيق متاحاً عالمياً
    app.init(); // ابدأ التطبيق
});
</script>