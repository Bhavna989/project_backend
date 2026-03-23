<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Only POST method allowed";
    exit;
}

if (!isset($_FILES['file'])) {
    echo "No file uploaded";
    exit;
}

if (!isset($_POST['survey_name'])) {
    echo "Survey name is required";
    exit;
}

$survey_name = $_POST['survey_name'];
$file = $_FILES['file'];

$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($fileExtension !== 'csv') {
    echo "Only CSV files are allowed";
    exit;
}

$handle = fopen($file['tmp_name'], "r");

if (!$handle) {
    echo "Unable to read file";
    exit;
}

$data = [];
$rowNumber = 0;

while (($row = fgetcsv($handle, 1000, ",")) !== false) {
    $rowNumber++;

    if (count($row) < 5) {
        continue;
    }

    if ($rowNumber == 1 && strtolower(trim($row[0])) == 'question') {
        continue;
    }

    $data[] = [
        "Question" => trim($row[0]),
        "Option1" => trim($row[1]),
        "Option2" => trim($row[2]),
        "Option3" => trim($row[3]),
        "Option4" => trim($row[4])
    ];
}

fclose($handle);

echo "<h2>Survey Name: " . htmlspecialchars($survey_name) . "</h2>";

foreach ($data as $row) {
    echo "<h3>" . htmlspecialchars($row['Question']) . "</h3>";
    echo "<ul>";
    echo "<li>" . htmlspecialchars($row['Option1']) . "</li>";
    echo "<li>" . htmlspecialchars($row['Option2']) . "</li>";
    echo "<li>" . htmlspecialchars($row['Option3']) . "</li>";
    echo "<li>" . htmlspecialchars($row['Option4']) . "</li>";
    echo "</ul>";
}
?>