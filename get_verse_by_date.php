<?php
date_default_timezone_set('Asia/Seoul');
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "q";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "DB 연결 실패"]);
    exit();
}

$date = isset($_GET['date']) ? $_GET['date'] : "";
if (!$date) {
    echo json_encode(["success" => false, "message" => "날짜가 지정되지 않음"]);
    exit();
}

// 📌 지정된 날짜의 오늘의 말씀 조회
$sql = "SELECT book, chapter, paragraph, sentence FROM daily_verse WHERE date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $date);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode([
        "success" => true,
        "book" => $row['book'],
        "chapter" => $row['chapter'],
        "paragraph" => $row['paragraph'],
        "sentence" => $row['sentence']
    ]);
} else {
    echo json_encode(["success" => false, "message" => "해당 날짜의 말씀이 없음"]);
}

$stmt->close();
$conn->close();
?>
