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
$todoID = $data['todoID'];
$title = $data['title'];
$details = $data['details'];

$sql = "UPDATE todos SET title = ?, details = ? WHERE todoID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $title, $details, $todoID);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}

$stmt->close();
$conn->close();
?>