<?php
session_start();
date_default_timezone_set('Asia/Seoul');
// MySQL 연결 설정
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "q";

$conn = new mysqli($servername, $username, $password, $dbname);

// 연결 확인
if ($conn->connect_error) {
    die("연결 실패: " . $conn->connect_error);
}

// 📌 오늘 날짜 (YYYYMMDD 형식으로 저장)
$today_date = date("Ymd");

// 📌 성경 권 이름 및 장 개수 배열
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

$books2 = [
    "창세기" => 50, "출애굽기" => 40, "레위기" => 27, "민수기" => 36, "신명기" => 34,
    "여호수아" => 24, "사사기" => 21, "룻기" => 4, "사무엘상" => 31, "사무엘하" => 24,
    "열왕기상" => 22, "열왕기하" => 25, "역대상" => 29, "역대하" => 36, "에스라" => 10,
    "느헤미야" => 13, "에스더" => 10, "욥기" => 42, "시편" => 150, "잠언" => 31,
    "전도서" => 12, "아가" => 8, "이사야" => 66, "예레미야" => 52, "예레미야애가" => 5,
    "에스겔" => 48, "다니엘" => 12, "호세아" => 14, "요엘" => 3, "아모스" => 9,
    "오바댜" => 1, "요나" => 4, "미가" => 7, "나훔" => 3, "하박국" => 3, "스바냐" => 3,
    "학개" => 2, "스가랴" => 14, "말라기" => 4, "마태복음" => 28, "마가복음" => 16,
    "누가복음" => 24, "요한복음" => 21, "사도행전" => 28, "로마서" => 16,
    "고린도전서" => 16, "고린도후서" => 13, "갈라디아서" => 6, "에베소서" => 6,
    "빌립보서" => 4, "골로새서" => 4, "데살로니가전서" => 5, "데살로니가후서" => 3,
    "디모데전서" => 6, "디모데후서" => 4, "디도서" => 3, "빌레몬서" => 1,
    "히브리서" => 13, "야고보서" => 5, "베드로전서" => 5, "베드로후서" => 3,
    "요한일서" => 5, "요한이서" => 1, "요한삼서" => 1, "유다서" => 1, "요한계시록" => 22
];

// 📌 JavaScript에서 사용할 책-장 개수 매핑 데이터
$books_json = json_encode($books);
$books2_json = json_encode($books2, JSON_UNESCAPED_UNICODE);

// 📌 한글 요일 변환
$weekdays = ["일", "월", "화", "수", "목", "금", "토"];
$day_index = date("w"); // 0 (일) ~ 6 (토)
$today_display = date("Y년 m월 d일") . " (" . $weekdays[$day_index] . ")";

// 📌 오늘의 성경 QT 구절 확인 (DB에서 오늘 날짜에 해당하는 구절이 있는지 확인)
$sql_check = "SELECT book, chapter, paragraph, sentence FROM daily_verse WHERE date = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("s", $today_date);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

// 📌 오늘의 구절이 이미 저장되어 있다면 가져오기
if ($result_check->num_rows > 0) {
    $random_verse = $result_check->fetch_assoc();
} else {
    // 📌 없으면 랜덤으로 선택 후 DB에 저장
    $sql_random = "SELECT book, chapter, paragraph, sentence FROM bible2 ORDER BY RAND() LIMIT 1";
    $result_random = $conn->query($sql_random);
    $random_verse = $result_random->fetch_assoc();

    // 📌 오늘의 구절을 DB에 저장
    $sql_insert = "INSERT INTO daily_verse (date, book, chapter, paragraph, sentence)
                   VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("siiis", $today_date, $random_verse['book'], $random_verse['chapter'], 
                                      $random_verse['paragraph'], $random_verse['sentence']);
    $stmt_insert->execute();
}


// 📌 숫자로 저장된 book 값을 실제 성경 이름으로 변환
$book_name = isset($books[$random_verse['book']]) ? $books[$random_verse['book']] : $random_verse['book'];

// // 현재 PHP 타임존 확인
// echo "현재 PHP 타임존: " . date_default_timezone_get() . "<br>";
// echo "현재 PHP 시간: " . date("Y-m-d H:i:s") . "<br>";

