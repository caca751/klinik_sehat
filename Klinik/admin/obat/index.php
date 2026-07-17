<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Data Obat';
$active = 'obat';

$kats = $pdo->query("SELECT * FROM kategori_obat ORDER BY nama_kategori")->fetchAll();
$suppliers = $pdo->query("SELECT * FROM supplier ORDER BY nama_supplier")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $act = $_POST['act'] ?? '';
    $nama = clean($_POST['nama_obat']);
    $id_kat = (int)$_POST['id_kategori'];
    $id_sup = (int)$_POST['id_supplier'];
    $desc = clean($_POST['deskripsi']);
    $exp = clean($_POST['tanggal_expired']);

    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $up = upload_file($_FILES['gambar'], UPLOAD_OBAT);
        if (!$up['success']) { set_flash('error', $up['msg']); redirect(''); }
        $gambar = $up['file'];
    }

    if ($act === 'tambah') {
        $kode = generate_kode_obat($pdo);
        $pdo->prepare("INSERT INTO obat (kode_obat,nama_obat,id_kategori,id_supplier,deskripsi,gambar,tanggal_expired,created_at) VALUES (?,?,?,?,?,?,?,NOW())")
            ->execute([$kode, $nama, $id_kat, $id_sup, $desc, $gambar, $exp ?: null]);
        set_flash('success', 'Obat berhasil ditambahkan.');
    } elseif ($act === 'edit') {
        $id = (int)$_POST['id'];
        if ($gambar === null) {
            $pdo->prepare("UPDATE obat SET nama_obat=?,id_kategori=?,id_supplier=?,deskripsi=?,tanggal_expired=? WHERE id_obat=?")
                ->execute([$nama, $id_kat, $id_sup, $desc, $exp ?: null, $id]);
        } else {
            $old = $pdo->prepare("SELECT gambar FROM obat WHERE id_obat=?");
            $old->execute([$id]); $o = $old->fetch();
            if ($o['gambar'] && file_exists(UPLOAD_OBAT . $o['gambar'])) unlink(UPLOAD_OBAT . $o['gambar']);
            $pdo->prepare("UPDATE obat SET nama_obat=?,id_kategori=?,id_supplier=?,deskripsi=?,gambar=?,tanggal_expired=? WHERE id_obat=?")
                ->execute([$nama, $id_kat, $id_sup, $desc, $gambar, $exp ?: null, $id]);
        }
        set_flash('success', 'Obat berhasil diupdate.');
    }
    redirect('');
}
if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id = (int)$_GET['hapus'];
    $o = $pdo->prepare("SELECT gambar FROM obat WHERE id_obat=?");
    $o->execute([$id]); $row = $o->fetch();
    if ($row['gambar'] && file_exists(UPLOAD_OBAT . $row['gambar'])) unlink(UPLOAD_OBAT . $row['gambar']);
    $pdo->prepare("DELETE FROM obat WHERE id_obat=?")->execute([$id]);
    set_flash('success', 'Obat berhasil dihapus.');
    redirect('');
}

$obats = $pdo->query("SELECT o.*, k.nama_kategori, s.nama_supplier FROM obat o LEFT JOIN kategori_obat k ON k.id_kategori=o.id_kategori LEFT JOIN supplier s ON s.id_supplier=o.id_supplier ORDER BY o.id_obat")->fetchAll();

