<?php
session_start();
date_default_timezone_set('Asia/Seoul');
header("Content-Type: application/json; charset=UTF-8"); // JSON 형식 설정

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "로그인이 필요합니다!", "redirect" => "login/login.html"]);
    exit();
}

// MySQL 연결
$servername = "localhost";
$username_db = "root";
$password = "";
$dbname = "q";

$conn = new mysqli($servername, $username_db, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "데이터베이스 연결 실패"]);
    exit();
}

// 입력 값 확인
if (!isset($_POST['qt_content']) || trim($_POST['qt_content']) === "") {
    echo json_encode(["success" => false, "message" => "QT 내용을 입력해주세요."]);
    exit();
}

$user_id = $_SESSION['user_id'];
$today_date = date("Y-m-d");
$qt_content = trim($_POST['qt_content']);

// 기존 QT 확인
$sql_check = "SELECT id FROM user_qt WHERE user_id = ? AND qt_date = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("is", $user_id, $today_date);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

// 기존 QT가 있다면 업데이트, 없으면 새로 삽입
if ($result_check->num_rows > 0) {
    $sql_update = "UPDATE user_qt SET content = ? WHERE user_id = ? AND qt_date = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sis", $qt_content, $user_id, $today_date);
    $stmt_update->execute();
    $stmt_update->close();
} else {
    $sql_insert = "INSERT INTO user_qt (user_id, qt_date, content) VALUES (?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iss", $user_id, $today_date, $qt_content);
    $stmt_insert->execute();
    $stmt_insert->close();
}

$conn->close();

// ✅ JSON 응답 반환 (성공)
echo json_encode(["success" => true, "message" => "QT 저장이 완료되었습니다!", "redirect" => "index.php"]);
exit();
?>