// // MySQL 서버 시간 확인
// $result = $conn->query("SELECT NOW() as mysql_time");
// $row = $result->fetch_assoc();
// echo "현재 MySQL 시간: " . $row['mysql_time'] . "<br>";

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>와~우리교회 고등부</title>
    <link href="imge/logo.png" rel="shortcut icon" type="imge/x-icon"> <!--서버 아이콘 변경-->
    <!-- <link rel="stylesheet" href="styles.css"> -->
    <style>
        /* 전체 페이지 기본 스타일 */
        body {
            font-family: 'Noto Sans KR', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            width: 100%;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            /* box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); */
            color: #000000;
            box-shadow: 0 0 2px 1px;
        }

        h2 {
            margin: 30px auto;
            text-align: center;
        }

        h3 {
            margin: -10px auto;
            text-align: center;
        }

        .verse {
            text-align: center;
        }

        /* 헤더 스타일 */
        header {
            font-size: 24px;
            font-weight: bold;
            text-align: center;
            padding: 15px 0;
            background: #FF5733;
            color: white;
            border-radius: 10px;
            margin: 10px auto;
        }

        .qt-button {
            display: block;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background: #FF5733;
            color: white;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            text-align: center;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 15px;
            transition: background 0.3s ease-in-out;
            margin: 5px auto;
        
        }

        .qt-button:hover {
            background: #E04D2C;
        }

        /* .qt-button {
            display: inline-block;
            padding: 12px 20px;
            background: #FF5733;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
            margin: 5px;
            cursor: pointer;
            transition: background 0.3s ease-in-out;
        }

        .qt-button:hover {
            background: #E04D2C;
        } */


        /* 로그인 및 회원가입 버튼 */
        .auth-buttons {
            text-align: center;
            margin: 20px 0;
        }

        .auth-buttons .auth-button {
            display: inline-block;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: bold;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            margin: 5px;
            cursor: pointer;
            transition: background 0.3s ease-in-out;
        }

        .login-button {
            background: #FF5733;
        }

        .register-button {
            background: #FF5733;
        }

        .auth-buttons .auth-button:hover {
            opacity: 0.8;
        }

        /* 로그인 상태에서 표시할 환영 메시지 */
        .auth-section {
            text-align: center;
            margin: 20px 0;
        }

        .welcome-message {
            font-size: 18px;
            color: #333;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .logout-button {
            display: inline-block;
            padding: 12px 20px;
            background: #FF5733;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
            margin: 5px;
            cursor: pointer;
            transition: background 0.3s ease-in-out;
        }

        .logout-button:hover {
            background: #E04D2C;
        }

        .admin-button {
            display: inline-block;
            padding: 12px 20px;
            background: #FF5733;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
            margin: 5px;
            cursor: pointer;
            transition: background 0.3s ease-in-out;
        }

        .admin-button:hover {
            background: #E04D2C;
        }

        .myinfo-button {
            display: inline-block;
            padding: 12px 20px;
            background: #FF5733;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            margin-top: 10px;
            font-size: 16px;
            font-weight: bold;
            margin: 5px;
            cursor: pointer;
            transition: background 0.3s ease-in-out;
        }

        .myinfo-button:hover {
            background: #E04D2C;
        }

        /* 성경 검색 섹션 */
        .search {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .search h2 {
            font-size: 20px;
            margin-bottom: 15px;
        }

        .search select,
        .search input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .search button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            background: #FF5733;
            color: white;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s ease-in-out;
        }

        .search button:hover {
            background: #E04D2C;
        }

        /* 검색 결과 스타일 */
        .results {
            margin-top: 20px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .results ul {
            list-style: none;
            padding: 0;
        }

        .results li {
            font-size: 18px;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        /* 메인 화면으로 돌아가기 버튼 */
        .back-to-main {
            margin-top: 20px;
            text-align: center;
        }

        .back-button {
            display: inline-block;
            padding: 10px 15px;
            background: #FF5733;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
        }

        .back-button:hover {
            background: #E04D2C;
        }

        /* 로그인 및 회원가입 폼 스타일 */
        .auth-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: auto;
            text-align: center;
        }

        .auth-box h2 {
            margin-bottom: 20px;
            font-size: 22px;
        }

        .auth-box input, .auth-box select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .auth-box button {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            color: white;
            border: none;
            cursor: pointer;
        }

        .auth-box button {
            background: #9C1032;
        }

        .auth-box button:hover {
            background: #C02045;
        }

        /* 오류 메시지 스타일 */
        .error-message {
            display: none;
            color: red;
            font-size: 14px;
            margin-bottom: 10px;
        }

        /* 반응형 디자인 */
        @media screen and (max-width: 768px) {
            .container {
                width: 95%;
            }

            .auth-box {
                width: 90%;
            }
        }

        

    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // 📌 책 선택 시 자동으로 장 개수 업데이트
            const books = <?= $books2_json ?>;
            const bookSelect = document.getElementById("book");
            const chapterSelect = document.getElementById("chapter");

            bookSelect.addEventListener("change", function () {
                const selectedBook = bookSelect.value;
                chapterSelect.innerHTML = "<option value=''>장 선택</option>"; // 기존 옵션 초기화

                if (selectedBook in books) {
                    for (let i = 1; i <= books[selectedBook]; i++) {
                        let option = document.createElement("option");
                        option.value = i;
                        option.textContent = i + "장";
                        chapterSelect.appendChild(option);
                    }
                }
            });
        });

        function redirectToQt() {
            const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
            if (!isLoggedIn) {
                window.location.href = "login/login.html";
            } else {
                window.location.href = "qt_write.php";
            }
        }

    </script>
