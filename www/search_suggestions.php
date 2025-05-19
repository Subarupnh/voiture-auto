<?php
// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Nombre de voitures par page
define('CARS_PER_PAGE', 3);

// Récupérer la page actuelle depuis les paramètres GET (par défaut 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // S'assurer que la page est au moins 1

// Calculer l'offset pour la requête SQL
$offset = ($page - 1) * CARS_PER_PAGE;

try {
    // Connexion à la base de données
    $pdo = connectDB();

    // Récupérer les voitures pour la page actuelle
    $query = "SELECT * FROM animes LIMIT :limit OFFSET :offset"; // Corrected table name
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', CARS_PER_PAGE, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Compter le nombre total de voitures
    $countQuery = "SELECT COUNT(*) FROM animes"; // Corrected table name
    $totalCars = $pdo->query($countQuery)->fetchColumn();

    // Calculer le nombre total de pages
    $totalPages = ceil($totalCars / CARS_PER_PAGE);

} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - Pagination</title>
</head>
<body>
    <h1>Liste des voitures</h1>
    <ul>
        <?php foreach ($cars as $car): ?>
            <li>
                <strong><?php echo htmlspecialchars($car['name']); ?></strong><br>
                <?php echo htmlspecialchars($car['description']); ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Pagination -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">Précédent</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" <?php if ($i === $page) echo 'style="font-weight: bold;"'; ?>>
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Suivant</a>
        <?php endif; ?>
    </div>
</body>
</html>