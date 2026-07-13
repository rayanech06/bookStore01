<?php
require_once 'includes/config.php';

$pageTitle  = 'Administration — Livres';
$activePage = 'admin';
$cssPath    = '';

$db      = getDB();
$action  = $_GET['action'] ?? 'list';
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$msgType = '';

// Couleurs fallback par genre
$genreColors = [
    'غموض'     => '#3A2050',
    'مغامرات'  => '#1A3A5C',
    'رومانسية' => '#8B3A5A',
    'إثارة'    => '#5C2020',
    'تاريخي'   => '#5C3A10',
    'كلاسيكي'  => '#2C3E50',
];

// ── Dossier uploads ──────────────────────────────────────────
define('UPLOAD_DIR',  __DIR__ . '/uploads/');
define('UPLOAD_URL',  'uploads/');
define('MAX_SIZE',    3 * 1024 * 1024);   // 3 Mo
define('ALLOWED_EXT', ['jpg','jpeg','png','webp','gif']);

/**
 * Traite l'upload d'une image de couverture.
 * Retourne ['ok' => true, 'path' => '...'] ou ['ok' => false, 'error' => '...']
 */
function handleUpload(string $fieldName): array {
    if (empty($_FILES[$fieldName]['name'])) {
        return ['ok' => false, 'error' => ''];   // Aucun fichier = pas d'erreur
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Erreur lors du téléchargement (code '.$file['error'].').'];
    }

    if ($file['size'] > MAX_SIZE) {
        return ['ok' => false, 'error' => 'Image trop lourde (max 3 Mo).'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT)) {
        return ['ok' => false, 'error' => 'Format non autorisé. Utilisez : JPG, PNG, WEBP ou GIF.'];
    }

    // Vérifier que c'est bien une vraie image
    $info = @getimagesize($file['tmp_name']);
    if (!$info) {
        return ['ok' => false, 'error' => 'Le fichier uploadé n\'est pas une image valide.'];
    }

    // Nom unique pour éviter les collisions
    $newName = 'cover_' . uniqid() . '_' . time() . '.' . $ext;
    $dest    = UPLOAD_DIR . $newName;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['ok' => false, 'error' => 'Impossible de sauvegarder l\'image sur le serveur.'];
    }

    return ['ok' => true, 'path' => UPLOAD_URL . $newName];
}

