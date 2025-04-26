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

    if (empty($username) || empty($password)) {
        echo json_encode(["message" => "아이디와 비밀번호를 입력해주세요."]);
        exit();
    }

    // 🔹 사용자 정보 조회 (비밀번호 포함)
    $stmt = $conn->prepare("SELECT id, username, password, name, is_admin, is_banned FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
    
        // 🔹 차단된 계정인지 확인
        if ($user['is_banned'] == 1) {
            echo json_encode(["message" => "이 계정은 관리자에 의해 차단되었습니다. 관리자에게 문의하시기 바랍니다."]);
            exit();
        }
    
        // 🔹 비밀번호 검증 (해시 없이 단순 비교)
        if ($password === $user['password']) {     
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['is_admin'] = $user['is_admin']; // ✅ 관리자 여부 저장
    
            // ✅ 관리자일 경우 (관리자) 추가
            $userType = ($user['is_admin'] == 1) ? "(관리자)" : "";

            echo json_encode([
                "message" => "로그인 성공",
                "name" => $user['name'],
                "user_type" => $userType // ✅ 여기서 정상적으로 전송
            ]);
        } else {
            echo json_encode(["message" => "비밀번호가 올바르지 않습니다."]);
        }    
    } else {
        echo json_encode(["message" => "존재하지 않는 아이디입니다."]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["message" => "잘못된 요청입니다."]);
}
?>
