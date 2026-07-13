<?php
require_once 'includes/config.php';

$pageTitle  = 'Boutique';
$activePage = 'shop';
$cssPath    = '';

// ── Récupérer les livres (avec filtre genre) ──────────────────
$db     = getDB();
$genre  = $_GET['genre'] ?? '';
$search = trim($_GET['q'] ?? '');

$where  = [];
$params = [];

if ($genre) {
    $where[]         = 'genre = :genre';
    $params[':genre'] = $genre;
}
if ($search) {
    $where[]          = '(titre LIKE :q OR auteur LIKE :q2)';
    $params[':q']     = "%$search%";
    $params[':q2']    = "%$search%";
}

$sql = 'SELECT * FROM livres' . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . ' ORDER BY id';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$livres = $stmt->fetchAll();

// Genres distincts pour filtres
$genres = $db->query('SELECT DISTINCT genre FROM livres ORDER BY genre')->fetchAll(PDO::FETCH_COLUMN);

// ── Ajouter au panier (AJAX-like via POST) ────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $lid = (int)$_POST['livre_id'];
    if (!isset($_SESSION['panier'])) $_SESSION['panier'] = [];
    $found = false;
    foreach ($_SESSION['panier'] as &$item) {
        if ($item['id'] == $lid) { $item['qte']++; $found = true; break; }
    }
    if (!$found) {
        $s = $db->prepare('SELECT id, titre, prix, image_url FROM livres WHERE id = ?');
        $s->execute([$lid]);
        $livre = $s->fetch();
        if ($livre) {
            $_SESSION['panier'][] = ['id' => $livre['id'], 'titre' => $livre['titre'], 'prix' => $livre['prix'], 'img' => $livre['image_url'], 'qte' => 1];
        }
    }
    header('Location: index.php' . ($genre ? '?genre=' . urlencode($genre) : ''));
    exit;
}

// Couleurs fallback par genre
$genreColors = [
    'غموض'     => ['#3A2050','#1A0830'],
    'مغامرات'  => ['#1A3A5C','#0D1E30'],
    'رومانسية' => ['#8B3A5A','#5C1A30'],
    'إثارة'    => ['#5C2020','#3A0D0D'],
    'تاريخي'   => ['#5C3A10','#3A2008'],
    'كلاسيكي'  => ['#2C3E50','#1A2530'],
];
function coverGrad($genre, $arr) {
    $c = $arr[$genre] ?? ['#6B4A2E','#3A2010'];
    return "linear-gradient(150deg, {$c[0]} 0%, {$c[1]} 100%)";
}

include 'includes/header.php';
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge">✦ &nbsp;Un nouveau chapitre commence&nbsp; ✦</div>
  <h1>Bienvenue dans notre<br><em>librairie de romans</em> 🌸</h1>
  <p class="hero-tagline">❤ &nbsp; Books · Dreams · Adventures &nbsp; ❤</p>
  <div class="hero-features">
    <div class="hero-feat">❤ Livres soigneusement sélectionnés</div>
    <div class="hero-feat">📖 Romans arabes & classiques</div>
    <div class="hero-feat">🎁 Livraison au Maroc</div>
    <div class="hero-feat">💬 Sur Commande ♡</div>
  </div>
</section>

