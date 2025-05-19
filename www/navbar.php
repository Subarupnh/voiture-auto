<nav class="navbar navbar-expand-lg navbar-dark bg-dark rounded-bottom">
    <div class="container"> <!-- Classe 'container' pour centrer -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto"> <!-- Classe 'mx-auto' pour centrer les éléments de la navbar -->
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item"><a class="nav-link" href="welcome.php">Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></a></li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="crud.php">Gestion des Utilisateurs</a></li>
                        <li class="nav-item"><a class="nav-link" href="crudimage.php">Gestion des Voitures</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="logout.php?token=<?php echo $_SESSION['logout_token'] ?? ''; ?>">Déconnexion</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="login_register.php?action=login">Connexion</a></li>
                    <li class="nav-item"><a class="nav-link" href="login_register.php?action=register">Inscription</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>