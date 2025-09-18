<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>kaivet - لوحة التحكم الاحترافية</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/showdown/2.1.0/showdown.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="icon" type="image/png" href="favicon.png">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #f1f5f9; /* Slate 100 */
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #e2e8f0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #94a3b8; border-radius: 10px; }
        .autocomplete-list { position: absolute; z-index: 1000; max-height: 200px; overflow-y: auto; border: 1px solid #e5e7eb; border-top: none; background-color: white; width: 100%; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1); border-radius: 0 0 0.75rem; }
        .autocomplete-item { padding: 0.75rem 1rem; cursor: pointer; transition: background-color 0.2s; }
        .autocomplete-item:hover { background-color: #f3f4f6; }
        .nav-link.active {
            background-color: #e0f2fe; /* Sky 100 */
            color: #0284c7; /* Sky 600 */
            font-weight: 700;
        }
     .croppie-container { width: 100%; height: 350px; }
        .nav-link.active i {
            color: #0ea5e9; /* Sky 500 */
        }
        #map { height: 300px; }
        .case-card-details { max-height: 0; overflow: hidden; transition: max-height 0.5s ease-in-out; }
        .loader { border: 5px solid #f3f3f3; border-top: 5px solid #0ea5e9; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .loading-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 9999; backdrop-filter: blur(4px); }
        .tab-btn.active { border-color: #0ea5e9; color: #0ea5e9; background-color: #e0f2fe; }
        #finance-pie-chart-container, #finance-line-chart-container { position: relative; height: 320px; width: 100%; }
        .chat-session-item.active { background-color: #f0f9ff; border-right: 4px solid #0ea5e9; }
        @keyframes blink { 50% { opacity: 0; } }
        .typing-cursor { display: inline-block; width: 8px; height: 1em; background-color: #0c4a6e; margin-right: 4px; animation: blink 1s step-end infinite; }
        
        /* Table alternate row styling */
        #all-sales-list tbody tr:nth-child(even),
        #daily-sales-list tbody tr:nth-child(even),
        #monthly-sales-list tbody tr:nth-child(even),
        #daily-expenses-list tbody tr:nth-child(even),
        #monthly-expenses-list tbody tr:nth-child(even),
        #all-expenses-list tbody tr:nth-child(even),
        #daily-services-list tbody tr:nth-child(even),
        #monthly-services-list tbody tr:nth-child(even),
        #all-services-list tbody tr:nth-child(even),
        #suppliers-list-table tbody tr:nth-child(even), /* إضافة للميزة الجديدة */
        #top-products-table tr:nth-child(even) {
            background-color: #f8fafc; /* Slate 50 */
        }
        
        .message-actions { display: flex; align-items: center; gap: 0.5rem; visibility: hidden; opacity: 0; transition: opacity 0.2s; }
        .flex:hover .message-actions { visibility: visible; opacity: 1; }
        .session-actions-wrapper { position: absolute; top: 50%; left: 0.5rem; transform: translateY(-50%); }
        .session-actions { visibility: hidden; opacity: 0; transition: opacity 0.2s; }
        .chat-session-item:hover .session-actions { visibility: visible; opacity: 1; }
        .session-menu { position: absolute; left: 100%; top: -10px; background-color: #1f2937; border-radius: 0.5rem; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1); z-index: 20; min-width: 160px; }
        .message-content table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        .message-content th, .message-content td { border: 1px solid #e5e7eb; padding: 0.5rem; text-align: right; }
        .message-content th { background-color: #f9fafb; }
        .message-content ul, .message-content ol { margin-right: 1.5rem; margin-top: 0.5rem; margin-bottom: 0.5rem; }
        .message-content li { margin-bottom: 0.25rem; }
        .message-content h1, .message-content h2, .message-content h3 { font-weight: bold; margin-top: 1rem; margin-bottom: 0.5rem; }
        .message-content h1 { font-size: 1.5rem; }
        .message-content h2 { font-size: 1.25rem; }
        .message-content h3 { font-size: 1.1rem; }
        .recording-indicator { width: 10px; height: 10px; background-color: #ef4444; border-radius: 50%; animation: blink 1.2s infinite; }

        @media print {
            body * { visibility: hidden; }
            #reports-view, #reports-view * { visibility: visible; }
            #reports-view { position: absolute; left: 0; top: 0; width: 100%; padding: 2rem; }
            aside, #reports-view .no-print { display: none !important; }
            .printable-area { page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-100">

    <div id="loading-overlay" class="loading-overlay hidden"><div class="loader"></div></div>
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden"></div>

    <div class="relative min-h-screen md:flex">
        
        <?php include 'partials/sidebar.php'; ?>

        <div class="flex-1 flex flex-col">
            
            <?php include 'partials/header.php'; ?>

            <main class="flex-grow p-4 md:p-8 overflow-y-auto">
                <?php
                // تضمين جميع أقسام العرض (Views)
                include 'views/dashboard.php';
                include 'views/ai-chat.php';
                include 'views/sales.php';
                include 'views/services.php';
                include 'views/promotions.php';
                include 'views/clinic_services.php';
                include 'views/inventory.php';
                include 'views/doctors.php';
                include 'views/cases.php';
                include 'views/expenses.php';
                include 'views/suppliers.php'; // <-- الميزة الجديدة هنا
                include 'views/reports.php';
                include 'views/todo.php';
                include 'views/settings-profile.php';
                ?>
            </main>
        </div>
    </div>

    <?php include 'partials/modals.php'; ?>
    
    <?php include 'partials/footer.php'; ?>

</body>
</html>