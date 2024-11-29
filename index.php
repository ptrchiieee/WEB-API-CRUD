<?php
include "../task-api/config/db.php";
header("Content-Type: application/json");

// Get the HTTP request method
$requestMethod = $_SERVER["REQUEST_METHOD"];

// Get the task ID if it's provided in the URL (for specific task operations)
$task_id = isset($_GET["id"]) ? trim($_GET["id"], "/") : null;

// Handle the request URL (to manage any custom routes)
$request = isset($_GET['request']) ? explode("/", trim($_GET['request'], "/")): [];

switch($requestMethod) {
    case 'POST':
        createTask();
        break;
        
    case 'GET':
        if ($task_id) {
            getTask($task_id); // Get a single task by ID
        } else {
            getTasks(); // Get all tasks
        }
        break;

    case 'PUT':
    case 'PATCH':
        updateTask($task_id); // Update task with the specified ID
        break;

    case 'DELETE':
        if ($task_id) {
            deleteTask($task_id); // Delete task by ID
        } else {
            deleteAllTasks(); // Delete all tasks
        }
        break;

    default:
        http_response_code(405); // Method Not Allowed
        echo json_encode(["message" => "Method not allowed"]);
        break;
}

mysqli_close($connected); // Close the database connection


// Function to create a task
function createTask() {
    global $connected;

    $data = json_decode(file_get_contents("php://input"), true);

    $title = $data['title'];
    $description = $data['description'];

    if(!empty($title)) {
        $sql = "INSERT INTO tasks (title, description) VALUES ('$title', '$description')";

        if (mysqli_query($connected, $sql)) {
            http_response_code(201);
            echo json_encode(["message" => "Task created successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error creating task"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Title is required"]);
    }
}

// Function to get all tasks
function getTasks() {
    global $connected;

    $sql = "SELECT * FROM tasks";
    $result = mysqli_query($connected, $sql);

    if (mysqli_num_rows($result) > 0) {
        $tasks = mysqli_fetch_all($result, MYSQLI_ASSOC);
        echo json_encode($tasks);
    } else {
        echo json_encode(["message" => "No tasks found"]);
    }
}

// Function to get a single task by ID
function getTask($id) {
    global $connected;

    $sql = "SELECT * FROM tasks WHERE id = $id";
    $result = mysqli_query($connected, $sql);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        echo json_encode(["message" => "Task not found"]);
    }
}

// Function to update a task by ID
function updateTask($id) {
    global $connected;

    if ($id) {
        $data = json_decode(file_get_contents("php://input"), true);

        $title = $data['title'] ?? null;
        $description = $data['description'] ?? null;

        if ($title || $description) {
            // Only update non-null fields
            $updates = [];
            if ($title) {
                $updates[] = "title = '$title'";
            }
            if ($description) {
                $updates[] = "description = '$description'";
            }

            $sql = "UPDATE tasks SET " . implode(", ", $updates) . " WHERE id = $id";

            if (mysqli_query($connected, $sql)) {
                echo json_encode(["message" => "Task updated successfully"]);
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error updating task"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "At least one field (title or description) is required to update"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Task ID is required"]);
    }
}

// Function to delete a task by ID
function deleteTask($id) {
    global $connected;

    if ($id) {
        $sql = "DELETE FROM tasks WHERE id = $id";

        if (mysqli_query($connected, $sql)) {
            echo json_encode(["message" => "Task deleted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error deleting task"]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Task ID is required"]);
    }
}

// Function to delete all tasks
function deleteAllTasks() {
    global $connected;

    $sql = "DELETE FROM tasks";

    if (mysqli_query($connected, $sql)) {
        echo json_encode(["message" => "All tasks deleted"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Error deleting all tasks"]);
    }
}
?>