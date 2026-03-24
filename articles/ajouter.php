<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../connexion_db.php';

if (!isset($_SESSION['utilisateur']) || !in_array($_SESSION['utilisateur']['role'], ['editeur', 'administrateur'])) {
    header('Location: ' . $base_url . 'connexion/connexion.php');
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom ASC")->fetchAll();

$erreurs = [];
$succes  = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre   = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $cat_id  = (int)($_POST['categorie_id'] ?? 0);
    $image   = null;

    if (empty($titre)) {
        $erreurs[] = "Le titre est obligatoire.";
    } elseif (strlen($titre) > 255) {
        $erreurs[] = "Le titre ne doit pas dépasser 255 caractères.";
    }

    if (empty($contenu)) $erreurs[] = "Le contenu est obligatoire.";
    if ($cat_id <= 0)    $erreurs[] = "Veuillez sélectionner une catégorie.";

    // Traitement de l'image
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
                $image = $nom;
            } else {
                $erreurs[] = "Erreur lors de l'upload.";
            }
        }
    }

    if (empty($erreurs)) {
        $stmt = $pdo->prepare("
            INSERT INTO articles (titre, contenu, categorie_id, auteur_id, image, date_publication)
            VALUES (:titre, :contenu, :categorie_id, :auteur_id, :image, NOW())
        ");
        $stmt->bindValue(':titre', $titre);
        $stmt->bindValue(':contenu', $contenu);
        $stmt->bindValue(':categorie_id', $cat_id, PDO::PARAM_INT);
        $stmt->bindValue(':auteur_id', $_SESSION['utilisateur']['id'], PDO::PARAM_INT);
        $stmt->bindValue(':image', $image);
        $stmt->execute();
        $succes = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un article — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php require '../menu.php'; ?>
<div class="main">
    <div class="form-card">
        <h2>Ajouter un article</h2>

        <?php if ($succes): ?>
            <div class="alert alert-succes">Article publié. <a href="../accueil.php">Voir les articles</a></div>
        <?php endif; ?>

        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-erreur"><ul><?php foreach ($erreurs as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>

        <form id="form-article" method="POST" action="ajouter.php" enctype="multipart/form-data" novalidate>

            <div class="form-group">
                <label for="titre">Titre *</label>
                <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" maxlength="255" placeholder="Titre de l'article">
                <span class="form-error" id="err-titre"></span>
            </div>

            <div class="form-group">
                <label for="categorie_id">Catégorie *</label>
                <select id="categorie_id" name="categorie_id">
                    <option value="">-- Choisir une catégorie --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= (isset($_POST['categorie_id']) && (int)$_POST['categorie_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <span class="form-error" id="err-categorie"></span>
            </div>

            <div class="form-group">
                <label for="contenu">Contenu *</label>
                <textarea id="contenu" name="contenu" rows="10" placeholder="Rédigez le contenu..."><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>
                <span class="form-error" id="err-contenu"></span>
            </div>

            <div class="form-group">
                <label for="image">Image <small>(optionnel — JPG, PNG, GIF, WEBP — max 2 Mo)</small></label>
                <input type="file" id="image" name="image" accept=".jpg,.jpeg,.png,.gif,.webp">
                <span class="form-error" id="err-image"></span>
                <div id="image-preview" class="image-preview" style="display:none;">
                    <img id="preview-img" src="" alt="Aperçu">
                </div>
            </div>

            <div class="form-actions">
                <a href="../accueil.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Publier l'article</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('form-article').addEventListener('submit', function(e) {
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

// Aperçu image
document.getElementById('image').addEventListener('change', function() {
    const preview = document.getElementById('image-preview');
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