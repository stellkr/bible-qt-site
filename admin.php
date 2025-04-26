<?php
session_start();
date_default_timezone_set('Asia/Seoul');

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['is_admin'])) {
    $_SESSION['is_admin'] = 0; // 기본값 설정 (0 = 일반 사용자)
}

if ($_SESSION['is_admin'] != 1) {
    header("Location: index.php");
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


// ✅ 사용자 목록 가져오기
$sql_users = "SELECT id, username, name, grade, class, is_banned FROM users";
$result_users = $conn->query($sql_users);

// ✅ QT 목록 가져오기 (`user_qt` 테이블 사용)
$sql_qt = "SELECT qt.id, u.username, u.name, qt.qt_date, qt.content 
           FROM user_qt qt
           JOIN users u ON qt.user_id = u.id
           ORDER BY qt.qt_date DESC";
$result_qt = $conn->query($sql_qt);

// ✅ 사용자 활동 로그 가져오기
$sql_logs = "SELECT * FROM user_logs ORDER BY log_time DESC LIMIT 10";
$result_logs = $conn->query($sql_logs);

// ✅ QT 작성 통계 가져오기
$sql_stats = "SELECT qt_date, COUNT(*) as count FROM user_qt GROUP BY qt_date ORDER BY qt_date DESC LIMIT 7";
$result_stats = $conn->query($sql_stats);

$stats_data = [];
while ($row = $result_stats->fetch_assoc()) {
    $stats_data[] = ["date" => $row['qt_date'], "count" => $row['count']];
}

$today_date = date("Y-m-d");  // YYYY-MM-DD 형식
$sql_verse = "SELECT date, book, chapter, paragraph, sentence FROM daily_verse WHERE date = '$today_date' LIMIT 1";
$result_verse = $conn->query($sql_verse);
$today_verse = $result_verse->fetch_assoc();

// ✅ 오늘의 말씀 성경 이름 변환
$book_name = isset($books[$today_verse['book']]) ? $books[$today_verse['book']] : "알 수 없음";

$today_verse_json = json_encode([
    "date" => $today_verse['date'],
    "book" => $today_verse['book'],
    "chapter" => $today_verse['chapter'],
    "paragraph" => $today_verse['paragraph'],
    "sentence" => $today_verse['sentence']
], JSON_UNESCAPED_UNICODE);

// 📌 한글 요일 변환
$weekdays = ["일", "월", "화", "수", "목", "금", "토"];
$day_index = date("w"); // 0 (일) ~ 6 (토)
$today_display = date("Y년 m월 d일") . " (" . $weekdays[$day_index] . ")";

if (!isset($stats_data) || empty($stats_data)) {
    $stats_data = [];
}

?>

<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>와~우리교회 고등부 관리자 페이지</title>
    <link href="imge/logo.png" rel="shortcut icon" type="imge/x-icon"> <!--서버 아이콘 변경-->
    <!-- <link rel="stylesheet" href="admin.css"> -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- 그래프 라이브러리 -->
</head>
<body>
    <div class="admin-container">
        <header>와~우리교회 고등부 관리자 페이지</header>
        <a href="index.php" class="logout-button">메인페이지</a>
        <a href="admin_logout.php" class="logout-button">로그아웃</a>
        
        <h3>📖 오늘의 말씀ㅣ<?= $today_display ?></h3>
        <h3><strong><?= $book_name ?> <?= $today_verse['chapter'] ?>장 <?= $today_verse['paragraph'] ?>절</strong></h3>
        <p>"<?= htmlspecialchars($today_verse['sentence']) ?>"</p>
        <button id="editVerseBtn">오늘의 말씀 수정</button>

        <!-- ✅ 모달 창 -->
        <div id="editVerseModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>📖 오늘의 말씀 수정</h3>
                <form id="updateVerseForm">
                <label>날짜</label>
                <input type="date" name="date" id="dateInput" style="font-family: 'Noto Sans KR', sans-serif;">


                    <label>성경</label>
                    <select name="book" id="bookSelect">
                        <?php foreach ($books as $key => $name) : ?>
                            <option value="<?= $key ?>"><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label>장</label>
                    <input type="number" name="chapter" id="chapterInput" required>

                    <label>절</label>
                    <input type="number" name="paragraph" id="paragraphInput" required>

                    <label>말씀</label>
                    <textarea name="sentence" id="sentenceInput" style="font-family: 'Noto Sans KR', sans-serif;" required></textarea>

                    <button type="submit">수정하기</button>
                </form>
            </div>
        </div>

        <!-- ✅ 관리자 등록 모달 -->
        <div id="adminModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>👑 관리자 등록</h3>
                <form id="adminRegisterForm">
                    <label for="username">아이디</label>
                    <input type="text" id="username" name="username" required>

                    <label for="password">비밀번호</label>
                    <input type="password" id="password" name="password" required>

                    <label for="name">이름</label>
                    <input type="text" id="name" name="name" required>

                    <button type="submit">등록하기</button>
                </form>
            </div>
        </div>



        <!-- 🔍 사용자 검색 -->
        <div class="search-section">
            <input type="text" id="searchUser" placeholder="이름 또는 아이디 검색" onkeyup="searchUsers()">
        </div>

        <h3>👥 사용자 목록</h3>
        <!-- ✅ 관리자 등록 버튼 -->
<button id="openAdminModal" class="admin-register-button">관리자 등록</button>

        <table id="userTable">
            <tr>
                <th>이름</th>
                <th>아이디</th>
                <th>학년</th>
                <th>반</th>
                <th>차단</th>
                <th>관리</th>
                <th>관리자</th>
            </tr>
            <?php while ($user = $result_users->fetch_assoc()) : ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['grade']) ?></td>
                    <td><?= htmlspecialchars($user['class']) ?></td>
                    <td>
                    <button class="ban-user" 
                            data-id="<?= $user['id'] ?>" 
                            data-status="<?= $user['is_banned'] ?>" 
                            data-name="<?= htmlspecialchars($user['name']) ?>" 
                            data-username="<?= htmlspecialchars($user['username']) ?>">
                        <?= $user['is_banned'] ? "차단 해제" : "차단" ?>
                    </button>
                    </td>
                    <td>
                        <button class="edit-button" onclick="location.href='edit_user.php?id=<?= $user['id'] ?>'">정보수정</button>
                    </td>
                    <td>
                        <button class="toggle-admin" data-id="<?= $user['id'] ?>" 
                                data-status="<?= !empty($user['is_admin']) ? 1 : 0 ?>">
                            <?= !empty($user['is_admin']) ? "관리자 해제" : "관리자 등록" ?>
                        </button>
                    </td>

                </tr>
            <?php endwhile; ?>
        </table>

                <!-- 🔍 사용자 검색 -->
        <div class="search-section">
            <input type="text" id="searchQT" placeholder="아이디 또는 날짜(YYYYMMDD) 검색" onkeyup="searchQTs()">
        </div>
        <h3>📖 사용자 QT 목록</h3>
        <table id="qtTable">
            <tr>
                <th>이름</th>
                <th>아이디</th>
                <th>날짜</th>
                <th>QT 내용</th>
                <th>관리</th>
            </tr>
            <?php while ($qt = $result_qt->fetch_assoc()) : ?>
                <tr>
                    <td><?= htmlspecialchars($qt['name']) ?></td>
                    <td><?= htmlspecialchars($qt['username']) ?></td>
                    <td><?= htmlspecialchars($qt['qt_date']) ?></td>
                    <td>
                        <button class="view-qt" data-content="<?= htmlspecialchars($qt['content']) ?>">
                            자세히 보기
                        </button>
                    </td>
                    <td>
                        <button class="delete-qt" data-id="<?= $qt['id'] ?>">삭제</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>

        <!-- ✅ 모달 창 (QT 상세 내용 표시) -->
        <div id="qtModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>📖 QT 내용</h3>
                <p id="qtText"></p>
            </div>
        </div>


        <!-- 📊 QT 작성 통계 그래프 -->
        <h3>📈 QT 작성 통계</h3>
        <canvas id="qtChart"></canvas>
    </div>

    <script>
    // ✅ 사용자 검색
    function searchUsers() {
        let input = document.getElementById("searchUser").value.toLowerCase();
        let rows = document.getElementById("userTable").rows;

        for (let i = 1; i < rows.length; i++) {
            let name = rows[i].cells[0].innerText.toLowerCase();
            let username = rows[i].cells[1].innerText.toLowerCase();
            rows[i].style.display = (name.includes(input) || username.includes(input)) ? "" : "none";
        }
    }

    // ✅ QT 검색
    function searchQTs() {
        let input = document.getElementById("searchQT").value.toLowerCase();
        let rows = document.getElementById("qtTable").rows;

        for (let i = 1; i < rows.length; i++) {
            let username = rows[i].cells[1].innerText.toLowerCase();
            let date = rows[i].cells[2].innerText.toLowerCase();
            rows[i].style.display = (username.includes(input) || date.includes(input)) ? "" : "none";
        }
    }

    // ✅ QT 작성 통계 그래프
    document.addEventListener("DOMContentLoaded", function () {
        let canvas = document.getElementById("qtChart");
        
        if (!canvas) {  // `qtChart`가 존재하는지 확인
            console.error("qtChart 요소를 찾을 수 없습니다.");
            return;
        }

        let ctx = canvas.getContext("2d");
        let chartData = <?= json_encode($stats_data) ?>;

        if (!chartData || !Array.isArray(chartData) || chartData.length === 0) {
            console.error("차트 데이터를 불러올 수 없습니다.");
            return;
        }

        let labels = chartData.map(item => item.date);
        let counts = chartData.map(item => item.count);

        new Chart(ctx, {
            type: "line",
            data: {
                labels: labels,
                datasets: [{
                    label: "QT 작성 수",
                    data: counts,
                    borderColor: "#FF5733",
                    backgroundColor: "rgba(255, 87, 51, 0.2)",
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });


    document.addEventListener("DOMContentLoaded", function () {
        // ✅ QT 내용 보기 버튼 이벤트
        document.querySelectorAll(".view-qt").forEach(button => {
            button.addEventListener("click", function () {
                const content = this.getAttribute("data-content");
                document.getElementById("qtText").innerText = content;
                document.getElementById("qtModal").style.display = "block";
            });
        });

        // ✅ 모달 닫기 버튼 이벤트
        document.querySelector(".close").addEventListener("click", function () {
            document.getElementById("qtModal").style.display = "none";
        });

        // ✅ 모달 창 밖을 클릭하면 닫기
        window.onclick = function (event) {
            let modal = document.getElementById("qtModal");
            if (event.target === modal) {
                modal.style.display = "none";
            }
        };
    });

    // ✅ QT 삭제 기능
    document.querySelectorAll(".delete-qt").forEach(button => {
        button.addEventListener("click", function() {
            const qtId = this.getAttribute("data-id");

            if (confirm("해당 QT를 삭제하시겠습니까?")) {
                fetch("delete_qt.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: new URLSearchParams({ id: qtId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("QT가 삭제되었습니다.");
                        location.reload();
                    } else {
                        alert("QT 삭제 실패: " + data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
            }
        });
    });

    document.querySelectorAll(".ban-user").forEach(button => {
        button.addEventListener("click", function() {
            const userId = this.getAttribute("data-id");
            const isBanned = this.getAttribute("data-status");
            const userName = this.getAttribute("data-name"); // 사용자 이름 가져오기
            const userUsername = this.getAttribute("data-username"); // 사용자 아이디 가져오기

            fetch("toggle_ban.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ id: userId, status: isBanned })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let message = (isBanned == "1") 
                        ? `${userName}님의 계정 차단이 해제되었습니다.\n차단해제된 아이디: ${userUsername}`
                        : `${userName}님의 계정이 차단되었습니다.\n차단된 아이디: ${userUsername}`;
                    
                    alert(message);
                    location.reload();
                } else {
                    alert("상태 변경 실패: " + data.message);
                }
            })
            .catch(error => console.error("Error:", error));
        });
    });

    // ✅ 오늘의 말씀 수정 모달 열기/닫기
    document.getElementById("editVerseBtn").addEventListener("click", function () {
        document.getElementById("editVerseModal").style.display = "block";
    });

    document.querySelector(".close").addEventListener("click", function () {
        document.getElementById("editVerseModal").style.display = "none";
    });

    document.addEventListener("DOMContentLoaded", function () {
        const bookSelect = document.getElementById("bookSelect");
        const chapterInput = document.getElementById("chapterInput");
        const paragraphInput = document.getElementById("paragraphInput");
        const sentenceInput = document.getElementById("sentenceInput");

        function fetchVerse() {
            const book = bookSelect.value;
            const chapter = chapterInput.value;
            const paragraph = paragraphInput.value;

            if (!book || !chapter || !paragraph) return; // 값이 모두 입력되었을 때만 요청

            fetch("get_verse.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ book, chapter, paragraph })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    sentenceInput.value = data.sentence; // 해당 절의 말씀 자동 입력
                } else {
                    sentenceInput.value = "해당 구절을 찾을 수 없습니다.";
                }
            })
            .catch(error => console.error("Error:", error));
        }

        bookSelect.addEventListener("change", fetchVerse);
        chapterInput.addEventListener("input", fetchVerse);
        paragraphInput.addEventListener("input", fetchVerse);
    });


    document.addEventListener("DOMContentLoaded", function () {
        const dateSelect = document.getElementById("dateSelect");
        const bookSelect = document.getElementById("bookSelect");
        const chapterInput = document.getElementById("chapterInput");
        const paragraphInput = document.getElementById("paragraphInput");
        const sentenceInput = document.getElementById("sentenceInput");
        const updateVerseBtn = document.getElementById("updateVerseBtn");

        function fetchVerseByDate() {
            const date = dateSelect.value;
            if (!date) return;

            fetch("get_verse_by_date.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ date })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bookSelect.value = data.book;
                    chapterInput.value = data.chapter;
                    paragraphInput.value = data.paragraph;
                    sentenceInput.value = data.sentence;
                } else {
                    bookSelect.value = "";
                    chapterInput.value = "";
                    paragraphInput.value = "";
                    sentenceInput.value = "";
                }
            })
            .catch(error => console.error("Error:", error));
        }

        dateSelect.addEventListener("change", fetchVerseByDate);

        updateVerseBtn.addEventListener("click", function (event) {
            event.preventDefault();
            const date = dateSelect.value;
            const book = bookSelect.value;
            const chapter = chapterInput.value;
            const paragraph = paragraphInput.value;
            const sentence = sentenceInput.value;

            fetch("update_verse.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ date, book, chapter, paragraph, sentence })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            })
            .catch(error => console.error("Error:", error));
        });
    });


    document.addEventListener("DOMContentLoaded", function () {
            const todayVerse = <?= $today_verse_json ?>;
            
            document.getElementById("editVerseBtn").addEventListener("click", function () {
                document.getElementById("editVerseModal").style.display = "block";

                // ✅ 오늘의 말씀 데이터 자동 기입
                document.getElementById("dateInput").value = todayVerse.date;
                document.getElementById("bookSelect").value = todayVerse.book;
                document.getElementById("chapterInput").value = todayVerse.chapter;
                document.getElementById("paragraphInput").value = todayVerse.paragraph;
                document.getElementById("sentenceInput").value = todayVerse.sentence;
            });

            document.querySelector(".close").addEventListener("click", function () {
                document.getElementById("editVerseModal").style.display = "none";
            });

            // ✅ 오늘의 말씀 업데이트 AJAX 요청
            document.getElementById("updateVerseForm").addEventListener("submit", function(event) {
                event.preventDefault();
                const formData = new FormData(this);

                fetch("update_verse.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("오늘의 말씀이 수정되었습니다.");
                        location.reload();
                    } else {
                        alert("수정 실패: " + data.message);
                    }
                })
                .catch(error => console.error("Error:", error));
            });
        });

    document.getElementById("dateInput").addEventListener("change", function () {
        let selectedDate = this.value;

        fetch("get_verse_by_date.php?date=" + selectedDate)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById("bookSelect").value = data.book;
                document.getElementById("chapterInput").value = data.chapter;
                document.getElementById("paragraphInput").value = data.paragraph;
                document.getElementById("sentenceInput").value = data.sentence;
            } else {
                // ✅ 해당 날짜에 말씀이 없으면 입력칸 초기화
                document.getElementById("bookSelect").value = "";
                document.getElementById("chapterInput").value = "";
                document.getElementById("paragraphInput").value = "";
                document.getElementById("sentenceInput").value = "";
            }
        })
        .catch(error => console.error("Error:", error));
    });

    document.addEventListener("DOMContentLoaded", function () {
        const adminModal = document.getElementById("adminModal");
        const openModalBtn = document.getElementById("openAdminModal");
        const closeModalBtn = document.querySelector(".modal .close");
        const adminRegisterForm = document.getElementById("adminRegisterForm");

        // ✅ 모달 열기
        openModalBtn.addEventListener("click", function () {
            adminModal.style.display = "block";
        });

        // ✅ 모달 닫기
        closeModalBtn.addEventListener("click", function () {
            adminModal.style.display = "none";
        });

        // ✅ 모달 외부 클릭 시 닫기
        window.addEventListener("click", function (event) {
            if (event.target === adminModal) {
                adminModal.style.display = "none";
            }
        });

        // ✅ 관리자 등록 처리
        adminRegisterForm.addEventListener("submit", function (event) {
            event.preventDefault();

            const formData = new FormData(adminRegisterForm);

            fetch("make_admin.php", {
                method: "POST",
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("관리자 등록 중 오류가 발생했습니다.");
            });
        });
    });

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".toggle-admin").forEach(button => {
        button.addEventListener("click", function () {
            const userId = this.getAttribute("data-id");
            const currentStatus = parseInt(this.getAttribute("data-status"));

            fetch("toggle_admin.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ id: userId, status: currentStatus })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    this.innerText = data.new_status ? "관리자 해제" : "관리자 등록";
                    this.setAttribute("data-status", data.new_status);
                }
            })
            .catch(error => console.error("Error:", error));
        });
    });
});



    </script>
