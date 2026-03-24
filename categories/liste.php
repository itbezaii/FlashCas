<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../connexion_db.php';

// Accès réservé aux éditeurs et administrateurs
if (!isset($_SESSION['utilisateur']) || !in_array($_SESSION['utilisateur']['role'], ['editeur', 'administrateur'])) {
    header('Location: ' . $base_url . 'connexion/connexion.php');
    exit;
}

$categories = $pdo->query("
    SELECT categories.*, COUNT(articles.id) AS nb_articles
    FROM categories
    LEFT JOIN articles ON articles.categorie_id = categories.id
    GROUP BY categories.id
    ORDER BY categories.nom ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catégories — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require '../menu.php'; ?>

<div class="main">
    <div class="list-header">
        <h2>Gestion des catégories</h2>
        <a href="ajouter.php" class="btn btn-primary">+ Nouvelle catégorie</a>
    </div>

    <?php if (empty($categories)): ?>
        <p class="no-result">Aucune catégorie enregistrée.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Articles</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $i => $cat): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($cat['nom']) ?></td>
                        <td><?= (int)$cat['nb_articles'] ?></td>
                        <td class="actions">
                            <a href="modifier.php?id=<?= (int)$cat['id'] ?>" class="btn btn-secondary btn-sm">Modifier</a>
                            <?php if ((int)$cat['nb_articles'] === 0): ?>
                                <a href="supprimer.php?id=<?= (int)$cat['id'] ?>" class="btn btn-danger btn-sm">Supprimer</a>
                            <?php else: ?>
                                <span class="btn btn-disabled btn-sm" title="Impossible : des articles utilisent cette catégorie">Supprimer</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

</body>
</html>