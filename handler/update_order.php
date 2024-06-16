<?php
session_start();
if (!$_SESSION["loggedin"]) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "todo_app";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$data = json_decode(file_get_contents('php://input'), true);

foreach ($data as $item) {
    $todoID = $item['id'];
    $position = $item['position'];

    $sql = "UPDATE todos SET position = ? WHERE todoID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $position, $todoID);

    if (!$stmt->execute()) {
        echo "error";
        exit();
    }
}

echo "success";

$stmt->close();
$conn->close();
?>