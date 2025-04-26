<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "q";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "데이터베이스 연결 실패"]));
}

// ✅ 관리자 계정만 변경 가능
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(["success" => false, "message" => "권한이 없습니다."]);
    exit();
}

// ✅ 요청 데이터 받기
$user_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$status = isset($_POST['status']) ? intval($_POST['status']) : 0;

if ($user_id === 0) {
    echo json_encode(["success" => false, "message" => "유효하지 않은 사용자 ID입니다."]);
    exit();
}

// ✅ 현재 권한 상태 확인
$sql_check = "SELECT is_admin FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_check);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(["success" => false, "message" => "사용자를 찾을 수 없습니다."]);
    exit();
}

// ✅ 새 상태 설정 (관리자 추가 or 해제)
$new_status = ($user['is_admin'] == 1) ? 0 : 1;
$sql_update = "UPDATE users SET is_admin = ? WHERE id = ?";
$stmt = $conn->prepare($sql_update);
$stmt->bind_param("ii", $new_status, $user_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => $new_status ? "관리자로 등록되었습니다." : "관리자 권한이 해제되었습니다.",
        "new_status" => $new_status
    ]);
} else {
    echo json_encode(["success" => false, "message" => "관리자 권한 변경 실패."]);
}

$stmt->close();
$conn->close();
?>
