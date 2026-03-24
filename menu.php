<?php
// menu.php — inclus dans le <body> de chaque page
require_once __DIR__ . '/config.php';

$role         = $_SESSION['utilisateur']['role'] ?? null;
$pageCourante = $_SERVER['PHP_SELF'];
?>

<nav>
    <a href="<?= $base_url ?>accueil.php" class="nav-brand">📰 FlashCas</a>

    <ul>
        <li>
            <a href="<?= $base_url ?>accueil.php"
               class="<?= strpos($pageCourante, 'accueil.php') !== false ? 'actif' : '' ?>">
                Accueil
            </a>
        </li>

        <?php if ($role === 'editeur' || $role === 'administrateur'): ?>
            <li>
                <a href="<?= $base_url ?>articles/ajouter.php"
                   class="<?= strpos($pageCourante, 'articles/ajouter') !== false ? 'actif' : '' ?>">
                    Nouvel article
                </a>
            </li>
            <li>
                <a href="<?= $base_url ?>categories/liste.php"
                   class="<?= strpos($pageCourante, 'categories/') !== false ? 'actif' : '' ?>">
                    Catégories
                </a>
            </li>
        <?php endif; ?>

        <?php if ($role === 'administrateur'): ?>
            <li>
                <a href="<?= $base_url ?>utilisateurs/liste.php"
                   class="<?= strpos($pageCourante, 'utilisateurs/') !== false ? 'actif' : '' ?>">
                    Utilisateurs
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <div class="nav-user">
        <?php if ($role): ?>
            Bonjour, <span><?= htmlspecialchars($_SESSION['utilisateur']['prenom'] . ' ' . $_SESSION['utilisateur']['nom']) ?></span>
            &nbsp;·&nbsp;
            <a href="<?= $base_url ?>deconnexion.php">Déconnexion</a>
        <?php else: ?>
            <a href="<?= $base_url ?>connexion/connexion.php">Connexion</a>
        <?php endif; ?>
    </div>
</nav>