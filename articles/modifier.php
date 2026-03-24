<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../connexion_db.php';

if (!isset($_SESSION['utilisateur']) || !in_array($_SESSION['utilisateur']['role'], ['editeur', 'administrateur'])) {
    header('Location: ' . $base_url . 'connexion/connexion.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: ../accueil.php');
    exit;
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
$stmt->bindValue(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$article = $stmt->fetch();

if (!$article) {
    header('Location: ../accueil.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom ASC")->fetchAll();

$erreurs = [];
$succes  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre   = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $cat_id  = (int)($_POST['categorie_id'] ?? 0);
    $image   = $article['image']; // conserver l'ancienne par défaut

    if (empty($titre))   $erreurs[] = "Le titre est obligatoire.";
    elseif (strlen($titre) > 255) $erreurs[] = "Le titre ne doit pas dépasser 255 caractères.";
    if (empty($contenu)) $erreurs[] = "Le contenu est obligatoire.";
    if ($cat_id <= 0)    $erreurs[] = "Veuillez sélectionner une catégorie.";

    // Nouvelle image uploadée
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file    = $_FILES['image'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max     = 2 * 1024 * 1024;

        if (!in_array($ext, $allowed)) {
            $erreurs[] = "Format non autorisé. Utilisez JPG, PNG, GIF ou WEBP.";
        } elseif ($file['size'] > $max) {
            $erreurs[] = "L'image ne doit pas dépasser 2 Mo.";
        } else {
            $nom = uniqid('img_') . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], '../uploads/' . $nom)) {
                // Supprimer l'ancienne image
                if ($article['image'] && file_exists('../uploads/' . $article['image'])) {
                    unlink('../uploads/' . $article['image']);
                }
                $image = $nom;
            } else {
                $erreurs[] = "Erreur lors de l'upload.";
            }
        }
    }

    // Supprimer l'image si case cochée
    if (isset($_POST['supprimer_image']) && !isset($_FILES['image']['name'][0])) {
        if ($article['image'] && file_exists('../uploads/' . $article['image'])) {
            unlink('../uploads/' . $article['image']);
        }
        $image = null;
    }

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("
            UPDATE articles
            SET titre = :titre, contenu = :contenu, categorie_id = :categorie_id, image = :image
            WHERE id = :id
        ");
        $stmt->bindValue(':titre', $titre);
        $stmt->bindValue(':contenu', $contenu);
        $stmt->bindValue(':categorie_id', $cat_id, PDO::PARAM_INT);
        $stmt->bindValue(':image', $image);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Rafraîchir
        $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = :id");
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $article = $stmt->fetch();
        $succes  = true;
    }
}

$val_titre  = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['titre'] ?? '')   : $article['titre'];
$val_contenu = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['contenu'] ?? '') : $article['contenu'];
$val_cat_id  = $_SERVER['REQUEST_METHOD'] === 'POST' ? (int)($_POST['categorie_id'] ?? 0) : (int)$article['categorie_id'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un article — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require '../menu.php'; ?>
<div class="main">
    <div class="form-card">
        <h2>Modifier l'article</h2>

        <?php if ($succes): ?>
            <div class="alert alert-succes">Article modifié. <a href="detail.php?id=<?= $id ?>">Voir l'article</a></div>
        <?php endif; ?>

        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-erreur"><ul><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form id="form-modifier" method="POST" action="modifier.php?id=<?= $id ?>" enctype="multipart/form-data" novalidate>

            <div class="form-group">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($val_titre) ?>" maxlength="255">
                <span class="form-error" id="err-titre"></span>
            </div>

            <div class="form-group">
                <label for="categorie_id">Catégorie *</label>
                <select id="categorie_id" name="categorie_id">
                    <option value="">-- Choisir une catégorie --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= $val_cat_id === (int)$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="form-error" id="err-categorie"></span>
            </div>

            <div class="form-group">
                <label for="contenu">Contenu *</label>
                <textarea id="contenu" name="contenu" rows="10"><?= htmlspecialchars($val_contenu) ?></textarea>
                <span class="form-error" id="err-contenu"></span>
            </div>

            <div class="form-group">
                <label>Image actuelle</label>
                <?php if ($article['image']): ?>
                    <div class="image-preview">
                        <img src="../uploads/<?= htmlspecialchars($article['image']) ?>" alt="Image actuelle">
                    </div>
                    <label class="checkbox-label">
                        <input type="checkbox" name="supprimer_image" value="1">
                        Supprimer l'image actuelle
                    </label>
                <?php else: ?>
                    <p class="text-muted">Aucune image.</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="image">Nouvelle image <small>(optionnel — JPG, PNG, GIF, WEBP — max 2 Mo)</small></label>
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp">
                <span class="form-error" id="err-image"></span>
                <div id="image-preview-new" class="image-preview" style="display:none;">
                    <img id="preview-img" src="" alt="Aperçu">
                </div>
            </div>

            <div class="form-actions">
                <a href="detail.php?id=<?= $id ?>" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('form-modifier').addEventListener('submit', function(e) {
    let valide = true;
    document.querySelectorAll('.form-error').forEach(el => el.textContent = '');

    const titre     = document.getElementById('titre').value.trim();
    const contenu   = document.getElementById('contenu').value.trim();
    const categorie = document.getElementById('categorie_id').value;
    const image     = document.getElementById('image').files[0];

    if (!titre) { document.getElementById('err-titre').textContent = 'Le titre est obligatoire.'; valide = false; }
    else if (titre.length > 255) { document.getElementById('err-titre').textContent = 'Max 255 caractères.'; valide = false; }
    if (!categorie) { document.getElementById('err-categorie').textContent = 'Veuillez sélectionner une catégorie.'; valide = false; }
    if (!contenu)   { document.getElementById('err-contenu').textContent = 'Le contenu est obligatoire.'; valide = false; }

    if (image) {
        const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowed.includes(image.type)) { document.getElementById('err-image').textContent = 'Format non autorisé.'; valide = false; }
        else if (image.size > 2 * 1024 * 1024) { document.getElementById('err-image').textContent = "Max 2 Mo."; valide = false; }
    }

    if (!valide) e.preventDefault();
});

document.getElementById('image').addEventListener('change', function() {
    const preview = document.getElementById('image-preview-new');
    const img     = document.getElementById('preview-img');
    if (this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => { img.src = e.target.result; preview.style.display = 'block'; };
        reader.readAsDataURL(this.files[0]);
    } else {
        preview.style.display = 'none';
    }
});
</script>
</body>
</html>