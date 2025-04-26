<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login/login.html");
    exit();
}

// MySQL 연결 설정
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "q";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
}

// 📌 오늘 날짜 및 요일 설정
$today_date = date("Ymd");
$weekdays = ["일", "월", "화", "수", "목", "금", "토"];
$day_index = date("w"); // 0 (일) ~ 6 (토)
$today_display = date("Y년 m월 d일") . " (" . $weekdays[$day_index] . ")";

// 📌 오늘의 성경말씀 가져오기
$sql_qt = "SELECT book, chapter, paragraph, sentence FROM daily_verse WHERE date = '$today_date'";
$result_qt = $conn->query($sql_qt);

if ($result_qt->num_rows > 0) {
    $random_verse = $result_qt->fetch_assoc();
} else {
    // 📌 없으면 랜덤으로 선택 후 DB에 저장
    $sql_random = "SELECT book, chapter, paragraph, sentence FROM bible2 ORDER BY RAND() LIMIT 1";
    $result_random = $conn->query($sql_random);
    $random_verse = $result_random->fetch_assoc();

    $sql_insert = "INSERT INTO daily_verse (date, book, chapter, paragraph, sentence)
                   VALUES ('$today_date', '{$random_verse['book']}', '{$random_verse['chapter']}', 
                           '{$random_verse['paragraph']}', '{$random_verse['sentence']}')";
    $conn->query($sql_insert);
}

// 📌 숫자로 저장된 book 값을 실제 성경 이름으로 변환
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

$book_name = isset($books[$random_verse['book']]) ? $books[$random_verse['book']] : $random_verse['book'];

$username = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QT 작성</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .qt-container {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .qt-container h2 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #FF5733;
        }

        .qt-container textarea {
            width: 100%;
            height: 150px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: none;
            font-size: 16px;
        }

        .qt-container button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background: #FF5733;
            color: white;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }

        .qt-container button:hover {
            background: #E04D2C;
        }

        .back-button {
            display: block;
            margin-top: 15px;
            padding: 10px;
            font-size: 16px;
            background: #333;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }

        .back-button:hover {
            background: #555;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelector("form").addEventListener("submit", function(event) {
                event.preventDefault();
                
                const qtContent = document.querySelector("textarea[name='qt_content']").value.trim();
                if (qtContent === "") {
                    alert("QT 내용을 입력해주세요.");
                    return;
                }

                fetch("save_qt.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: new URLSearchParams({ qt_content: qtContent })
                })
                .then(response => response.json()) // JSON 응답 파싱
                .then(data => {
                    console.log("서버 응답:", data); // 응답을 콘솔에서 확인

                    if (data.success) {
                        alert(data.message); // 성공 메시지 표시
                        setTimeout(() => {
                            window.location.href = "index.php"; // 0.5초 후 메인화면으로 이동
                        }, 500);
                    } else {
                        alert(data.message); // 오류 메시지 표시
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("서버와의 통신 중 오류가 발생했습니다.");
                });
            });
        });
    </script>



</head>
<body>

<div class="qt-container">
    <h2>📖 오늘의 말씀 QT (<?= htmlspecialchars($username) ?>님)</h2>

    <!-- 오늘의 말씀 표시 -->
    <section class="qt-section">
        <h3>📅 <?= $today_display ?></h3>
        <h3><?= $book_name ?> <?= $random_verse['chapter'] ?>장 <?= $random_verse['paragraph'] ?>절</h3>
        <p class="verse">"<?= $random_verse['sentence'] ?>"</p>
    </section>

    <h3>✏️ QT 작성</h3>
    <form action="save_qt.php" method="POST">
        <textarea name="qt_content" placeholder="오늘의 말씀에 대한 QT를 작성하세요"></textarea>
        <button type="submit">QT 저장</button>
    </form>

    <a href="index.php" class="back-button">🏠 메인 화면으로</a>
</div>

</body>
</html>