</head>
<body>

<div class="container">
    <header>
    <?php if (isset($_SESSION['user_id'])): ?>
        📖 와~우리교회 고등부 <?= $_SESSION['name'] ?>님 반갑습니다!
        <?php else: ?>
            📖 와~우리교회 고등부에 오신 것을 환영합니다!
            <?php endif; ?>
    </header>

    <!-- 오늘의 성경 QT -->
    <section class="qt-section">
        <h2>📅 오늘의 성경말씀ㅣ<?= $today_display ?></h2>
        <h3><?= $book_name ?> <?= $random_verse['chapter'] ?>장 <?= $random_verse['paragraph'] ?>절</h3>
        <p class="verse">"<?= $random_verse['sentence'] ?>"</p>
        <button class="qt-button" onclick="redirectToQt()">QT 작성하기</button>
    </section>
        
    <!-- 성경 검색 -->
    <section class="search">
        <h2>🔎 성경 구절 검색</h2>
        <form action="search.php" method="GET">
            <!-- 성경 선택 -->
            <select id="book" name="book">
                <option value="">성경 선택</option>
                <?php foreach ($books2 as $name => $chapters) : ?>
                    <option value="<?= $name ?>"><?= $name ?></option>
                <?php endforeach; ?>
            </select>

            <!-- 장 선택 -->
            <select id="chapter" name="chapter">
                <option value="">장 선택</option>
            </select>

            <!-- 절 입력 -->
            <input type="number" name="paragraph" placeholder=" 절 입력" min="1">
            <button type="submit">검색</button>
        </form>
        
    </section>

    <div class="auth-section">
        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <p class="welcome-message"><?= $_SESSION['name'] ?>님(관리자), 반갑습니다!</p>
            <?php else: ?>
                <p class="welcome-message"><?= $_SESSION['name'] ?>님, 반갑습니다!</p>
            <?php endif; ?>
            <a href="myinfo.php" class="auth-button myinfo-button">내 정보</a>
            
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                <a href="admin.php" class="auth-button admin-button">관리자 페이지</a>
            <?php endif; ?>
            <a href="logout.php" class="auth-button logout-button">로그아웃</a>


        <?php else: ?>
            <div class="auth-buttons">
                <a href="login/login.html" class="auth-button login-button">로그인</a>
                <a href="register/register.html" class="auth-button register-button">회원가입</a>
            </div>
        <?php endif; ?>
    </div>


</div>

</body>
</html>

