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

            if ($type == 'get_user_students') {
                $sql = "SELECT user_student.id user_student_id, user_student.level, user_student.exp, user_student.power, student.name, student.quality, user_student.innate, student.id, user.power user_power
                 FROM user JOIN user_student ON user_student.user_id = user.id 
                    JOIN student ON user_student.student_id = student.id WHERE user.tele_id=? 
                    ORDER BY user_student.power DESC";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute([$user_id]);

                $students = $stmt->fetchAll();

                $response = [];
                foreach($students as $student) {
                    $sql = "SELECT * FROM user_student_item JOIN item ON item.id = user_student_item.item_id WHERE user_student_item.user_student_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$student['user_student_id']]);

                    $items = $stmt->fetchAll();

                    $student['items'] = $items;
                    $response[] = $student;
                }

                echo json_encode($response);
            } else if ($type == 'upgrade_student') {
                $user_student_id = $data['user_student_id'];

                $sql = "SELECT user_student.id user_student_id, user_student.level, user_student.exp, user_student.power, student.name, student.quality, user_student.innate, user.ore, student.id, student.cost, user.power user_power FROM user JOIN user_student ON user_student.user_id = user.id 
                    JOIN student ON user_student.student_id = student.id WHERE user_student.id=?";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute([$user_student_id]);

                $student = $stmt->fetch();

                if ($student['ore'] >= $student['exp']) {
                    $ore = $student['ore'] - $student['exp'];

                    $level = $student['level'] + 1;

                    $exp = round(pow($level, 1.2) * ($student['cost']/10));
                    $power = round(pow($level, 1.1) * ($student['cost']/10));

                    $user_power = $student['user_power'] + $power - $student['power'];
                    $sql = "UPDATE user SET ore = ?, power = ? WHERE tele_id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$ore, $user_power, $user_id]);

                    $innate = $student['innate'];
                    if ($level % 10 == 0) {
                        if (str_contains($student['innate'], 'Tốc độ đào mỏ')) {
                            $innate = "Tốc độ đào mỏ +" . ($level/10 + 1); 
                        } else if (str_contains($student['innate'], 'cấp')){
                            $innate = substr_replace($innate, $level/10 + 1, -1);
                        }
                    }

                    $sql = "UPDATE user_student SET level = ?, exp = ?, power = ?, innate = ? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$level, $exp, $power, $innate, $user_student_id]);

                    echo json_encode(['ore' => $ore]);
                } else {
                    echo json_encode(['msg' => "Không đủ Ore!"]);
                }
            } else if ($type == 'recuit_student') {
                $sql = "SELECT * FROM user JOIN student ON user.recuit_student_id = student.id WHERE user.tele_id=?";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute([$user_id]);

                $student = $stmt->fetch();

                if (!$student) {
                    $randomNumber = rand(1, 7);
                    $sql = "UPDATE user SET recuit_student_id = ? WHERE tele_id=?";
                    $stmt = $conn->prepare($sql);

                    $stmt->execute([$randomNumber, $user_id]);

                    $sql = "SELECT * FROM user JOIN student ON user.recuit_student_id = student.id WHERE user.tele_id=? ";
                    $stmt = $conn->prepare($sql);

                    // Thực thi truy vấn
                    $stmt->execute([$user_id]);

                    $student = $stmt->fetch();
                }
                echo json_encode($student);
            } else if ($type == 'recuit_student_renew') {
                // Chuẩn bị truy vấn SQL
                $sql = "SELECT * FROM user WHERE tele_id=?";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute([$user_id]);

                $user = $stmt->fetch();

                $randomNumber = rand(1, 7);
                if ($randomNumber == $user['recuit_student_id']) {
                    $randomNumber = rand(1, 7);
                }

                if ($user["ore"] > 100) {
                    $ore = $user["ore"] - 100;

                    $sql = "UPDATE user SET recuit_student_id = ?, ore = ? WHERE tele_id=?";
                    $stmt = $conn->prepare($sql);

                    $stmt->execute([$randomNumber, $ore, $user_id]);

                    $sql = "SELECT * FROM user JOIN student ON user.recuit_student_id = student.id WHERE user.tele_id=? ";
                    $stmt = $conn->prepare($sql);

                    // Thực thi truy vấn
                    $stmt->execute([$user_id]);

                    $student = $stmt->fetch();

                    echo json_encode($student);
                } else {
                    echo json_encode(['msg' => "Không đủ Ore!"]);
                }
            }else if ($type == 'recuit_student_with_cost') {
                $sql = "SELECT user.id, user.recuit_student_id, student.cost, user.ore, student.innate, user.power FROM user JOIN student ON user.recuit_student_id = student.id WHERE user.tele_id=? ";
                $stmt = $conn->prepare($sql);

                // Thực thi truy vấn
                $stmt->execute([$user_id]);

                $student = $stmt->fetch();

                if ($student["ore"] > $student['cost']) {
                    $sql = "INSERT user_student(user_id, student_id, level, exp, power, innate) VALUES (?,?,?,?,?,?)";
                    $stmt = $conn->prepare($sql);

                    $exp = round(pow(1, 1.2) * ($student['cost']/10));
                    $power = round(pow(1, 1.1) * ($student['cost']/10));

                    $user_power = $student['power'] + $power;
                    $stmt->execute([$student['id'], $student['recuit_student_id'], 1, $exp , $power, $student['innate']]);

                    $ore = $student["ore"] - $student['cost'];
                    $randomNumber = rand(1, 7);
                    if ($randomNumber == $student['recuit_student_id']) {
                        $randomNumber = rand(1, 7);
                    }

                    $sql = "UPDATE user SET recuit_student_id = ?, ore = ?, power = ? WHERE tele_id=?";
                    $stmt = $conn->prepare($sql);

                    $stmt->execute([$randomNumber, $ore, $user_power, $user_id]);

                    $sql = "SELECT * FROM user JOIN student ON user.recuit_student_id = student.id WHERE user.tele_id=? ";
                    $stmt = $conn->prepare($sql);

                    // Thực thi truy vấn
                    $stmt->execute([$user_id]);

                    $student = $stmt->fetch();

                    echo json_encode($student);
                } else {
                    echo json_encode(['msg' => "Không đủ Ore!"]);
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
