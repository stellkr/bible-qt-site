<?php
session_start();
date_default_timezone_set('Asia/Seoul');
if (!isset($_SESSION['user_id'])) {
    header("Location: login/login.html");
    exit();
}

$servername = "localhost";
$username_db = "root";
$password_db = "";
$dbname = "q";

$conn = new mysqli($servername, $username_db, $password_db, $dbname);
if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
}

// ✅ 성경 번호 → 성경 이름 변환 배열
$books = [
    1 => "창세기", 2 => "출애굽기", 3 => "레위기", 4 => "민수기", 5 => "신명기",
    6 => "여호수아", 7 => "사사기", 8 => "룻기", 9 => "사무엘상", 10 => "사무엘하",
    11 => "열왕기상", 12 => "열왕기하", 13 => "역대상", 14 => "역대하", 15 => "에스라",
    16 => "느헤미야", 17 => "에스더", 18 => "욥기", 19 => "시편", 20 => "잠언",
    21 => "전도서", 22 => "아가", 23 => "이사야", 24 => "예레미야", 25 => "예레미야애가",
    26 => "에스겔", 27 => "다니엘", 28 => "호세아", 29 => "요엘", 30 => "아모스",
    31 => "오바댜", 32 => "요나", 33 => "미가", 34 => "나훔", 35 => "하박국",
    36 => "스바냐", 37 => "학개", 38 => "스가랴", 39 => "말라기", 40 => "마태복음",
    41 => "마가복음", 42 => "누가복음", 43 => "요한복음", 44 => "사도행전", 45 => "로마서",
    46 => "고린도전서", 47 => "고린도후서", 48 => "갈라디아서", 49 => "에베소서",
    50 => "빌립보서", 51 => "골로새서", 52 => "데살로니가전서", 53 => "데살로니가후서",
    54 => "디모데전서", 55 => "디모데후서", 56 => "디도서", 57 => "빌레몬서",
    58 => "히브리서", 59 => "야고보서", 60 => "베드로전서", 61 => "베드로후서",
    62 => "요한일서", 63 => "요한이서", 64 => "요한삼서", 65 => "유다서", 66 => "요한계시록"
];

// ✅ 현재 로그인한 사용자 정보 가져오기
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, name, grade, class FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ✅ 사용자가 작성한 모든 QT 가져오기 (최신 순)
$sql_qts = "SELECT qt_date, content FROM user_qt WHERE user_id = ? ORDER BY qt_date DESC";
$stmt_qts = $conn->prepare($sql_qts);
$stmt_qts->bind_param("i", $user_id);
$stmt_qts->execute();
$result_qts = $stmt_qts->get_result();

$qts = [];
while ($row = $result_qts->fetch_assoc()) {
    $qts[$row['qt_date']] = $row['content'];
}
$stmt_qts->close();

// ✅ 오늘의 말씀 가져오기
// ✅ 오늘의 말씀 가져오기 (번호 → 성경 이름 변환)
$sql_verses = "SELECT date, book, chapter, paragraph, sentence FROM daily_verse ORDER BY date DESC";
$result_verses = $conn->query($sql_verses);

$verses = [];
while ($row = $result_verses->fetch_assoc()) {
    $book_name = isset($books[$row['book']]) ? $books[$row['book']] : "알 수 없음"; // 성경 이름 변환
    $verses[$row['date']] = [
        "book" => $book_name,
        "chapter" => $row['chapter'],
        "paragraph" => $row['paragraph'],
        "sentence" => $row['sentence']
    ];
}


$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>내정보</title>
    <link href="imge/logo.png" rel="shortcut icon" type="imge/x-icon"> <!--서버 아이콘 변경-->
    <!-- <link rel="stylesheet" href="style.css"> -->
</head>
<body>
    <div class="info-container">
        <h2><?= htmlspecialchars($user['name'])?>님의 정보</h2>
        <form id="updateForm">
            <label>이름</label>
            <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>

            <label>아이디</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" readonly>

            <label>학년</label>
            <select name="grade" id="grade" required>
                <option value="1학년" <?= ($user['grade'] == "1학년") ? 'selected' : '' ?>>1학년</option>
                <option value="2학년" <?= ($user['grade'] == "2학년") ? 'selected' : '' ?>>2학년</option>
                <option value="3학년" <?= ($user['grade'] == "3학년") ? 'selected' : '' ?>>3학년</option>
            </select>

            <label>반</label>
            <select name="class" id="class" required></select>

            <label>현재 비밀번호 입력</label>
            <input type="password" name="current_password" required>

            <label>새 비밀번호 (변경 안할 경우 입력하지 마세요)</label>
            <input type="password" name="new_password">

            <button type="submit">정보 수정</button>
        </form>

        <!-- ✅ QT 조회 버튼 -->
        <button id="viewQTBtn">오늘의 QT 조회</button>

 <!-- ✅ QT 모달 창 -->
        <div id="qtModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>📖 QT 기록</h3>
                <select id="qtDateSelect">
                    <?php foreach ($qts as $date => $content) : ?>
                        <option value="<?= htmlspecialchars($date) ?>"><?= htmlspecialchars($date) ?></option>
                    <?php endforeach; ?>
                </select>
                <h4 id="qtVerse"></h4>
                <p id="qtSentence"></p>
                <h3>✏️ QT 내용</h3>
                <p id="qtText"></p>
            </div>
        </div>

        <a href="index.php" class="back-button">메인 화면</a>
    </div>

    <style>
