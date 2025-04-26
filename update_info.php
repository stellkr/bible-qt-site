<?php
session_start();
date_default_timezone_set('Asia/Seoul');
header("Content-Type: application/json");

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "로그인이 필요합니다!"]);
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

// **✅ 입력 값 검증**
$user_id = $_SESSION['user_id'];
$name = trim($_POST['name']);
$grade = trim($_POST['grade']);
$class = trim($_POST['class']);
$current_password = trim($_POST['current_password']);
$new_password = trim($_POST['new_password']);

if (empty($name) || empty($grade) || empty($class) || empty($current_password)) {
    echo json_encode(["success" => false, "message" => "모든 필드를 입력해주세요."]);
    exit();
}

// **✅ 현재 비밀번호 검증**
$sql_check = "SELECT password FROM users WHERE id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
$user = $result_check->fetch_assoc();

if (!$user || $user['password'] !== $current_password) {
    echo json_encode(["success" => false, "message" => "현재 비밀번호가 일치하지 않습니다."]);
    exit();
}

// **✅ 정보 업데이트 (비밀번호 변경 여부 확인)**
if (!empty($new_password)) {
    $sql_update = "UPDATE users SET name = ?, grade = ?, class = ?, password = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssssi", $name, $grade, $class, $new_password, $user_id);
} else {
    $sql_update = "UPDATE users SET name = ?, grade = ?, class = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssi", $name, $grade, $class, $user_id);
}

if ($stmt_update->execute()) {
    echo json_encode(["success" => true, "message" => "정보가 성공적으로 수정되었습니다!"]);
} else {
    echo json_encode(["success" => false, "message" => "정보 수정 실패"]);
}

$conn->close();
exit();
?>
