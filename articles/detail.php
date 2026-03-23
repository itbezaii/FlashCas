<?php
session_start();
require '../connexion_db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../accueil.php');
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    SELECT articles.*, categories.nom AS categorie,
           utilisateurs.nom AS auteur_nom, utilisateurs.prenom AS auteur_prenom
    FROM articles
    JOIN categories ON articles.categorie_id = categories.id
    JOIN utilisateurs ON articles.auteur_id = utilisateurs.id
    WHERE articles.id = :id
");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$article = $stmt->fetch();

if (!$article) {
    header('Location: ../accueil.php');
    exit;
}

$role = $_SESSION['utilisateur']['role'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['titre']) ?> — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require '../menu.php'; ?>

<div class="main">
    <div class="detail-card">
        <a href="../accueil.php" class="back-link">← Retour aux articles</a>

        <span class="badge"><?= htmlspecialchars($article['categorie']) ?></span>

        <h1 class="detail-title"><?= htmlspecialchars($article['titre']) ?></h1>

        <div class="detail-meta">
            Par <strong><?= htmlspecialchars($article['auteur_prenom'] . ' ' . $article['auteur_nom']) ?></strong>
            · Publié le <?= date('d/m/Y à H\hi', strtotime($article['date_publication'])) ?>
        </div>

        <!-- Image de l'article -->
        <?php if ($article['image']): ?>
            <div class="detail-image">
                <img src="../uploads/<?= htmlspecialchars($article['image']) ?>"
                     alt="<?= htmlspecialchars($article['titre']) ?>">
            </div>
        <?php endif; ?>

        <div class="detail-content">
            <?= nl2br(htmlspecialchars($article['contenu'])) ?>
        </div>

        <!-- Actions éditeur/admin -->
        <?php if ($role === 'editeur' || $role === 'administrateur'): ?>
            <div class="detail-actions">
                <a href="modifier.php?id=<?= $id ?>" class="btn btn-secondary">Modifier</a>
                <a href="supprimer.php?id=<?= $id ?>" class="btn btn-danger">Supprimer</a>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>