<?php
    require("connection.php");

    function registerUser($username, $email, $password) {
        global $con;
        $stmt = $con->prepare("INSERT INTO users(username, email, password) VALUES(:username, :email, :password)");
        $stmt->bindParam("username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":password", $password);
        $stmt->execute();
        header("Location: dashboard.php");
    }

    if(isset($_POST["submit"])){
        $username = $_POST["username"];
        $email = $_POST["email"];
        $password = PASSWORD_HASH($_POST["password"], PASSWORD_DEFAULT);

        $stmt = $con->prepare("SELECT * FROM users WHERE username=:username OR email=:email");
        $stmt->bindParam(":username", $username);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $userAlreadyExists = $stmt->fetchColumn();

        if(!$userAlreadyExists){
            registerUser($username, $email, $password);
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="./styles.css">

        <link rel="icon" type="image/x-icon" href="./assets/icon.png">
        <title>User Authentication App</title>
    </head>
    <body>
        <div class="container">
            <h2>Registrierung</h2>
            <form id="registerForm" action="index.php" method="POST">
                <label for="username">Vorname:</label>
                <input type="text" id="username" name="username" autocomplete="off" required><br><br>
            
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" autocomplete="off" required><br><br>
            
                <label for="password">Passwort:</label>
                <input type="password" id="password" name="password" autocomplete="off" required><br><br>
            
                <button type="submit" class="submitButton" name="submit">
                    <i class="bi bi-person-fill-add"></i>
                    Registrieren
                </button><br/>
            
                <a class="registerLink" href="./login.php">
                    Login
                </a>
            </form>
    </body>
</html>