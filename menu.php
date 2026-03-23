<?php
// menu.php — inclus dans le <body> de chaque page
// Affiche la navbar en fonction du rôle de l'utilisateur connecté

$base = '/projetBackend/Site-web/';

// Rôle courant (null si visiteur non connecté)
$role = $_SESSION['utilisateur']['role'] ?? null;

// Page active pour surligner le lien courant
$pageCourante = $_SERVER['PHP_SELF'];
?>

<nav>
    <a href="<?= $base ?>accueil.php" class="nav-brand">📰 ESPACTU</a>

    <ul>
        <!-- Liens accessibles à tous -->
        <li>
            <a href="<?= $base ?>accueil.php"
               class="<?= strpos($pageCourante, 'accueil.php') !== false ? 'actif' : '' ?>">
                Accueil
            </a>
        </li>

        <!-- Liens réservés aux éditeurs et administrateurs -->
        <?php if ($role === 'editeur' || $role === 'administrateur'): ?>
            <li>
                <a href="<?= $base ?>articles/ajouter.php"
                   class="<?= strpos($pageCourante, 'articles/ajouter.php') !== false ? 'actif' : '' ?>">
                    Nouvel article
                </a>
            </li>
            <li>
                <a href="<?= $base ?>categories/liste.php"
                   class="<?= strpos($pageCourante, 'categories/') !== false ? 'actif' : '' ?>">
                    Catégories
                </a>
            </li>
        <?php endif; ?>

        <!-- Liens réservés aux administrateurs uniquement -->
        <?php if ($role === 'administrateur'): ?>
            <li>
                <a href="<?= $base ?>utilisateurs/liste.php"
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
            <a href="<?= $base ?>deconnexion.php">Déconnexion</a>
        <?php else: ?>
            <a href="<?= $base ?>connexion/connexion.php">Connexion</a>
        <?php endif; ?>
    </div>
</nav>