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

            $sql = "SELECT * FROM user WHERE tele_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if ($type == 'get_item_equipment') {
                $type_equipment = $data['type_equipment'];
                $sql = "SELECT item.id, item.name, item.power, user_item.quantity, item.type_equipment FROM user_item JOIN item ON item.id = user_item.item_id 
                    WHERE item.type_equipment = ? AND user_item.user_id = ? AND user_item.quantity > 0 ORDER BY item.power DESC";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$type_equipment, $user['id']]);
                $items = $stmt->fetchAll();

                echo json_encode($items);
            } else if ($type == 'equipment') {
                $type_equipment = $data['type_equipment'];
                $item_id = $data['item_id'];
                $user_student_id = $data['user_student_id'];

                $sql = "SELECT * FROM user_student_item WHERE type_equipment = ? AND user_student_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$type_equipment, $user_student_id]);
                $user_student_item = $stmt->fetch();

                $power = $user['power'];
                if ($user_student_item) {
                    $sql = "UPDATE user_student_item SET item_id = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$item_id, $user_student_item['id']]);

                    $sql = "SELECT user_item.id, item.power, user_item.quantity FROM user_item JOIN item ON item.id = user_item.item_id WHERE user_item.item_id = ? AND user_item.user_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$user_student_item['item_id'], $user['id']]);
                    $user_item = $stmt->fetch();

                    $power -= $user_item['power'];

                    $sql = "UPDATE user_item SET quantity = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$user_item['quantity'] + 1, $user_item['id']]);
                } else {
                    $sql = "INSERT user_student_item(user_student_id, item_id, type_equipment) VALUES(?,?,?)";
                    $stmt = $conn->prepare($sql); 
                    $stmt->execute([$user_student_id, $item_id, $type_equipment]);
                }

                $sql = "SELECT user_item.id, item.power, user_item.quantity FROM user_item JOIN item ON item.id = user_item.item_id WHERE user_item.item_id = ? AND user_item.user_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$item_id, $user['id']]);
                $user_item_equipment = $stmt->fetch();

                $sql = "UPDATE user_item SET quantity = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_item_equipment['quantity'] - 1, $user_item_equipment['id']]);

                $power += $user_item_equipment['power'];

                $sql = "UPDATE user SET power = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$power, $user['id']]);

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
