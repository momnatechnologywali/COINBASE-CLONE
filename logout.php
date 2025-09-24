<?php
// logout.php - Simple logout
session_start();
session_destroy();
echo "<script>window.location.href='index.php';</script>";
?>
