<?php
session_start();
date_default_timezone_set('Asia/Seoul');
header('Content-Type: application/json');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "q";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "데이터베이스 연결 실패"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['id'];
    $current_status = $_POST['status'];

    $new_status = ($current_status == 1) ? 0 : 1;

    $sql = "UPDATE users SET is_banned = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $new_status, $user_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "상태 변경 실패"]);
    }

    $stmt->close();
}
$conn->close();
?>
