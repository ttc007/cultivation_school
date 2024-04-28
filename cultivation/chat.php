<?php
header("Location: http://localhost/game.php");

// Đảm bảo không có mã HTML hoặc dữ liệu nào được xuất ra trình duyệt sau lệnh chuyển hướng.
exit;
$input=file_get_contents("php://input");
$update=json_decode($input);
$message=$update->message;
$text=$message->text;

if ($text == '/play') {
    file_get_contents("https://api.telegram.org/bot6900283007:AAFtf_JQy_fFoNv2Kx6PYzufnWnFWB3c0q0/sendMessage?chat_id=7175978402&text=Web apps:
    Cultivation School (cultivation_school_app (https://t.me/cultivation_school_bot/cultivation_school_app))");
} else {
   file_get_contents("https://api.telegram.org/bot6900283007:AAFtf_JQy_fFoNv2Kx6PYzufnWnFWB3c0q0/sendMessage?chat_id=7175978402&text=Chơi game vui vẻ!"); 
}


?>