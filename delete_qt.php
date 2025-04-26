<?php
session_start();
header('Content-Type: application/json');
date_default_timezone_set('Asia/Seoul');

if (!isset($_SESSION['username']) || $_SESSION['username'] !== "admin") {
    echo json_encode(["success" => false, "message" => "권한이 없습니다."]);
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
    $qt_id = $_POST['id'];

    $sql = "DELETE FROM user_qt WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $qt_id);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => "삭제 실패"]);
    }

    $stmt->close();
}
$conn->close();
?>