// ══════════════════════════════════════════════════════════════
//  TRAITEMENT DES ACTIONS POST
// ══════════════════════════════════════════════════════════════

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── AJOUTER ────────────────────────────────────────────────
    if (isset($_POST['ajouter_livre'])) {
        $titre  = trim($_POST['titre']       ?? '');
        $auteur = trim($_POST['auteur']      ?? '');
        $isbn   = trim($_POST['isbn']        ?? '');
        $genre  = trim($_POST['genre']       ?? '');
        $prix   = (float)($_POST['prix']     ?? 0);
        $pages  = (int)($_POST['pages']      ?? 0);
        $stock  = (int)($_POST['stock']      ?? 0);
        $desc   = trim($_POST['description'] ?? '');

        // Priorité : fichier uploadé > URL saisie
        $img = trim($_POST['image_url'] ?? '');
        $upload = handleUpload('image_file');
        if ($upload['ok']) {
            $img = $upload['path'];
        } elseif (!empty($upload['error'])) {
            $message = '⚠ ' . $upload['error'];
            $msgType = 'error';
            $action  = 'ajouter';
        }

        if (!$message && $titre && $auteur && $prix > 0) {
            $db->prepare('
                INSERT INTO livres (titre, auteur, isbn, genre, prix, pages, stock, image_url, description)
                VALUES (?,?,?,?,?,?,?,?,?)
            ')->execute([$titre,$auteur,$isbn,$genre,$prix,$pages,$stock,$img,$desc]);
            $message = '✅ Livre "' . htmlspecialchars($titre) . '" ajouté avec succès !';
            $msgType = 'success';
            $action  = 'list';
        } elseif (!$message) {
            $message = '⚠ Titre, auteur et prix sont obligatoires.';
            $msgType = 'error';
            $action  = 'ajouter';
        }
    }

    // ── MODIFIER ───────────────────────────────────────────────
    if (isset($_POST['modifier_livre'])) {
        $lid    = (int)$_POST['livre_id'];
        $titre  = trim($_POST['titre']       ?? '');
        $auteur = trim($_POST['auteur']      ?? '');
        $isbn   = trim($_POST['isbn']        ?? '');
        $genre  = trim($_POST['genre']       ?? '');
        $prix   = (float)($_POST['prix']     ?? 0);
        $pages  = (int)($_POST['pages']      ?? 0);
        $stock  = (int)($_POST['stock']      ?? 0);
        $desc   = trim($_POST['description'] ?? '');

        // Récupérer l'ancienne image
        $oldImg = $db->prepare('SELECT image_url FROM livres WHERE id=?');
        $oldImg->execute([$lid]);
        $currentImg = $oldImg->fetchColumn() ?? '';

        // Priorité : fichier uploadé > URL saisie > ancienne image
        $img    = trim($_POST['image_url'] ?? '') ?: $currentImg;
        $upload = handleUpload('image_file');

        if ($upload['ok']) {
            // Supprimer l'ancien fichier local s'il existe
            if ($currentImg && strpos($currentImg, 'uploads/') === 0) {
                $oldPath = __DIR__ . '/' . $currentImg;
                if (file_exists($oldPath)) @unlink($oldPath);
            }
            $img = $upload['path'];
        } elseif (!empty($upload['error'])) {
            $message = '⚠ ' . $upload['error'];
            $msgType = 'error';
            $action  = 'modifier';
            $id      = $lid;
        }

        if (!$message && $titre && $auteur && $prix > 0) {
            $db->prepare('
                UPDATE livres
                SET titre=?, auteur=?, isbn=?, genre=?, prix=?, pages=?, stock=?, image_url=?, description=?
                WHERE id=?
            ')->execute([$titre,$auteur,$isbn,$genre,$prix,$pages,$stock,$img,$desc,$lid]);
            $message = '✅ Livre modifié avec succès !';
            $msgType = 'success';
            $action  = 'list';
        } elseif (!$message) {
            $message = '⚠ Titre, auteur et prix sont obligatoires.';
            $msgType = 'error';
            $action  = 'modifier';
            $id      = $lid;
        }
    }

    // ── SUPPRIMER ──────────────────────────────────────────────
    if (isset($_POST['supprimer_livre'])) {
        $lid  = (int)$_POST['livre_id'];
        $s    = $db->prepare('SELECT titre, image_url FROM livres WHERE id=?');
        $s->execute([$lid]);
        $row  = $s->fetch();
        $nom  = $row['titre']     ?? '';
        $img  = $row['image_url'] ?? '';

        // Supprimer l'image locale si elle est dans uploads/
        if ($img && strpos($img, 'uploads/') === 0) {
            $path = __DIR__ . '/' . $img;
            if (file_exists($path)) @unlink($path);
        }

        $db->prepare('DELETE FROM livres WHERE id=?')->execute([$lid]);
        $message = '🗑 Livre "' . htmlspecialchars($nom) . '" supprimé.';
        $msgType = 'info';
        $action  = 'list';
    }
}

// ══════════════════════════════════════════════════════════════
//  CHARGEMENT DES DONNÉES
// ══════════════════════════════════════════════════════════════

// Recherche dans la liste
$search = trim($_GET['q'] ?? '');
$gFilter = $_GET['genre'] ?? '';

// Liste livres
$where  = [];
$params = [];
if ($search) {
    $where[]       = '(titre LIKE :q OR auteur LIKE :q2 OR isbn LIKE :q3)';
    $params[':q']  = "%$search%";
    $params[':q2'] = "%$search%";
    $params[':q3'] = "%$search%";
}
if ($gFilter) {
    $where[]           = 'genre = :genre';
    $params[':genre']  = $gFilter;
}
$sql   = 'SELECT * FROM livres' . ($where ? ' WHERE '.implode(' AND ',$where) : '') . ' ORDER BY id DESC';
$stmt  = $db->prepare($sql);
$stmt->execute($params);
$livres = $stmt->fetchAll();

// Genres pour filtre
$genres = $db->query('SELECT DISTINCT genre FROM livres ORDER BY genre')->fetchAll(PDO::FETCH_COLUMN);

// Stats
$stats = $db->query('SELECT COUNT(*) AS nb, SUM(prix) AS total, SUM(stock) AS stock_total FROM livres')->fetch();

// Livre à modifier
$livreEdit = null;
if ($action === 'modifier' && $id) {
    $s = $db->prepare('SELECT * FROM livres WHERE id=?');
    $s->execute([$id]);
    $livreEdit = $s->fetch();
    if (!$livreEdit) { $action = 'list'; }
}

include 'includes/header.php';
?>

<!-- ════════════════════════════════════════════════════════════
     STYLES ADMIN SUPPLÉMENTAIRES
═══════════════════════════════════════════════════════════════ -->
<style>
  .admin-layout {
    max-width: 1200px;
    margin: 0 auto;
    padding: 36px 40px 80px;
    flex: 1;
  }

  /* ── TABS ── */
  .admin-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 28px;
    border-bottom: 2px dashed var(--pink-light);
    padding-bottom: 14px;
  }
  .admin-tab {
    padding: 9px 20px;
    border-radius: 10px 10px 0 0;
    border: 1.5px solid var(--cream-dark);
    border-bottom: none;
    background: var(--cream);
    color: var(--brown-light);
    font-weight: 700;
    font-size: .85rem;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
  }
  .admin-tab:hover { background: var(--pink-pale); color: var(--brown); }
  .admin-tab.active { background: var(--white); border-color: var(--pink-light); color: var(--brown); }

  /* ── CARD FORMULAIRE ── */
  .admin-form-card {
    background: var(--white);
    border: 2px solid var(--pink-light);
    border-radius: var(--radius);
    overflow: hidden;
    box-shadow: var(--shadow);
  }
  .admin-form-head {
    background: linear-gradient(135deg, var(--pink-pale), var(--cream));
    padding: 18px 28px;
    border-bottom: 1.5px dashed var(--pink-light);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
  .admin-form-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--brown);
  }
  .admin-form-body { padding: 28px; }

  /* ── GRID FORMULAIRE ── */
  .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
  .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .col-span-2  { grid-column: span 2; }
  .col-span-3  { grid-column: span 3; }

  /* ── PREVIEW IMAGE ── */
  .img-preview {
    width: 80px; height: 110px;
    border-radius: 8px;
    object-fit: cover;
    border: 2px solid var(--pink-light);
    display: none;
    flex-shrink: 0;
  }
  .img-preview.show { display: block; }

  /* ── TABLE ADMIN ── */
  .admin-table { width: 100%; border-collapse: collapse; }
  .admin-table th {
    background: linear-gradient(135deg, var(--pink-pale), var(--cream));
    font-size: .78rem; font-weight: 700;
    color: var(--brown-mid);
    text-transform: uppercase;
    letter-spacing: .8px;
    padding: 13px 16px;
    text-align: left;
    border-bottom: 1.5px dashed var(--pink-light);
    white-space: nowrap;
  }
  .admin-table td {
    padding: 12px 16px;
    border-bottom: 1px dashed var(--cream-dark);
    vertical-align: middle;
    font-size: .875rem;
  }
  .admin-table tr:last-child td { border-bottom: none; }
  .admin-table tr:hover td { background: var(--pink-pale); }

  /* ── STOCK INDICATOR ── */
  .stock-num {
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 10px;
    font-size: .8rem;
  }
  .stock-ok  { background: #EAFAF1; color: #1E8449; }
  .stock-low { background: #FEF9E7; color: #D4AC0D; }
  .stock-out { background: #FDEDEC; color: #C0392B; }

  /* ── ACTIONS ── */
  .action-btns { display: flex; gap: 6px; align-items: center; }

  .btn-edit {
    background: var(--pink-pale);
    border: 1.5px solid var(--pink-light);
    color: var(--brown-mid);
    padding: 5px 12px;
    border-radius: 8px;
    font-size: .78rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: all .2s;
    font-family: 'Lato', sans-serif;
  }
  .btn-edit:hover { background: var(--pink-light); border-color: var(--pink); color: var(--brown); }

  .btn-del {
    background: #FDEDEC;
    border: 1.5px solid #F1948A;
    color: var(--danger);
    padding: 5px 10px;
    border-radius: 8px;
    font-size: .78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all .2s;
    font-family: 'Lato', sans-serif;
  }
  .btn-del:hover { background: var(--danger); color: var(--white); border-color: var(--danger); }

  /* ── RECHERCHE ADMIN ── */
  .admin-search {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }

  /* ── MINI COVER ── */
  .mini-cover {
    width: 36px; height: 50px;
    border-radius: 5px;
    object-fit: cover;
    flex-shrink: 0;
    border: 1.5px solid var(--cream-dark);
  }
  .mini-cover-fallback {
    width: 36px; height: 50px;
    border-radius: 5px;
    display: flex; align-items: center; justify-content: center;
    font-size: .5rem;
    color: rgba(255,255,255,.8);
    text-align: center;
    flex-shrink: 0;
    padding: 3px;
    line-height: 1.2;
  }

  /* ── CONFIRM DELETE MODAL ── */
  .confirm-overlay {
    position: fixed; inset: 0;
    background: rgba(59,31,15,.55);
    backdrop-filter: blur(3px);
    z-index: 500;
    display: none;
    align-items: center;
    justify-content: center;
  }
  .confirm-overlay.open { display: flex; }
  .confirm-box {
    background: var(--white);
    border: 2px solid var(--pink-light);
    border-radius: var(--radius);
    padding: 36px 40px;
    max-width: 420px;
    width: 90%;
    text-align: center;
    box-shadow: var(--shadow-lg);
  }
  .confirm-icon { font-size: 3rem; margin-bottom: 12px; }
  .confirm-title { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 700; color: var(--brown); margin-bottom: 8px; }
  .confirm-text  { font-size: .875rem; color: var(--brown-light); margin-bottom: 24px; }
  .confirm-btns  { display: flex; gap: 10px; justify-content: center; }

  /* ── UPLOAD WIDGET ── */
  .cover-upload-box {
    background: var(--parchment);
    border: 2px dashed var(--pink-light);
    border-radius: 12px;
    padding: 18px;
    display: flex;
    flex-direction: column;
    gap: 14px;
  }

  .drop-zone {
    border: 2px dashed var(--cream-dark);
    border-radius: 10px;
    padding: 24px 16px;
    text-align: center;
    cursor: pointer;
    background: var(--white);
    transition: all .25s;
  }

  .drop-zone:hover,
  .drop-zone.dragover {
    border-color: var(--pink);
    background: var(--pink-pale);
  }

  .drop-zone-inner { pointer-events: none; }

  .drop-icon { font-size: 2rem; margin-bottom: 8px; opacity: .6; }

  .drop-text {
    font-size: .875rem;
    color: var(--brown-mid);
    font-weight: 500;
    line-height: 1.5;
    margin-bottom: 5px;
  }

  .drop-link {
    color: var(--pink);
    font-weight: 700;
    text-decoration: underline;
  }

  .drop-hint { font-size: .72rem; color: var(--brown-light); }

  .file-selected {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--white);
    border: 1.5px solid var(--pink-light);
    border-radius: 8px;
    padding: 8px 12px;
  }

  .file-tag {
    font-size: .82rem;
    font-weight: 700;
    color: var(--brown-mid);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 360px;
  }

  .file-clear {
    background: none;
    border: none;
    color: var(--brown-light);
    cursor: pointer;
    font-size: .9rem;
    padding: 2px 6px;
    border-radius: 4px;
    transition: all .2s;
  }

  .file-clear:hover { color: var(--danger); background: #FDEDEC; }

  .upload-or {
    text-align: center;
    position: relative;
    font-size: .78rem;
    color: var(--brown-light);
  }

  .upload-or::before, .upload-or::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 40%;
    height: 1px;
    background: var(--cream-dark);
  }

  .upload-or::before { left: 0; }
  .upload-or::after  { right: 0; }

  .cover-preview-wrap {
    display: flex;
    align-items: center;
    gap: 14px;
    background: var(--white);
    border: 1.5px solid var(--pink-light);
    border-radius: 10px;
    padding: 12px 16px;
  }

  .cover-preview-img {
    width: 60px;
    height: 84px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid var(--pink-light);
    flex-shrink: 0;
  }

  .cover-preview-info {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 8px;
  }
    .admin-layout { padding: 20px 16px 60px; }
    .form-grid-3 { grid-template-columns: 1fr; }
    .form-grid-2 { grid-template-columns: 1fr; }
    .col-span-2, .col-span-3 { grid-column: span 1; }
    .admin-table th:nth-child(3),
    .admin-table td:nth-child(3) { display: none; }
  }
</style>

<!-- ════════════════════════════════════════════════════════════
     PAGE ADMIN
═══════════════════════════════════════════════════════════════ -->
<div class="admin-layout">

  <!-- ── EN-TÊTE ── -->
  <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
      <h1 class="page-h1">⚙️ Administration <span style="color:var(--pink)">♡</span></h1>
      <p class="page-sub">Gérez les livres de votre librairie</p>
    </div>
    <a href="index.php" class="btn btn-ghost btn-sm">← Voir la boutique</a>
  </div>

  <!-- ── STATS ── -->
  <div class="stats-row" style="margin-bottom:28px;">
    <div class="stat-card">
      <div class="stat-icon">📚</div>
      <div class="stat-num"><?= count($livres) ?></div>
      <div class="stat-label">Livres au catalogue</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">📦</div>
      <div class="stat-num"><?= $stats['stock_total'] ?? 0 ?></div>
      <div class="stat-label">Exemplaires en stock</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">🏷️</div>
      <div class="stat-num"><?= count($genres) ?></div>
      <div class="stat-label">Genres différents</div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">💰</div>
      <div class="stat-num"><?= number_format(($stats['total']??0)/max(1,count($livres)),0) ?> DH</div>
      <div class="stat-label">Prix moyen</div>
    </div>
  </div>

  <!-- ── ALERTE ── -->
  <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?>" style="margin-bottom:20px;">
      <?= $message ?>
    </div>
  <?php endif; ?>

  <!-- ── TABS ── -->
  <div class="admin-tabs">
    <a href="admin.php" class="admin-tab <?= $action==='list' ? 'active' : '' ?>">📋 Liste des livres</a>
    <a href="admin.php?action=ajouter" class="admin-tab <?= $action==='ajouter' ? 'active' : '' ?>">➕ Ajouter un livre</a>
    <?php if ($action === 'modifier' && $livreEdit): ?>
      <span class="admin-tab active">✏️ Modifier : <?= e(mb_substr($livreEdit['titre'],0,25)) ?>…</span>
    <?php endif; ?>
  </div>

  <!-- ══════════════════════════════════════════════════════════
       TAB : LISTE
  ═══════════════════════════════════════════════════════════ -->
  <?php if ($action === 'list'): ?>

    <!-- Barre de recherche + filtre -->
    <div class="admin-search">
      <form method="GET" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
        <input type="text" name="q" value="<?= e($search) ?>"
               class="form-input" style="max-width:280px;"
               placeholder="🔍 Titre, auteur, ISBN…">
        <select name="genre" class="form-select" style="max-width:160px;">
          <option value="">Tous les genres</option>
          <?php foreach ($genres as $g): ?>
            <option value="<?= e($g) ?>" <?= $gFilter===$g ? 'selected' : '' ?>><?= e($g) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-pink btn-sm">Filtrer</button>
        <?php if ($search || $gFilter): ?>
          <a href="admin.php" class="btn btn-ghost btn-sm">✕ Réinitialiser</a>
        <?php endif; ?>
      </form>
      <span style="margin-left:auto;font-size:.8rem;color:var(--brown-light);font-style:italic;">
        <?= count($livres) ?> livre<?= count($livres)!==1?'s':'' ?> trouvé<?= count($livres)!==1?'s':'' ?>
      </span>
    </div>

    <div class="table-card">
      <?php if (empty($livres)): ?>
        <div class="empty-state" style="padding:48px 20px;">
          <span class="empty-icon">📚</span>
          <h3>Aucun livre trouvé</h3>
          <p>Essayez une autre recherche ou <a href="admin.php?action=ajouter" style="color:var(--pink);font-weight:700;">ajoutez un livre</a>.</p>
        </div>
      <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="admin-table">
          <thead>
            <tr>
              <th style="width:44px">#</th>
              <th>Couverture</th>
              <th>Titre & Auteur</th>
              <th>Genre</th>
              <th>ISBN</th>
              <th>Prix</th>
              <th>Pages</th>
              <th>Stock</th>
              <th style="text-align:center;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($livres as $livre): ?>
            <?php
              $color    = $genreColors[$livre['genre']] ?? '#6B4A2E';
              $stockCls = $livre['stock'] <= 0 ? 'stock-out' : ($livre['stock'] <= 3 ? 'stock-low' : 'stock-ok');
            ?>
            <tr>
              <td style="color:var(--brown-light);font-size:.8rem;"><?= $livre['id'] ?></td>

              <!-- Couverture miniature -->
              <td>
                <?php if ($livre['image_url']): ?>
                  <img src="<?= e($livre['image_url']) ?>"
                       class="mini-cover"
                       onerror="this.style.display='none';this.nextElementSibling.style.display='flex';"
                       alt="">
                  <div class="mini-cover-fallback" style="background:<?= e($color) ?>;display:none">
                    <?= e(mb_substr($livre['titre'],0,8)) ?>
                  </div>
                <?php else: ?>
                  <div class="mini-cover-fallback" style="background:<?= e($color) ?>">
                    <?= e(mb_substr($livre['titre'],0,8)) ?>
                  </div>
                <?php endif; ?>
              </td>

              <!-- Titre & Auteur -->
              <td>
                <div style="font-family:'Playfair Display',serif;font-weight:700;font-size:.9rem;color:var(--brown);margin-bottom:2px;">
                  <?= e($livre['titre']) ?>
                </div>
                <div style="font-size:.75rem;color:var(--brown-light);font-style:italic;">
                  <?= e($livre['auteur']) ?>
                </div>
              </td>

              <!-- Genre -->
              <td>
                <?php if ($livre['genre']): ?>
                  <span style="background:var(--pink-pale);color:var(--brown-mid);border:1px solid var(--pink-light);
                               padding:3px 10px;border-radius:10px;font-size:.75rem;font-weight:700;">
                    <?= e($livre['genre']) ?>
                  </span>
                <?php endif; ?>
              </td>

              <!-- ISBN -->
              <td style="font-size:.75rem;color:var(--brown-light);font-family:monospace;"><?= e($livre['isbn'] ?? '—') ?></td>

              <!-- Prix -->
              <td style="font-family:'Playfair Display',serif;font-weight:700;color:var(--brown-mid);white-space:nowrap;">
                <?= number_format($livre['prix'],2) ?> DH
              </td>

              <!-- Pages -->
              <td style="color:var(--brown-light);font-size:.82rem;"><?= $livre['pages'] ?: '—' ?></td>

              <!-- Stock -->
              <td>
                <span class="stock-num <?= $stockCls ?>">
                  <?= $livre['stock'] ?>
                </span>
              </td>

              <!-- Actions -->
              <td>
                <div class="action-btns">
                  <a href="admin.php?action=modifier&id=<?= $livre['id'] ?>" class="btn-edit">✏️ Modifier</a>
                  <button type="button" class="btn-del"
                    onclick="confirmDelete(<?= $livre['id'] ?>, '<?= e(addslashes($livre['titre'])) ?>')">
                    🗑 Supprimer
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

  <!-- ══════════════════════════════════════════════════════════
       TAB : AJOUTER
  ═══════════════════════════════════════════════════════════ -->
  <?php elseif ($action === 'ajouter'): ?>

    <div class="admin-form-card">
      <div class="admin-form-head">
        <span class="admin-form-title">➕ Ajouter un nouveau livre</span>
        <a href="admin.php" class="btn btn-ghost btn-sm">← Annuler</a>
      </div>
      <div class="admin-form-body">
        <form method="POST" enctype="multipart/form-data" id="bookForm">

          <!-- Ligne 1 : Titre (plein) -->
          <div class="form-group" style="margin-bottom:18px;">
            <label class="form-label">📖 Titre du livre *</label>
            <input type="text" name="titre" class="form-input" required
                   placeholder="ex: شبكة العنكبوت"
                   value="<?= e($_POST['titre'] ?? '') ?>">
          </div>

          <!-- Ligne 2 : Auteur + Genre -->
          <div class="form-grid-2" style="margin-bottom:18px;">
            <div class="form-group">
              <label class="form-label">✍️ Auteur *</label>
              <input type="text" name="auteur" class="form-input" required
                     placeholder="ex: أحمد خالد توفيق"
                     value="<?= e($_POST['auteur'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">🏷️ Genre</label>
              <select name="genre" class="form-select" id="genreSelect" onchange="updateColor()">
                <option value="">-- Choisir un genre --</option>
                <?php
                  $allGenres = ['غموض','مغامرات','رومانسية','إثارة','تاريخي','كلاسيكي'];
                  foreach ($allGenres as $g):
                ?>
                  <option value="<?= e($g) ?>" <?= (($_POST['genre']??'')===$g)?'selected':'' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
                <option value="autre">Autre…</option>
              </select>
              <input type="text" name="genre_custom" id="genreCustom" class="form-input"
                     style="margin-top:8px;display:none" placeholder="Saisir le genre manuellement">
            </div>
          </div>

          <!-- Ligne 3 : Prix + Pages + Stock -->
          <div class="form-grid-3" style="margin-bottom:18px;">
            <div class="form-group">
              <label class="form-label">💰 Prix (DH) *</label>
              <input type="number" name="prix" class="form-input" required
                     min="0" step="0.01" placeholder="85.00"
                     value="<?= e($_POST['prix'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">📄 Nombre de pages</label>
              <input type="number" name="pages" class="form-input"
                     min="1" placeholder="ex: 350"
                     value="<?= e($_POST['pages'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">📦 Stock initial</label>
              <input type="number" name="stock" class="form-input"
                     min="0" value="<?= e($_POST['stock'] ?? '10') ?>">
            </div>
          </div>

          <!-- Ligne 4 : ISBN -->
          <div class="form-group" style="margin-bottom:18px;">
            <label class="form-label">🔢 ISBN</label>
            <input type="text" name="isbn" class="form-input"
                   placeholder="ex: 978-977-..."
                   value="<?= e($_POST['isbn'] ?? '') ?>">
          </div>

          <!-- Ligne 5 : Couverture (upload local OU URL) -->
          <div class="form-group" style="margin-bottom:18px;">
            <label class="form-label">🖼️ Couverture du livre</label>
            <div class="cover-upload-box" id="uploadBox">

              <!-- Zone de drop -->
              <div class="drop-zone" id="dropZone" onclick="document.getElementById('imageFileAjouter').click()">
                <div class="drop-zone-inner">
                  <div class="drop-icon">📁</div>
                  <div class="drop-text">Glissez une image ici<br>ou <span class="drop-link">cliquez pour parcourir</span></div>
                  <div class="drop-hint">JPG, PNG, WEBP, GIF — max 3 Mo</div>
                </div>
              </div>

              <input type="file" id="imageFileAjouter" name="image_file"
                     accept="image/jpeg,image/png,image/webp,image/gif"
                     style="display:none"
                     onchange="handleFileSelect(this, 'previewAjouter', 'imgUrlAjouter', 'dropZone', 'fileNameAjouter')">

              <!-- Nom du fichier sélectionné -->
              <div class="file-selected" id="fileNameAjouter" style="display:none">
                <span class="file-tag">📎 <span id="fileNameTextAjouter"></span></span>
                <button type="button" onclick="clearFile('imageFileAjouter','previewAjouter','imgUrlAjouter','dropZone','fileNameAjouter')" class="file-clear">✕</button>
              </div>

              <!-- OU séparateur -->
              <div class="upload-or"><span>ou</span></div>

              <!-- URL externe -->
              <div>
                <label class="form-label" style="font-size:.75rem;margin-bottom:4px;">🔗 URL d'une image en ligne</label>
                <input type="text" name="image_url" id="imgUrlAjouter" class="form-input"
                       placeholder="https://covers.openlibrary.org/b/isbn/..."
                       oninput="previewFromUrl(this.value, 'previewAjouter')">
                <p style="font-size:.7rem;color:var(--brown-light);margin-top:4px;">
                  💡 <code>https://covers.openlibrary.org/b/isbn/ISBN13-L.jpg</code>
                </p>
              </div>

              <!-- Aperçu -->
              <div class="cover-preview-wrap" id="previewAjouter" style="display:none">
                <img class="cover-preview-img" id="previewImgAjouter" src="" alt="Aperçu">
                <div class="cover-preview-info">
                  <span style="font-size:.78rem;color:var(--brown-mid);font-weight:700;">Aperçu couverture</span>
                  <button type="button" onclick="clearAll('imageFileAjouter','previewAjouter','imgUrlAjouter','dropZone','fileNameAjouter')"
                          class="btn btn-ghost btn-sm" style="padding:4px 10px;font-size:.72rem;">✕ Supprimer</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Ligne 6 : Description -->
          <div class="form-group" style="margin-bottom:24px;">
            <label class="form-label">📝 Description</label>
            <textarea name="description" class="form-textarea" rows="3"
                      placeholder="Courte description du livre…"><?= e($_POST['description'] ?? '') ?></textarea>
          </div>

          <div style="display:flex;gap:10px;">
            <button type="submit" name="ajouter_livre" class="btn btn-pink">
              ➕ Ajouter le livre
            </button>
            <a href="admin.php" class="btn btn-ghost">Annuler</a>
          </div>
        </form>
      </div>
    </div>

  <!-- ══════════════════════════════════════════════════════════
       TAB : MODIFIER
  ═══════════════════════════════════════════════════════════ -->
  <?php elseif ($action === 'modifier' && $livreEdit): ?>

    <div class="admin-form-card">
      <div class="admin-form-head">
        <span class="admin-form-title">✏️ Modifier le livre</span>
        <a href="admin.php" class="btn btn-ghost btn-sm">← Annuler</a>
      </div>
      <div class="admin-form-body">

        <!-- Preview actuelle -->
        <div style="display:flex;align-items:center;gap:16px;padding:16px 20px;background:var(--pink-pale);border:1.5px dashed var(--pink-light);border-radius:10px;margin-bottom:24px;">
          <?php $color = $genreColors[$livreEdit['genre']] ?? '#6B4A2E'; ?>
          <?php if ($livreEdit['image_url']): ?>
            <img src="<?= e($livreEdit['image_url']) ?>"
                 style="width:52px;height:72px;object-fit:cover;border-radius:7px;border:2px solid var(--pink-light);"
                 onerror="this.style.display='none'" alt="">
          <?php else: ?>
            <div style="width:52px;height:72px;border-radius:7px;background:<?= e($color) ?>;
                        display:flex;align-items:center;justify-content:center;
                        font-size:.55rem;color:rgba(255,255,255,.8);text-align:center;padding:4px;">
              <?= e(mb_substr($livreEdit['titre'],0,15)) ?>
            </div>
          <?php endif; ?>
          <div>
            <div style="font-family:'Playfair Display',serif;font-weight:700;font-size:1rem;color:var(--brown);">
              <?= e($livreEdit['titre']) ?>
            </div>
            <div style="font-size:.82rem;color:var(--brown-light);font-style:italic;">
              <?= e($livreEdit['auteur']) ?>
            </div>
            <div style="font-size:.75rem;color:var(--brown-light);margin-top:4px;">
              ID : #<?= $livreEdit['id'] ?>
            </div>
          </div>
        </div>

        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="livre_id" value="<?= $livreEdit['id'] ?>">

          <!-- Titre -->
          <div class="form-group" style="margin-bottom:18px;">
            <label class="form-label">📖 Titre *</label>
            <input type="text" name="titre" class="form-input" required
                   value="<?= e($livreEdit['titre']) ?>">
          </div>

          <!-- Auteur + Genre -->
          <div class="form-grid-2" style="margin-bottom:18px;">
            <div class="form-group">
              <label class="form-label">✍️ Auteur *</label>
              <input type="text" name="auteur" class="form-input" required
                     value="<?= e($livreEdit['auteur']) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">🏷️ Genre</label>
              <select name="genre" class="form-select">
                <option value="">-- Choisir --</option>
                <?php
                  $allGenres = ['غموض','مغامرات','رومانسية','إثارة','تاريخي','كلاسيكي'];
                  foreach ($allGenres as $g):
                ?>
                  <option value="<?= e($g) ?>" <?= $livreEdit['genre']===$g?'selected':'' ?>><?= e($g) ?></option>
                <?php endforeach; ?>
                <?php if ($livreEdit['genre'] && !in_array($livreEdit['genre'],$allGenres)): ?>
                  <option value="<?= e($livreEdit['genre']) ?>" selected><?= e($livreEdit['genre']) ?></option>
                <?php endif; ?>
              </select>
            </div>
          </div>

          <!-- Prix + Pages + Stock -->
          <div class="form-grid-3" style="margin-bottom:18px;">
            <div class="form-group">
              <label class="form-label">💰 Prix (DH) *</label>
              <input type="number" name="prix" class="form-input" required
                     min="0" step="0.01" value="<?= e($livreEdit['prix']) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">📄 Pages</label>
              <input type="number" name="pages" class="form-input"
                     min="1" value="<?= e($livreEdit['pages']) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">📦 Stock</label>
              <input type="number" name="stock" class="form-input"
                     min="0" value="<?= e($livreEdit['stock']) ?>">
            </div>
          </div>

          <!-- ISBN -->
          <div class="form-group" style="margin-bottom:18px;">
            <label class="form-label">🔢 ISBN</label>
            <input type="text" name="isbn" class="form-input"
                   value="<?= e($livreEdit['isbn'] ?? '') ?>">
          </div>

          <!-- Couverture : upload local OU URL -->
          <div class="form-group" style="margin-bottom:18px;">
            <label class="form-label">🖼️ Couverture du livre</label>
            <div class="cover-upload-box">

              <!-- Aperçu de l'image actuelle -->
              <?php if ($livreEdit['image_url']): ?>
              <div style="display:flex;align-items:center;gap:12px;padding:12px;background:var(--parchment);border:1.5px dashed var(--cream-dark);border-radius:10px;margin-bottom:12px;">
                <img src="<?= e($livreEdit['image_url']) ?>"
                     style="width:52px;height:72px;object-fit:cover;border-radius:6px;border:2px solid var(--pink-light);"
                     onerror="this.parentElement.style.display='none'" alt="Couverture actuelle">
                <div>
                  <div style="font-size:.78rem;font-weight:700;color:var(--brown-mid);">Image actuelle</div>
                  <div style="font-size:.7rem;color:var(--brown-light);word-break:break-all;max-width:240px;">
                    <?= e(basename($livreEdit['image_url'])) ?>
                  </div>
                </div>
              </div>
              <?php endif; ?>

              <!-- Zone de drop -->
              <div class="drop-zone" id="dropZone" onclick="document.getElementById('imageFileModifier').click()">
                <div class="drop-zone-inner">
                  <div class="drop-icon">📁</div>
                  <div class="drop-text">Nouvelle image : glissez ici<br>ou <span class="drop-link">cliquez pour parcourir</span></div>
                  <div class="drop-hint">JPG, PNG, WEBP, GIF — max 3 Mo</div>
                </div>
              </div>

              <input type="file" id="imageFileModifier" name="image_file"
                     accept="image/jpeg,image/png,image/webp,image/gif"
                     style="display:none"
                     onchange="handleFileSelect(this, 'previewModifier', 'imgUrlModifier', 'dropZone', 'fileNameModifier')">

              <div class="file-selected" id="fileNameModifier" style="display:none">
                <span class="file-tag">📎 <span id="fileNameTextModifier"></span></span>
                <button type="button" onclick="clearFile('imageFileModifier','previewModifier','imgUrlModifier','dropZone','fileNameModifier')" class="file-clear">✕</button>
              </div>

              <div class="upload-or"><span>ou</span></div>

              <div>
                <label class="form-label" style="font-size:.75rem;margin-bottom:4px;">🔗 URL d'une image en ligne</label>
                <input type="text" name="image_url" id="imgUrlModifier" class="form-input"
                       oninput="previewFromUrl(this.value, 'previewModifier')"
                       value="<?= e($livreEdit['image_url'] ?? '') ?>">
              </div>

              <div class="cover-preview-wrap" id="previewModifier" style="display:none">
                <img class="cover-preview-img" id="previewImgModifier" src="" alt="Aperçu">
                <div class="cover-preview-info">
                  <span style="font-size:.78rem;color:var(--brown-mid);font-weight:700;">Nouvelle couverture</span>
                  <button type="button" onclick="clearAll('imageFileModifier','previewModifier','imgUrlModifier','dropZone','fileNameModifier')"
                          class="btn btn-ghost btn-sm" style="padding:4px 10px;font-size:.72rem;">✕ Annuler</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="form-group" style="margin-bottom:24px;">
            <label class="form-label">📝 Description</label>
            <textarea name="description" class="form-textarea" rows="3"><?= e($livreEdit['description'] ?? '') ?></textarea>
          </div>

          <div style="display:flex;gap:10px;">
            <button type="submit" name="modifier_livre" class="btn btn-pink">
              💾 Enregistrer les modifications
            </button>
            <a href="admin.php" class="btn btn-ghost">Annuler</a>
          </div>
        </form>
      </div>
    </div>

  <?php endif; ?>
</div><!-- /admin-layout -->

<!-- ══════════════════════════════════════════════════════════
     MODAL CONFIRMATION SUPPRESSION
═══════════════════════════════════════════════════════════ -->
<div class="confirm-overlay" id="confirmOverlay">
  <div class="confirm-box">
    <div class="confirm-icon">🗑️</div>
    <div class="confirm-title">Supprimer ce livre ?</div>
    <p class="confirm-text" id="confirmText">Cette action est irréversible.</p>
    <div class="confirm-btns">
      <button onclick="closeConfirm()" class="btn btn-ghost">Annuler</button>
      <form method="POST" id="deleteForm" style="display:inline;">
        <input type="hidden" name="livre_id" id="deleteId">
        <button type="submit" name="supprimer_livre" class="btn btn-pink"
                style="background:var(--danger);box-shadow:0 4px 14px rgba(192,57,43,.4);">
          🗑 Oui, supprimer
        </button>
      </form>
    </div>
  </div>
</div>

<script>
// ── Sélection de fichier depuis l'input ───────────────────────
function handleFileSelect(input, previewId, urlInputId, dropZoneId, fileNameId) {
  const file = input.files[0];
  if (!file) return;

  // Vérification taille côté client
  if (file.size > 3 * 1024 * 1024) {
    alert('⚠ Image trop lourde ! Maximum 3 Mo.');
    input.value = '';
    return;
  }

  // Afficher le nom du fichier
  document.getElementById(fileNameId).style.display = 'flex';
  document.getElementById('fileNameText' + fileNameId.replace('fileName','')).textContent = file.name;

  // Cacher la drop zone
  document.getElementById(dropZoneId).style.display = 'none';

  // Vider l'URL externe
  document.getElementById(urlInputId).value = '';

  // Aperçu via FileReader
  const reader = new FileReader();
  reader.onload = (e) => {
    showPreview(previewId, e.target.result);
  };
  reader.readAsDataURL(file);
}

// ── Aperçu depuis URL externe ─────────────────────────────────
function previewFromUrl(url, previewId) {
  if (!url.trim()) {
    hidePreview(previewId);
    return;
  }
  const img = document.getElementById('previewImg' + previewId.replace('preview',''));
  img.src = url.trim();
  img.onload  = () => showPreview(previewId, url.trim());
  img.onerror = () => hidePreview(previewId);
}

// ── Afficher / cacher l'aperçu ────────────────────────────────
function showPreview(previewId, src) {
  const wrap = document.getElementById(previewId);
  const suffix = previewId.replace('preview', '');
  const img  = document.getElementById('previewImg' + suffix);
  if (wrap && img) {
    img.src = src;
    wrap.style.display = 'flex';
  }
}

function hidePreview(previewId) {
  const wrap = document.getElementById(previewId);
  if (wrap) wrap.style.display = 'none';
}

// ── Effacer le fichier sélectionné ────────────────────────────
function clearFile(inputId, previewId, urlInputId, dropZoneId, fileNameId) {
  document.getElementById(inputId).value   = '';
  document.getElementById(fileNameId).style.display = 'none';
  document.getElementById(dropZoneId).style.display = 'block';
  hidePreview(previewId);
}

// ── Tout effacer (fichier + URL + aperçu) ────────────────────
function clearAll(inputId, previewId, urlInputId, dropZoneId, fileNameId) {
  clearFile(inputId, previewId, urlInputId, dropZoneId, fileNameId);
  document.getElementById(urlInputId).value = '';
}

// ── Drag & Drop ───────────────────────────────────────────────
document.querySelectorAll('.drop-zone').forEach(zone => {
  zone.addEventListener('dragover', e => {
    e.preventDefault();
    zone.classList.add('dragover');
  });
  zone.addEventListener('dragleave', () => zone.classList.remove('dragover'));
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('dragover');

    const file = e.dataTransfer.files[0];
    if (!file || !file.type.startsWith('image/')) {
      alert('⚠ Veuillez déposer une image (JPG, PNG, WEBP, GIF).');
      return;
    }

    // Trouver quel input file est associé à cette drop zone
    const form = zone.closest('form');
    if (!form) return;
    const input = form.querySelector('input[type="file"]');
    if (!input) return;

    // Simuler la sélection
    const dt = new DataTransfer();
    dt.items.add(file);
    input.files = dt.files;
    input.dispatchEvent(new Event('change'));
  });
});

