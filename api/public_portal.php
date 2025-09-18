<?php
// api/public_portal.php
// (الإصدار 4.0: النهائي - يجلب العيادات، الأطباء، الخدمات، العروض، والتقييمات المزدوجة)

require '../db_connect.php'; // يوفر $conn
header('Content-Type: application/json; charset=utf-8');

// هذا الملف عام ولا يتطلب جلسة أدمن، لذلك لا نتحقق من $_SESSION['user_id']

try {
    
    $action = $_GET['action'] ?? 'list_all'; 
    
    if ($action === 'get_clinic_details') {
        // ========== منطق جلب تفاصيل عيادة واحدة ==========
        
        if (!isset($_GET['id'])) {
            throw new Exception("ID العيادة مطلوب.");
        }
        $clinic_user_id = intval($_GET['id']);
        
        $response_data = [
            'clinic_info' => [],
            'doctors' => [],
            'services' => [], 
            'promotions' => [],
            'doctor_reviews' => [],
            'clinic_reviews' => [] 
        ];

        // 1. جلب معلومات العيادة (من الإعدادات)
        $stmt_settings = $conn->prepare("SELECT setting_key, setting_value FROM user_settings WHERE user_id = ?");
        $stmt_settings->bind_param("i", $clinic_user_id);
        $stmt_settings->execute();
        $settings_result = $stmt_settings->get_result();
        $settings_raw = $settings_result->fetch_all(MYSQLI_ASSOC);
        $stmt_settings->close();
        
        $clinic_info = [];
        foreach ($settings_raw as $setting) {
            $clinic_info[$setting['setting_key']] = $setting['setting_value'];
        }
        $response_data['clinic_info'] = $clinic_info;

        // 2. جلب قائمة الأطباء لهذه العيادة (مع تقييم كل طبيب)
        $sql_doctors = "
            SELECT 
                d.*, 
                COALESCE(AVG(r.rating), 0) AS avg_rating, 
                COUNT(r.id) AS review_count
            FROM doctors d
            LEFT JOIN reviews r ON d.id = r.doctor_id
            WHERE d.user_id = ? AND d.is_active = 1
            GROUP BY d.id
            ORDER BY d.name ASC
        ";
        $stmt_doctors = $conn->prepare($sql_doctors);
        $stmt_doctors->bind_param("i", $clinic_user_id);
        $stmt_doctors->execute();
        $response_data['doctors'] = $stmt_doctors->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_doctors->close();

        // 3. جلب تقييمات الأطباء (آخر 20 تقييم)
        $sql_reviews = "
            SELECT r.*, d.name as doctor_name
            FROM reviews r
            JOIN doctors d ON r.doctor_id = d.id
            WHERE d.user_id = ?
            ORDER BY r.created_at DESC
            LIMIT 20
        ";
        $stmt_reviews = $conn->prepare($sql_reviews);
        $stmt_reviews->bind_param("i", $clinic_user_id);
        $stmt_reviews->execute();
        $response_data['doctor_reviews'] = $stmt_reviews->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_reviews->close();

        // 4. جلب الخدمات التي تقدمها العيادة (من الجدول الجديد)
        $stmt_services = $conn->prepare("SELECT * FROM clinic_services WHERE user_id = ? ORDER BY service_name ASC");
        $stmt_services->bind_param("i", $clinic_user_id);
        $stmt_services->execute();
        $response_data['services'] = $stmt_services->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_services->close();

        // 5. جلب تقييمات العيادة نفسها (من الجدول الجديد clinic_reviews)
        $stmt_clinic_reviews = $conn->prepare("SELECT * FROM clinic_reviews WHERE clinic_user_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmt_clinic_reviews->bind_param("i", $clinic_user_id);
        $stmt_clinic_reviews->execute();
        $response_data['clinic_reviews'] = $stmt_clinic_reviews->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_clinic_reviews->close();
        
        // 6. جلب العروض النشطة (من الجدول الجديد promotions)
        $stmt_promos = $conn->prepare("SELECT * FROM clinic_promotions WHERE user_id = ? AND is_active = 1 AND expiry_date >= CURDATE() AND (start_date IS NULL OR start_date <= CURDATE()) ORDER BY expiry_date ASC");
        $stmt_promos->bind_param("i", $clinic_user_id);
        $stmt_promos->execute();
        $response_data['promotions'] = $stmt_promos->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt_promos->close();
        
        echo json_encode(['success' => true, 'data' => $response_data]);

    } else {
        // ========== المنطق المحدث لجلب (كل العيادات) ==========
        // يجلب الآن الإحداثيات، وتقييم العيادة، وصورة بروفايل العيادة (الأدمن)
        
        $sql_all_clinics = "
            SELECT
                u.id AS clinic_owner_user_id,
                s_name.setting_value AS clinic_name,
                s_addr.setting_value AS clinic_address,
                s_pic.setting_value AS profile_pic,
                s_lat.setting_value AS clinic_lat,
                s_lng.setting_value AS clinic_lng,
                COALESCE(AVG(cr.rating), 0) AS avg_clinic_rating,
                COUNT(DISTINCT cr.id) AS total_clinic_reviews,
                COUNT(DISTINCT d.id) AS doctor_count
            FROM users u
            LEFT JOIN user_settings s_name ON u.id = s_name.user_id AND s_name.setting_key = 'clinic_name'
            LEFT JOIN user_settings s_addr ON u.id = s_addr.user_id AND s_addr.setting_key = 'clinic_address'
            LEFT JOIN user_settings s_pic ON u.id = s_pic.user_id AND s_pic.setting_key = 'profile_pic'
            LEFT JOIN user_settings s_lat ON u.id = s_lat.user_id AND s_lat.setting_key = 'clinic_lat'
            LEFT JOIN user_settings s_lng ON u.id = s_lng.user_id AND s_lng.setting_key = 'clinic_lng'
            LEFT JOIN doctors d ON u.id = d.user_id AND d.is_active = 1
            LEFT JOIN clinic_reviews cr ON u.id = cr.clinic_user_id
            WHERE
                s_name.setting_value IS NOT NULL AND s_name.setting_value != ''
                AND s_lat.setting_value IS NOT NULL AND s_lat.setting_value != ''
                AND s_lng.setting_value IS NOT NULL AND s_lng.setting_value != ''
            GROUP BY 
                u.id, s_name.setting_value, s_addr.setting_value, s_pic.setting_value, s_lat.setting_value, s_lng.setting_value
            ORDER BY
                avg_clinic_rating DESC;
        ";
        
        $stmt = $conn->prepare($sql_all_clinics);
        $stmt->execute();
        $result = $stmt->get_result();
        $clinics_list = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        echo json_encode(['success' => true, 'clinics' => $clinics_list]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>