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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Protection CSRF (à implémenter)
  //if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
  //  die("Erreur de sécurité.");
  //}

    if (isset($_POST['deactivate'])) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $query = "UPDATE users SET active = FALSE WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['activate'])) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $query = "UPDATE users SET active = TRUE WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['change_password'])) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $new_password = $_POST['new_password'];
        if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $new_password)) {
            $error_message = "Le mot de passe doit comporter au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
        } else {
            $new_password_hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $query = "UPDATE users SET password = :password WHERE id = :id";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':password', $new_password_hashed, PDO::PARAM_STR);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    if (isset($_POST['change_role'])) {
        $id = filter_var($_POST['id'], FILTER_VALIDATE_INT);
        $new_role = $_POST['new_role'];
        // Validation du rôle
        if (!in_array($new_role, ['user', 'admin'])) {
            die("Rôle invalide."); // Ou gérer l'erreur d'une manière plus appropriée
        }
        $query = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':role', $new_role, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }

    if (isset($_POST['create_user'])) {
        $username = $_POST['username'];
         if (strlen($username) < 3 || strlen($username) > 50) {
            $error_message = "Le nom d'utilisateur doit comporter entre 3 et 50 caractères.";
         } else {
                $password = $_POST['password'];
               // Valider le mot de passe (complexité)
                if (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
                    $error_message = "Le mot de passe doit comporter au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
                } else {
                $password_hashed = password_hash($password, PASSWORD_BCRYPT);
                $role = $_POST['role'];
                $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, :role)";
                $stmt = $pdo->prepare($query);
                $stmt->bindParam(':username', $username, PDO::PARAM_STR);
                $stmt->bindParam(':password', $password_hashed, PDO::PARAM_STR);
                $stmt->bindParam(':role', $role, PDO::PARAM_STR);
                $stmt->execute();
             }
         }
    }
}

$query = "SELECT * FROM users";
$stmt = $pdo->prepare($query);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
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
                    <li class="nav-item"><a class="nav-link" href="crudimage.php">Gestion des Voitures</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h1 style="color: var(--accent-color);">Gestion des Utilisateurs</h1>

        <div class="form-container">
            <h2 style="color: var(--accent-color);">Créer un nouvel utilisateur</h2>
            <form action="crud.php" method="POST">
                <!-- <input type="hidden" name="csrf_token" value=""> à faire -->
                <div class="mb-3">
                    <label for="username" class="form-label">Nom d'utilisateur</label>
                    <input type="text" class="form-control rounded" id="username" name="username" placeholder="Nom d'utilisateur" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe</label>
                    <input type="password" class="form-control rounded" id="password" name="password" placeholder="Mot de passe" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Rôle</label>
                    <select class="form-select rounded" id="role" name="role" required>
                        <option value="user">Utilisateur</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>
                <button type="submit" name="create_user" class="btn btn-primary rounded-pill">Créer l'utilisateur</button>
            </form>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom d'utilisateur</th>
                        <th>Rôle</th>
                        <th>Actif</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td><?php echo $user['active'] ? 'Oui' : 'Non'; ?></td>
                            <td>
                                <form action="crud.php" method="POST" onsubmit="return confirm('Confirmer cette action?');" style="display:inline;">
                                     <!-- <input type="hidden" name="csrf_token" value=""> à faire -->
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                    <select name="new_role" class="form-select rounded">
                                        <option value="user" <?php if ($user['role'] === 'user') echo 'selected'; ?>>Utilisateur</option>
                                        <option value="admin" <?php if ($user['role'] === 'admin') echo 'selected'; ?>>Administrateur</option>
                                    </select>
                                    <button type="submit" name="change_role" class="btn btn-primary btn-sm rounded-pill">Changer le rôle</button>
                                </form>
                                <?php if ($user['active']): ?>
                                    <form action="crud.php" method="POST" onsubmit="return confirm('Confirmer cette action?');" style="display:inline;">
                                         <!-- <input type="hidden" name="csrf_token" value=""> à faire -->
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                        <button type="submit" name="deactivate" class="btn btn-danger btn-sm rounded-pill">Désactiver</button>
                                    </form>
                                <?php else: ?>
                                    <form action="crud.php" method="POST" onsubmit="return confirm('Confirmer cette action?');" style="display:inline;">
                                         <!-- <input type="hidden" name="csrf_token" value=""> à faire -->
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                        <button type="submit" name="activate" class="btn btn-success btn-sm rounded-pill">Activer</button>
                                    </form>
                                <?php endif; ?>
                                <form action="crud.php" method="POST" onsubmit="return confirm('Confirmer cette action?');" style="display:inline;">
                                     <!-- <input type="hidden" name="csrf_token" value=""> à faire -->
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($user['id']); ?>">
                                    <input type="password" name="new_password" class="form-control form-control-sm rounded" placeholder="Nouveau mot de passe" required>
                                    <button type="submit" name="change_password" class="btn btn-primary btn-sm rounded-pill">Changer le mot de passe</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>