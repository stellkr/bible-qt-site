<?php
session_start();
date_default_timezone_set('Asia/Seoul');
header('Content-Type: application/json');

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "q";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "데이터베이스 연결 실패"]));
}

// ✅ 관리자 확인
if (!isset($_SESSION['username']) || $_SESSION['username'] !== "admin") {
    die(json_encode(["success" => false, "message" => "권한이 없습니다."]));
}

// ✅ 요청 데이터 확인
$date = isset($_POST['date']) ? trim($_POST['date']) : "";
$book = isset($_POST['book']) ? intval($_POST['book']) : 0;
$chapter = isset($_POST['chapter']) ? intval($_POST['chapter']) : 0;
$paragraph = isset($_POST['paragraph']) ? intval($_POST['paragraph']) : 0;
$sentence = isset($_POST['sentence']) ? trim($_POST['sentence']) : "";

if (empty($date) || $book == 0 || $chapter == 0 || $paragraph == 0 || empty($sentence)) {
    die(json_encode(["success" => false, "message" => "모든 필드를 입력해주세요."]));
}

// ✅ 기존 데이터 확인
$sql_check = "SELECT * FROM daily_verse WHERE date = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $date);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

// ✅ 기존 데이터가 있으면 업데이트, 없으면 새로 추가
if ($result_check->num_rows > 0) {
    $sql_update = "UPDATE daily_verse SET book = ?, chapter = ?, paragraph = ?, sentence = ? WHERE date = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("iiiss", $book, $chapter, $paragraph, $sentence, $date);
    $stmt_update->execute();
} else {
    $sql_insert = "INSERT INTO daily_verse (date, book, chapter, paragraph, sentence) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("siiis", $date, $book, $chapter, $paragraph, $sentence);
    $stmt_insert->execute();
}

echo json_encode(["success" => true, "message" => "오늘의 말씀이 업데이트되었습니다."]);
?>
