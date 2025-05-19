<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Configuration de la base de données (incluse ici pour éviter l'erreur)
define('DB_HOST', 'db');
define('DB_NAME', 'login_system');
define('DB_USER', 'root');
define('DB_PASS', 'root');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Fonction pour récupérer les catégories (Marques)
function getCategories($pdo) {
    $query = "SELECT * FROM categories";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer les modèles par catégorie
function getModelsByCategory($pdo, $categoryId) {
    $query = "SELECT * FROM models WHERE category_id = :category_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$categories = getCategories($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Suppression d'une voiture
    if (isset($_POST['delete_voiture'])) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $query = "DELETE FROM animes WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    // Création d'un modèle
    if (isset($_POST['create_model'])) {
        $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
        $model_name = trim(htmlspecialchars($_POST['model_name'], ENT_QUOTES, 'UTF-8'));
        $query = "INSERT INTO models (category_id, name) VALUES (:category_id, :model_name)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $stmt->bindParam(':model_name', $model_name, PDO::PARAM_STR);
        $stmt->execute();
    }

    // Création d'une marque
    if (isset($_POST['create_marque'])) {
        $marque_name = trim(htmlspecialchars($_POST['marque_name'], ENT_QUOTES, 'UTF-8'));
        $query = "INSERT INTO categories (name) VALUES (:marque_name)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':marque_name', $marque_name, PDO::PARAM_STR);
        $stmt->execute();
        // Mettre à jour la liste des catégories
        $categories = getCategories($pdo);
    }

    if (isset($_POST['update_voiture'])) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $description = trim(htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8'));
        $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
        $model_id = filter_var($_POST['model_id'], FILTER_VALIDATE_INT);
        $image = $_FILES['image'];

        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if ($image['error'] === UPLOAD_ERR_OK && in_array($image['type'], $allowedMimeTypes)) {
            $imageData = file_get_contents($image['tmp_name']);
            $query = "UPDATE animes SET description = :description, image = :image, category_id = :category_id, model_id = :model_id WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':image', $imageData, PDO::PARAM_LOB);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindParam(':model_id', $model_id, PDO::PARAM_INT);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            echo "Veuillez télécharger une image au format JPG, JPEG ou PNG.";
        }
    }

    if (isset($_POST['create_voiture'])) {
       $description = trim(htmlspecialchars($_POST['description'], ENT_QUOTES, 'UTF-8'));
        $category_id = filter_var($_POST['category_id'], FILTER_VALIDATE_INT);
        $model_id = filter_var($_POST['model_id'], FILTER_VALIDATE_INT);
        $image = $_FILES['image'];

        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if ($image['error'] === UPLOAD_ERR_OK && in_array($image['type'], $allowedMimeTypes)) {
            $imageData = file_get_contents($image['tmp_name']);
            $query = "INSERT INTO animes (description, image, category_id, model_id) VALUES (:description, :image, :category_id, :model_id)";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':image', $imageData, PDO::PARAM_LOB);
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindParam(':model_id', $model_id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            echo "Veuillez télécharger une image au format JPG, JPEG ou PNG.";
        }
    }
}

$query = "SELECT animes.*, categories.name AS category_name, models.name AS model_name FROM animes
          LEFT JOIN categories ON animes.category_id = categories.id
          LEFT JOIN models ON animes.model_id = models.id";
$stmt = $pdo->prepare($query);
$stmt->execute();
$voitures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Voitures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body style="background-color: var(--bg-color); color: var(--text-color);">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark rounded-bottom">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="crud.php">Gestion des Utilisateurs</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 style="color: var(--accent-color);">Gestion des Voitures</h1>

        <div class="row">
            <div class="col-md-6">
                <div class="form-container">
                    <h2 style="color: var(--accent-color);">Ajouter une Nouvelle Voiture</h2>
                    <form action="crudimage.php" method="POST" enctype="multipart/form-data">
                        <!-- CSRF Token -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description de la voiture</label>
                            <textarea class="form-control rounded" id="description" name="description" placeholder="Description de la voiture" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Marque de la voiture</label>
                            <select class="form-select rounded" id="category_id" name="category_id" required onchange="loadModels()">
                                <option value="">Sélectionner une marque</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="model_id" class="form-label">Modèle de la voiture</label>
                            <select class="form-select rounded" id="model_id" name="model_id" required>
                                <option value="">Sélectionner un modèle</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image (JPG, JPEG, PNG)</label>
                            <input type="file" class="form-control rounded" id="image" name="image" accept="image/jpeg, image/jpg, image/png" required>
                        </div>
                        <button type="submit" name="create_voiture" class="btn btn-primary rounded-pill">Ajouter la voiture</button>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-container">
                    <h2 style="color: var(--accent-color);">Modifier une Voiture</h2>
                    <form action="crudimage.php" method="POST" enctype="multipart/form-data">
                        <!-- CSRF Token -->
                        <div class="mb-3">
                            <label for="id" class="form-label">Sélectionner une Voiture</label>
                            <select class="form-select rounded" id="id" name="id" required>
                                <?php foreach ($voitures as $voiture): ?>
                                    <option value="<?php echo htmlspecialchars($voiture['id']); ?>"><?php echo htmlspecialchars($voiture['model_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Nouvelle Description de la voiture</label>
                            <textarea class="form-control rounded" id="description" name="description" placeholder="Nouvelle description de la voiture" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Marque de la voiture</label>
                            <select class="form-select rounded" id="category_id" name="category_id" required onchange="loadModels()">
                                <option value="">Sélectionner une marque</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="model_id" class="form-label">Modèle de la voiture</label>
                            <select class="form-select rounded" id="model_id" name="model_id" required>
                                <option value="">Sélectionner un modèle</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Nouvelle Image (JPG, JPEG, PNG)</label>
                            <input type="file" class="form-control rounded" id="image" name="image" accept="image/jpeg, image/jpg, image/png" required>
                        </div>
                        <button type="submit" name="update_voiture" class="btn btn-primary rounded-pill">Mettre à jour la voiture</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-container">
                    <h2 style="color: var(--accent-color);">Créer un Nouveau Modèle</h2>
                    <form action="crudimage.php" method="POST">
                        <!-- CSRF Token -->
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Marque de la voiture</label>
                            <select class="form-select rounded" id="category_id" name="category_id" required>
                                <option value="">Sélectionner une marque</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo htmlspecialchars($category['id']); ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="model_name" class="form-label">Nom du Modèle</label>
                            <input type="text" class="form-control rounded" id="model_name" name="model_name" placeholder="Nom du Modèle" required>
                        </div>
                        <button type="submit" name="create_model" class="btn btn-primary rounded-pill">Créer le Modèle</button>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-container">
                    <h2 style="color: var(--accent-color);">Créer une Nouvelle Marque</h2>
                    <form action="crudimage.php" method="POST">
                        <!-- CSRF Token -->
                        <div class="mb-3">
                            <label for="marque_name" class="form-label">Nom de la Marque</label>
                            <input type="text" class="form-control rounded" id="marque_name" name="marque_name" placeholder="Nom de la Marque" required>
                        </div>
                        <button type="submit" name="create_marque" class="btn btn-primary rounded-pill">Créer la Marque</button>
                    </form>
                </div>
            </div>
        </div>

        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Modèle</th>
                        <th>Description</th>
                        <th>Marque</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($voitures as $voiture): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($voiture['id']); ?></td>
                        <td><?php echo htmlspecialchars($voiture['model_name'] ?? ''); ?></td>
                        <td class="description-text" style="white-space: pre-wrap; word-break: break-word;">
                            <?php echo nl2br(htmlspecialchars($voiture['description'])); ?>
                        </td>
                        <td><?php echo htmlspecialchars($voiture['category_name'] ?? ''); ?></td>
                        <td><img src="data:image/png;base64,<?php echo base64_encode($voiture['image']); ?>" alt="<?php echo htmlspecialchars($voiture['model_name']); ?>" style="max-width: 100px; border-radius: 0.5rem;"></td>
                        <td>
                            <form action="crudimage.php" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette voiture ?');">
                                <!-- CSRF Token -->
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($voiture['id']); ?>">
                                <button type="submit" name="delete_voiture" class="btn btn-danger btn-sm rounded-pill">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadModels() {
            var categoryId = document.getElementById('category_id').value;
            var modelSelect = document.getElementById('model_id');

            // Clear existing options
            modelSelect.innerHTML = '<option value="">Sélectionner un modèle</option>';

            if (categoryId) {
                // Make an AJAX request to fetch models
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'get_models.php?category_id=' + encodeURIComponent(categoryId), true); // Added encodeURIComponent
                xhr.onload = function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        var models = JSON.parse(xhr.responseText);
                        models.forEach(function(model) {
                            var option = document.createElement('option');
                            option.value = model.id;
                            option.text = model.name;
                            modelSelect.add(option);
                        });
                    } else {
                        console.error('Request failed with status:', xhr.status);
                    }
                };
                xhr.onerror = function() {
                    console.error('Request failed');
                };
                xhr.send();
            }
        }
    </script>
</body>
</html>