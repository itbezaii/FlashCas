<?php
session_start();
require '../connexion_db.php';

// Si déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['utilisateur'])) {
    header('Location: ../accueil.php');
    exit;
}

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login = trim($_POST['login'] ?? '');
    $mdp   = $_POST['mot_de_passe'] ?? '';

    // Validation serveur — obligatoire pour la sécurité
    if (empty($login) || empty($mdp)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT id, nom, prenom, login, mot_de_passe, role
             FROM utilisateurs
             WHERE login = :login
             LIMIT 1'
        );
        $stmt->bindValue(':login', $login);
        $stmt->execute();
        $utilisateur = $stmt->fetch();

        if ($utilisateur && password_verify($mdp, $utilisateur['mot_de_passe'])) {
            // Connexion réussie — régénération de l'ID de session (protection fixation)
            session_regenerate_id(true);

            // Convention unique utilisée dans tout le projet
            $_SESSION['utilisateur'] = [
                'id'     => $utilisateur['id'],
                'nom'    => $utilisateur['nom'],
                'prenom' => $utilisateur['prenom'],
                'login'  => $utilisateur['login'],
                'role'   => $utilisateur['role'],
            ];

            header('Location: /projetBackend/FlashCas/accueil.php');
            exit;
        } else {
            $erreur = 'Login ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — ESPACTU</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php require '../menu.php'; ?>

<div class="main">
    <div class="form-card connexion-card">
        <h2>Connexion</h2>

        <?php if ($erreur): ?>
            <div class="alert alert-erreur">
                <?= htmlspecialchars($erreur) ?>
            </div>
        <?php endif; ?>

        <form id="form-connexion" method="POST" action="connexion.php" novalidate>

            <div class="form-group">
                <label for="login">Login</label>
                <input
                    type="text"
                    id="login"
                    name="login"
                    value="<?= htmlspecialchars($_POST['login'] ?? '') ?>"
                    autocomplete="username"
                    placeholder="Votre identifiant"
                >
                <span class="form-error" id="err-login"></span>
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Mot de passe</label>
                <input
                    type="password"
                    id="mot_de_passe"
                    name="mot_de_passe"
                    autocomplete="current-password"
                    placeholder="Votre mot de passe"
                >
                <span class="form-error" id="err-mdp"></span>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Se connecter</button>
            </div>

        </form>
    </div>
</div>

<script src="js/connexion.js"></script>

</body>
</html>