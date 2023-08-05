<?php
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $message = htmlspecialchars($message);
    echo $message;
}
header("Location: index.php?message=" . urlencode($message));
?>