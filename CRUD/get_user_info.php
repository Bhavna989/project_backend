<?php
header("Content-Type: application/json; charset=UTF-8");
require_once "../db_connection.php";

$method = $_SERVER["REQUEST_METHOD"];


$id = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

function respond(int $status, array $payload): void {
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function getInputData(): array {
    $raw = file_get_contents("php://input");
    $data = [];

    $json = json_decode($raw, true);
    if (is_array($json)) return $json;

    parse_str($raw, $data);
    return is_array($data) ? $data : [];
}


// CREATE (POST)
if ($method === "POST") {
    $name  = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($name === "" || $email === "" || $password === "") {
        respond(400, ["error" => "Name, Email, and Password required"]);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(400, ["error" => "Invalid email format"]);
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check duplicate email
    $check = mysqli_prepare($con, "SELECT id FROM users WHERE email=?");
    mysqli_stmt_bind_param($check, "s", $email);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        respond(409, ["error" => "Email already exists"]);
    }

    $stmt = mysqli_prepare($con, "INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashedPassword);

    if (mysqli_stmt_execute($stmt)) {
        respond(201, [
            "message" => "User created",
            "id" => mysqli_insert_id($con),
            "name" => $name,
            "email" => $email
        ]);
    } else {
        respond(500, ["error" => "Insert failed", "details" => mysqli_error($con)]);
    }
}


//  READ (GET) 
if ($method === "GET") {
    
    if ($id > 0) {
        $stmt = mysqli_prepare($con, "SELECT id, name, email FROM users WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user) {
            respond(200, $user);
        } else {
            respond(404, ["error" => "User not found"]);
        }
    }

    // Get all users
    $result = mysqli_query($con, "SELECT id, name, email FROM users ORDER BY id DESC");
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }

    respond(200, $users);
}


// UPDATE (PUT)
if ($method === "PUT") {
    if ($id <= 0) {
        respond(400, ["error" => "ID required in URL like ?id=11"]);
    }

    $data = getInputData();

    $name  = trim($data["name"] ?? "");
    $email = trim($data["email"] ?? "");
    $password = $data["password"] ?? ""; 

    if ($email === "") {
        respond(400, ["error" => "Email required"]);
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(400, ["error" => "Invalid email format"]);
    }

    // Check user exists
    $check = mysqli_prepare($con, "SELECT id FROM users WHERE id=?");
    mysqli_stmt_bind_param($check, "i", $id);
    mysqli_stmt_execute($check);
    $res = mysqli_stmt_get_result($check);
    if (!mysqli_fetch_assoc($res)) {
        respond(404, ["error" => "User not found"]);
    }

    // Check duplicate email used by another user
    $dup = mysqli_prepare($con, "SELECT id FROM users WHERE email=? AND id<>?");
    mysqli_stmt_bind_param($dup, "si", $email, $id);
    mysqli_stmt_execute($dup);
    mysqli_stmt_store_result($dup);
    if (mysqli_stmt_num_rows($dup) > 0) {
        respond(409, ["error" => "Email already exists for another user"]);
    }

    // Update (PUT)
    if ($password !== "") {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($con, "UPDATE users SET name=?, email=?, password=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $hashedPassword, $id);
    } else {
        $stmt = mysqli_prepare($con, "UPDATE users SET name=?, email=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $id);
    }

    if (mysqli_stmt_execute($stmt)) {
        respond(200, ["message" => "User updated", "id" => $id, "email" => $email]);
    } else {
        respond(500, ["error" => "Update failed", "details" => mysqli_error($con)]);
    }
}


//  DELETE (DELETE)
if ($method === "DELETE") {
    if ($id <= 0) {
        respond(400, ["error" => "ID required in URL "]);
    }

    $stmt = mysqli_prepare($con, "DELETE FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $id)

    if (!mysqli_stmt_execute($stmt)) {
        respond(500, ["error" => "Delete failed", "details" => mysqli_error($con)]);
    }

    if (mysqli_stmt_affected_rows($stmt) > 0) {
        respond(200, ["message" => "User deleted", "id" => $id]);
    } else {
        respond(404, ["error" => "User not found"]);
    }
}

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
?>