<?php
session_start();
date_default_timezone_set('Asia/Seoul');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
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

// 사용자 ID 확인
if (!isset($_GET['id'])) {
    die("잘못된 접근입니다.");
}

$user_id = $_GET['id'];

// 사용자 정보 조회
$sql = "SELECT username, name, grade, class FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("사용자를 찾을 수 없습니다.");
}

// 학년과 반 선택 데이터
$grades = [
    "1학년" => ["1반", "2반", "3반"],
    "2학년" => ["1반", "2반"],
    "3학년" => ["1반", "2반", "3반", "4반"]
];

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>사용자 정보 수정</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <div class="edit-container">
        <h2>사용자 정보 수정</h2>
        <form action="update_user.php" method="POST">
            <input type="hidden" name="id" value="<?= $user_id ?>">

            <label>아이디:</label>
            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" readonly>

            <label>이름:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label>학년:</label>
            <select name="grade" id="grade-select" required>
                <?php foreach ($grades as $grade => $classes) : ?>
                    <option value="<?= $grade ?>" <?= ($user['grade'] == $grade) ? 'selected' : '' ?>><?= $grade ?></option>
                <?php endforeach; ?>
            </select>

            <label>반:</label>
            <select name="class" id="class-select" required>
                <?php foreach ($grades[$user['grade']] as $class) : ?>
                    <option value="<?= $class ?>" <?= ($user['class'] == $class) ? 'selected' : '' ?>><?= $class ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit">정보 수정</button>
        </form>


        <a href="admin.php" class="back-button">돌아가기</a>
    </div>

    <script>
        // 학년 선택 시 반 자동 변경
        document.getElementById('grade-select').addEventListener('change', function () {
            const grade = this.value;
            const classSelect = document.getElementById('class-select');

            const classOptions = {
                "1학년": ["1반", "2반", "3반"],
                "2학년": ["1반", "2반"],
                "3학년": ["1반", "2반", "3반", "4반"]
            };

            classSelect.innerHTML = "";
            classOptions[grade].forEach(cls => {
                let option = document.createElement("option");
                option.value = cls;
                option.textContent = cls;
                classSelect.appendChild(option);
            });
        });
    </script>
</body>
</html>

<style>

/* 전체 페이지 스타일 */
body {
    font-family: 'Noto Sans KR', sans-serif;
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

/* 수정 페이지 컨테이너 */
.edit-container {
    width: 100%;
    max-width: 400px;
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

/* 제목 스타일 */
.edit-container h2 {
    color: #ff5733;
    margin-bottom: 20px;
    font-size: 22px;
}

/* 폼 스타일 */
form {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}

/* 라벨 스타일 */
form label {
    font-size: 14px;
    font-weight: bold;
    margin: 5px 0;
    text-align: left;
    width: 100%;
}

/* 입력 필드 및 드롭다운 스타일 */
form input,
form select {
    width: calc(100% - 20px); /* 동일한 너비로 정렬 */
    padding: 12px;
    margin-bottom: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    box-sizing: border-box; /* 패딩 포함 */
}

/* 읽기 전용 입력 필드 스타일 */
form input[readonly] {
    background-color: #f0f0f0;
    cursor: not-allowed;
}

/* 버튼 컨테이너 스타일 */
.button-container {
    display: flex;
    flex-direction: column;
    gap: 16px; /* 버튼 사이 간격 */
}

/* 정보 수정 버튼 */
button {
    background: #ff5733;
    color: white;
    border: none;
    width: 100%;
    padding: 12px;
    text-align: center;
    font-size: 15px;
    font-weight: bold;
    border-radius: 5px;
    cursor: pointer;
    box-sizing: border-box;
}

button:hover {
    background: #e04d2c;
}

/* 돌아가기 버튼 */
.back-button {
    background: #6c757d;
    color: white;
    border: none;
    width: 100%;
    padding: 12px;
    text-align: center;
    font-size: 15px;
    font-weight: bold;
    border-radius: 5px;
    text-decoration: none;
    box-sizing: border-box;
    margin-top: 10px; /* 버튼 간격 띄우기 */
}

.back-button:hover {
    background: #5a6268;
}


/* 돌아가기 버튼 */
/* 돌아가기 버튼 (기존 스타일 유지) */
.back-button {
    display: block;
    width: 100%; /* 정보 수정 버튼과 동일한 너비 */
    padding: 12px;
    text-align: center;
    font-size: 15px;
    font-weight: bold;
    border-radius: 5px;
    text-decoration: none;
    background: #6c757d; /* 기존 배경색 유지 */
    color: white;
    border: none;
}

.back-button:hover {
    background: #5a6268; /* 호버 시 색상 변경 */
}


/* 반응형 디자인 */
@media screen and (max-width: 480px) {
    .edit-container {
        width: 90%;
    }
}



</style>