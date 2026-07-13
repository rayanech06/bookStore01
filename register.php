<?php
require_once 'includes/config.php';
$pageTitle  = 'Inscription';
$activePage = 'login';
$cssPath    = '';

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom      = trim($_POST['nom']      ?? '');
    $prenom   = trim($_POST['prenom']   ?? '');
    $email    = trim($_POST['email']    ?? '');
    $tel      = trim($_POST['telephone']?? '');
    $adresse  = trim($_POST['adresse']  ?? '');
    $mdp      = trim($_POST['mot_de_passe'] ?? '');
    $mdp2     = trim($_POST['mdp2']     ?? '');

    if (!$nom || !$prenom || !$email || !$mdp) {
        $error = 'Veuillez remplir tous les champs obligatoires (*).';
    } elseif ($mdp !== $mdp2) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($mdp) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        $db = getDB();
        $chk = $db->prepare('SELECT id FROM clients WHERE email = ?');
        $chk->execute([$email]);
        if ($chk->fetch()) {
            $error = 'Cet email est déjà utilisé.';
        } else {
            $db->prepare('INSERT INTO clients (nom,prenom,email,telephone,adresse,mot_de_passe) VALUES (?,?,?,?,?,SHA2(?,256))')
               ->execute([$nom,$prenom,$email,$tel,$adresse,$mdp]);
            $success = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter ♡';
        }
    }
}

include 'includes/header.php';
?>

<div style="min-height:60vh;display:flex;align-items:center;justify-content:center;padding:40px 20px;">
  <div class="form-card" style="max-width:560px;">
    <div style="text-align:center;font-size:2.5rem;margin-bottom:10px;">🌸</div>
    <h2 class="form-title">Créer un compte</h2>
    <p class="form-subtitle">Rejoignez notre communauté de lecteurs ♡</p>

    <?php if ($error):   ?><div class="alert alert-error">⚠ <?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?> <a href="login.php">→ Se connecter</a></div><?php endif; ?>

    <form method="POST">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Prénom *</label>
          <input type="text" name="prenom" class="form-input" value="<?= e($_POST['prenom']??'') ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Nom *</label>
          <input type="text" name="nom" class="form-input" value="<?= e($_POST['nom']??'') ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">📧 Email *</label>
        <input type="email" name="email" class="form-input" value="<?= e($_POST['email']??'') ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">📞 Téléphone</label>
        <input type="text" name="telephone" class="form-input" value="<?= e($_POST['telephone']??'') ?>" placeholder="06XXXXXXXX">
      </div>
      <div class="form-group">
        <label class="form-label">📍 Adresse</label>
        <textarea name="adresse" class="form-textarea" rows="2" placeholder="Ville, Maroc"><?= e($_POST['adresse']??'') ?></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">🔒 Mot de passe *</label>
          <input type="password" name="mot_de_passe" class="form-input" required>
        </div>
        <div class="form-group">
          <label class="form-label">🔒 Confirmer *</label>
          <input type="password" name="mdp2" class="form-input" required>
        </div>
      </div>
      <button type="submit" class="btn btn-pink" style="width:100%;justify-content:center;margin-top:8px">
        🌸 Créer mon compte
      </button>
    </form>
    <span class="form-link">Déjà inscrit ? <a href="login.php">Se connecter ♡</a></span>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
