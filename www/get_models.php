<?php
$host = 'db';
$dbname = 'login_system';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$category_id = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT); // Added validation
if (!$category_id) {
    echo json_encode([]);
    exit();
}

$query = "SELECT * FROM models WHERE category_id = :category_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
$stmt->execute();
$models = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($models);
?>