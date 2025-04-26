<?php
session_start();
date_default_timezone_set('Asia/Seoul');
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "q";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "데이터베이스 연결 실패"]));
}

// ✅ 관리자 계정 확인 (관리자만 실행 가능)
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(["success" => false, "message" => "권한이 없습니다."]);
    exit();
}

// ✅ 입력값 받기
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$name = isset($_POST['name']) ? trim($_POST['name']) : '';

if (empty($username) || empty($password) || empty($name)) {
    echo json_encode(["success" => false, "message" => "모든 필드를 입력해주세요."]);
    exit();
}

// ✅ 아이디 중복 확인
$stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "이미 존재하는 아이디입니다."]);
    $stmt->close();
    exit();
}
$stmt->close();

// ✅ 관리자 등록 (is_admin = 1)
$stmt = $conn->prepare("INSERT INTO users (username, password, name, is_admin) VALUES (?, ?, ?, 1)");
$stmt->bind_param("sss", $username, $password, $name);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "관리자 계정이 성공적으로 등록되었습니다."]);
} else {
    echo json_encode(["success" => false, "message" => "관리자 등록 실패. 다시 시도해주세요."]);
}

$stmt->close();
$conn->close();
?>
