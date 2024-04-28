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
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if ($type == 'check_transaction') {
                $url = "https://tonapi.io/v2/blockchain/accounts/UQAjvkriPSbfOkhDOTMGvWX6UmOqvT9n27I6Mm1wpr5JQTrH/transactions";
                $url = 'transaction.json';
                $transactions = file_get_contents($url);

                $transactions = json_decode($transactions);
                $transactions = $transactions->transactions;

                $quantity = $data['amount'];
                $value = ($quantity / 10000) * 1000000000;
                $address = $data['address'];

                $transaction_checks = [];
                foreach($transactions as $transaction) {
                  $source_address = $transaction->in_msg->source->address;
                  $tran_value = $transaction->in_msg->value;
                  if ($transaction->success && $address == $source_address && $value == $tran_value) {
                    $transaction_checks[] = $transaction->hash;
                  }
                }

                if (empty($transaction_checks)) {
                  echo json_encode(['msg' => 'Không tìm thấy transaction!']);
                } else {
                  foreach($transaction_checks as $hash) {
                    $sql = "SELECT * FROM transaction WHERE hash = ? AND value = ? AND source_address = ?";
                    $stmt = $conn->prepare($sql);

                    // Thực thi truy vấn
                    $stmt->execute([$hash, $value, $address]);

                    $transaction = $stmt->fetch();

                    if (!$transaction) {
                      $sql = "INSERT transaction(hash, source_address, value) VALUES (?,?,?)";
                      $stmt = $conn->prepare($sql);
                      $stmt->execute([$hash, $address, $value]);

                      $sql = "UPDATE user SET ore = ? WHERE tele_id=?";
                      $stmt = $conn->prepare($sql);

                      $quantity += $user['ore'];
                      $stmt->execute([$quantity, $user_id]);

                      echo json_encode(["ore" => $quantity]);
                      exit;
                    }
                  }
                }

                echo json_encode(['msg' => 'Không tìm thấy transaction!']);
            } else if ($type == 'check_wallet') {
                if (!$user['wallet']) {
                    echo json_encode(['msg' => 'Vui lòng kết nối wallet!']);
                } else {
                    echo json_encode(['result' => 200]);
                }
            } else if ($type == 'sell_market') {
                if ($user['ore'] < 100) {
                    echo json_encode(['msg' => 'Bạn không đủ 100<img src="img/ore.png" class="symbol"> làm phí lót sàn!']); 
                } else {
                    $quantity = $data['quantity'];
                    $price_ton = $data['price_ton'];

                    $ton_value = (int)$quantity * (float)$price_ton * 1000000000;

                    if ($ton_value == 0) {
                        echo json_encode(['msg' => 'Không thể xác định số lượng <img src="img/ton.png" class="symbol"> bạn muốn thu về sau khi bán vật phẩm!']);
                        exit;
                    }

                    $ore = $user['ore'] - 100;

                    $user_item_id = $data['user_item_id'];
                    $sql = "SELECT * FROM user_item WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$user_item_id]);
                    $user_item = $stmt->fetch();

                    if ($quantity > $user_item['quantity']) {
                        echo json_encode(['msg' => 'Số lượng bạn muốn bán vượt qua số lượng bạn có!']);
                        exit;
                    }

                    $quantity = $data['quantity'];
                    $price_ton = $data['price_ton'];


                    $sql = "UPDATE user SET ore = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$ore, $user['id']]);

                    $sql = "UPDATE user_item SET quantity = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$user_item['quantity'] - $quantity, $user_item_id]);

                    $sql = "INSERT market_place(user_sell_id, item_id, quantity, ton_value) VALUES (?,?,?,?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$user['id'], $user_item['item_id'], $quantity, $ton_value]);

                    echo json_encode(['result' => 200]);
                }
            } else if ($type == 'get_sell_market') {
                $sql = "SELECT item.id, item.name, market_place.quantity, market_place.ton_value, market_place.id market_place_id  FROM market_place JOIN item ON item.id = market_place.item_id WHERE user_sell_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user['id']]);
                $items = $stmt->fetchAll();

                echo json_encode($items);
            } else if ($type == 'remove_market_place') {
                $market_place_id = $data['market_place_id'];
                $sql = "SELECT * FROM market_place WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$market_place_id]);
                $market_place = $stmt->fetch();

                $sql = "SELECT * FROM user_item WHERE item_id=? AND user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$market_place['item_id'], $market_place['user_sell_id']]);
                $user_item = $stmt->fetch();

                $sql = "UPDATE user_item SET quantity = ? WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$user_item['quantity'] + $market_place['quantity'], $user_item['id']]);

                $sql = "DELETE FROM market_place WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$market_place_id]);
            } else if ($type == 'get_buy_market') {
                if (isset($data['search'])) {
                    $sql = "SELECT item.id, item.name, market_place.quantity, market_place.ton_value, market_place.id market_place_id  FROM market_place JOIN item ON item.id = market_place.item_id WHERE user_sell_id<>? AND item.name LIKE ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$user['id'], '%' . $data['search'] . '%']);
                    $items = $stmt->fetchAll();

                } else {
                    $sql = "SELECT item.id, item.name, market_place.quantity, market_place.ton_value, market_place.id market_place_id  FROM market_place JOIN item ON item.id = market_place.item_id WHERE user_sell_id<>?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$user['id']]);
                    $items = $stmt->fetchAll();
                }

                echo json_encode($items);
            } else if ($type == 'buy_market_get_wallet') {
                $market_place_id = $data['market_place_id'];
                $sql = "SELECT * FROM market_place JOIN user ON user.id = market_place.user_sell_id WHERE market_place.id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$market_place_id]);
                $user = $stmt->fetch();

                echo json_encode($user);
            } else if ($type == 'check_transaction_market') {
                $market_place_id = $data['market_place_id'];
                $sql = "SELECT * FROM market_place JOIN user ON user.id = market_place.user_sell_id WHERE market_place.id=?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$market_place_id]);
                $market_place = $stmt->fetch();

                $url = "https://tonapi.io/v2/blockchain/accounts/UQAjvkriPSbfOkhDOTMGvWX6UmOqvT9n27I6Mm1wpr5JQTrH/transactions";
                $url = 'transaction.json';
                $transactions = file_get_contents($url);

                $transactions = json_decode($transactions);
                $transactions = $transactions->transactions;

                $value = $market_place['ton_value'] * 0.1;
                $address = $data['address'];

                // echo $value;
                // echo $address;
                $transaction_checks = [];
                foreach($transactions as $transaction) {
                  $source_address = $transaction->in_msg->source->address;
                  $tran_value = $transaction->in_msg->value;
                  if ($transaction->success && $address == $source_address && $value == $tran_value) {
                    $transaction_checks[] = $transaction->hash;
                  }
                }

                if (empty($transaction_checks)) {
                  echo json_encode(['msg' => 'Không tìm thấy transaction!']); exit;
                } else {
                  foreach($transaction_checks as $hash) {
                    $sql = "SELECT * FROM transaction WHERE hash = ? AND value = ? AND source_address = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$hash, $value, $address]);

                    $transaction = $stmt->fetch();

                    if (!$transaction) {
                        $sql = "INSERT transaction(hash, source_address, value) VALUES (?,?,?)";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$hash, $address, $value]);

                        $sql = "SELECT * FROM user_item WHERE item_id =? AND user_id = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$market_place['item_id'], $user['id']]);
                        $user_item = $stmt->fetch();

                        if(!$user_item) {
                            $sql = "INSERT user_item(user_id, item_id, quantity) VALUES (?,?,?) ";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$user['id'], $market_place['item_id'], $market_place['quantity']]);
                        } else {
                            $sql = "UPDATE user_item SET quantity = ? WHERE user_id = ? AND item_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute([$user_item['quantity'] + $market_place['quantity'], $user['id'], $market_place['item_id']]);
                        }

                        $sql = "DELETE FROM market_place WHERE id=?";
                        $stmt = $conn->prepare($sql);
                        $stmt->execute([$market_place_id]);

                        echo json_encode(["result" => 200]);
                        exit;
                    }
                  }
                }

                echo json_encode(['msg' => 'Không tìm thấy transaction!']);
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
