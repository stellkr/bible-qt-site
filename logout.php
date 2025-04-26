<?php
session_start();
date_default_timezone_set('Asia/Seoul');
session_destroy();
header("Location: index.php");
exit();
?>