<!-- BOUTIQUE -->
<div class="shop-layout">

  <!-- ── CATALOGUE ── -->
  <div class="catalogue">
    <!-- Barre de recherche -->
    <form method="GET" style="margin-bottom:18px; display:flex; gap:10px;">
      <?php if ($genre): ?>
        <input type="hidden" name="genre" value="<?= e($genre) ?>">
      <?php endif; ?>
      <input type="text" name="q" value="<?= e($search) ?>"
             placeholder="🔍 Rechercher titre, auteur…"
             class="form-input" style="max-width:320px;">
      <button type="submit" class="btn btn-pink btn-sm">Rechercher</button>
      <?php if ($search || $genre): ?>
        <a href="index.php" class="btn btn-ghost btn-sm">✕ Réinitialiser</a>
      <?php endif; ?>
    </form>

    <!-- Filtres par genre -->
    <div class="filter-tabs">
      <a href="index.php<?= $search ? '?q='.urlencode($search) : '' ?>"
         class="filter-tab <?= !$genre ? 'active' : '' ?>">Tous</a>
      <?php foreach ($genres as $g): ?>
        <a href="index.php?genre=<?= urlencode($g) ?><?= $search ? '&q='.urlencode($search) : '' ?>"
           class="filter-tab <?= $genre === $g ? 'active' : '' ?>"><?= e($g) ?></a>
      <?php endforeach; ?>
    </div>

    <div class="section-header">
      <h2 class="section-title">📚 Notre catalogue <span style="color:var(--pink)">♡</span></h2>
      <span class="result-count"><?= count($livres) ?> livre<?= count($livres) !== 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($livres)): ?>
      <div class="empty-state">
        <span class="empty-icon">📚</span>
        <h3>Aucun livre trouvé</h3>
        <p>Essayez une autre recherche ou genre.</p>
      </div>
    <?php else: ?>
    <div class="books-grid">
      <?php foreach ($livres as $livre): ?>
        <?php
          $grad     = coverGrad($livre['genre'], $genreColors);
          $stockCls = $livre['stock'] <= 3 ? 'stock-low' : 'stock-ok';
          $stockTxt = $livre['stock'] <= 3 ? "⚠ {$livre['stock']} restants" : "✓ En stock";
        ?>
        <div class="book-card" onclick="showDetail(<?= $livre['id'] ?>)"
             data-id="<?= $livre['id'] ?>"
             data-titre="<?= e($livre['titre']) ?>"
             data-auteur="<?= e($livre['auteur']) ?>"
             data-isbn="<?= e($livre['isbn']) ?>"
             data-prix="<?= $livre['prix'] ?>"
             data-genre="<?= e($livre['genre']) ?>"
             data-pages="<?= $livre['pages'] ?>"
             data-desc="<?= e($livre['description']) ?>"
             data-img="<?= e($livre['image_url']) ?>"
             data-grad="<?= e($grad) ?>">
          <div class="book-cover">
            <div class="book-cover-fallback" style="background:<?= e($grad) ?>">
              <span class="cover-title-fallback"><?= e($livre['titre']) ?></span>
            </div>
            <?php if ($livre['image_url']): ?>
            <img src="<?= e($livre['image_url']) ?>" alt="<?= e($livre['titre']) ?>"
                 onerror="this.style.display='none'"
                 onload="this.previousElementSibling.style.display='none'"
                 loading="lazy"
                 style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center top;">
            <?php endif; ?>
            <span class="genre-badge"><?= e($livre['genre']) ?></span>
            <span class="stock-badge <?= $stockCls ?>"><?= $stockTxt ?></span>
          </div>
          <div class="book-body">
            <div class="book-title"><?= e($livre['titre']) ?></div>
            <div class="book-author"><?= e($livre['auteur']) ?></div>
            <div class="book-footer">
              <span class="book-price"><?= number_format($livre['prix'],2) ?> DH</span>
              <form method="POST" onclick="event.stopPropagation()">
                <input type="hidden" name="livre_id" value="<?= $livre['id'] ?>">
                <button type="submit" name="ajouter" class="btn-add" title="Ajouter au panier">+</button>
              </form>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- ── PANNEAU DROIT ── -->
  <div class="right-panel">

    <!-- Détail livre -->
    <div class="detail-card" id="detailCard" style="display:none">
      <div class="detail-cover" id="detailCover">
        <div class="detail-cover-fallback" id="detailFallback"></div>
      </div>
      <div class="detail-body">
        <div class="detail-name"     id="detailTitle"></div>
        <div class="detail-author-txt" id="detailAuthor"></div>
        <div class="detail-meta">
          <div class="meta-chip"><div class="meta-label">ISBN</div><div class="meta-value isbn" id="detailISBN"></div></div>
          <div class="meta-chip"><div class="meta-label">Prix</div><div class="meta-value price" id="detailPrix"></div></div>
          <div class="meta-chip"><div class="meta-label">Genre</div><div class="meta-value" id="detailGenre"></div></div>
          <div class="meta-chip"><div class="meta-label">Pages</div><div class="meta-value" id="detailPages"></div></div>
        </div>
        <p id="detailDesc" style="font-size:.84rem;color:var(--brown-light);margin-bottom:14px;font-style:italic;"></p>
        <form method="POST">
          <input type="hidden" name="livre_id" id="detailLivreId">
          <button type="submit" name="ajouter" class="btn btn-pink" style="width:100%">
            🛒 &nbsp;Ajouter au panier
          </button>
        </form>
      </div>
    </div>

    <!-- Mini panier -->
    <div class="cart-sidebar">
      <div class="cart-head">
        <span class="cart-head-title">🛒 Mon panier <span style="color:var(--pink)">♡</span></span>
        <span class="cart-head-count">
          <?= isset($_SESSION['panier']) ? array_sum(array_column($_SESSION['panier'],'qte')) : 0 ?> article(s)
        </span>
      </div>

      <?php if (empty($_SESSION['panier'])): ?>
        <div class="cart-empty">
          <span class="cart-empty-icon">🛒</span>
          Votre panier est vide.<br>
          <em>Choisissez un livre ♡</em>
        </div>
      <?php else: ?>
        <div class="cart-items">
          <?php foreach ($_SESSION['panier'] as $item): ?>
          <div class="cart-item">
            <div class="cart-item-dot"></div>
            <div class="cart-item-info">
              <div class="cart-item-title"><?= e($item['titre']) ?> ×<?= $item['qte'] ?></div>
              <div class="cart-item-price"><?= number_format($item['prix'] * $item['qte'], 2) ?> DH</div>
            </div>
            <a href="panier.php?retirer=<?= $item['id'] ?>" class="remove-btn" title="Retirer">✕</a>
          </div>
          <?php endforeach; ?>
        </div>
        <?php $total = array_sum(array_map(fn($i) => $i['prix'] * $i['qte'], $_SESSION['panier'])); ?>
        <div class="cart-foot">
          <div class="total-row">
            <span class="total-label">Total ♡</span>
            <span class="total-amount"><?= number_format($total, 2) ?> DH</span>
          </div>
          <a href="panier.php" class="btn btn-brown" style="width:100%;justify-content:center">
            ✅ &nbsp;Voir le panier
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
function showDetail(id) {
  const card = document.querySelector(`[data-id="${id}"]`);
  if (!card) return;
  const dc = document.getElementById('detailCard');
  dc.style.display = 'block';

  const img = card.dataset.img;
  const grad = card.dataset.grad;
  const fb  = document.getElementById('detailFallback');
  fb.style.background = grad;
  fb.textContent = card.dataset.titre;
  fb.style.display = 'flex';

  // Remove old image
  const oldImg = document.getElementById('detailImg');
  if (oldImg) oldImg.remove();

  if (img) {
    const el = document.createElement('img');
    el.id = 'detailImg';
    el.src = img;
    el.alt = card.dataset.titre;
    el.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;object-position:center top;';
    el.onerror = () => el.remove();
    el.onload  = () => { fb.style.display = 'none'; };
    document.getElementById('detailCover').appendChild(el);
  }

  document.getElementById('detailTitle').textContent  = card.dataset.titre;
  document.getElementById('detailAuthor').textContent = card.dataset.auteur;
  document.getElementById('detailISBN').textContent   = card.dataset.isbn;
  document.getElementById('detailPrix').textContent   = parseFloat(card.dataset.prix).toFixed(2) + ' DH';
  document.getElementById('detailGenre').textContent  = card.dataset.genre;
  document.getElementById('detailPages').textContent  = card.dataset.pages + ' pages';
  document.getElementById('detailDesc').textContent   = card.dataset.desc;
  document.getElementById('detailLivreId').value      = id;

  dc.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}
</script>

<?php include 'includes/footer.php'; ?>
