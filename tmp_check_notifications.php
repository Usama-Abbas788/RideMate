<?php
require 'config/database.php';
$result = $conn->query('SHOW COLUMNS FROM notifications');
if (!$result) {
    echo 'ERROR: ' . $conn->error . PHP_EOL;
    exit(1);
}
while ($row = $result->fetch_assoc()) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | ' . $row['Null'] . ' | ' . $row['Key'] . ' | ' . ($row['Default'] ?? 'NULL') . PHP_EOL;
}
