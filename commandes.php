<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? 'Book Store' ?> — Books · Dreams · Adventures</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Lato:wght@300;400;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $cssPath ?? '' ?>css/style.css">
</head>
<body>

<header class="site-header">
  <div class="header-inner">
    <a href="index.php" class="logo">
      <span class="logo-book">📖</span>
      <div>
        <div class="logo-text">Book Store</div>
        <div class="logo-tagline">Books · Dreams · Adventures</div>
      </div>
    </a>
    <nav class="header-nav">
      <a href="index.php"      class="nav-link <?= ($activePage??'') === 'shop'   ? 'active' : '' ?>">🛍️ Boutique</a>
      <a href="commandes.php"  class="nav-link <?= ($activePage??'') === 'orders'  ? 'active' : '' ?>">📋 Commandes</a>
      <a href="admin.php"      class="nav-link <?= ($activePage??'') === 'admin'   ? 'active' : '' ?>">⚙️ Admin</a>
      <?php if (isset($_SESSION['client_id'])): ?>
        <span class="nav-client">👤 <?= e($_SESSION['client_nom']) ?></span>
        <a href="logout.php" class="nav-link">🚪 Déconnexion</a>
      <?php else: ?>
        <a href="login.php" class="nav-link <?= ($activePage??'') === 'login' ? 'active' : '' ?>">🔑 Connexion</a>
      <?php endif; ?>
      <a href="panier.php" class="cart-pill">
        🛒 <span id="cartBadge"><?= isset($_SESSION['panier']) ? array_sum(array_column($_SESSION['panier'],'qte')) : 0 ?></span>
      </a>
    </nav>
  </div>
</header>
