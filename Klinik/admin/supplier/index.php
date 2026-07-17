<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Data Supplier';
$active = 'supplier';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $act = $_POST['act'] ?? '';
    $nama = clean($_POST['nama_supplier']);
    $alamat = clean($_POST['alamat']);
    $hp = clean($_POST['no_hp']);
    $email = clean($_POST['email']);

    if ($act === 'tambah') {
        $pdo->prepare("INSERT INTO supplier (nama_supplier, alamat, no_hp, email) VALUES (?,?,?,?)")->execute([$nama, $alamat, $hp, $email]);
        set_flash('success', 'Supplier berhasil ditambahkan.');
    } elseif ($act === 'edit') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE supplier SET nama_supplier=?, alamat=?, no_hp=?, email=? WHERE id_supplier=?")->execute([$nama, $alamat, $hp, $email, $id]);
        set_flash('success', 'Supplier berhasil diupdate.');
    }
    redirect('');
}
if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $pdo->prepare("DELETE FROM supplier WHERE id_supplier=?")->execute([(int)$_GET['hapus']]);
    set_flash('success', 'Supplier berhasil dihapus.');
    redirect('');
}

$suppliers = $pdo->query("SELECT * FROM supplier ORDER BY id_supplier")->fetchAll();
require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-truck me-2"></i>Daftar Supplier</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm"><i class="fas fa-plus me-1"></i> Tambah</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>#</th><th>Nama Supplier</th><th>No. HP</th><th>Email</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($suppliers as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($s['nama_supplier']) ?></td>
                    <td><?= e($s['no_hp']) ?></td>
                    <td><?= e($s['email']) ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalDetail"
                            data-nama="<?= e($s['nama_supplier']) ?>" data-alamat="<?= e($s['alamat']) ?>" data-hp="<?= e($s['no_hp']) ?>" data-email="<?= e($s['email']) ?>"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm"
                            data-id="<?= $s['id_supplier'] ?>" data-nama="<?= e($s['nama_supplier']) ?>" data-alamat="<?= e($s['alamat']) ?>" data-hp="<?= e($s['no_hp']) ?>" data-email="<?= e($s['email']) ?>"><i class="fas fa-edit"></i></button>
                        <a href="?hapus=<?= $s['id_supplier'] ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="konfirmHapus(this.href,'Hapus supplier ini?');return false;"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalForm" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <?= csrf_field() ?>
            <input type="hidden" name="act" id="fAct" value="tambah">
            <input type="hidden" name="id" id="fId">
            <div class="modal-header"><h6 class="modal-title">Form Supplier</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nama Supplier</label><input type="text" name="nama_supplier" id="fNama" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" id="fAlamat" class="form-control" rows="2"></textarea></div>
                <div class="row">
                    <div class="col-6 mb-3"><label class="form-label">No. HP</label><input type="text" name="no_hp" id="fHp" class="form-control"></div>
                    <div class="col-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" id="fEmail" class="form-control"></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h6 class="modal-title">Detail Supplier</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p><strong>Nama:</strong> <span id="dNama"></span></p>
            <p><strong>Alamat:</strong> <span id="dAlamat"></span></p>
            <p><strong>No. HP:</strong> <span id="dHp"></span></p>
            <p><strong>Email:</strong> <span id="dEmail"></span></p>
        </div>
    </div></div>
</div>
<?php
$extra_js = '<script>
$("#table").DataTable({responsive:true, language:{url:"https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"}});
$("#modalForm").on("show.bs.modal", function(e){
    var b = $(e.relatedTarget);
    var id = b.attr("data-id") || "";
    var nama = b.attr("data-nama") || "";
    var alamat = b.attr("data-alamat") || "";
    var hp = b.attr("data-hp") || "";
    var email = b.attr("data-email") || "";

    if (id) {
        $("#fAct").val("edit");
        $("#fId").val(id);
        $("#fNama").val(nama);
        $("#fAlamat").val(alamat);
        $("#fHp").val(hp);
        $("#fEmail").val(email);
        $(this).find(".modal-title").text("Edit Supplier");
    } else {
        $("#fAct").val("tambah");
        $("#fId").val("");
        $("#fNama").val("");
        $("#fAlamat").val("");
        $("#fHp").val("");
        $("#fEmail").val("");
        $(this).find(".modal-title").text("Tambah Supplier");
    }
});
$("#modalDetail").on("show.bs.modal", function(e){
    var b = $(e.relatedTarget);
    $("#dNama").text(b.attr("data-nama") || "-");
    $("#dAlamat").text(b.attr("data-alamat") || "-");
    $("#dHp").text(b.attr("data-hp") || "-");
    $("#dEmail").text(b.attr("data-email") || "-");
});
</script>';
require_once '../../includes/footer_admin.php';