</body>
</html>



<style>
    /* 전체 페이지 스타일 */
body {
    font-family: 'Noto Sans KR', sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

/* 관리자 컨테이너 */
/* .admin-container {
    width: 100%;
    max-width: 600px;
    margin: 30px auto;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    text-align: center;
} */

.admin-container {
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

header {
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    padding: 15px 0;
    background: #FF5733;
    color: white;
    border-radius: 10px;
    margin-bottom: 20px;
}

/* 제목 스타일 */
.admin-container h2, .admin-container h3 {
    color: #333;
    margin-bottom: 15px;
}

/* 로그아웃 버튼 */
.logout-button {
    display: inline-block;
    padding: 10px 15px;
    background: #FF5733;
    color: white;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    margin-bottom: 20px;
}

.logout-button:hover {
    background: #E04D2C;
}

/* 테이블 기본 스타일 */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

th, td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: center;
}

th {
    background: #FF5733;
    color: white;
    font-weight: bold;
}

/* 선택 박스 & 버튼 스타일 */
select {
    padding: 5px;
    border: 1px solid #ccc;
    border-radius: 3px;
}

button {
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    color: white;
    font-size: 14px;
    cursor: pointer;
    font-weight: bold;
}

/* 사용자 관리 버튼 */
.update-user {
    background: #3498DB;
}

.delete-user {
    background: #E74C3C;
}

.update-user:hover {
    background: #2980B9;
}

.delete-user:hover {
    background: #C0392B;
}

/* QT 관리 버튼 */
.delete-qt {
    background: #E67E22;
}

.delete-qt:hover {
    background: #D35400;
}

/* 반응형 디자인 */
@media screen and (max-width: 768px) {
    .admin-container {
        width: 95%;
    }

    th, td {
        font-size: 14px;
    }

    button {
        padding: 6px 10px;
        font-size: 12px;
    }
}

/* ✅ 모달 스타일 */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 15% auto;
    padding: 20px;
    width: 80%;
    max-width: 500px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

.close {
    color: red;
    float: right;
    font-size: 25px;
    cursor: pointer;
}

.close:hover {
    color: darkred;
}

/* ✅ 차단 버튼 스타일 */
/* .ban-button {
    background-color: #ccc;
    color: #666;
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    font-weight: bold;
    cursor: not-allowed;
    opacity: 0.7;
}

.ban-button.active {
    background-color: #FF5733;
    color: white;
    cursor: pointer;
    opacity: 1;
}

.ban-button.active:hover {
    background-color: #E04D2C;
} */

/* ✅ QT 내용 자세히 보기 버튼 스타일 */
.view-qt {
    background-color: #FFA500;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
}

.view-qt:hover {
    background-color: #FF8C00;
}

/* ✅ 정보수정 버튼 스타일 */
.edit-button {
    background-color: #FF8C00;
    color: white;
    padding: 8px 12px;
    border: none;
    border-radius: 5px;
    font-size: 14px;
    font-weight: bold;
    cursor: pointer;
}

.edit-button:hover {
    background-color: #FF7F00;
}

/* ✅ 사용자 정보 수정 페이지 스타일 */
.edit-container {
    width: 50%;
    margin: auto;
    padding: 20px;
    background: white;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    text-align: center;
}

.edit-container h2 {
    color: #FF5733;
}

.edit-container input, .edit-container select {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.edit-container button {
    width: 100%;
    padding: 10px;
    font-size: 16px;
    background: #FF5733;
    color: white;
    border-radius: 5px;
    border: none;
    cursor: pointer;
}

.edit-container button:hover {
    background: #E04D2C;
}


/* ✅ 모달 기본 스타일 */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

/* ✅ 모달 컨텐츠 스타일 */
.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 20px;
    width: 90%;
    max-width: 500px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

/* ✅ 모달 닫기 버튼 */
.close {
    color: red;
    float: right;
    font-size: 25px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: darkred;
}

.admin-register-button {
    display: block;
    margin-bottom: 15px;
    padding: 10px 15px;
    background: #FF5733;
    color: white;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.admin-register-button:hover {
    background: #E04D2C;
}

/* ✅ 모달 스타일 */
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
    margin: 10% auto;
    padding: 20px;
    width: 90%;
    max-width: 400px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
}

.modal-content input {
    width: 100%;
    padding: 10px;
    margin: 5px 0 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
}

.modal-content button {
    width: 100%;
    padding: 10px;
    margin-top: 10px;
    background-color: #FF5733;
    color: white;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.modal-content button:hover {
    background-color: #E04D2C;
}

.close {
    color: red;
    float: right;
    font-size: 25px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover {
    color: darkred;
}


/* ✅ 입력 폼 스타일 */
.modal-content label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
    text-align: left;
}

.modal-content input,
.modal-content select,
.modal-content textarea {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 16px;
    box-sizing: border-box;
}

/* ✅ 텍스트 입력창 크기 조절 */
.modal-content textarea {
    height: 80px;
    resize: none;
}

/* ✅ 버튼 스타일 */
.modal-content button {
    width: 100%;
    padding: 10px;
    margin-top: 15px;
    background-color: #FF5733;
    color: white;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.modal-content button:hover {
    background-color: #E04D2C;
}

button {
    background-color: #FF5733;
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background-color: #E04D2C;
}

/* 🔹 검색 창 컨테이너 */
.search-section {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px; /* 검색 창 사이 간격 */
    margin-top: 20px;
    width: 100%;
}

/* 🔹 개별 검색 입력 필드 스타일 */
#searchUser,
#searchQT {
    width: 300px; /* 입력 필드 크기 통일 */
    padding: 10px;
    border: 2px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    transition: all 0.3s ease-in-out;
    text-align: center; /* 텍스트 중앙 정렬 */
}

/* 🔹 입력 필드 포커스 효과 */
#searchUser:focus,
#searchQT:focus {
    border-color: #FF5733;
    outline: none;
    box-shadow: 0 0 8px rgba(255, 87, 51, 0.5);
}

/* 🔹 검색 필드 안의 플레이스홀더 스타일 */
#searchUser::placeholder,
#searchQT::placeholder {
    color: #999;
}

/* ✅ 반응형 조정 */
@media screen and (max-width: 600px) {
    .modal-content {
        width: 95%;
        margin: 15% auto;
    }
}

</style>