// ── Genre custom ─────────────────────────────────────────────
function updateColor() {
  const sel    = document.getElementById('genreSelect');
  const custom = document.getElementById('genreCustom');
  if (!sel || !custom) return;
  if (sel.value === 'autre') {
    custom.style.display = 'block';
    custom.name = 'genre';
    sel.name    = 'genre_sel';
  } else {
    custom.style.display = 'none';
    custom.name = 'genre_custom';
    sel.name    = 'genre';
  }
}

// ── Confirm suppression ─────────────────────────────────────
function confirmDelete(id, titre) {
  document.getElementById('deleteId').value = id;
  document.getElementById('confirmText').textContent =
    `Vous allez supprimer "${titre}". Cette action est irréversible.`;
  document.getElementById('confirmOverlay').classList.add('open');
}
function closeConfirm() {
  document.getElementById('confirmOverlay').classList.remove('open');
}
document.getElementById('confirmOverlay').addEventListener('click', function(e) {
  if (e.target === this) closeConfirm();
});

// ── Pré-charger l'aperçu si URL déjà remplie (page modifier) ──
window.addEventListener('load', () => {
  const urlMod = document.getElementById('imgUrlModifier');
  if (urlMod && urlMod.value.trim()) {
    previewFromUrl(urlMod.value.trim(), 'previewModifier');
  }
});
</script>

<?php include 'includes/footer.php'; ?>
