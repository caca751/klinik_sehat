<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Data Kategori';
$active = 'kategori';

/* ---------- HANDLER ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $act = $_POST['act'] ?? '';
    $nama = clean($_POST['nama_kategori']);
    $desc = clean($_POST['deskripsi']);

    if ($act === 'tambah') {
        $stmt = $pdo->prepare("INSERT INTO kategori_obat (nama_kategori, deskripsi) VALUES (?,?)");
        $stmt->execute([$nama, $desc]);
        set_flash('success', 'Kategori berhasil ditambahkan.');
    } elseif ($act === 'edit') {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE kategori_obat SET nama_kategori=?, deskripsi=? WHERE id_kategori=?");
        $stmt->execute([$nama, $desc, $id]);
        set_flash('success', 'Kategori berhasil diupdate.');
    }
    redirect('');
}
if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM kategori_obat WHERE id_kategori=?")->execute([$id]);
    set_flash('success', 'Kategori berhasil dihapus.');
    redirect('');
}

$kats = $pdo->query("SELECT * FROM kategori_obat ORDER BY id_kategori")->fetchAll();

require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-tags me-2"></i>Daftar Kategori Obat</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm">
            <i class="fas fa-plus me-1"></i> Tambah
        </button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>#</th><th>Nama Kategori</th><th>Deskripsi</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($kats as $i => $k): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($k['nama_kategori']) ?></td>
                    <td><?= e($k['deskripsi']) ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalDetail"
                            data-nama="<?= e($k['nama_kategori']) ?>" data-desk="<?= e($k['deskripsi']) ?>"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm"
                            data-id="<?= $k['id_kategori'] ?>" data-nama="<?= e($k['nama_kategori']) ?>" data-desk="<?= e($k['deskripsi']) ?>"><i class="fas fa-edit"></i></button>
                        <a href="?hapus=<?= $k['id_kategori'] ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="konfirmHapus(this.href,'Hapus kategori ini?');return false;"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="modalForm" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="act" id="fAct" value="tambah">
            <input type="hidden" name="id" id="fId">
            <div class="modal-header"><h6 class="modal-title">Form Kategori</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nama Kategori</label>
                    <input type="text" name="nama_kategori" id="fNama" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="fDesk" class="form-control" rows="3"></textarea></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-primary">Simpan</button></div>
        </form>
    </div>
</div>

<!-- Modal Detail -->
<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h6 class="modal-title">Detail Kategori</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p><strong>Nama:</strong> <span id="dNama"></span></p>
            <p><strong>Deskripsi:</strong> <span id="dDesk"></span></p>
        </div>
    </div></div>
</div>
<?php
$extra_js = '<script>
$("#table").DataTable({responsive:true, language:{url:"https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"}});
$("#modalForm").on("show.bs.modal", function(e){
    var b = $(e.relatedTarget);
    if (b.data("id")) { $("#fAct").val("edit"); $("#fId").val(b.data("id")); $("#fNama").val(b.data("nama")); $("#fDesk").val(b.data("desk")); $(this).find(".modal-title").text("Edit Kategori"); }
    else { $("#fAct").val("tambah"); $("#fId").val(""); $("#fNama").val(""); $("#fDesk").val(""); $(this).find(".modal-title").text("Tambah Kategori"); }
});
$("#modalDetail").on("show.bs.modal", function(e){
    var b = $(e.relatedTarget);
    $("#dNama").text(b.data("nama")); $("#dDesk").text(b.data("desk"));
});
</script>';
require_once '../../includes/footer_admin.php';