function img_obat($g) {
    return $g ? URL_OBAT . e($g) : BASE_URL . 'assets/images/no-image.svg';
}
require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-pills me-2"></i>Daftar Obat</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm"><i class="fas fa-plus me-1"></i> Tambah</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>Gambar</th><th>Kode</th><th>Nama</th><th>Kategori</th><th>Harga</th><th>Stok</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($obats as $o): ?>
                <tr>
                    <td><img src="<?= img_obat($o['gambar']) ?>" class="img-thumb"></td>
                    <td><?= e($o['kode_obat']) ?></td>
                    <td><?= e($o['nama_obat']) ?></td>
                    <td><?= e($o['nama_kategori']) ?></td>
                    <td><?= rupiah($o['harga']) ?></td>
                    <td><span class="badge bg-<?= $o['stok'] <= 20 ? 'warning' : 'light' ?>"><?= $o['stok'] ?></span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalDetail"
                            data-kode="<?= e($o['kode_obat']) ?>" data-nama="<?= e($o['nama_obat']) ?>" data-kat="<?= e($o['nama_kategori']) ?>" data-sup="<?= e($o['nama_supplier']) ?>" data-harga="<?= rupiah($o['harga']) ?>" data-stok="<?= $o['stok'] ?>" data-exp="<?= e($o['tanggal_expired']) ?>" data-desc="<?= e($o['deskripsi']) ?>" data-img="<?= img_obat($o['gambar']) ?>"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm"
                            data-id="<?= $o['id_obat'] ?>" data-nama="<?= e($o['nama_obat']) ?>" data-kat="<?= $o['id_kategori'] ?>" data-sup="<?= $o['id_supplier'] ?>" data-harga="<?= $o['harga'] ?>" data-stok="<?= $o['stok'] ?>" data-exp="<?= e($o['tanggal_expired']) ?>" data-desc="<?= e($o['deskripsi']) ?>" data-img="<?= img_obat($o['gambar']) ?>"><i class="fas fa-edit"></i></button>
                        <a href="?hapus=<?= $o['id_obat'] ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="konfirmHapus(this.href,'Hapus obat ini?');return false;"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="post" enctype="multipart/form-data" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="act" id="fAct" value="tambah">
            <input type="hidden" name="id" id="fId">
            <div class="modal-header"><h6 class="modal-title">Form Obat</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3"><label class="form-label">Nama Obat</label><input type="text" name="nama_obat" id="fNama" class="form-control" required></div>
                        <div class="row">
                            <div class="col-6 mb-3"><label class="form-label">Kategori</label>
                                <select name="id_kategori" id="fKat" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($kats as $k): ?><option value="<?= $k['id_kategori'] ?>"><?= e($k['nama_kategori']) ?></option><?php endforeach; ?>
                                </select></div>
                            <div class="col-6 mb-3"><label class="form-label">Supplier</label>
                                <select name="id_supplier" id="fSup" class="form-select" required>
                                    <option value="">-- Pilih --</option>
                                    <?php foreach ($suppliers as $s): ?><option value="<?= $s['id_supplier'] ?>"><?= e($s['nama_supplier']) ?></option><?php endforeach; ?>
                                </select></div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3"><label class="form-label">Harga (Rp)</label><input type="number" name="harga" id="fHarga" class="form-control" min="0" required></div>
                            <div class="col-6 mb-3"><label class="form-label">Stok</label><input type="number" name="stok" id="fStok" class="form-control" min="0" required></div>
                        </div>
                        <div class="mb-3"><label class="form-label">Tanggal Expired</label><input type="date" name="tanggal_expired" id="fExp" class="form-control"></div>
                        <div class="mb-3"><label class="form-label">Deskripsi</label><textarea name="deskripsi" id="fDesc" class="form-control" rows="2"></textarea></div>
                    </div>
                    <div class="col-md-4 text-center">
                        <label class="form-label">Gambar Obat</label>
                        <img id="fPreview" src="<?= BASE_URL ?>assets/images/no-image.svg" class="img-thumb mb-2" style="width:100%;height:140px">
                        <input type="file" name="gambar" class="form-control" accept="image/*" onchange="previewImg(this)">
                        <small class="text-muted">Kosongkan jika tidak diubah (edit)</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h6 class="modal-title">Detail Obat</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-4 text-center"><img id="dImg" src="" class="img-thumb" style="width:100%;height:160px"></div>
                <div class="col-md-8">
                    <p><strong>Kode:</strong> <span id="dKode"></span></p>
                    <p><strong>Nama:</strong> <span id="dNama"></span></p>
                    <p><strong>Kategori:</strong> <span id="dKat"></span></p>
                    <p><strong>Supplier:</strong> <span id="dSup"></span></p>
                    <p><strong>Harga:</strong> <span id="dHarga"></span></p>
                    <p><strong>Stok:</strong> <span id="dStok"></span></p>
                    <p><strong>Expired:</strong> <span id="dExp"></span></p>
                    <p><strong>Deskripsi:</strong> <span id="dDesc"></span></p>
                </div>
            </div>
        </div>
    </div></div>
</div>
<?php
$extra_js = '<script>
$("#table").DataTable({responsive:true, language:{url:"https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"}});
function previewImg(input){ if(input.files && input.files[0]){ var r=new FileReader(); r.onload=function(e){ $("#fPreview").attr("src",e.target.result); }; r.readAsDataURL(input.files[0]); } }
$("#modalForm").on("show.bs.modal", function(e){
    var b = $(e.relatedTarget);
    if (b.data("id")) {
        $("#fAct").val("edit"); $("#fId").val(b.data("id")); $("#fNama").val(b.data("nama"));
        $("#fKat").val(b.data("kat")); $("#fSup").val(b.data("sup")); $("#fHarga").val(b.data("harga"));
        $("#fStok").val(b.data("stok")); $("#fExp").val(b.data("exp")); $("#fDesc").val(b.data("desc"));
        $("#fPreview").attr("src", b.data("img")); $(this).find(".modal-title").text("Edit Obat");
    } else {
        $("#fAct").val("tambah"); $("#fId").val(""); $("#fNama").val(""); $("#fKat").val(""); $("#fSup").val("");
        $("#fHarga").val(""); $("#fStok").val(""); $("#fExp").val(""); $("#fDesc").val(""); $("#fPreview").attr("src","'.BASE_URL.'assets/images/no-image.svg");
        $(this).find(".modal-title").text("Tambah Obat");
    }
});
$("#modalDetail").on("show.bs.modal", function(e){
    var b = $(e.relatedTarget);
    $("#dImg").attr("src", b.data("img")); $("#dKode").text(b.data("kode")); $("#dNama").text(b.data("nama"));
    $("#dKat").text(b.data("kat")); $("#dSup").text(b.data("sup")); $("#dHarga").text(b.data("harga"));
    $("#dStok").text(b.data("stok")); $("#dExp").text(b.data("exp")); $("#dDesc").text(b.data("desc"));
});
</script>';
require_once '../../includes/footer_admin.php';
