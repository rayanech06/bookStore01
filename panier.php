<?php
require_once 'includes/config.php';
$pageTitle  = 'Mon Panier';
$activePage = 'panier';
$cssPath    = '';

// ── Retirer un article ────────────────────────────────────────
if (isset($_GET['retirer'])) {
    $rid = (int)$_GET['retirer'];
    $_SESSION['panier'] = array_values(
        array_filter($_SESSION['panier'] ?? [], fn($i) => $i['id'] != $rid)
    );
    header('Location: panier.php');
    exit;
}

// ── Vider le panier ───────────────────────────────────────────
if (isset($_GET['vider'])) {
    $_SESSION['panier'] = [];
    header('Location: panier.php');
    exit;
}

// ── Mettre à jour quantités ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    foreach ($_SESSION['panier'] as &$item) {
        $k = 'qte_' . $item['id'];
        if (isset($_POST[$k])) {
            $q = max(1, (int)$_POST[$k]);
            $item['qte'] = $q;
        }
    }
    header('Location: panier.php');
    exit;
}

// ── Passer commande ───────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commander'])) {
    if (empty($_SESSION['client_id'])) {
        header('Location: login.php?redirect=panier.php');
        exit;
    }
    if (!empty($_SESSION['panier'])) {
        $db    = getDB();
        $total = array_sum(array_map(fn($i) => $i['prix'] * $i['qte'], $_SESSION['panier']));
        $adresse = trim($_POST['adresse'] ?? '');

        $db->prepare('INSERT INTO commandes (client_id, total, adresse_livraison) VALUES (?,?,?)')
           ->execute([$_SESSION['client_id'], $total, $adresse]);
        $cmdId = $db->lastInsertId();

        $stmt = $db->prepare('INSERT INTO lignes_commande (commande_id, livre_id, quantite, prix_unitaire) VALUES (?,?,?,?)');
        foreach ($_SESSION['panier'] as $item) {
            $stmt->execute([$cmdId, $item['id'], $item['qte'], $item['prix']]);
            // Mettre à jour le stock
            $db->prepare('UPDATE livres SET stock = stock - ? WHERE id = ?')
               ->execute([$item['qte'], $item['id']]);
        }
        $_SESSION['panier'] = [];
        $_SESSION['success'] = "✅ Commande #$cmdId passée avec succès ! Merci ♡";
        header('Location: commandes.php');
        exit;
    }
}

$panier = $_SESSION['panier'] ?? [];
$total  = array_sum(array_map(fn($i) => $i['prix'] * $i['qte'], $panier));

include 'includes/header.php';
?>

<div class="panier-layout">
  <div class="page-header-row">
    <div>
      <h1 class="page-h1">🛒 Mon Panier <span style="color:var(--pink)">♡</span></h1>
      <p class="page-sub"><?= count($panier) ?> article(s) dans votre panier</p>
    </div>
    <?php if (!empty($panier)): ?>
      <a href="panier.php?vider=1" class="btn btn-ghost btn-sm"
         onclick="return confirm('Vider le panier ?')">🗑 Vider le panier</a>
    <?php endif; ?>
  </div>

  <?php if (empty($panier)): ?>
    <div class="empty-state">
      <span class="empty-icon">🛒</span>
      <h3>Votre panier est vide</h3>
      <p>Découvrez notre catalogue et ajoutez vos romans préférés ♡</p>
      <a href="index.php" class="btn btn-pink" style="margin-top:20px">📚 Voir le catalogue</a>
    </div>

  <?php else: ?>
    <form method="POST">
      <div class="table-card" style="margin-bottom:24px">
        <table class="panier-table">
          <thead>
            <tr>
              <th>Livre</th>
              <th>Prix unitaire</th>
              <th>Quantité</th>
              <th>Sous-total</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($panier as $item): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:12px;">
                  <?php if ($item['img']): ?>
                    <img src="<?= e($item['img']) ?>" alt=""
                         style="width:44px;height:60px;object-fit:cover;border-radius:6px;flex-shrink:0;"
                         onerror="this.style.display='none'">
                  <?php endif; ?>
                  <div>
                    <div style="font-family:'Playfair Display',serif;font-weight:700;font-size:.9rem;color:var(--brown)"><?= e($item['titre']) ?></div>
                  </div>
                </div>
              </td>
              <td style="font-weight:700;color:var(--brown-mid)"><?= number_format($item['prix'],2) ?> DH</td>
              <td>
                <input type="number" name="qte_<?= $item['id'] ?>" value="<?= $item['qte'] ?>"
                       min="1" max="10" class="qty-input">
              </td>
              <td style="font-family:'Playfair Display',serif;font-weight:700;color:var(--pink);font-size:1rem;">
                <?= number_format($item['prix'] * $item['qte'], 2) ?> DH
              </td>
              <td>
                <a href="panier.php?retirer=<?= $item['id'] ?>" class="remove-btn" title="Retirer"
                   style="width:28px;height:28px;border:1.5px solid var(--pink-light);background:var(--pink-pale)">✕</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div style="display:flex;gap:10px;margin-bottom:28px;">
        <button type="submit" name="update" class="btn btn-ghost btn-sm">🔄 Mettre à jour</button>
        <a href="index.php" class="btn btn-ghost btn-sm">← Continuer les achats</a>
      </div>
    </form>

    <!-- Récapitulatif & commande -->
    <div style="max-width:440px;margin-left:auto;">
      <div class="table-card">
        <div style="background:linear-gradient(135deg,var(--pink-pale),var(--cream));padding:16px 20px;border-bottom:1.5px dashed var(--pink-light);">
          <span style="font-family:'Playfair Display',serif;font-weight:700;color:var(--brown);font-size:1rem;">
            Récapitulatif de commande ♡
          </span>
        </div>
        <div style="padding:18px 20px;">
          <?php foreach ($panier as $item): ?>
          <div style="display:flex;justify-content:space-between;margin-bottom:8px;font-size:.85rem;">
            <span style="color:var(--brown)"><?= e($item['titre']) ?> ×<?= $item['qte'] ?></span>
            <span style="font-weight:700;color:var(--brown-mid)"><?= number_format($item['prix']*$item['qte'],2) ?> DH</span>
          </div>
          <?php endforeach; ?>
          <div style="border-top:1.5px dashed var(--cream-dark);margin:14px 0;padding-top:14px;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-family:'Playfair Display',serif;font-size:1rem;color:var(--brown-mid)">Total ♡</span>
            <span style="font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;color:var(--brown)"><?= number_format($total,2) ?> DH</span>
          </div>
        </div>
      </div>

      <!-- Formulaire de commande -->
      <form method="POST" style="margin-top:18px;">
        <?php if (!isset($_SESSION['client_id'])): ?>
          <div class="alert alert-info">
            🔑 <a href="login.php?redirect=panier.php" style="color:var(--brown);font-weight:700">Connectez-vous</a>
            pour finaliser votre commande.
          </div>
        <?php endif; ?>
        <div class="form-group">
          <label class="form-label">Adresse de livraison</label>
          <textarea name="adresse" class="form-textarea" rows="3"
                    placeholder="Votre adresse complète au Maroc…"
                    required><?= isset($_SESSION['client_id']) ? '' : '' ?></textarea>
        </div>
        <button type="submit" name="commander" class="btn btn-brown" style="width:100%;justify-content:center">
          ✅ &nbsp;Confirmer la commande — <?= number_format($total,2) ?> DH
        </button>
      </form>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
