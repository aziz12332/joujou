<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>JouJou — Setup</title>
<style>
body{font-family:sans-serif;max-width:480px;margin:80px auto;padding:20px;background:#fdf8f2;color:#2d1a22}
h2{color:#7a2840;margin-bottom:1rem}
input,button{width:100%;padding:12px;margin-bottom:12px;border-radius:8px;border:1.5px solid #f0c4d4;font-size:14px;box-sizing:border-box}
button{background:#7a2840;color:white;border:none;cursor:pointer;font-size:14px;letter-spacing:1px}
button:hover{background:#d4708a}
.msg{padding:12px;border-radius:8px;margin-bottom:12px}
.ok{background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9}
.err{background:#fce8e0;color:#b71c1c;border:1px solid #f4b8a8}
.warn{background:#fff8e1;color:#f57f17;border:1px solid #ffe082;font-size:13px}
</style>
</head>
<body>
<h2>🌸 JouJou — Configuration initiale</h2>

<?php
require_once 'api/config.php';

$done   = false;
$errors = [];
$msg    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pwd  = trim($_POST['password']  ?? '');
    $pwd2 = trim($_POST['password2'] ?? '');

    if (strlen($pwd) < 8)          $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    if ($pwd !== $pwd2)             $errors[] = 'Les mots de passe ne correspondent pas.';

    if (empty($errors)) {
        $hash = password_hash($pwd, PASSWORD_BCRYPT, ['cost' => 12]);
        $db   = getDB();
        $stmt = $db->prepare('UPDATE admin SET password_hash = ? WHERE username = ?');
        $stmt->execute([$hash, 'admin']);
        $done = true;
    }
}
?>

<?php if ($done): ?>
  <div class="msg ok">✅ Mot de passe mis à jour avec succès !<br>Vous pouvez maintenant vous connecter au panneau d'administration.<br><strong>Supprimez ce fichier setup.php de votre serveur !</strong></div>
  <a href="admin-jj-k9x72m.html"><button type="button">→ Aller au panneau admin</button></a>
<?php else: ?>
  <?php if (!empty($errors)): ?>
    <div class="msg err"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
  <?php endif; ?>
  <div class="msg warn">⚠️ Définissez votre mot de passe administrateur, puis supprimez ce fichier setup.php de votre hébergement.</div>
  <form method="POST">
    <label>Nouveau mot de passe (min. 8 caractères)</label>
    <input type="password" name="password" placeholder="Mot de passe..." required>
    <label>Confirmer le mot de passe</label>
    <input type="password" name="password2" placeholder="Confirmer..." required>
    <button type="submit">Enregistrer le mot de passe</button>
  </form>
<?php endif; ?>

</body>
</html>
