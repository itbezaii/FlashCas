<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/connexion_db.php';

$articles_par_page = 5;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

$cat_filtre = isset($_GET['cat']) && !empty(trim($_GET['cat'])) ? trim($_GET['cat']) : null;
$recherche  = isset($_GET['q']) && !empty(trim($_GET['q'])) ? trim($_GET['q']) : null;

// Catégories autorisées (whitelist)
$cats_autorisees = ['Technologie', 'Sport', 'Politique', 'Education', 'Culture'];
if ($cat_filtre && !in_array($cat_filtre, $cats_autorisees)) {
    $cat_filtre = null;
}

// Construction des conditions WHERE
$conditions = [];
$params     = [];

if ($cat_filtre) {
    $conditions[] = "categories.nom = :cat";
    $params[':cat'] = $cat_filtre;
}

if ($recherche) {
    $conditions[] = "(articles.titre LIKE :q OR articles.contenu LIKE :q2 OR categories.nom LIKE :q3)";
    $params[':q']  = '%' . $recherche . '%';
    $params[':q2'] = '%' . $recherche . '%';
    $params[':q3'] = '%' . $recherche . '%';
}

$where_sql = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";

// Compter le total
$stmt_total = $pdo->prepare("
    SELECT COUNT(*) FROM articles
    JOIN categories ON articles.categorie_id = categories.id
    JOIN utilisateurs ON articles.auteur_id = utilisateurs.id
    $where_sql
");
foreach ($params as $key => $val) {
    $stmt_total->bindValue($key, $val);
}
$stmt_total->execute();
$total = $stmt_total->fetchColumn();

$total_pages = max(1, ceil($total / $articles_par_page));
if ($page > $total_pages) $page = $total_pages;
$offset = ($page - 1) * $articles_par_page;

// Récupérer les articles
$stmt = $pdo->prepare("
    SELECT articles.*, categories.nom AS categorie,
           utilisateurs.nom AS auteur
    FROM articles
    JOIN categories ON articles.categorie_id = categories.id
    JOIN utilisateurs ON articles.auteur_id = utilisateurs.id
    $where_sql
    ORDER BY articles.date_publication DESC
    LIMIT :limite OFFSET :offset
");
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limite', $articles_par_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();

// Base URL pour la pagination (conserve le filtre et la recherche)
$query_params = [];
if ($cat_filtre) $query_params[] = "cat=" . urlencode($cat_filtre);
if ($recherche)  $query_params[] = "q=" . urlencode($recherche);
$query_base = $base_url . 'accueil.php?' . (empty($query_params) ? '' : implode('&', $query_params) . '&') . 'page=';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FlashCas — L'actualité en temps réel</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require 'menu.php'; ?>

<div class="hero">
    <h1>Toute l'actualité en temps réel</h1>
    <p>Articles vérifiés · Mis à jour chaque jour</p>

    <!-- Barre de recherche -->
    <form method="GET" action="<?= $base_url ?>accueil.php" class="search-form">
        <div class="search-bar">
            <input
                type="text"
                name="q"
                value="<?= htmlspecialchars($recherche ?? '') ?>"
                placeholder="Rechercher un article..."
                autocomplete="off"
            >
            <button type="submit">🔍</button>
        </div>
    </form>

    <!-- Filtres catégories -->
    <div class="cats">
        <a href="<?= $base_url ?>accueil.php<?= $recherche ? '?q=' . urlencode($recherche) : '' ?>"
           class="cat <?= !$cat_filtre ? 'active' : '' ?>">Tous</a>

        <?php foreach ($cats_autorisees as $c): ?>
            <a href="<?= $base_url ?>accueil.php?cat=<?= urlencode($c) ?><?= $recherche ? '&q=' . urlencode($recherche) : '' ?>"
               class="cat <?= $cat_filtre === $c ? 'active' : '' ?>">
                <?= htmlspecialchars($c) ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="main">

    <!-- Message de recherche actif -->
    <?php if ($recherche): ?>
        <p class="search-info">
            <?= $total ?> résultat(s) pour
            <strong>"<?= htmlspecialchars($recherche) ?>"</strong>
            <?= $cat_filtre ? ' dans <strong>' . htmlspecialchars($cat_filtre) . '</strong>' : '' ?>
            — <a href="<?= $base_url ?>accueil.php">Réinitialiser</a>
        </p>
    <?php endif; ?>

    <?php if (empty($articles)): ?>
        <p class="no-result">
            Aucun article trouvé
            <?= $recherche ? ' pour <strong>"' . htmlspecialchars($recherche) . '"</strong>' : '' ?>
            <?= $cat_filtre ? ' dans la catégorie <strong>' . htmlspecialchars($cat_filtre) . '</strong>' : '' ?>.
        </p>

    <?php else: ?>
        <?php foreach ($articles as $i => $article): ?>
            <div class="list-card"

                 onclick="location.href='<?= $base_url ?>articles/detail.php?id=<?= (int)$article['id'] ?>'">
                <div class="list-num">
                    <?= str_pad($i + 1 + $offset, 2, '0', STR_PAD_LEFT) ?>
                </div>
                <div class="list-body">
                    <p class="list-title"><?= htmlspecialchars($article['titre']) ?></p>
                    <div class="list-meta">
                        <span class="badge"><?= htmlspecialchars($article['categorie']) ?></span>
                        <span>
                            Par <?= htmlspecialchars($article['auteur']) ?>
                            · <?= date('d/m/Y', strtotime($article['date_publication'])) ?>
                        </span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= $query_base . ($page - 1) ?>" class="page-btn">← Précédent</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="<?= $query_base . $i ?>"
                   class="page-btn <?= $i === $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <span class="page-info">Page <?= $page ?> sur <?= $total_pages ?></span>

            <?php if ($page < $total_pages): ?>
                <a href="<?= $query_base . ($page + 1) ?>" class="page-btn">Suivant →</a>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
