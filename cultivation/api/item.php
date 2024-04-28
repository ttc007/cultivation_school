<?php
require_once "db.php";

// Kiểm tra xem request có phải là POST không
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Kiểm tra xem có dữ liệu gửi đến không
    $data = json_decode(file_get_contents("php://input"), true);
    if (isset($data['user_id'])) {
        try {
            // Tạo kết nối PDO
            // Lấy thông tin từ request
            $type = $data['type'];
            $user_id = $data['user_id'];

            if ($type == 'get_bag') {
                $sql = "SELECT item.id item_id, item.name item_name, user_item.quantity, item.type, user_item.id user_item_id, item.cost FROM user 
                    JOIN user_item ON user.id = user_item.user_id 
                    JOIN item ON item.id = user_item.item_id 
                    WHERE user.tele_id = ? AND user_item.quantity > 0";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_id]);
                $user_items = $stmt->fetchAll();

                echo json_encode($user_items);
            } else if ($type == 'sell') {
                $quantity = $data['quantity'];
                $user_item_id = $data['user_item_id'];

                $sql = "SELECT item.id item_id, item.name item_name, user_item.quantity, item.type, user_item.id user_item_id, item.cost FROM user 
                    JOIN user_item ON user.id = user_item.user_id 
                    JOIN item ON item.id = user_item.item_id 
                    WHERE user_item.id = ?";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute([$user_item_id]);

                $user_item = $stmt->fetch();

                if ($user_item['quantity'] - $quantity >= 0) {
                    $sql = "UPDATE user_item SET quantity = ? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $quantityUpdate = $user_item['quantity'] - $quantity;
                    $stmt->execute([$quantityUpdate , $user_item_id]);

                    $sql = "SELECT * FROM user WHERE tele_id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();

                    $ore = $user['ore'] + $user_item['cost'] * $quantity;

                    $sql = "UPDATE user SET ore = ? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$ore, $user['id']]);

                    echo json_encode(['ore' => $ore]);
                } else {
                    echo json_encode(['msg' => 'Không đủ số lượng!']);
                }
            } else if ($type == 'get_user_item') {
                $user_item_id = $data['user_item_id'];
                $sql = "SELECT item.id item_id, item.name item_name, user_item.quantity, item.type, user_item.id user_item_id, item.cost FROM user 
                    JOIN user_item ON user.id = user_item.user_id 
                    JOIN item ON item.id = user_item.item_id 
                    WHERE user_item.id = ?";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute([$user_item_id]);

                $user_item = $stmt->fetch();

                echo json_encode($user_item);


            } else if ($type == 'get_items') {
                $sql = "SELECT * FROM item";
                $stmt = $conn->prepare($sql);
                $stmt->execute([]);

                $items = $stmt->fetchAll();

                echo json_encode($items);
            } else if ($type == 'get_item') {
                $sql = "SELECT * FROM item WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$data['id']]);

                $item = $stmt->fetch();

                echo json_encode($item);
            } else if ($type == 'buy') {
                $item_id = $data['item_id'];
                $quantity = $data['quantity'];

                $sql = "SELECT * FROM item WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$item_id]);
                $item = $stmt->fetch();

                $ore = $item['cost'] * 2 * $quantity;

                $sql = "SELECT * FROM user WHERE tele_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();

                if ($user['ore'] >= $ore) {
                    $user_ore = $user['ore'] - $ore;
                    $sql = "UPDATE user SET ore = ? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$user_ore, $user['id']]);

                    $sql = "SELECT * FROM user_item WHERE user_id = ? AND item_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$user['id'], $item_id]);
                    $user_item = $stmt->fetch();
                    
                    if(!$user_item) {
                        $sql = "INSERT user_item(user_id, item_id, quantity) VALUES (?,?,?) ";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$user['id'], $item_id, $quantity]);
                    } else {
                        $sql = "UPDATE user_item SET quantity = ? WHERE user_id = ? AND item_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$user_item['quantity'] + $quantity, $user['id'], $item_id]);
                    }

                    echo json_encode(['ore' => $user_ore]);
                } else {
                    echo json_encode(['msg' => 'Không đủ Ore!']);
                }
            } else if ($type == 'get_item_medicine') {
                $type_craff = $data['type_craff'];

                $sql = "SELECT * FROM item WHERE type = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$type_craff]);
                $items = $stmt->fetchAll();

                $sql = "SELECT * FROM user WHERE tele_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();

                $response = [];
                foreach($items as $item) {
                    $sql = "SELECT item.id item_craff_id, item.name item_craff_name, item_craff.quantity item_craff_quantity, item_craff.item_craff_id item_craff_required_id
                        FROM item_craff 
                        JOIN item ON item_craff.item_craff_id = item.id WHERE item_craff.item_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$item['id']]);
                    $item_craffs = $stmt->fetchAll();

                    $item_craff_response = [];
                    foreach($item_craffs as $item_craff) {
                        $sql = "SELECT * FROM user_item WHERE item_id = ? AND user_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$item_craff['item_craff_required_id'], $user['id']]);
                        $user_item = $stmt->fetch();

                        if ($user_item) {
                            $item_craff['user_item_quantity'] = $user_item['quantity'];
                        }
                        $item_craff_response[] = $item_craff;
                    }

                    $item['item_craffs'] = $item_craff_response;
                    $response[] = $item;
                }
                echo json_encode($response);
            } else if ($type == 'medicine_check') {
                $type_innate = $data['type_innate'];

                $sql = "SELECT * FROM user JOIN user_student ON user.id = user_student.user_id WHERE tele_id=? AND user_student.innate LIKE ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_id, $type_innate]);
                $user_students = $stmt->fetchAll();

                $item_id = $data['item_id'];
                $sql = "SELECT * FROM item WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$item_id]);
                $item = $stmt->fetch();

                if (empty($user_students)) {
                    echo json_encode(['msg' => 'Không có học viên nào có thiên phú đủ yêu cầu!']);
                } else {
                    $innate_level = 1;
                    foreach($user_students as $user_student) {
                        $innate_level_student = substr($user_student['innate'], -1);
                        $innate_level_student = (int) $innate_level_student;
                        if ($innate_level_student > $innate_level) {
                            $innate_level = $innate_level_student;
                        }
                    }

                    $innate_required = substr($item['required_innate'], -1);
                    $innate_required = (int) $innate_required;

                    if ($innate_level <  $innate_required) {
                        echo json_encode(['msg' => 'Không có học viên nào có thiên phú đủ yêu cầu!']);
                    } else {
                        $sql = "SELECT * FROM user WHERE tele_id=?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch();

                        $sql = "SELECT * FROM item_craff WHERE item_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$item_id]);
                        $item_craffs = $stmt->fetchAll();

                        foreach($item_craffs as $item_craff) {
                            $sql = "SELECT * FROM user_item WHERE item_id = ? AND user_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$item_craff['item_craff_id'], $user['id']]);
                            $check = $stmt->fetch();

                            if (!($check && $check['quantity'] >= $item_craff['quantity'])) {
                                echo json_encode(['msg' => 'Không đủ vật liệu']);
                                exit;
                            }
                        }

                        $sql = "SELECT * FROM user_item WHERE user_id = ? AND item_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$user['id'], $item_id]);
                        $user_item = $stmt->fetch();
                        
                        if(!$user_item) {
                            $sql = "INSERT user_item(user_id, item_id, quantity) VALUES (?,?,?) ";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$user['id'], $item_id, 1]);
                        } else {
                            $sql = "UPDATE user_item SET quantity = ? WHERE user_id = ? AND item_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$user_item['quantity'] + 1, $user['id'], $item_id]);
                        }

                        foreach($item_craffs as $item_craff) {
                            $sql = "SELECT * FROM user_item WHERE item_id = ? AND user_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$item_craff['item_craff_id'], $user['id']]);
                            $check = $stmt->fetch();
                            
                            $sql = "UPDATE user_item SET quantity = ? WHERE user_id = ? AND item_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$check['quantity'] - $item_craff['quantity'], $user['id'], $item_craff['item_craff_id']]);
                        }

                        $sql = "SELECT * FROM item WHERE id=?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$item_id]);
                        $item_response = $stmt->fetch();
                        echo json_encode($item_response);
                    }
                }
                
            }
            

        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
        // Đóng kết nối
        $conn = null;
    } else {
        // Trường hợp dữ liệu không hợp lệ
        http_response_code(400); // Bad Request
        echo "Invalid request. Missing parameters.";
    }
} else {
    // Trường hợp request không phải là POST
    echo "Method not allowed. Only POST requests are allowed.";
}
?>
