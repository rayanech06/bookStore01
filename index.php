<?php
require_once 'includes/config.php';
$pageTitle  = 'Commandes par client';
$activePage = 'orders';
$cssPath    = '';

$db = getDB();

// ── Mettre à jour statut (admin rapide) ───────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['statut'], $_POST['commande_id'])) {
    $db->prepare('UPDATE commandes SET statut = ? WHERE id = ?')
       ->execute([$_POST['statut'], (int)$_POST['commande_id']]);
    header('Location: commandes.php' . (isset($_GET['client_id']) ? '?client_id='.(int)$_GET['client_id'] : ''));
    exit;
}

// ── Filtre par client ─────────────────────────────────────────
$filterClientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : 0;

// ── Liste des clients avec nb commandes ───────────────────────
$clients = $db->query('
    SELECT c.*, COUNT(cmd.id) AS nb_commandes,
           COALESCE(SUM(cmd.total),0) AS total_achats
    FROM clients c
    LEFT JOIN commandes cmd ON cmd.client_id = c.id
    GROUP BY c.id
    ORDER BY c.nom, c.prenom
')->fetchAll();

// ── Commandes (filtrées ou toutes) ────────────────────────────
if ($filterClientId) {
    $stmtCmd = $db->prepare('
        SELECT cmd.*, c.prenom, c.nom, c.email, c.telephone
        FROM commandes cmd
        JOIN clients c ON c.id = cmd.client_id
        WHERE cmd.client_id = ?
        ORDER BY cmd.created_at DESC
    ');
    $stmtCmd->execute([$filterClientId]);
} else {
    $stmtCmd = $db->query('
        SELECT cmd.*, c.prenom, c.nom, c.email, c.telephone
        FROM commandes cmd
        JOIN clients c ON c.id = cmd.client_id
        ORDER BY cmd.created_at DESC
    ');
}
$commandes = $stmtCmd->fetchAll();

// ── Lignes de chaque commande ─────────────────────────────────
$lignesAll = $db->query('
    SELECT lc.*, l.titre, l.auteur, l.image_url, l.genre
    FROM lignes_commande lc
    JOIN livres l ON l.id = lc.livre_id
    ORDER BY lc.commande_id
')->fetchAll();

// Indexer par commande_id
$lignesMap = [];
foreach ($lignesAll as $lg) {
    $lignesMap[$lg['commande_id']][] = $lg;
}

// ── Stats globales ────────────────────────────────────────────
$stats = $db->query('
    SELECT
      COUNT(*) AS total_cmds,
      SUM(total) AS ca_total,
      SUM(CASE WHEN statut="en_attente" THEN 1 ELSE 0 END) AS en_attente,
      SUM(CASE WHEN statut="livree"     THEN 1 ELSE 0 END) AS livrees
    FROM commandes
')->fetch();

// Labels statut
$statutLabels = [
    'en_attente' => '🕐 En attente',
    'confirmee'  => '✅ Confirmée',
    'expediee'   => '🚚 Expédiée',
    'livree'     => '🏠 Livrée',
    'annulee'    => '❌ Annulée',
];

include 'includes/header.php';
?>

<?php if (isset($_SESSION['success'])): ?>
  <div class="alert alert-success" style="max-width:900px;margin:20px auto 0;">
    <?= e($_SESSION['success']) ?>
  </div>
  <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<div class="page-wrap">

  <!-- ── EN-TÊTE ── -->
  <div class="page-header-row">
    <div>
      <h1 class="page-h1">📋 Commandes par client <span style="color:var(--pink)">♡</span></h1>
      <p class="page-sub">Vue d'ensemble de toutes les commandes</p>
    </div>
    <a href="index.php" class="btn btn-ghost btn-sm">← Retour boutique</a>
  </div>

  <!-- ── STATS ── -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-icon">📦</div>
      <div class="stat-num"><?= $stats['total_cmds'] ?></div>
      <div class="stat-label">Commandes totales</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">💰</div>
      <div class="stat-num"><?= number_format($stats['ca_total'] ?? 0, 0) ?> DH</div>
      <div class="stat-label">Chiffre d'affaires</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">🕐</div>
      <div class="stat-num"><?= $stats['en_attente'] ?></div>
      <div class="stat-label">En attente</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">🏠</div>
      <div class="stat-num"><?= $stats['livrees'] ?></div>
      <div class="stat-label">Livrées</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">👥</div>
      <div class="stat-num"><?= count($clients) ?></div>
      <div class="stat-label">Clients</div>
    </div>
  </div>

  <!-- ── LAYOUT DEUX COLONNES ── -->
  <div style="display:grid;grid-template-columns:280px 1fr;gap:28px;align-items:start;">

    <!-- ── LISTE CLIENTS ── -->
    <div>
      <div class="table-card">
        <div style="background:linear-gradient(135deg,var(--pink-pale),var(--cream));padding:14px 18px;border-bottom:1.5px dashed var(--pink-light);">
          <span style="font-family:'Playfair Display',serif;font-weight:700;color:var(--brown);">👥 Clients</span>
        </div>
        <div style="padding:8px;">
          <a href="commandes.php"
             style="display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:8px;text-decoration:none;
                    font-size:.84rem;font-weight:700;
                    color:<?= !$filterClientId ? 'var(--brown)' : 'var(--brown-mid)' ?>;
                    background:<?= !$filterClientId ? 'var(--pink-pale)' : 'transparent' ?>;
                    border:1.5px solid <?= !$filterClientId ? 'var(--pink)' : 'transparent' ?>;
                    margin-bottom:6px;">
            👥 Tous les clients
            <span style="margin-left:auto;background:var(--pink-light);color:var(--brown);border-radius:10px;padding:2px 8px;font-size:.72rem;">
              <?= count($commandes) ?>
            </span>
          </a>
          <?php foreach ($clients as $cl): ?>
            <?php $isActive = $filterClientId === (int)$cl['id']; ?>
            <a href="commandes.php?client_id=<?= $cl['id'] ?>"
               style="display:flex;flex-direction:column;padding:10px 12px;border-radius:8px;text-decoration:none;
                      margin-bottom:4px;transition:all .2s;
                      background:<?= $isActive ? 'var(--pink-pale)' : 'transparent' ?>;
                      border:1.5px solid <?= $isActive ? 'var(--pink)' : 'transparent' ?>;">
              <span style="font-size:.84rem;font-weight:700;color:var(--brown);">
                👤 <?= e($cl['prenom'].' '.$cl['nom']) ?>
              </span>
              <span style="font-size:.72rem;color:var(--brown-light);margin-top:2px;">
                <?= e($cl['email']) ?>
              </span>
              <div style="display:flex;gap:8px;margin-top:5px;">
                <span style="font-size:.7rem;background:var(--pink-light);color:var(--brown);padding:2px 8px;border-radius:10px;">
                  📦 <?= $cl['nb_commandes'] ?> cmd
                </span>
                <span style="font-size:.7rem;background:var(--cream-dark);color:var(--brown-mid);padding:2px 8px;border-radius:10px;">
                  <?= number_format($cl['total_achats'],0) ?> DH
                </span>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- ── COMMANDES ── -->
    <div>
      <?php if ($filterClientId):
        $cl = array_values(array_filter($clients, fn($c) => (int)$c['id'] === $filterClientId))[0] ?? null;
        if ($cl): ?>
        <!-- Fiche client -->
        <div style="background:var(--white);border:2px solid var(--pink-light);border-radius:var(--radius);padding:20px 24px;margin-bottom:20px;">
          <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
            <div style="font-size:2.5rem;">👤</div>
            <div style="flex:1;">
              <div style="font-family:'Playfair Display',serif;font-size:1.2rem;font-weight:700;color:var(--brown);">
                <?= e($cl['prenom'].' '.$cl['nom']) ?>
              </div>
              <div style="font-size:.84rem;color:var(--brown-light);margin-top:2px;">
                📧 <?= e($cl['email']) ?>
                <?php if ($cl['telephone']): ?> &nbsp;·&nbsp; 📞 <?= e($cl['telephone']) ?><?php endif; ?>
              </div>
              <?php if ($cl['adresse']): ?>
              <div style="font-size:.82rem;color:var(--brown-light);margin-top:2px;">📍 <?= e($cl['adresse']) ?></div>
              <?php endif; ?>
            </div>
            <div style="text-align:right;">
              <div style="font-family:'Playfair Display',serif;font-size:1.4rem;font-weight:700;color:var(--brown-mid);">
                <?= number_format($cl['total_achats'],2) ?> DH
              </div>
              <div style="font-size:.75rem;color:var(--brown-light);">total dépensé</div>
            </div>
          </div>
        </div>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (empty($commandes)): ?>
        <div class="empty-state">
          <span class="empty-icon">📭</span>
          <h3>Aucune commande</h3>
          <p>Ce client n'a pas encore passé de commande.</p>
        </div>
      <?php else: ?>
        <?php foreach ($commandes as $cmd): ?>
        <div class="table-card" style="margin-bottom:20px;">

          <!-- Header commande -->
          <div class="commande-header" style="border:none;border-radius:0;border-bottom:1.5px dashed var(--pink-light);margin-bottom:0;">
            <div>
              <div class="commande-id">Commande #<?= $cmd['id'] ?></div>
              <div class="commande-date">
                📅 <?= date('d/m/Y à H:i', strtotime($cmd['created_at'])) ?>
                <?php if (!$filterClientId): ?>
                  &nbsp;·&nbsp; 👤 <?= e($cmd['prenom'].' '.$cmd['nom']) ?>
                <?php endif; ?>
              </div>
            </div>
            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
              <span class="statut statut-<?= $cmd['statut'] ?>"><?= $statutLabels[$cmd['statut']] ?></span>
              <div class="commande-total"><?= number_format($cmd['total'],2) ?> DH</div>

              <!-- Changer statut -->
              <form method="POST" style="display:flex;gap:6px;align-items:center;">
                <input type="hidden" name="commande_id" value="<?= $cmd['id'] ?>">
                <select name="statut" class="form-select" style="width:150px;padding:6px 10px;font-size:.78rem;">
                  <?php foreach ($statutLabels as $val => $lbl): ?>
                    <option value="<?= $val ?>" <?= $cmd['statut'] === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-pink btn-sm">✓</button>
              </form>
            </div>
          </div>

          <!-- Livres commandés -->
          <div style="padding:0;">
            <?php if (!empty($lignesMap[$cmd['id']])): ?>
            <table style="width:100%;border-collapse:collapse;">
              <thead>
                <tr>
                  <th style="padding:10px 18px;font-size:.75rem;font-weight:700;color:var(--brown-mid);text-transform:uppercase;letter-spacing:.8px;text-align:left;border-bottom:1px dashed var(--cream-dark);background:var(--parchment);">Livre</th>
                  <th style="padding:10px 18px;font-size:.75rem;font-weight:700;color:var(--brown-mid);text-transform:uppercase;letter-spacing:.8px;text-align:center;border-bottom:1px dashed var(--cream-dark);background:var(--parchment);">Qté</th>
                  <th style="padding:10px 18px;font-size:.75rem;font-weight:700;color:var(--brown-mid);text-transform:uppercase;letter-spacing:.8px;text-align:right;border-bottom:1px dashed var(--cream-dark);background:var(--parchment);">Prix</th>
                  <th style="padding:10px 18px;font-size:.75rem;font-weight:700;color:var(--brown-mid);text-transform:uppercase;letter-spacing:.8px;text-align:right;border-bottom:1px dashed var(--cream-dark);background:var(--parchment);">Sous-total</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($lignesMap[$cmd['id']] as $lg): ?>
                <tr>
                  <td style="padding:12px 18px;border-bottom:1px dashed var(--cream-dark);">
                    <div style="display:flex;align-items:center;gap:12px;">
                      <?php if ($lg['image_url']): ?>
                        <img src="<?= e($lg['image_url']) ?>" alt=""
                             style="width:38px;height:52px;object-fit:cover;border-radius:5px;flex-shrink:0;"
                             onerror="this.style.display='none'">
                      <?php endif; ?>
                      <div>
                        <div style="font-family:'Playfair Display',serif;font-weight:700;font-size:.875rem;color:var(--brown);">
                          <?= e($lg['titre']) ?>
                        </div>
                        <div style="font-size:.75rem;color:var(--brown-light);font-style:italic;">
                          <?= e($lg['auteur']) ?>
                          <?php if ($lg['genre']): ?>
                            &nbsp;·&nbsp;<span style="background:var(--pink-pale);color:var(--brown-mid);padding:1px 7px;border-radius:8px;font-style:normal;"><?= e($lg['genre']) ?></span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td style="padding:12px 18px;text-align:center;border-bottom:1px dashed var(--cream-dark);font-weight:700;color:var(--brown-mid);">×<?= $lg['quantite'] ?></td>
                  <td style="padding:12px 18px;text-align:right;border-bottom:1px dashed var(--cream-dark);color:var(--brown-light);"><?= number_format($lg['prix_unitaire'],2) ?> DH</td>
                  <td style="padding:12px 18px;text-align:right;border-bottom:1px dashed var(--cream-dark);font-weight:700;color:var(--pink);font-family:'Playfair Display',serif;"><?= number_format($lg['prix_unitaire']*$lg['quantite'],2) ?> DH</td>
                </tr>
                <?php endforeach; ?>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3" style="padding:12px 18px;text-align:right;font-weight:700;color:var(--brown-mid);">Total</td>
                  <td style="padding:12px 18px;text-align:right;font-family:'Playfair Display',serif;font-size:1.1rem;font-weight:700;color:var(--brown);"><?= number_format($cmd['total'],2) ?> DH</td>
                </tr>
              </tfoot>
            </table>
            <?php else: ?>
              <p style="padding:16px 20px;color:var(--brown-light);font-style:italic;">Aucun détail disponible.</p>
            <?php endif; ?>

            <?php if ($cmd['adresse_livraison']): ?>
            <div style="padding:12px 18px;border-top:1px dashed var(--cream-dark);font-size:.8rem;color:var(--brown-light);">
              📍 Livraison : <?= e($cmd['adresse_livraison']) ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div><!-- /grid -->
</div>

<?php include 'includes/footer.php'; ?>
