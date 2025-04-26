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
    die(json_encode(["message" => "데이터베이스 연결 실패"]));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $grade = isset($_POST['grade']) ? trim($_POST['grade']) : '';
    $class = isset($_POST['class']) ? trim($_POST['class']) : '';

    // 입력값 검증
    if (empty($username) || empty($password) || empty($name) || empty($grade) || empty($class)) {
        echo json_encode(["message" => "모든 필드를 입력해주세요."]);
        exit();
    }

    if (strlen($username) < 6 || preg_match('/[\x{3131}-\x{D7A3}]/u', $username)) {
        echo json_encode(["message" => "아이디는 6자 이상이며 한글을 포함할 수 없습니다."]);
        exit();
    }

    if (strlen($password) < 6) {
        echo json_encode(["message" => "비밀번호는 6자 이상이어야 합니다."]);
        exit();
    }

    // 아이디 중복 확인
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["message" => "이미 사용 중인 아이디입니다."]);
        $stmt->close();
        exit();
    }
    $stmt->close();

    // 📌 비밀번호를 그대로 저장 (보안상 추천하지 않음)
    $sql = "INSERT INTO users (username, password, name, grade, class) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $password, $name, $grade, $class);

    if ($stmt->execute()) {
        echo json_encode(["message" => "회원가입이 완료되었습니다"]);
    } else {
        echo json_encode(["message" => "회원가입 실패, 다시 시도해주세요"]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["message" => "잘못된 요청입니다."]);
}
?>
