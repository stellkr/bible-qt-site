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

// 📌 성경 책 번호 변환 배열
$books = [
    "창세기" => 1, "출애굽기" => 2, "레위기" => 3, "민수기" => 4, "신명기" => 5,
    "여호수아" => 6, "사사기" => 7, "룻기" => 8, "사무엘상" => 9, "사무엘하" => 10,
    "열왕기상" => 11, "열왕기하" => 12, "역대상" => 13, "역대하" => 14, "에스라" => 15,
    "느헤미야" => 16, "에스더" => 17, "욥기" => 18, "시편" => 19, "잠언" => 20,
    "전도서" => 21, "아가" => 22, "이사야" => 23, "예레미야" => 24, "예레미야애가" => 25,
    "에스겔" => 26, "다니엘" => 27, "호세아" => 28, "요엘" => 29, "아모스" => 30,
    "오바댜" => 31, "요나" => 32, "미가" => 33, "나훔" => 34, "하박국" => 35,
    "스바냐" => 36, "학개" => 37, "스가랴" => 38, "말라기" => 39, "마태복음" => 40,
    "마가복음" => 41, "누가복음" => 42, "요한복음" => 43, "사도행전" => 44, "로마서" => 45,
    "고린도전서" => 46, "고린도후서" => 47, "갈라디아서" => 48, "에베소서" => 49,
    "빌립보서" => 50, "골로새서" => 51, "데살로니가전서" => 52, "데살로니가후서" => 53,
    "디모데전서" => 54, "디모데후서" => 55, "디도서" => 56, "빌레몬서" => 57,
    "히브리서" => 58, "야고보서" => 59, "베드로전서" => 60, "베드로후서" => 61,
    "요한일서" => 62, "요한이서" => 63, "요한삼서" => 64, "유다서" => 65, "요한계시록" => 66
];

// 📌 검색 요청 처리
$book_name = isset($_GET['book']) ? $_GET['book'] : '';
$chapter = isset($_GET['chapter']) ? $_GET['chapter'] : '';
$paragraph = isset($_GET['paragraph']) ? $_GET['paragraph'] : '';

// 📌 성경 책 이름을 숫자로 변환 (DB에서 숫자로 저장된 경우 대비)
$book = isset($books[$book_name]) ? $books[$book_name] : '';

$query_selected = "SELECT * FROM bible2 WHERE book = '$book' AND chapter = '$chapter' AND paragraph = '$paragraph'";
$query_chapter = "SELECT * FROM bible2 WHERE book = '$book' AND chapter = '$chapter' ORDER BY paragraph ASC";

$result_selected = $conn->query($query_selected);
$result_chapter = $conn->query($query_chapter);
?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>성경 검색 결과</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<div class="container">
    <header>
        📖 성경 검색 결과
    </header>

    <!-- 검색 결과 표시 (선택한 절) -->
    <section class="results">
        <h2>📜 <strong><?= $book_name ?> <?= $chapter?>장 <?= $paragraph?>절</strong></h2>
        <?php if ($result_selected->num_rows > 0) : ?>
            <?php while ($row = $result_selected->fetch_assoc()) : ?>
                <p>"<?= $row['sentence'] ?>"</p>
            <?php endwhile; ?>
        <?php else : ?>
            <p>검색 결과가 없습니다.</p>
        <?php endif; ?>
    </section>

    <!-- 선택한 장 전체 표시 -->
    <section class="results full-chapter">
        <h2>📖 <?= $book_name ?> <?= $chapter ?>장</h2>
        <!-- <?php if ($result_chapter->num_rows > 0) : ?> -->
            <ul>
                <?php while ($row = $result_chapter->fetch_assoc()) : ?>
                    <li><strong><?= $row['paragraph'] ?>.</strong> <?= $row['sentence'] ?></li>
                <?php endwhile; ?>
            </ul>
        <?php else : ?>
            <p>이 장의 내용이 없습니다.</p>
        <?php endif; ?>
    </section>

    <!-- 메인 화면으로 돌아가기 버튼 -->
    <section class="back-to-main">
        <a href="index.php" class="back-button">🏠 메인 화면으로</a>
    </section>
</div>

</body>
</html>
