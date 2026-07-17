<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Metode Pembayaran';
$active = 'metode_pembayaran';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $act = $_POST['act'] ?? '';
    $nama = clean($_POST['nama_metode']);
    $tipe = in_array($_POST['tipe'], ['bank','ewallet']) ? $_POST['tipe'] : 'bank';
    $nomor = clean($_POST['nomor']);
    $pemilik = clean($_POST['pemilik']);
    $aktif = isset($_POST['aktif']) ? 1 : 0;

    if ($act === 'tambah') {
        $pdo->prepare("INSERT INTO metode_pembayaran (nama_metode, tipe, nomor, pemilik, aktif) VALUES (?,?,?,?,?)")->execute([$nama, $tipe, $nomor, $pemilik, $aktif]);
        set_flash('success', 'Metode pembayaran berhasil ditambahkan.');
    } elseif ($act === 'edit') {
        $id = (int)$_POST['id'];
        $pdo->prepare("UPDATE metode_pembayaran SET nama_metode=?, tipe=?, nomor=?, pemilik=?, aktif=? WHERE id_metode=?")->execute([$nama, $tipe, $nomor, $pemilik, $aktif, $id]);
        set_flash('success', 'Metode pembayaran berhasil diupdate.');
    }
    redirect('');
}
if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id = (int)$_GET['hapus'];
    $pdo->prepare("DELETE FROM metode_pembayaran WHERE id_metode=?")->execute([$id]);
    set_flash('success', 'Metode pembayaran berhasil dihapus.');
    redirect('');
}

$list = $pdo->query("SELECT * FROM metode_pembayaran ORDER BY tipe, nama_metode")->fetchAll();
require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-wallet me-2"></i>Daftar Metode Pembayaran</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm"><i class="fas fa-plus me-1"></i> Tambah</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>ID</th><th>Nama</th><th>Tipe</th><th>Nomor</th><th>Pemilik</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($list as $r): ?>
                <tr>
                    <td><?= e($r['id_metode']) ?></td>
                    <td><?= e($r['nama_metode']) ?></td>
                    <td><span class="badge bg-<?= $r['tipe']==='bank'?'primary':'info' ?>"><?= e($r['tipe']==='bank'?'Bank':'E-Wallet') ?></span></td>
                    <td><?= e($r['nomor']) ?></td>
                    <td><?= e($r['pemilik']) ?></td>
                    <td><?= $r['aktif'] ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>' ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm"
                            data-id="<?= e($r['id_metode']) ?>" data-nama="<?= e($r['nama_metode']) ?>" data-tipe="<?= e($r['tipe']) ?>" data-nomor="<?= e($r['nomor']) ?>" data-pemilik="<?= e($r['pemilik']) ?>" data-aktif="<?= e($r['aktif']) ?>"><i class="fas fa-edit"></i></button>
                        <a href="?hapus=<?= e($r['id_metode']) ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus metode ini?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<div class="modal fade" id="modalForm" tabindex="-1"><div class="modal-dialog">
    <form method="post" class="modal-content">
        <?= csrf_field() ?>
        <input type="hidden" name="act" id="act" value="tambah">
        <input type="hidden" name="id" id="id">
        <div class="modal-header"><h5 class="modal-title" id="modalTitle">Tambah Metode Pembayaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Nama Metode</label><input type="text" name="nama_metode" id="nama_metode" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Tipe</label><select name="tipe" id="tipe" class="form-select"><option value="bank">Bank</option><option value="ewallet">E-Wallet</option></select></div>
            <div class="mb-3"><label class="form-label">Nomor Rekening / Phone</label><input type="text" name="nomor" id="nomor" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Nama Pemilik</label><input type="text" name="pemilik" id="pemilik" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Status</label><select name="aktif" id="aktif" class="form-select"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
        </div>
        <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
    </form>
</div></div>
<script>
document.getElementById('modalForm').addEventListener('show.bs.modal',function(e){
    var btn=e.relatedTarget, id=btn.getAttribute('data-id');
    if(id) {
        document.getElementById('act').value='edit';
        document.getElementById('id').value=id;
        document.getElementById('nama_metode').value=btn.getAttribute('data-nama');
        document.getElementById('tipe').value=btn.getAttribute('data-tipe');
        document.getElementById('nomor').value=btn.getAttribute('data-nomor');
        document.getElementById('pemilik').value=btn.getAttribute('data-pemilik');
        document.getElementById('aktif').value=btn.getAttribute('data-aktif');
        document.getElementById('modalTitle').textContent='Edit Metode Pembayaran';
    } else {
        document.getElementById('act').value='tambah';
        document.getElementById('id').value='';
        document.getElementById('nama_metode').value='';
        document.getElementById('tipe').value='bank';
        document.getElementById('nomor').value='';
        document.getElementById('pemilik').value='';
        document.getElementById('aktif').value='1';
        document.getElementById('modalTitle').textContent='Tambah Metode Pembayaran';
    }
});
</script>
<?php
$extra_js = '<script>$("#table").DataTable({responsive:true, language:{url:"https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"}});</script>';
require_once '../../includes/header_admin.php';