body {
    font-family: 'Noto Sans KR', sans-serif;
    font-weight: 700; /* Bold 설정 */
    background-color: #f4f4f4;
    
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}


.info-container {
    max-width: 400px;
    width: 100%;
    padding: 20px;
    background-color: white;
    border-radius: 8px;
    /* box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); */
    color: #000000;
    box-shadow: 0 0 2px 1px;
}

.info-container input, 
.info-container select {
    width: calc(100% - 2px); /* 동일한 너비 적용 */
    padding: 8px; /* 내부 여백 조정 */
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 15px;
    box-sizing: border-box; /* 패딩 포함하여 크기 조정 */
    display: block;
}


.info-container h2 {
    font-size: 22px;
    margin-bottom: 12px;
    text-align: center;
}

.info-container label {
    font-weight: bold;
}

.info-container input, .info-container select {
    width: 100%;
    padding: 8px; /* 입력 필드 패딩을 줄여서 더 콤팩트하게 */
    margin-bottom: 8px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 15px; /* 글씨 크기 소폭 줄임 */
    margin-top: 5px;
    margin-bottom: 20px;
}

.info-container button {
    width: 100%;
    padding: 10px;
    font-size: 15px;
    background: #FF5733;
    color: white;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    margin-top: 10px;
    font-weight: bold;
}

.info-container button:hover {
    background: #E04D2C;
}

.back-button {
    display: block;
    margin-top: 12px;
    padding: 10px;
    font-size: 15px;
    background: #333;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    text-align: center;
}

.back-button:hover {
    background: #555;
}

/* ✅ 모달 창 스타일 (양옆 간격 줄임) */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.4);
}

.modal-content {
    background-color: white;
    margin: 10% auto; /* 모달이 화면 중앙에 적절히 위치하도록 */
    padding: 15px;
    width: 90%; /* 양옆 간격 줄여서 더 넓게 보이도록 */
    max-width: 450px; /* 기존보다 더 좁게 조정 */
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

.close {
    color: red;
    float: right;
    font-size: 20px;
    cursor: pointer;
}

.close:hover {
    color: darkred;
}

    </style>

    <script>

        document.addEventListener("DOMContentLoaded", function () {
                const gradeSelect = document.getElementById("grade");
                const classSelect = document.getElementById("class");
                const userClass = "<?= $user['class'] ?>"; // 현재 사용자의 반 정보

                const classOptions = {
                    "1학년": ["1반", "2반", "3반"],
                    "2학년": ["1반", "2반"],
                    "3학년": ["1반", "2반", "3반", "4반"]
                };

                function updateClassOptions(selectedGrade) {
                    classSelect.innerHTML = "";
                    classOptions[selectedGrade].forEach(cls => {
                        const option = document.createElement("option");
                        option.value = cls;
                        option.textContent = cls;
                        if (cls === userClass) {
                            option.selected = true;
                        }
                        classSelect.appendChild(option);
                    });
                }

                // ✅ **초기 반 옵션 설정**
                updateClassOptions(gradeSelect.value);

                // ✅ **학년 선택 시 반 자동 변경**
                gradeSelect.addEventListener("change", function () {
                    updateClassOptions(this.value);
                });
            });

        document.addEventListener("DOMContentLoaded", function () {
            const qtModal = document.getElementById("qtModal");
            const qtText = document.getElementById("qtText");
            const qtDateSelect = document.getElementById("qtDateSelect");
            const qtVerse = document.getElementById("qtVerse");
            const qtSentence = document.getElementById("qtSentence");
            const closeBtn = document.querySelector(".close");

            const qtData = <?= json_encode($qts) ?>;
            const verseData = <?= json_encode($verses) ?>;

            document.getElementById("viewQTBtn").addEventListener("click", function () {
                qtModal.style.display = "block";
                qtDateSelect.dispatchEvent(new Event("change"));
            });

            qtDateSelect.addEventListener("change", function () {
                const selectedDate = this.value;
                qtText.innerText = qtData[selectedDate] || "해당 날짜의 QT가 없습니다.";

                if (verseData[selectedDate]) {
                    qtVerse.innerText = `${verseData[selectedDate].book} ${verseData[selectedDate].chapter}장 ${verseData[selectedDate].paragraph}절`;
                    qtSentence.innerText = `"${verseData[selectedDate].sentence}"`;
                } else {
                    qtVerse.innerText = "오늘의 말씀이 없습니다.";
                    qtSentence.innerText = "";
                }
            });

            closeBtn.addEventListener("click", function () {
                qtModal.style.display = "none";
            });
        });

    </script>
</body>
</html>
