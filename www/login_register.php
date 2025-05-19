<?php
session_start();

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

$action = $_GET['action'] ?? 'login';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_start();
    $inputUsername = htmlspecialchars($_POST['username']);
    $inputPassword = htmlspecialchars($_POST['password']);

    if ($action === 'register') {
        $confirmPassword = htmlspecialchars($_POST['confirm_password']);
        if ($inputPassword !== $confirmPassword) {
            $error_message = "Les mots de passe ne correspondent pas.";
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/', $inputPassword)) {
            $error_message = "Le mot de passe doit comporter au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.";
        } else {
            $hashedPassword = password_hash($inputPassword, PASSWORD_BCRYPT);
            $query = "INSERT INTO users (username, password, role) VALUES (:username, :password, 'user')";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':username', $inputUsername, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            if ($stmt->execute()) {
                ob_end_clean();
                header("Location: login_register.php?action=login");
                exit();
            } else {
                $error_message = "Erreur lors de l'inscription.";
            }
        }
    } elseif ($action === 'login') {
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':username', $inputUsername, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (!$user['active']) {
                $error_message = "Votre compte a été désactivé.";
            } elseif (password_verify($inputPassword, $user['password'])) {
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                ob_end_clean();
                header("Location: index.php");
                exit();
            } else {
                $error_message = "Nom d'utilisateur ou mot de passe incorrect.";
            }
        } else {
            $error_message = "Nom d'utilisateur ou mot de passe incorrect.";
        }
    }
    ob_end_clean();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($action); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="form-container">
                    <h2 class="mb-4"><?php echo ucfirst($action); ?></h2>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    <form action="login_register.php?action=<?php echo $action; ?>" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <?php if ($action === 'register'): ?>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary"><?php echo ucfirst($action); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>