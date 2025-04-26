<?php
session_start();
session_destroy();
header("Location: login/login.html"); // 로그인 페이지로 이동
exit();
?>
