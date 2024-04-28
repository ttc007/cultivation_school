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

            // Thiết lập chế độ lỗi cho PDO để hiển thị thông báo lỗi chi tiết
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Chuẩn bị truy vấn SQL
            $sql = "SELECT * FROM user WHERE tele_id=?";
            $stmt = $conn->prepare($sql);

            // Thực thi truy vấn
            $stmt->execute([$user_id]);

            $user = $stmt->fetch();

            if (!$user) {
                $sql = "INSERT user(tele_id, ore, power) VALUES (?,?,?)";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_id, 1, 0]);
            }

            if ($type == 'get_ore') {
                $sql = "SELECT * FROM user WHERE tele_id=?";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute([$user_id]);

                $user = $stmt->fetch();

                echo json_encode(["ore" => $user['ore'], "wallet" => $user['wallet']]);
            } else if ($type == 'nap_ore') {
                $quantity = $data['amount'];

                $sql = "UPDATE user SET ore = ? WHERE tele_id=?";
                $stmt = $conn->prepare($sql);

                $quantity += $user['ore'];
                $stmt->execute([$quantity, $user_id]);

                echo json_encode(["ore" => $quantity]);
            } else if ($type == 'ore') {
                $quantity = 1;

                $sql = "SELECT * FROM user_student JOIN user ON user.id = user_student.user_id WHERE user.tele_id=?";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute([$user_id]);
                $user_students = $stmt->fetchAll();

                foreach($user_students as $user_student) {
                    if (str_contains($user_student['innate'], 'Tốc độ đào mỏ')) {
                        $innate = substr($user_student['innate'], -1);
                        $innate = (int) $innate;
                        $quantity += $innate;
                    }
                }

                $sql = "UPDATE user SET ore = ? WHERE tele_id=?";
                $stmt = $conn->prepare($sql);

                $ore = $user['ore'] + $quantity;
                $stmt->execute([$ore, $user_id]);

                echo json_encode(["ore" => $ore, 'quantity' => $quantity]);
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
