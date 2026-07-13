/* ============================================================
   Book Store — Feuille de style principale
   Thème : crème · rose poudré · brun chaud
   ============================================================ */

:root {
  --cream:       #F5EDE3;
  --cream-dark:  #EDD8C8;
  --parchment:   #FAF3EC;
  --brown:       #3B1F0F;
  --brown-mid:   #7A4928;
  --brown-light: #C4956A;
  --pink:        #E8A4A4;
  --pink-light:  #F2CECE;
  --pink-pale:   #FBF0F0;
  --sage:        #8FA67A;
  --gold:        #D4A84B;
  --white:       #FFFFFF;
  --danger:      #C0392B;
  --success:     #27AE60;
  --radius:      14px;
  --shadow:      0 4px 20px rgba(59,31,15,.10);
  --shadow-lg:   0 12px 40px rgba(59,31,15,.16);
}

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: 'Lato', 'Tajawal', sans-serif;
  background: var(--parchment);
  color: var(--brown);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

/* ── HEADER ─────────────────────────────────────────────── */
.site-header {
  background: var(--cream);
  border-bottom: 2px dashed var(--pink-light);
  padding: 0 40px;
  position: sticky;
  top: 0;
  z-index: 200;
  box-shadow: 0 2px 12px rgba(59,31,15,.07);
}
.header-inner {
  max-width: 1200px;
  margin: 0 auto;
  height: 70px;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.logo {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
}
.logo-book { font-size: 1.6rem; }
.logo-text {
  font-family: 'Playfair Display', serif;
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--brown);
  line-height: 1;
}
.logo-tagline { font-size: 0.65rem; color: var(--brown-light); letter-spacing: 1.5px; margin-top: 2px; }

.header-nav {
  display: flex;
  align-items: center;
  gap: 6px;
}
.nav-link {
  padding: 7px 14px;
  border-radius: 20px;
  text-decoration: none;
  font-size: 0.84rem;
  font-weight: 700;
  color: var(--brown-mid);
  transition: all .2s;
}
.nav-link:hover, .nav-link.active {
  background: var(--pink-pale);
  color: var(--brown);
}
.nav-client {
  font-size: 0.82rem;
  font-weight: 700;
  color: var(--brown-mid);
  background: var(--pink-pale);
  border: 1.5px solid var(--pink-light);
  padding: 5px 12px;
  border-radius: 20px;
}
.cart-pill {
  display: flex;
  align-items: center;
  gap: 6px;
  background: var(--pink-light);
  border: 2px solid var(--pink);
  border-radius: 20px;
  padding: 6px 16px;
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--brown);
  text-decoration: none;
  transition: all .2s;
}
.cart-pill:hover { background: var(--pink); color: var(--white); }
#cartBadge {
  background: var(--brown);
  color: var(--white);
  border-radius: 50%;
  width: 19px; height: 19px;
  display: inline-flex;
  align-items: center; justify-content: center;
  font-size: 0.72rem;
}

/* ── HERO ────────────────────────────────────────────────── */
.hero {
  background: linear-gradient(135deg, var(--cream) 0%, var(--pink-pale) 60%, var(--cream-dark) 100%);
  border-bottom: 2px dashed var(--pink-light);
  padding: 52px 40px 40px;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.hero::before, .hero::after {
  content: '✦';
  position: absolute;
  font-size: 5rem;
  color: var(--pink-light);
  opacity: .35;
  top: 20px;
}
.hero::before { left: 40px; }
.hero::after  { right: 40px; transform: rotate(20deg); }
.hero-badge {
  display: inline-block;
  background: var(--pink-pale);
  border: 1.5px solid var(--pink);
  border-radius: 30px;
  padding: 5px 20px;
  font-size: .78rem;
  color: var(--brown-mid);
  letter-spacing: 1px;
  font-weight: 700;
  margin-bottom: 14px;
}
.hero h1 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(1.8rem, 4vw, 3rem);
  font-weight: 700;
  color: var(--brown);
  letter-spacing: -1px;
  line-height: 1.2;
  margin-bottom: 10px;
}
.hero h1 em { font-style: italic; color: var(--brown-mid); }
.hero-tagline { font-size: .95rem; color: var(--brown-light); margin-bottom: 24px; letter-spacing: 2px; }
.hero-features { display: flex; justify-content: center; gap: 28px; flex-wrap: wrap; }
.hero-feat { display: flex; align-items: center; gap: 7px; font-size: .85rem; color: var(--brown-mid); font-weight: 700; }

