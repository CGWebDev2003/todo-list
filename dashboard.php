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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['logoutButton'])) {
        session_destroy();
        header("Location: login.php");
        exit();
    }

    if (isset($_POST['addTodoButton'])) {
        $title = $_POST['title'];
        $details = $_POST['details'];
        $category = $_POST['category'];
        $creator_id = $_COOKIE['user_id'];
        $stmt = $conn->prepare("SELECT MAX(position) as max_position FROM todos WHERE creator_id = ?");
        $stmt->bind_param("i", $creator_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $max_position = $row['max_position'];
        $new_position = $max_position + 1;

        $stmt = $conn->prepare("INSERT INTO todos (title, details, category, is_done, creator_id, position) VALUES (?, ?, ?, 0, ?, ?)");
        $stmt->bind_param("sssii", $title, $details, $category, $creator_id, $new_position);

        if ($stmt->execute()) {
            echo "New todo added successfully";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$sql = "SELECT todoID, title, details, category, is_done FROM todos WHERE creator_id = ? ORDER BY position";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles.css">
    <link rel="stylesheet" href="./components/header.css">
    <link rel="icon" type="image/x-icon" href="./assets/icon.png">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
    <title>Dashboard</title>
</head>
<body>
    <div class="header">
        <div class="headerContent">
            <div class="logoBox">
                <img class="logo" src="./assets/icon.png" alt="Icon"> 
                <h1 class="title">My List</h1>
            </div>

            <form method="POST" style="display:inline;">
                <button type="submit" class="logoutButton" id="logoutButton" name="logoutButton">
                    <i class="bi bi-box-arrow-left"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>
    <div class="content">
        <div class="helloBox">
            <h1 class="helloHeadline">Hallo <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
            <img class="listIcon" src="./assets/list_icon.png" alt="List Icon">
        </div>

        <div class="todoForm">
            <h2>Add New Todo</h2>
            <form method="POST" action="">
                <label for="title">Title:</label>
                <input type="text" id="title" class="titleInput" name="title" required><br>
                
                <label for="details">Details:</label><br/>
                <textarea id="details" class="detailsInput" name="details"></textarea><br>
                
                <label for="category">Category:</label><br/>
                <select id="category" class="categoryDropdown" name="category">
                    <option value="personal">Personal</option>
                    <option value="work">Work</option>
                    <option value="study">Study</option>
                    <option value="other">Other</option>
                </select><br>
                
                <button type="submit" name="addTodoButton">
                    <i class="bi bi-plus-circle"></i>
                     Add Todo
                </button>
            </form>
        </div>
        
        <div class="todoList">
            <h2>Todo List</h2>
            <div class="filter">
                <label for="categoryFilter"><i class="bi bi-funnel-fill"></i> Filter by Category:</label>
                <select id="categoryFilter">
                    <option value="all">All</option>
                    <option value="personal">Personal</option>
                    <option value="work">Work</option>
                    <option value="study">Study</option>
                    <option value="other">Other</option>
                </select>
                <button onclick="filterTodos()">Apply</button>
            </div>
            <ul id="todos">
            <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "todo_app";

                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $user_id = $_COOKIE['user_id'];

                $sql = "SELECT todoID, title, details, category, is_done FROM todos WHERE creator_id = ? ORDER BY position";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<li data-id='".$row['todoID']."' data-category='".$row['category']."'>";
                        echo "<span class='todoTitle'><i class='bi bi-grip-vertical'></i>" . htmlspecialchars($row['title']) . "</span>";
                        echo "<div class='actions'>";
                        echo "<button class='deleteButton' onclick='deleteTodo(".$row['todoID'].")'><i class='bi bi-trash3-fill'></i></button>";
                        echo "<button class='updateButton' onclick='enableEdit(".$row['todoID'].")'><i class='bi bi-pencil-square'></i></button>";
                        echo "<button class='detailsButton' onclick='showDetails(".$row['todoID'].")'><i class='bi bi-info-circle-fill'></i></button>";
                        echo "<input type='checkbox' class='completedCheckbox' onchange='markCompleted(".$row['todoID'].")'";
                        if ($row['is_done'] == 1) {
                            echo " checked";
                        }
                        echo ">";
                        echo "</div>";
                        echo "<div class='details' style='display:none;'>";
                        echo "<p>" . htmlspecialchars($row['details']) . "</p>";
                        echo "</div>";
                        echo "</li>";
                    }
                } else {
                    echo "No todos found.";
                }

                $stmt->close();
                $conn->close();
            ?>
            </ul>
        </div>
    </div>

    <script>
        function deleteTodo(id) {
            if (confirm("Are you sure you want to delete this todo?")) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "handler/delete_todo.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            console.log("Todo with ID " + id + " deleted successfully");
                            var todoElement = document.querySelector("li[data-id='" + id + "']");
                            if (todoElement) {
                                todoElement.remove();
                            }
                        } else {
                            console.error("Error deleting todo with ID " + id);
                        }
                    }
                };
                xhr.send("todoID=" + id);
            }
        }

        function enableEdit(todoID) {
            const todoItem = document.querySelector(`li[data-id='${todoID}']`);
            const titleElement = todoItem.querySelector('.todoTitle');
            const detailsElement = todoItem.querySelector('.details p');

            const title = titleElement.innerText;
            const details = detailsElement.innerText;

            titleElement.innerHTML = `<input type='text' id='editTitle_${todoID}' value='${title}'>`;
            detailsElement.innerHTML = `<textarea id='editDetails_${todoID}'>${details}</textarea>`;

            const actionsDiv = todoItem.querySelector('.actions');
            actionsDiv.innerHTML += `<br/><button class='saveButton' onclick='saveEdit(${todoID})'><i class='bi bi-floppy-fill'></i></button>`;
        }

        function saveEdit(todoID) {
            const title = document.getElementById(`editTitle_${todoID}`).value;
            const details = document.getElementById(`editDetails_${todoID}`).value;

            fetch('handler/update_todo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    todoID: todoID,
                    title: title,
                    details: details
                })
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'success') {
                    location.reload();
                } else {
                    alert('Error updating todo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function markCompleted(id) {
            var checkbox = document.querySelector("li[data-id='" + id + "'] .completedCheckbox");
            var isChecked = checkbox.checked ? 1 : 0;

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "handler/delete_todo.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {
                        console.log("Todo with ID " + id + " marked as completed");
                    } else {
                        console.error("Error marking todo as completed");
                    }
                }
            };
            xhr.send("todoID=" + id + "&isChecked=" + isChecked);
        }

        function showDetails(id) {
            var detailsDiv = document.querySelector("li[data-id='" + id + "'] .details");
            detailsDiv.style.display = detailsDiv.style.display === 'none' ? 'block' : 'none';
        }

        function filterTodos() {
            var categoryFilter = document.getElementById("categoryFilter").value;
            var todos = document.querySelectorAll("#todos li");

            todos.forEach(function(todo) {
                var category = todo.getAttribute("data-category");

                if (categoryFilter === "all" || category === categoryFilter) {
                    todo.style.display = "block";
                } else {
                    todo.style.display = "none";
                }
            });
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            let sortable = new Sortable(document.getElementById('todos'), {
                animation: 150,
                onEnd: function(evt) {
                    let todoItems = document.querySelectorAll('#todos li');
                    let order = [];
                    todoItems.forEach((item, index) => {
                        order.push({
                            id: item.getAttribute('data-id'),
                            position: index + 1
                        });
                    });
                    saveOrder(order);
                }
            });
        });

        function saveOrder(order) {
            fetch('handler/update_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(order)
            })
            .then(response => response.text())
            .then(data => {
                if (data === 'success') {
                    console.log('Order updated successfully');
                } else {
                    alert('Error updating order');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    </script>
</body>
</html>