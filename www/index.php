<?php
session_start();

// Configuration de la base de données
define('DB_HOST', 'db');
define('DB_NAME', 'login_system');
define('DB_USER', 'root');
define('DB_PASS', 'root');

function connectDB() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}

// Inclure la classe VoitureManager
require_once 'VoitureManager.php';

$pdo = connectDB();
$voitureManager = new VoitureManager($pdo);

// Nombre de voitures par page (2 images par page)
define('CARS_PER_PAGE', 2);

// Récupérer la page actuelle depuis les paramètres GET (par défaut 1)
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // S'assurer que la page est au moins 1

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Calculer l'offset pour la requête SQL
$offset = ($page - 1) * CARS_PER_PAGE;

// Si une recherche est effectuée, utiliser searchVoitures au lieu de getVoituresWithPagination
if (!empty($search)) {
    $voitures = $voitureManager->searchVoitures($search);
    $totalCars = count($voitures);
} else {
    $voitures = $voitureManager->getVoituresWithPagination($offset, CARS_PER_PAGE);
    $totalCars = $voitureManager->countVoitures();
}

$totalPages = ceil($totalCars / CARS_PER_PAGE);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-color: var(--bg-color); color: var(--text-color);">
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <form class="search-bar mb-3" method="GET" action="index.php">
            <div class="input-group">
                <input type="text" class="form-control" 
                       id="search-input" 
                       name="search" 
                       placeholder="Rechercher une voiture..." 
                       value="<?php echo htmlspecialchars($search ?? ''); ?>">
                <button class="btn btn-primary" type="submit">Rechercher</button>
            </div>
        </form>

        <?php if (isset($_GET['search']) && empty($voitures)): ?>
            <div class="alert alert-info text-center">
                Aucun véhicule ne correspond à votre recherche "<?php echo htmlspecialchars($_GET['search']); ?>"
            </div>
        <?php endif; ?>

        <div class="row" id="voitureList">
            <?php if (count($voitures) > 0): ?>
                <?php foreach ($voitures as $voiture): ?>
                    <div class="col-md-6 mb-3 voiture-item"> <!-- Changé de col-md-12 à col-md-6 -->
                        <div class="card h-100 rounded">
                            <img src="data:image/png;base64,<?php echo base64_encode($voiture['image']); ?>" class="card-img-top rounded-top" alt="<?php echo htmlspecialchars($voiture['model_name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($voiture['model_name'] ?? 'Inconnu'); ?></h5>
                                <button onclick="toggleDescription(<?php echo htmlspecialchars($voiture['id']); ?>)" class="toggle-description">
                                    Voir la description
                                </button>
                                <div id="description-<?php echo htmlspecialchars($voiture['id']); ?>" class="description-container" style="display: none;">
                                    <?php echo nl2br(htmlspecialchars($voiture['description'] ?? 'Aucune description disponible.')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="alert alert-info">
                        <?php if (!empty($search)): ?>
                            Aucun véhicule ne correspond à votre recherche "<?php echo htmlspecialchars($search); ?>"
                        <?php else: ?>
                            Aucun véhicule n'est disponible pour le moment.
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination mt-4">
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <!-- Lien vers la première page -->
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1">« First</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">« Previous</a>
                            </li>
                        <?php endif; ?>

                        <!-- Pages avec ellipses -->
                        <?php
                        $range = 1; // Nombre de pages à afficher autour de la page actuelle
                        $start = max(1, $page - $range);
                        $end = min($totalPages, $page + $range);

                        if ($start > 1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>

                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($end < $totalPages): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>

                        <!-- Lien vers la dernière page -->
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next »</a>
                            </li>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $totalPages; ?>">Last »</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleDescription(id) {
            const desc = document.getElementById('description-' + id);
            const btn = event.target;
            
            if (desc.style.display === 'none') {
                desc.style.display = 'block';
                btn.textContent = 'Masquer la description';
            } else {
                desc.style.display = 'none';
                btn.textContent = 'Voir la description';
            }
        }
    </script>
</body>
</html>