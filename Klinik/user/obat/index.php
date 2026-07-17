<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Daftar Obat';
$active = 'obat';

$kats = $pdo->query("SELECT * FROM kategori_obat ORDER BY nama_kategori")->fetchAll();
$apoteks = $pdo->query("SELECT * FROM apotek ORDER BY nama_apotek")->fetchAll();
$selectedApotek = isset($_GET['apotek']) ? (int)$_GET['apotek'] : 0;

function img_u($g) { return $g ? URL_OBAT . e($g) : BASE_URL . 'assets/images/no-image.svg'; }
require_once '../../includes/header_user.php';
?>
<div class="row mb-3">
    <div class="col-md-3 mb-2">
        <select id="filterApotek" class="form-select">
            <option value="">Semua Apotek</option>
            <?php foreach ($apoteks as $a): ?><option value="<?= $a['id_apotek'] ?>" <?= $selectedApotek==$a['id_apotek']?'selected':'' ?>><?= e($a['nama_apotek']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3 mb-2">
        <select id="filterKategori" class="form-select">
            <option value="">Semua Kategori</option>
            <?php foreach ($kats as $k): ?><option value="<?= $k['id_kategori'] ?>"><?= e($k['nama_kategori']) ?></option><?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3 mb-2">
        <input type="number" id="filterHargaMin" class="form-control" placeholder="Harga min (Rp)" min="0">
    </div>
    <div class="col-md-3 mb-2">
        <div class="input-group">
            <span class="input-group-text"><i class="fas fa-search"></i></span>
            <input type="text" id="liveSearch" class="form-control" placeholder="Cari nama obat...">
        </div>
    </div>
</div>
<?php if ($selectedApotek > 0): 
    $ap = $pdo->prepare("SELECT * FROM apotek WHERE id_apotek=?");
    $ap->execute([$selectedApotek]); $ap = $ap->fetch();
?>
<div class="alert alert-info d-flex align-items-center gap-2">
    <i class="fas fa-building"></i>
    <div>Menampilkan harga dan stok dari: <strong><?= e($ap['nama_apotek']) ?></strong> - <?= e($ap['alamat']) ?> <a href="<?= BASE_URL ?>user/obat/" class="btn btn-sm btn-outline-primary ms-2">Reset</a></div>
</div>
<?php endif; ?>
<div class="product-grid" id="produkGrid">
    <?php
    $sql = "SELECT o.*, k.nama_kategori FROM obat o LEFT JOIN kategori_obat k ON k.id_kategori=o.id_kategori ORDER BY o.nama_obat LIMIT 60";
    $produk = $pdo->query($sql)->fetchAll();
    foreach ($produk as $o):
        $img = $o['gambar'] ? URL_OBAT . e($o['gambar']) : BASE_URL . 'assets/images/no-image.svg';
        $harga = rupiah($o['harga']);
        $stok = (int)$o['stok'];
        if ($selectedApotek > 0) {
            $hs = $pdo->prepare("SELECT harga, stok FROM harga_stok_apotek WHERE id_obat=? AND id_apotek=?");
            $hs->execute([$o['id_obat'], $selectedApotek]);
            $hr = $hs->fetch();
            if ($hr) { $harga = rupiah($hr['harga']); $stok = (int)$hr['stok']; }
            else { $stok = 0; }
        }
    ?>
    <div class="product-card">
        <div class="img"><img src="<?= $img ?>" onerror="this.src='<?= BASE_URL ?>assets/images/no-image.svg'" alt=""></div>
        <div class="body">
            <div class="name"><?= e($o['nama_obat']) ?></div>
            <div class="cat"><?= e($o['nama_kategori']) ?></div>
            <div class="price"><?= $harga ?></div>
            <div class="foot">
                <a href="<?= BASE_URL ?>user/obat/detail.php?id=<?= $o['id_obat'] ?><?= $selectedApotek ? '&apotek=' . $selectedApotek : '' ?>" class="btn btn-sm btn-outline-primary flex-grow-1">Detail</a>
                <?php if ($stok > 0): ?>
                <button class="btn btn-sm btn-primary" onclick="addToCart(<?= $o['id_obat'] ?>, <?= $selectedApotek ?>)"><i class="fas fa-cart-plus"></i></button>
                <?php else: ?><span class="btn btn-sm btn-secondary disabled">Habis</span><?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php require_once '../../includes/footer_user.php'; ?>
