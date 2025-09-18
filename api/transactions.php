<?php
require '../db_connect.php';
header('Content-Type: application/json; charset=utf-8');

// Start session if not already started to access user_id
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents('php://input'), true);

try {
    $conn->begin_transaction();

    if ($method == 'POST') {
        $type = $data['type'] ?? '';
        
        if ($type === 'sale') {
            $inventory_id = $data['inventory_id'];
            $quantity = floatval($data['quantity']);
            $sale_type = $data['sale_type'] ?? 'package';
            $discount_percentage = floatval($data['discount'] ?? 0);

            $stmt = $conn->prepare("SELECT * FROM inventory WHERE id = ? AND user_id = ? AND is_deleted = 0 FOR UPDATE");
            $stmt->bind_param("ii", $inventory_id, $user_id);
            $stmt->execute();
            $item = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$item) {
                throw new Exception('الصنف غير موجود.');
            }

            $item['package_size'] = !empty($item['package_size']) && is_numeric($item['package_size']) && $item['package_size'] > 0 ? floatval($item['package_size']) : 1;
            $item['unit_sale_price'] = isset($item['unit_sale_price']) && is_numeric($item['unit_sale_price']) ? floatval($item['unit_sale_price']) : $item['price'];
            $item['current_package_volume'] = isset($item['current_package_volume']) && is_numeric($item['current_package_volume']) ? floatval($item['current_package_volume']) : 0;

            $sale_price_per_unit = 0;
            $purchase_price_per_unit = 0;

            if ($sale_type === 'package') {
                if ($item['quantity'] < $quantity) {
                    throw new Exception('الكمية غير كافية في المخزون.');
                }
                $sale_price_per_unit = $item['price'];
                $purchase_price_per_unit = $item['purchase_price'];
            } else { // 'unit' sale
                if (!$item['package_size'] > 0 || !$item['unit_sale_price'] > 0) {
                    throw new Exception('هذا الصنف غير مهيأ للبيع بالفرط.');
                }
                $total_available_units = ($item['quantity'] * $item['package_size']) + $item['current_package_volume'];
                if ($total_available_units < $quantity) {
                    throw new Exception('الكمية غير كافية في المخزون للبيع بالفرط.');
                }
                $sale_price_per_unit = $item['unit_sale_price'];
                $purchase_price_per_unit = $item['purchase_price'] / $item['package_size'];
            }
            
            $total_price = $sale_price_per_unit * $quantity;
            $total_purchase_price = $purchase_price_per_unit * $quantity;
            $base_profit = $total_price - $total_purchase_price;
            $discount_amount = $base_profit * ($discount_percentage / 100);
            $final_price = $total_price - $discount_amount;
            $final_profit = $final_price - $total_purchase_price;

            if ($sale_type === 'package') {
                $new_quantity = $item['quantity'] - $quantity;
                $stmt_update = $conn->prepare("UPDATE inventory SET quantity = ? WHERE id = ?");
                $stmt_update->bind_param("di", $new_quantity, $inventory_id);
                $stmt_update->execute();
                $stmt_update->close();
            } else { 
                $current_total_units = ($item['quantity'] * $item['package_size']) + $item['current_package_volume'];
                $new_total_units = $current_total_units - $quantity;
                $new_full_packages = floor($new_total_units / $item['package_size']);
                $new_package_volume = fmod($new_total_units, $item['package_size']);
                $stmt_update = $conn->prepare("UPDATE inventory SET quantity = ?, current_package_volume = ? WHERE id = ?");
                $stmt_update->bind_param("idi", $new_full_packages, $new_package_volume, $inventory_id);
                $stmt_update->execute();
                $stmt_update->close();
            }

            $sale_date = (new DateTime())->format('Y-m-d');
            $stmt_sale = $conn->prepare("INSERT INTO sales (user_id, inventory_id, item_name, category, quantity, price_at_sale, purchase_price_at_sale, total_price, discount, discount_amount, final_price, profit, sale_date, sale_type, unit_name_at_sale) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt_sale->bind_param("iisdsddddddddss", $user_id, $inventory_id, $item['name'], $item['type'], $quantity, $sale_price_per_unit, $purchase_price_per_unit, $total_price, $discount_percentage, $discount_amount, $final_price, $final_profit, $sale_date, $sale_type, $item['unit_name']);
            $stmt_sale->execute();
            $stmt_sale->close();
            echo json_encode(['success' => true, 'message' => 'تم تسجيل البيع بنجاح.']);

        } elseif ($type === 'service') {
            $description = $data['description'];
            $price = floatval($data['price']);
            $sale_date = (new DateTime())->format('Y-m-d');

            $stmt_service = $conn->prepare("INSERT INTO sales (user_id, inventory_id, item_name, category, quantity, price_at_sale, purchase_price_at_sale, total_price, discount, discount_amount, final_price, profit, sale_date, sale_type, unit_name_at_sale) VALUES (?, NULL, ?, 'service', 1, ?, 0, ?, 0, 0, ?, ?, ?, 'service', NULL)");
            
            if ($stmt_service === false) {
                throw new Exception("SQL prepare statement failed: " . $conn->error);
            }

            $stmt_service->bind_param("isdddds", $user_id, $description, $price, $price, $price, $price, $sale_date);
            $stmt_service->execute();
            $stmt_service->close();
            echo json_encode(['success' => true, 'message' => 'تم تسجيل الخدمة بنجاح.']);
        
        // --- ⭐⭐⭐ هذا هو الجزء الذي تمت إضافته --- ⭐⭐⭐
        } elseif ($type === 'expense') {
            $description = $data['description'];
            $amount = floatval($data['amount']);
            $expense_date = (new DateTime())->format('Y-m-d');

            if (empty($description) || $amount <= 0) {
                throw new Exception('يرجى إدخال وصف وقيمة صحيحة للمصروف.');
            }

            $stmt_expense = $conn->prepare("INSERT INTO expenses (user_id, description, amount, expense_date) VALUES (?, ?, ?, ?)");
            $stmt_expense->bind_param("isds", $user_id, $description, $amount, $expense_date);
            $stmt_expense->execute();
            $stmt_expense->close();
            echo json_encode(['success' => true, 'message' => 'تم تسجيل المصروف بنجاح.']);
        }
        // ----------------------------------------------------

    } elseif ($method == 'PUT') {
        // ... (كود التعديل لا يتغير) ...
        $type = $data['type'] ?? '';
        if ($type === 'sale') {
            $sale_id = $data['id'];
            $new_quantity = floatval($data['quantity']);
            $new_discount_percentage = floatval($data['discount'] ?? 0);

            $stmt_get_sale = $conn->prepare("SELECT * FROM sales WHERE id = ? AND user_id = ? FOR UPDATE");
            $stmt_get_sale->bind_param("ii", $sale_id, $user_id);
            $stmt_get_sale->execute();
            $original_sale = $stmt_get_sale->get_result()->fetch_assoc();
            $stmt_get_sale->close();

            if (!$original_sale) { throw new Exception("لم يتم العثور على سجل البيع."); }
            if ($original_sale['sale_type'] === 'service') { throw new Exception("لا يمكن تعديل الخدمات من هنا."); }

            $inventory_id = $original_sale['inventory_id'];
            $original_quantity = floatval($original_sale['quantity']);
            $sale_type = $original_sale['sale_type'];

            $stmt_get_item = $conn->prepare("SELECT * FROM inventory WHERE id = ? AND user_id = ? FOR UPDATE");
            $stmt_get_item->bind_param("ii", $inventory_id, $user_id);
            $stmt_get_item->execute();
            $item = $stmt_get_item->get_result()->fetch_assoc();
            $stmt_get_item->close();

            if (!$item) { throw new Exception("الصنف المرتبط بالبيع غير موجود."); }

            $item['package_size'] = !empty($item['package_size']) && is_numeric($item['package_size']) && $item['package_size'] > 0 ? floatval($item['package_size']) : 1;
            
            $pre_sale_quantity = $item['quantity'];
            $pre_sale_volume = $item['current_package_volume'];

            if ($sale_type === 'package') {
                $pre_sale_quantity += $original_quantity;
            } else {
                $total_current_units = ($item['quantity'] * $item['package_size']) + $item['current_package_volume'];
                $total_pre_sale_units = $total_current_units + $original_quantity;
                $pre_sale_quantity = floor($total_pre_sale_units / $item['package_size']);
                $pre_sale_volume = fmod($total_pre_sale_units, $item['package_size']);
            }

            if ($sale_type === 'package') {
                if ($pre_sale_quantity < $new_quantity) { throw new Exception('الكمية الجديدة غير كافية في المخزون.'); }
            } else {
                $total_available_units = ($pre_sale_quantity * $item['package_size']) + $pre_sale_volume;
                if ($total_available_units < $new_quantity) { throw new Exception('الكمية الجديدة غير كافية في المخزون للبيع بالفرط.'); }
            }

            if ($sale_type === 'package') {
                $final_item_quantity = $pre_sale_quantity - $new_quantity;
                $final_item_volume = $pre_sale_volume;
            } else {
                $new_total_units_after_sale = ($pre_sale_quantity * $item['package_size']) + $pre_sale_volume - $new_quantity;
                $final_item_quantity = floor($new_total_units_after_sale / $item['package_size']);
                $final_item_volume = fmod($new_total_units_after_sale, $item['package_size']);
            }
            
            $stmt_update_inv = $conn->prepare("UPDATE inventory SET quantity = ?, current_package_volume = ? WHERE id = ?");
            $stmt_update_inv->bind_param("idi", $final_item_quantity, $final_item_volume, $inventory_id);
            $stmt_update_inv->execute();
            $stmt_update_inv->close();

            $sale_price_per_unit = $original_sale['price_at_sale'];
            $purchase_price_per_unit = $original_sale['purchase_price_at_sale'];

            $new_total_price = $sale_price_per_unit * $new_quantity;
            $new_total_purchase_price = $purchase_price_per_unit * $new_quantity;
            $new_base_profit = $new_total_price - $new_total_purchase_price;
            $new_discount_amount = $new_base_profit * ($new_discount_percentage / 100);
            $new_final_price = $new_total_price - $new_discount_amount;
            $new_final_profit = $new_final_price - $new_total_purchase_price;
            
            $stmt_update_sale = $conn->prepare("UPDATE sales SET quantity = ?, discount = ?, discount_amount = ?, total_price = ?, final_price = ?, profit = ? WHERE id = ? AND user_id = ?");
            $stmt_update_sale->bind_param("ddddddii", $new_quantity, $new_discount_percentage, $new_discount_amount, $new_total_price, $new_final_price, $new_final_profit, $sale_id, $user_id);
            $stmt_update_sale->execute();
            $stmt_update_sale->close();

            echo json_encode(['success' => true, 'message' => 'تم تعديل البيع بنجاح.']);
        } elseif ($type === 'expense') {
            $stmt = $conn->prepare("UPDATE expenses SET description = ?, amount = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sdii", $data['description'], $data['amount'], $data['id'], $user_id);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => true, 'message' => 'تم تحديث المصروف بنجاح.']);
        } elseif ($type === 'service') {
             $stmt = $conn->prepare("UPDATE sales SET item_name = ?, price_at_sale = ?, total_price = ?, final_price = ?, profit = ? WHERE id = ? AND user_id = ? AND sale_type = 'service'");
             $price = floatval($data['price']);
             $description = $data['description'];
             
             $stmt->bind_param("sddddii", $description, $price, $price, $price, $price, $data['id'], $user_id);
             
             $stmt->execute();

             if ($stmt->affected_rows > 0) {
                  echo json_encode(['success' => true, 'message' => 'تم تحديث الخدمة بنجاح.']);
             } else {
                  echo json_encode(['success' => false, 'message' => 'لم يتم العثور على السجل للتحديث أو لم تتغير البيانات.']);
             }
             
             $stmt->close();
        }
    } elseif ($method == 'DELETE') {
        // ... (كود الحذف لا يتغير) ...
        $type = $data['type'] ?? '';
        if ($type === 'sale') {
            $stmt_get_sale = $conn->prepare("SELECT * FROM sales WHERE id = ? AND user_id = ? FOR UPDATE");
            $stmt_get_sale->bind_param("ii", $data['id'], $user_id);
            $stmt_get_sale->execute();
            $sale = $stmt_get_sale->get_result()->fetch_assoc();
            $stmt_get_sale->close();

            if ($sale) {
                 if ($sale['inventory_id']) { // Only return stock if it's a product sale
                    $stmt_get_item = $conn->prepare("SELECT * FROM inventory WHERE id = ? AND user_id = ? FOR UPDATE");
                    $stmt_get_item->bind_param("ii", $sale['inventory_id'], $user_id);
                    $stmt_get_item->execute();
                    $item = $stmt_get_item->get_result()->fetch_assoc();
                    $stmt_get_item->close();

                    $item['package_size'] = !empty($item['package_size']) && is_numeric($item['package_size']) && $item['package_size'] > 0 ? floatval($item['package_size']) : 1;

                    if ($sale['sale_type'] === 'package') {
                        $stmt_return_stock = $conn->prepare("UPDATE inventory SET quantity = quantity + ? WHERE id = ? AND user_id = ?");
                        $stmt_return_stock->bind_param("dii", $sale['quantity'], $sale['inventory_id'], $user_id);
                    } else { // 'unit'
                        if ($item && $item['package_size'] > 0) {
                            $total_current_units = ($item['quantity'] * $item['package_size']) + $item['current_package_volume'];
                            $new_total_volume = $total_current_units + $sale['quantity'];
                            $new_full_packages = floor($new_total_volume / $item['package_size']);
                            $new_open_pack_volume = fmod($new_total_volume, $item['package_size']);
                            
                            $stmt_return_stock = $conn->prepare("UPDATE inventory SET quantity = ?, current_package_volume = ? WHERE id = ? AND user_id = ?");
                            $stmt_return_stock->bind_param("idii", $new_full_packages, $new_open_pack_volume, $sale['inventory_id'], $user_id);
                        } else {
                            $stmt_return_stock = $conn->prepare("UPDATE inventory SET current_package_volume = current_package_volume + ? WHERE id = ? AND user_id = ?");
                            $stmt_return_stock->bind_param("dii", $sale['quantity'], $sale['inventory_id'], $user_id);
                        }
                    }
                    $stmt_return_stock->execute();
                    $stmt_return_stock->close();
                 }

                $stmt_delete = $conn->prepare("UPDATE sales SET is_deleted = 1 WHERE id = ? AND user_id = ?");
                $stmt_delete->bind_param("ii", $data['id'], $user_id);
                $stmt_delete->execute();
                $stmt_delete->close();
                echo json_encode(['success' => true, 'message' => 'تم إلغاء العملية بنجاح.']);
            } else {
                throw new Exception('لم يتم العثور على سجل البيع.');
            }
        } elseif ($type === 'service') {
            $stmt_delete = $conn->prepare("UPDATE sales SET is_deleted = 1 WHERE id = ? AND user_id = ? AND sale_type = 'service'");
            $stmt_delete->bind_param("ii", $data['id'], $user_id);
            $stmt_delete->execute();
            
            if ($stmt_delete->affected_rows > 0) {
                echo json_encode(['success' => true, 'message' => 'تم حذف الخدمة بنجاح.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'لم يتم العثور على الخدمة.']);
            }
            $stmt_delete->close();
        } elseif ($type === 'expense') {
            $stmt_delete = $conn->prepare("UPDATE expenses SET is_deleted = 1 WHERE id = ? AND user_id = ?");
            $stmt_delete->bind_param("ii", $data['id'], $user_id);
            $stmt_delete->execute();
            $stmt_delete->close();
            echo json_encode(['success' => true, 'message' => 'تم حذف المصروف.']);
        }
    }

    $conn->commit();
} catch (Exception $e) {
    if ($conn->in_transaction) $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
$conn->close();
?>