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

            if ($type == 'get_maps') {
                $sql = "SELECT * FROM map";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute();

                $maps = $stmt->fetchAll();

                $response = [];
                foreach($maps as $map) {
                    $data = $map;
                    $sql = "SELECT * FROM map_item JOIN item ON item.id = map_item.item_id WHERE map_id = ?";
                    $stmt = $conn->prepare($sql);

                    // Thực thi truy vấn
                    $stmt->execute([$map['id']]);
                    $items = $stmt->fetchAll();

                    // Sử dụng array_map để lấy ra mảng chứa tên của mỗi đối tượng
                    $array_names = array_map(function($item) {
                        return "<img src='img/item/" . $item['id'] . ".png' class='item-img'>" . $item['name'] . ":" . $item['ratio'] . "%";
                    }, $items);

                    // Kết hợp các tên bằng dấu phẩy
                    $string_names = implode('<br>', $array_names);

                    $data['items'] = $string_names;

                    $sql = "SELECT * FROM map_required JOIN item ON item.id = map_required.item_id WHERE map_id = ?";
                    $stmt = $conn->prepare($sql);

                    // Thực thi truy vấn
                    $stmt->execute([$map['id']]);
                    $requireds = $stmt->fetchAll();

                    // Sử dụng array_map để lấy ra mảng chứa tên của mỗi đối tượng
                    $array_names = array_map(function($item) {
                        return "<img src='img/item/" . $item['id'] . ".png' class='item-img'>" . $item['name'] . "<i> x" . $item['quantity'] . '</i>';
                    }, $requireds);

                    // Kết hợp các tên bằng dấu phẩy
                    $string_names = implode('<br>', $array_names);

                    if ($string_names == "") $string_names = "Không";

                    $data['requireds'] = $string_names;

                    $response[] = $data;
                }

                echo json_encode($response);
            } else if ($type == 'combat') {
                $map_id = $data['map_id'];

                $sql = "SELECT * FROM map WHERE id = ?";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute([$map_id]);
                $map = $stmt->fetch();

                $sql = "SELECT * FROM user WHERE tele_id = ?";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();

                if ($user['power'] < $map['power_required']) {
                    echo json_encode(['msg' => 'Chiến lực tổng quát không đủ!']);
                } else {
                    $sql = "SELECT * FROM map_required WHERE map_id = ?";
                    $stmt = $conn->prepare($sql);

                    // Thực thi truy vấn
                    $stmt->execute([$map_id]);
                    $requireds = $stmt->fetchAll();

                    if (!empty($requireds)) {
                        foreach($requireds as $required) {
                            $sql = "SELECT * FROM user_item WHERE item_id = ? AND user_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$required['item_id'], $user['id']]);
                            $check = $stmt->fetch();
                            if (!($check && $check['quantity'] >= $required['quantity'])) {
                                echo json_encode(['msg' => 'Vật phẩm tiêu hao không đủ!']);
                                exit;
                            }
                        }

                        foreach($requireds as $required) {
                            $sql = "SELECT * FROM user_item WHERE item_id = ? AND user_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$required['item_id'], $user['id']]);
                            $check = $stmt->fetch();
                            
                            $sql = "UPDATE user_item SET quantity = ? WHERE user_id = ? AND item_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$check['quantity'] - $required['quantity'], $user['id'], $required['item_id']]);
                        }
                    }

                    echo json_encode(['result' => 200]);
                }
            } else if ($type == 'combat_collect') {
                $map_id = $data['map_id'];

                $sql = "SELECT * FROM map_item JOIN item ON item.id = map_item.item_id WHERE map_id = ?";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute([$map_id]);
                $map_items = $stmt->fetchAll();

                $randomNumber = rand(1,100);

                foreach($map_items as $map_item) {
                    if ($randomNumber <= $map_item['ratio']) {
                        $sql = "SELECT * FROM user WHERE user.tele_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$user_id]);
                        $user = $stmt->fetch();

                        $sql = "SELECT * FROM user_item WHERE user_id = ? AND item_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$user['id'], $map_item['id']]);
                        $user_item = $stmt->fetch();
                        
                        if(!$user_item) {
                            $sql = "INSERT user_item(user_id, item_id, quantity) VALUES (?,?,?) ";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$user['id'], $map_item['id'], 1]);
                        } else {
                            $sql = "UPDATE user_item SET quantity = ? WHERE user_id = ? AND item_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$user_item['quantity'] + 1, $user['id'], $map_item['id']]);
                        }
                        echo json_encode($map_item);
                        exit;
                    }
                    $randomNumber -= $map_item['ratio'];
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
