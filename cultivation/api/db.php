<?php
// Thông tin kết nối cơ sở dữ liệu
$servername = "localhost"; // Tên máy chủ
$username = "root"; // Tên người dùng cơ sở dữ liệu
$password = ""; // Mật khẩu cơ sở dữ liệu
$dbname = "cultivation"; // Tên cơ sở dữ liệu
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
