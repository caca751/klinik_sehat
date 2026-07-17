<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Checkout';
$active = 'obat';
$uid = $_SESSION['user']['id_user'];

$stmt = $pdo->prepare("SELECT k.*, o.nama_obat, o.harga, o.stok FROM keranjang k JOIN obat o ON o.id_obat=k.id_obat WHERE k.id_user=?");
$stmt->execute([$uid]);
$items = $stmt->fetchAll();
if (empty($items)) { set_flash('error', 'Keranjang kosong.'); redirect(BASE_URL . 'user/keranjang/'); }

$metodes = $pdo->query("SELECT * FROM metode_pembayaran WHERE aktif=1 ORDER BY tipe, nama_metode")->fetchAll();
$user = $pdo->prepare("SELECT id_user, alamat FROM users WHERE id_user=?");
$user->execute([$uid]); $user = $user->fetch();

$total = 0;
foreach ($items as $it) $total += $it['harga'] * $it['jumlah'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token tidak valid.'); redirect(''); }
    $metode_id = !empty($_POST['id_metode']) ? (int)$_POST['id_metode'] : null;
    $alamat = clean($_POST['alamat']);
    if (empty($alamat)) { set_flash('error', 'Alamat pengiriman wajib diisi.'); redirect(''); }

    try {
        $pdo->beginTransaction();
        $kode = generate_kode_pesanan($pdo);
        $ins = $pdo->prepare("INSERT INTO pesanan (kode_pesanan, id_user, tanggal, total, status) VALUES (?,?,NOW(),?,'Menunggu Pembayaran')");
        $ins->execute([$kode, $uid, $total]);
        $id_pesanan = $pdo->lastInsertId();

        $insD = $pdo->prepare("INSERT INTO detail_pesanan (id_pesanan, id_obat, id_apotek, harga, jumlah, subtotal) VALUES (?,?,?,?,?,?)");
        $upStokObat = $pdo->prepare("UPDATE obat SET stok = stok - ? WHERE id_obat=? AND stok >= ?");
        $upStokApotek = $pdo->prepare("UPDATE harga_stok_apotek SET stok = stok - ? WHERE id_obat=? AND id_apotek=? AND stok >= ?");
        foreach ($items as $it) {
            $sub = $it['harga'] * $it['jumlah'];
            $insD->execute([$id_pesanan, $it['id_obat'], $it['id_apotek'] ?? null, $it['harga'], $it['jumlah'], $sub]);
            $upStokObat->execute([$it['jumlah'], $it['id_obat'], $it['jumlah']]);
            if (($it['id_apotek'] ?? 0) > 0) {
                $upStokApotek->execute([$it['jumlah'], $it['id_obat'], $it['id_apotek'], $it['jumlah']]);
            }
        }
        $metode_nama = 'Menunggu';
        if ($metode_id) {
            $m = $pdo->prepare("SELECT nama_metode FROM metode_pembayaran WHERE id_metode=?");
            $m->execute([$metode_id]);
            $mn = $m->fetch();
            if ($mn) $metode_nama = $mn['nama_metode'];
        }
        $pdo->prepare("INSERT INTO pembayaran (id_pesanan, id_metode, status) VALUES (?,?, 'Menunggu')")->execute([$id_pesanan, $metode_id]);
        /* simpan alamat ke user */
        $pdo->prepare("UPDATE users SET alamat=? WHERE id_user=?")->execute([$alamat, $uid]);
        $pdo->prepare("DELETE FROM keranjang WHERE id_user=?")->execute([$uid]);
        $pdo->commit();

        send_notif($pdo, $uid, 'Pesanan Dibuat', "Pesanan $kode berhasil dibuat. Silakan lakukan pembayaran.");
        set_flash('success', 'Pesanan berhasil dibuat. Silakan lakukan pembayaran.');
        redirect(BASE_URL . 'user/pesanan/detail.php?id=' . $id_pesanan);
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('error', 'Gagal membuat pesanan: ' . $e->getMessage());
        redirect('');
    }
}

require_once '../../includes/header_user.php';
?>
<div class="row g-3">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">Alamat & Pembayaran</div>
            <div class="card-body">
                <form method="post" id="formCheckout">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label">Alamat Pengiriman</label>
                        <textarea name="alamat" class="form-control" rows="3" required><?= e($user['alamat'] ?? '') ?></textarea></div>
                    <div class="mb-3"><label class="form-label">Metode Pembayaran</label>
                        <select name="id_metode" class="form-select" required id="selectMetode">
                            <option value="">Pilih Metode Pembayaran</option>
                            <?php foreach($metodes as $m): ?>
                            <option value="<?= $m['id_metode'] ?>" data-nama="<?= e($m['nama_metode']) ?>" data-tipe="<?= e($m['tipe']) ?>" data-nomor="<?= e($m['nomor']) ?>" data-pemilik="<?= e($m['pemilik']) ?>">
                                <?= e($m['nama_metode']) ?> (<?= e($m['tipe']==='bank'?'Bank':'E-Wallet') ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div id="detailMetode" class="mt-2 small text-muted" style="display:none"></div>
                    </div>
                    <div class="alert alert-info small">
                        <i class="fas fa-info-circle me-1"></i> Setelah checkout, silakan lakukan pembayaran sesuai instruksi.
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card sticky-summary">
            <div class="card-header">Ringkasan</div>
            <div class="card-body">
                <?php foreach ($items as $it): ?>
                <div class="d-flex justify-content-between mb-2">
                    <span><?= e($it['nama_obat']) ?> × <?= $it['jumlah'] ?></span>
                    <span><?= rupiah($it['harga'] * $it['jumlah']) ?></span>
                </div>
                <?php endforeach; ?>
                <hr>
                <div class="d-flex justify-content-between fw-bold">
                    <span>Total</span><span class="text-primary"><?= rupiah($total) ?></span>
                </div>
                <button type="submit" form="formCheckout" class="btn btn-primary w-100 mt-3"><i class="fas fa-check me-1"></i> Buat Pesanan</button>
                <a href="<?= BASE_URL ?>user/keranjang/" class="btn btn-outline-secondary w-100 mt-2">Kembali</a>
            </div>
        </div>
    </div>
</div>
<?php require_once '../../includes/footer_user.php'; ?>
