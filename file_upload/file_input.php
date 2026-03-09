<?php
header("Content-Type: application/json");
require_once "db_connection.php"; 

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Only POST method allowed"
    ]);
    exit;
}


// Check file upload
if (!isset($_FILES['file'])) {
    echo json_encode([
        "status" => false,
        "message" => "No file uploaded"
    ]);
    exit;
}

$file = $_FILES['file'];

// Validate file type (basic check)
$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($fileExtension !== 'csv') {
    echo json_encode([
        "status" => false,
        "message" => "Only CSV files are allowed"
    ]);
    exit;
}

// Open CSV file
$handle = fopen($file['tmp_name'], "r");

if (!$handle) {
    echo json_encode([
        "status" => false,
        "message" => "Unable to read file"
    ]);
    exit;
}

$data = [];
$rowNumber = 0;

// Read CSV
while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {

    $rowNumber++;

    // Skip empty rows
    if (count($row) < 3) {
        continue;
    }

    

    // Optional: Skip header row
    if ($rowNumber == 1 && strtolower($row[0]) == 'name') {
        continue;
    }

    $data[] = [
        "name" => trim($row[0]),
        "email" => trim($row[1]),
        "password" => $row[2]  // Unhashed password
    ];
}

$stmt = $con->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
foreach ($data as $user) {
    $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);
    $stmt->bind_param("sss", $user['name'], $user['email'], $hashedPassword);
    $stmt->execute();
}
$stmt->close();
fclose($handle);
echo json_encode([
    "status" => true,
    "message" => "File uploaded and data inserted successfully"
]);