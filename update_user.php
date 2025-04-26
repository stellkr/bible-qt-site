<?php
session_start();
date_default_timezone_set('Asia/Seoul');
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') {
    die("권한이 없습니다.");
}

// MySQL 연결
$servername = "localhost";
$username_db = "root";
$password = "";
$dbname = "q";

$conn = new mysqli($servername, $username_db, $password, $dbname);
if ($conn->connect_error) {
    die("데이터베이스 연결 실패: " . $conn->connect_error);
}

// 입력 데이터 확인
if (!isset($_POST['id'], $_POST['name'], $_POST['grade'], $_POST['class'])) {
    die("모든 정보를 입력해주세요.");
}

$user_id = trim($_POST['id']);
$name = trim($_POST['name']);
$grade = trim($_POST['grade']);
$class = trim($_POST['class']);
$password = trim($_POST['password']);


// 사용자 정보 업데이트 (비밀번호 확인 제거)
$sql_update = "UPDATE users SET name = ?, grade = ?, class = ? WHERE id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("sssi", $name, $grade, $class, $user_id);
$stmt_update->execute();

if ($stmt_update->execute()) {
    echo "<script>alert('사용자 정보가 업데이트되었습니다.'); window.location.href='admin.php';</script>";
} else {
    echo "<script>alert('업데이트 실패. 다시 시도해주세요.'); window.history.back();</script>";
}

$stmt_update->close();
$conn->close();
?>
