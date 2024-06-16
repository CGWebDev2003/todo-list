<?php
    require("connection.php");

    if(isset($_POST["submit"])) {
        $email = $_POST["email"];
        $password = $_POST["password"];

        $stmt = $con->prepare("SELECT * FROM users WHERE email=:email");
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $userExists = $stmt->fetchAll();

        if (count($userExists) > 0) {
            $passwordHashed = $userExists[0]["password"];
            $checkPassword = password_verify($password, $passwordHashed);

            if($checkPassword === false) {
                echo "Login fehlgeschlagen!";
            }

            if($checkPassword === true) {
                session_start();
                $_SESSION["username"] = $userExists[0]["username"];
                $_SESSION["loggedin"] = true;

                $userId = $userExists[0]["id"];
                setcookie("user_id", $userId, time() + (86400 * 30), "/"); // 86400 = 1 day

                header("Location: dashboard.php");
                exit();
            }
        } else {
            echo "Login fehlgeschlagen!";
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
            <h2>Login</h2>
            <form id="loginForm" action="login.php" method="POST">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" required><br><br>

                <label for="password">Passwort:</label>
                <input type="password" id="password" name="password" required><br><br>

                <button type="submit" class="submitButton" name="submit">
                    <i class="bi bi-unlock-fill"></i>
                    Login
                </button><br/>

                <a class="registerLink" href="./index.php">
                    Registrieren
                </a>
            </form>
        </div>

    </body>
</html>