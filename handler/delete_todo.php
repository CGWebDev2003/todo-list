<?php
session_start();
if (!$_SESSION["loggedin"]) {
    header("HTTP/1.1 403 Forbidden");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['todoID'])) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "todo_app";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $todoID = $_POST['todoID'];

    $stmt = $conn->prepare("DELETE FROM todos WHERE todoID = ?");
    $stmt->bind_param("i", $todoID);

    if ($stmt->execute()) {
        echo "Todo deleted successfully";
    } else {
        echo "Error deleting todo: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("HTTP/1.1 405 Method Not Allowed");
}
?>