/* ── LAYOUT BOUTIQUE ─────────────────────────────────────── */
.shop-layout {
  max-width: 1200px;
  margin: 0 auto;
  padding: 36px 40px 80px;
  display: grid;
  grid-template-columns: 1fr 370px;
  gap: 36px;
  align-items: start;
  flex: 1;
}
.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.3rem;
  font-weight: 600;
  color: var(--brown);
}
.result-count { font-size: .8rem; color: var(--brown-light); font-style: italic; }

/* ── FILTRES ─────────────────────────────────────────────── */
.filter-tabs { display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap; }
.filter-tab {
  padding: 6px 16px;
  border-radius: 20px;
  border: 1.5px solid var(--cream-dark);
  background: var(--cream);
  color: var(--brown-light);
  font-size: .8rem; font-weight: 700;
  cursor: pointer;
  text-decoration: none;
  transition: all .2s;
}
.filter-tab:hover { border-color: var(--pink); color: var(--brown-mid); background: var(--pink-pale); }
.filter-tab.active { background: var(--pink-light); border-color: var(--pink); color: var(--brown); }

/* ── GRILLE LIVRES ───────────────────────────────────────── */
.books-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(178px, 1fr)); gap: 18px; }

.book-card {
  background: var(--white);
  border: 2px solid var(--cream-dark);
  border-radius: var(--radius);
  overflow: hidden;
  cursor: pointer;
  transition: all .25s;
  text-decoration: none;
  color: inherit;
  display: flex;
  flex-direction: column;
}
.book-card:hover {
  border-color: var(--pink);
  transform: translateY(-4px) rotate(.4deg);
  box-shadow: var(--shadow-lg);
}
.book-cover {
  height: 200px;
  position: relative;
  overflow: hidden;
  background: var(--cream-dark);
  flex-shrink: 0;
}
.book-cover img {
  width: 100%; height: 100%;
  object-fit: cover;
  object-position: center top;
  display: block;
  transition: transform .35s;
}
.book-card:hover .book-cover img { transform: scale(1.05); }
.book-cover-fallback {
  position: absolute; inset: 0;
  display: flex; align-items: center; justify-content: center;
  padding: 16px;
}
.book-cover-fallback::after {
  content: '';
  position: absolute;
  left: 0; top: 0; bottom: 0;
  width: 9px;
  background: rgba(0,0,0,.15);
}
.cover-title-fallback {
  font-family: 'Playfair Display', serif;
  font-size: .82rem; font-weight: 600; font-style: italic;
  color: rgba(255,255,255,.92);
  text-align: center; line-height: 1.35; z-index: 1;
}
.genre-badge {
  position: absolute;
  bottom: 8px; right: 8px;
  font-size: .62rem; font-weight: 700;
  padding: 3px 8px;
  border-radius: 10px;
  background: rgba(0,0,0,.4);
  backdrop-filter: blur(4px);
  color: rgba(255,255,255,.95);
  z-index: 2;
}
.stock-badge {
  position: absolute;
  top: 8px; left: 8px;
  font-size: .6rem; font-weight: 700;
  padding: 3px 7px;
  border-radius: 8px;
  z-index: 2;
}
.stock-ok  { background: rgba(39,174,96,.85); color: #fff; }
.stock-low { background: rgba(230,126,34,.9); color: #fff; }

.book-body { padding: 12px 13px 13px; flex: 1; display: flex; flex-direction: column; }
.book-title {
  font-family: 'Playfair Display', serif;
  font-size: .9rem; font-weight: 600;
  color: var(--brown);
  margin-bottom: 2px;
  line-height: 1.3;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.book-author { font-size: .73rem; color: var(--brown-light); margin-bottom: 10px; font-style: italic; }
.book-footer { display: flex; align-items: center; justify-content: space-between; margin-top: auto; }
.book-price { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 700; color: var(--brown-mid); }

/* ── BOUTONS ─────────────────────────────────────────────── */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 9px 20px;
  border-radius: 30px;
  border: none;
  cursor: pointer;
  font-family: 'Lato', sans-serif;
  font-size: .875rem;
  font-weight: 700;
  text-decoration: none;
  transition: all .2s;
}
.btn-pink { background: linear-gradient(135deg, var(--pink), #D97070); color: var(--white); box-shadow: 0 4px 14px rgba(232,164,164,.45); }
.btn-pink:hover { opacity: .88; transform: translateY(-1px); }
.btn-brown { background: linear-gradient(135deg, var(--brown-mid), var(--brown)); color: var(--white); box-shadow: 0 4px 14px rgba(59,31,15,.25); }
.btn-brown:hover { opacity: .88; transform: translateY(-1px); }
.btn-ghost { background: transparent; border: 1.5px solid var(--cream-dark); color: var(--brown-mid); }
.btn-ghost:hover { border-color: var(--pink); color: var(--brown); background: var(--pink-pale); }
.btn-sm { padding: 6px 14px; font-size: .8rem; }
.btn-add {
  background: var(--pink-pale);
  border: 1.5px solid var(--pink-light);
  color: var(--pink);
  width: 30px; height: 30px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all .2s;
}
.btn-add:hover { background: var(--pink); color: var(--white); border-color: var(--pink); transform: scale(1.1); }

/* ── PANNEAU DÉTAIL ──────────────────────────────────────── */
.right-panel { position: sticky; top: 88px; }
.detail-card {
  background: var(--white);
  border: 2px solid var(--pink-light);
  border-radius: var(--radius);
  overflow: hidden;
  margin-bottom: 20px;
}
.detail-cover { height: 260px; position: relative; overflow: hidden; background: var(--cream-dark); }
.detail-cover img { width: 100%; height: 100%; object-fit: cover; object-position: center top; }
.detail-cover-fallback {
  position: absolute; inset: 0;
  display: flex; align-items: center; justify-content: center;
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem; font-style: italic; font-weight: 600;
  color: rgba(255,255,255,.9);
  text-align: center; padding: 24px; line-height: 1.4;
}
.detail-cover::after { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 10px; background: rgba(0,0,0,.12); z-index: 1; }
.detail-body { padding: 18px 20px; }
.detail-name { font-family: 'Playfair Display', serif; font-size: 1.1rem; font-weight: 700; color: var(--brown); margin-bottom: 2px; }
.detail-author-txt { font-size: .82rem; color: var(--brown-light); font-style: italic; margin-bottom: 14px; }
.detail-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
.meta-chip { background: var(--parchment); border: 1.5px solid var(--cream-dark); border-radius: 10px; padding: 9px 12px; }
.meta-label { font-size: .63rem; color: var(--brown-light); text-transform: uppercase; letter-spacing: 1.2px; font-weight: 700; margin-bottom: 3px; }
.meta-value { font-size: .88rem; font-weight: 700; color: var(--brown); }
.meta-value.isbn { color: var(--brown-mid); font-size: .78rem; }
.meta-value.price { color: var(--pink); font-size: 1rem; font-family: 'Playfair Display', serif; }

/* ── PANIER SIDEBAR ──────────────────────────────────────── */
.cart-sidebar {
  background: var(--white);
  border: 2px solid var(--pink-light);
  border-radius: var(--radius);
  overflow: hidden;
}
.cart-head {
  background: linear-gradient(135deg, var(--pink-pale), var(--cream));
  padding: 14px 20px;
  border-bottom: 1.5px dashed var(--pink-light);
  display: flex; align-items: center; justify-content: space-between;
}
.cart-head-title { font-family: 'Playfair Display', serif; font-size: 1rem; font-weight: 700; color: var(--brown); }
.cart-head-count { font-size: .75rem; color: var(--brown-light); font-style: italic; }
.cart-empty { padding: 36px 20px; text-align: center; color: var(--brown-light); font-size: .875rem; font-style: italic; }
.cart-empty-icon { font-size: 2.2rem; margin-bottom: 8px; opacity: .5; display: block; }
.cart-items { padding: 0 8px; max-height: 280px; overflow-y: auto; }
.cart-item {
  display: flex; align-items: center; gap: 10px;
  padding: 10px;
  border-bottom: 1px dashed var(--cream-dark);
  border-radius: 8px;
  transition: background .15s;
}
.cart-item:last-child { border-bottom: none; }
.cart-item:hover { background: var(--pink-pale); }
.cart-item-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--pink); flex-shrink: 0; }
.cart-item-info { flex: 1; min-width: 0; }
.cart-item-title { font-size: .82rem; font-weight: 700; color: var(--brown); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cart-item-price { font-size: .75rem; color: var(--brown-light); font-style: italic; }
.remove-btn {
  background: none; border: 1.5px solid transparent; color: var(--brown-light);
  width: 24px; height: 24px; border-radius: 50%; cursor: pointer;
  font-size: .7rem; display: flex; align-items: center; justify-content: center;
  transition: all .2s;
}
.remove-btn:hover { background: var(--pink-pale); border-color: var(--pink-light); color: var(--pink); }
.cart-foot { padding: 16px 20px; border-top: 1.5px dashed var(--pink-light); background: linear-gradient(135deg, var(--parchment), var(--cream)); }
.total-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.total-label { font-family: 'Playfair Display', serif; font-size: .9rem; color: var(--brown-mid); font-style: italic; }
.total-amount { font-family: 'Playfair Display', serif; font-size: 1.3rem; font-weight: 700; color: var(--brown); }

/* ── FORMULAIRES ─────────────────────────────────────────── */
.form-card {
  background: var(--white);
  border: 2px solid var(--pink-light);
  border-radius: var(--radius);
  padding: 36px 40px;
  max-width: 480px;
  margin: 48px auto;
  box-shadow: var(--shadow);
}
.form-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.6rem; font-weight: 700;
  color: var(--brown);
  margin-bottom: 6px;
  text-align: center;
}
.form-subtitle { text-align: center; color: var(--brown-light); font-size: .875rem; margin-bottom: 28px; }
.form-group { margin-bottom: 18px; }
.form-label { display: block; font-size: .8rem; font-weight: 700; color: var(--brown); margin-bottom: 6px; letter-spacing: .3px; }
.form-input, .form-select, .form-textarea {
  width: 100%;
  border: 1.5px solid var(--cream-dark);
  border-radius: 10px;
  padding: 10px 14px;
  font-family: 'Lato', sans-serif;
  font-size: .875rem;
  color: var(--brown);
  background: var(--parchment);
  outline: none;
  transition: border-color .2s;
}
.form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--pink); background: var(--white); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-link { color: var(--brown-mid); font-size: .84rem; text-align: center; margin-top: 16px; display: block; }
.form-link a { color: var(--pink); font-weight: 700; }

/* ── ALERTES ─────────────────────────────────────────────── */
.alert {
  padding: 12px 18px;
  border-radius: 10px;
  font-size: .875rem;
  font-weight: 700;
  margin-bottom: 18px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.alert-error   { background: #FDEDEC; border: 1.5px solid #F1948A; color: var(--danger); }
.alert-success { background: #EAFAF1; border: 1.5px solid #82E0AA; color: var(--success); }
.alert-info    { background: var(--pink-pale); border: 1.5px solid var(--pink-light); color: var(--brown-mid); }

/* ── PAGE COMMANDES ──────────────────────────────────────── */
.page-wrap { max-width: 1100px; margin: 0 auto; padding: 40px 40px 80px; flex: 1; }
.page-header-row { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 30px; flex-wrap: wrap; gap: 12px; }
.page-h1 { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 700; color: var(--brown); }
.page-sub { color: var(--brown-light); font-size: .875rem; margin-top: 2px; }

/* ── TABLE ───────────────────────────────────────────────── */
.table-card { background: var(--white); border: 2px solid var(--pink-light); border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow); margin-bottom: 28px; }
.table-card table { width: 100%; border-collapse: collapse; }
.table-card th {
  background: linear-gradient(135deg, var(--pink-pale), var(--cream));
  font-size: .8rem; font-weight: 700;
  color: var(--brown-mid);
  text-transform: uppercase;
  letter-spacing: .8px;
  padding: 14px 18px;
  text-align: left;
  border-bottom: 1.5px dashed var(--pink-light);
}
.table-card td { padding: 13px 18px; border-bottom: 1px dashed var(--cream-dark); vertical-align: middle; font-size: .875rem; }
.table-card tr:last-child td { border-bottom: none; }
.table-card tr:hover td { background: var(--pink-pale); }

/* ── STATUTS ─────────────────────────────────────────────── */
.statut {
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: .72rem; font-weight: 700;
  letter-spacing: .5px;
}
.statut-en_attente  { background: #FEF9E7; color: #D4AC0D;  border: 1px solid #F9E79F; }
.statut-confirmee   { background: #EBF5FB; color: #2980B9;  border: 1px solid #AED6F1; }
.statut-expediee    { background: #EAF0FB; color: #1A5276;  border: 1px solid #85C1E9; }
.statut-livree      { background: #EAFAF1; color: #1E8449;  border: 1px solid #82E0AA; }
.statut-annulee     { background: #FDEDEC; color: #C0392B;  border: 1px solid #F1948A; }

/* ── DÉTAIL COMMANDE ─────────────────────────────────────── */
.commande-header {
  background: linear-gradient(135deg, var(--cream), var(--pink-pale));
  border: 2px solid var(--pink-light);
  border-radius: var(--radius);
  padding: 24px 28px;
  margin-bottom: 24px;
  display: flex; flex-wrap: wrap; gap: 20px;
  align-items: center; justify-content: space-between;
}
.commande-id { font-family: 'Playfair Display', serif; font-size: 1.4rem; font-weight: 700; color: var(--brown); }
.commande-date { color: var(--brown-light); font-size: .84rem; margin-top: 3px; }
.commande-total { font-family: 'Playfair Display', serif; font-size: 1.6rem; font-weight: 700; color: var(--brown-mid); }

/* ── STATS CARDS ─────────────────────────────────────────── */
.stats-row { display: grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap: 16px; margin-bottom: 30px; }
.stat-card {
  background: var(--white);
  border: 2px solid var(--pink-light);
  border-radius: var(--radius);
  padding: 20px 24px;
  box-shadow: var(--shadow);
  text-align: center;
}
.stat-icon { font-size: 2rem; margin-bottom: 8px; }
.stat-num { font-family: 'Playfair Display', serif; font-size: 1.8rem; font-weight: 700; color: var(--brown); }
.stat-label { font-size: .8rem; color: var(--brown-light); font-weight: 700; margin-top: 2px; letter-spacing: .5px; }

/* ── PANIER PAGE ─────────────────────────────────────────── */
.panier-layout { max-width: 900px; margin: 0 auto; padding: 40px 40px 80px; flex: 1; }
.panier-table { width: 100%; }
.qty-input {
  width: 56px; text-align: center;
  border: 1.5px solid var(--cream-dark); border-radius: 8px;
  padding: 5px; font-family: 'Lato', sans-serif; font-size: .875rem;
  background: var(--parchment);
}

/* ── EMPTY STATE ─────────────────────────────────────────── */
.empty-state { text-align: center; padding: 60px 20px; }
.empty-icon  { font-size: 3.5rem; margin-bottom: 12px; opacity: .4; display: block; }
.empty-state h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--brown-mid); margin-bottom: 8px; }
.empty-state p  { font-size: .875rem; color: var(--brown-light); }

/* ── FOOTER ──────────────────────────────────────────────── */
.site-footer { background: var(--cream); border-top: 2px dashed var(--pink-light); padding: 28px 20px; text-align: center; margin-top: auto; }
.footer-inner { }
.footer-logo { display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 1.1rem; margin-bottom: 6px; }
.footer-logo strong { font-family: 'Playfair Display', serif; font-size: 1.15rem; color: var(--brown-mid); }
.site-footer p { font-size: .82rem; color: var(--brown-light); margin-bottom: 2px; }
.footer-heart { color: var(--pink); font-weight: 700; }
.footer-sub { font-style: italic; opacity: .7; }

/* ── SCROLLBAR ───────────────────────────────────────────── */
::-webkit-scrollbar { width: 5px; }
::-webkit-scrollbar-track { background: var(--parchment); }
::-webkit-scrollbar-thumb { background: var(--pink-light); border-radius: 3px; }

/* ── RESPONSIVE ──────────────────────────────────────────── */
@media (max-width: 900px) {
  .shop-layout { grid-template-columns: 1fr; padding: 20px 20px 60px; }
  .right-panel { position: static; }
  .header-inner, .hero { padding-left: 20px; padding-right: 20px; }
  .hero::before, .hero::after { display: none; }
  .page-wrap { padding: 24px 20px 60px; }
  .form-card { margin: 24px 20px; padding: 24px 20px; }
  .panier-layout { padding: 24px 20px 60px; }
  .form-row { grid-template-columns: 1fr; }
  .site-header { padding: 0 20px; }
  .header-nav { gap: 4px; }
  .nav-link { padding: 6px 10px; font-size: .78rem; }
}
