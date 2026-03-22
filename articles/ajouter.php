<?php
session_start();
require '../connexion_db.php';

if (!isset($_SESSION['role']) || 
    !in_array($_SESSION['role'], ['editeur', 'admin'])) {
    header('Location: ../connexion.php');
    exit;
}

$erreurs = [];
$succes = false;

$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titre       = trim($_POST['titre'] ?? '');
    $contenu     = trim($_POST['contenu'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $categorie_id = (int)($_POST['categorie_id'] ?? 0);

    // Validation PHP
    if (empty($titre))        $erreurs[] = "Le titre est obligatoire.";
    if (strlen($titre) > 255) $erreurs[] = "Le titre ne doit pas dépasser 255 caractères.";
    if (empty($contenu))      $erreurs[] = "Le contenu est obligatoire.";
    if (empty($description))  $erreurs[] = "La description est obligatoire.";
    if ($categorie_id === 0)  $erreurs[] = "Veuillez choisir une catégorie.";


    if (empty($erreurs)) {
        $stmt = $pdo->prepare("
            INSERT INTO articles (titre, description, contenu, categorie_id, auteur_id, date_publication)
            VALUES (:titre, :description, :contenu, :categorie_id, :auteur_id, NOW())
        ");
        $stmt->execute([
            ':titre'        => $titre,
            ':description'  => $description,
            ':contenu'      => $contenu,
            ':categorie_id' => $categorie_id,
            ':auteur_id'    => $_SESSION['id']
        ]);
        $succes = true;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un article</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/form.css">
</head>
<body>

<?php require '../menu.php'; ?>

<div class="form-container">
    <div class="form-header">
        <h1>Ajouter un article</h1>
        <a href="../accueil.php" class="btn-retour">← Retour</a>
    </div>

    <?php if ($succes): ?>
        <div class="alert alert-succes">
            Article publié avec succès !
            <a href="../accueil.php">Voir l'accueil</a>
        </div>
    <?php endif; ?>

    <?php if (!empty($erreurs)): ?>
        <div class="alert alert-erreur">
            <ul>
                <?php foreach ($erreurs as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form id="formArticle" method="POST" action="">

        <div class="form-group">
            <label for="titre">Titre *</label>
            <input 
                type="text" 
                id="titre" 
                name="titre" 
                placeholder="Titre de l'article"
                value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>"
                maxlength="255"
            >
            <span class="erreur-js" id="err-titre"></span>
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <input 
                type="text" 
                id="description" 
                name="description" 
                placeholder="Résumé en une phrase"
                value="<?= htmlspecialchars($_POST['description'] ?? '') ?>"
            >
            <span class="erreur-js" id="err-description"></span>
        </div>

        <div class="form-group">
            <label for="categorie_id">Catégorie *</label>
            <select id="categorie_id" name="categorie_id">
                <option value="0">-- Choisir une catégorie --</option>
                <?php foreach ($categories as $cat): ?>
                    <option 
                        value="<?= $cat['id'] ?>"
                        <?= (isset($_POST['categorie_id']) && $_POST['categorie_id'] == $cat['id']) ? 'selected' : '' ?>
                    >
                        <?= htmlspecialchars($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <span class="erreur-js" id="err-categorie"></span>
        </div>

        <div class="form-group">
            <label for="contenu">Contenu *</label>
            <textarea 
                id="contenu" 
                name="contenu" 
                rows="10" 
                placeholder="Écrivez le contenu complet de l'article..."
            ><?= htmlspecialchars($_POST['contenu'] ?? '') ?></textarea>
            <span class="erreur-js" id="err-contenu"></span>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Publier l'article</button>
            <a href="../accueil.php" class="btn-annuler">Annuler</a>
        </div>

    </form>
</div>

<script>
document.getElementById('formArticle').addEventListener('submit', function(e) {
    let valide = true;

    document.querySelectorAll('.erreur-js').forEach(el => el.textContent = '');

    const titre       = document.getElementById('titre').value.trim();
    const description = document.getElementById('description').value.trim();
    const categorie   = document.getElementById('categorie_id').value;
    const contenu     = document.getElementById('contenu').value.trim();

    if (titre === '') {
        document.getElementById('err-titre').textContent = 'Le titre est obligatoire.';
        valide = false;
    } else if (titre.length > 255) {
        document.getElementById('err-titre').textContent = 'Le titre est trop long.';
        valide = false;
    }

    if (description === '') {
        document.getElementById('err-description').textContent = 'La description est obligatoire.';
        valide = false;
    }

    if (categorie === '0') {
        document.getElementById('err-categorie').textContent = 'Veuillez choisir une catégorie.';
        valide = false;
    }

    if (contenu === '') {
        document.getElementById('err-contenu').textContent = 'Le contenu est obligatoire.';
        valide = false;
    }

    if (!valide) e.preventDefault();
});
</script>

</body>
</html>