<?php
date_default_timezone_set('Asia/Seoul');
$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "q";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "데이터베이스 연결 실패"]));
}

// ✅ 요청된 데이터 받기
$book = isset($_POST['book']) ? intval($_POST['book']) : 0;
$chapter = isset($_POST['chapter']) ? intval($_POST['chapter']) : 0;
$paragraph = isset($_POST['paragraph']) ? intval($_POST['paragraph']) : 0;

if ($book === 0 || $chapter === 0 || $paragraph === 0) {
    die(json_encode(["success" => false, "message" => "잘못된 요청입니다."]));
}

// ✅ 해당 성경 구절 찾기
$sql = "SELECT sentence FROM bible2 WHERE book = ? AND chapter = ? AND paragraph = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $book, $chapter, $paragraph);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(["success" => true, "sentence" => $row['sentence']]);
} else {
    echo json_encode(["success" => false, "message" => "해당 구절을 찾을 수 없습니다."]);
}

$stmt->close();
$conn->close();
?>
