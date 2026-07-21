<?php
require_once '../../config/koneksi.php';
require_admin();
$page_title = 'Data User';
$active = 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $act = $_POST['act'] ?? '';
    $nama = clean($_POST['nama']);
    $email = clean($_POST['email']);
    $hp = clean($_POST['no_hp']);
    $alamat = clean($_POST['alamat']);
    $role = in_array($_POST['role'], ['admin', 'customer']) ? $_POST['role'] : 'customer';

    $birthdate = null;
    if (!empty($_POST['birth_year']) && !empty($_POST['birth_month']) && !empty($_POST['birth_day'])) {
        $birthdate = sprintf('%04d-%02d-%02d', (int)$_POST['birth_year'], (int)$_POST['birth_month'], (int)$_POST['birth_day']);
        if (!checkdate((int)$_POST['birth_month'], (int)$_POST['birth_day'], (int)$_POST['birth_year'])) {
            set_flash('error', 'Tanggal lahir user tidak valid.'); redirect('');
        }
    }

    if ($act === 'tambah') {
        if (!valid_email($email)) { set_flash('error', 'Email tidak valid.'); redirect(''); }
        $cek = $pdo->prepare("SELECT id_user FROM users WHERE email=?");
        $cek->execute([$email]);
        if ($cek->fetch()) { set_flash('error', 'Email sudah terdaftar.'); redirect(''); }
        $pass = password_hash($_POST['password'] ?: 'customer123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (nama,email,password,no_hp,alamat,birthdate,role,created_at) VALUES (?,?,?,?,?,?,?,NOW())")->execute([$nama, $email, $pass, $hp, $alamat, $birthdate, $role]);
        set_flash('success', 'User berhasil ditambahkan.');
    } elseif ($act === 'edit') {
        $id = (int)$_POST['id'];
        if (!valid_email($email)) { set_flash('error', 'Email tidak valid.'); redirect(''); }
        $cek = $pdo->prepare("SELECT id_user FROM users WHERE email=? AND id_user!=?");
        $cek->execute([$email, $id]);
        if ($cek->fetch()) { set_flash('error', 'Email sudah digunakan user lain.'); redirect(''); }
        if (!empty($_POST['password'])) {
            $pdo->prepare("UPDATE users SET nama=?,email=?,password=?,no_hp=?,alamat=?,birthdate=?,role=? WHERE id_user=?")
                ->execute([$nama, $email, password_hash($_POST['password'], PASSWORD_DEFAULT), $hp, $alamat, $birthdate, $role, $id]);
        } else {
            $pdo->prepare("UPDATE users SET nama=?,email=?,no_hp=?,alamat=?,birthdate=?,role=? WHERE id_user=?")
                ->execute([$nama, $email, $hp, $alamat, $birthdate, $role, $id]);
        }
        set_flash('success', 'User berhasil diupdate.');
    }
    redirect('');
}
if (isset($_GET['hapus'])) {
    if (!csrf_get_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id = (int)$_GET['hapus'];
    if ($id === $_SESSION['user']['id_user']) { set_flash('error', 'Tidak dapat menghapus akun sendiri.'); redirect(''); }
    $pdo->prepare("DELETE FROM users WHERE id_user=?")->execute([$id]);
    set_flash('success', 'User berhasil dihapus.');
    redirect('');
}

$users = $pdo->query("SELECT * FROM users ORDER BY id_user")->fetchAll();
require_once '../../includes/header_admin.php';
?>
<div class="card">
    <div class="card-header">
        <span><i class="fas fa-users me-2"></i>Daftar User</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalForm"><i class="fas fa-plus me-1"></i> Tambah</button>
    </div>
    <div class="card-body">
        <table id="table" class="table table-hover" style="width:100%">
            <thead><tr><th>#</th><th>Nama</th><th>Email</th><th>No. HP</th><th>Usia</th><th>Role</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php foreach ($users as $i => $u): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($u['nama']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['no_hp']) ?></td>
                <td><?= $u['birthdate'] ? date_diff(new DateTime($u['birthdate']), new DateTime())->y . ' thn' : '<span class="text-muted">-</span>' ?></td>
                    <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'primary' : 'secondary' ?>"><?= e($u['role']) ?></span></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalDetail"
                            data-nama="<?= e($u['nama']) ?>" data-email="<?= e($u['email']) ?>" data-hp="<?= e($u['no_hp']) ?>" data-alamat="<?= e($u['alamat']) ?>" data-role="<?= e($u['role']) ?>" data-birthdate="<?= e($u['birthdate']) ?>" data-created="<?= e($u['created_at']) ?>"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalForm"
                            data-id="<?= $u['id_user'] ?>" data-nama="<?= e($u['nama']) ?>" data-email="<?= e($u['email']) ?>" data-hp="<?= e($u['no_hp']) ?>" data-alamat="<?= e($u['alamat']) ?>" data-birthdate="<?= e($u['birthdate']) ?>" data-role="<?= e($u['role']) ?>"><i class="fas fa-edit"></i></button>
                        <a href="?hapus=<?= $u['id_user'] ?>&csrf_token=<?= csrf_token() ?>" class="btn btn-sm btn-danger" onclick="konfirmHapus(this.href,'Hapus user ini?');return false;"><i class="fas fa-trash"></i></a>
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
            <div class="modal-header"><h6 class="modal-title">Form User</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="nama" id="fNama" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" id="fEmail" class="form-control" required></div>
                <div class="row">
                    <div class="col-6 mb-3"><label class="form-label">No. HP</label><input type="text" name="no_hp" id="fHp" class="form-control"></div>
                    <div class="col-6 mb-3"><label class="form-label">Role</label>
                        <select name="role" id="fRole" class="form-select"><option value="customer">Customer</option><option value="admin">Admin</option></select></div>
                </div>
                <div class="row">
                    <div class="col-4 mb-3"><label class="form-label">Tanggal Lahir</label>
                        <select name="birth_day" id="fBirthDay" class="form-select">
                            <option value="">Tanggal</option>
                            <?php for ($d = 1; $d <= 31; $d++): ?>
                            <option value="<?= $d ?>"><?= $d ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-4 mb-3"><label class="form-label">Bulan</label>
                        <select name="birth_month" id="fBirthMonth" class="form-select">
                            <option value="">Bulan</option>
                            <?php $months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des']; ?>
                            <?php foreach ($months as $m => $label): ?>
                            <option value="<?= $m + 1 ?>"><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-4 mb-3"><label class="form-label">Tahun</label>
                        <select name="birth_year" id="fBirthYear" class="form-select">
                            <option value="">Tahun</option>
                            <?php $currentYear = (int)date('Y'); ?>
                            <?php for ($y = $currentYear; $y >= 1900; $y--): ?>
                            <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3"><label class="form-label">Password <?= '<small class="text-muted">(kosongkan jika tidak diubah)</small>' ?></label>
                    <input type="password" name="password" class="form-control" placeholder="Min 6 karakter"></div>
                <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" id="fAlamat" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button class="btn btn-primary">Simpan</button></div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h6 class="modal-title">Detail User</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p><strong>Nama:</strong> <span id="dNama"></span></p>
            <p><strong>Email:</strong> <span id="dEmail"></span></p>
            <p><strong>No. HP:</strong> <span id="dHp"></span></p>
            <p><strong>Role:</strong> <span id="dRole"></span></p>
            <p><strong>Tanggal Lahir:</strong> <span id="dBirthdate"></span></p>
            <p><strong>Usia:</strong> <span id="dAge"></span></p>
            <p><strong>Alamat:</strong> <span id="dAlamat"></span></p>
            <p><strong>Bergabung:</strong> <span id="dCreated"></span></p>
        </div>
    </div></div>
</div>
<?php
$extra_js = <<<'EOT'
<script>
$("#table").DataTable({responsive:true, language:{url:"https://cdn.datatables.net/plug-ins/1.13.8/i18n/id.json"}});
$("#modalForm").on("show.bs.modal", function(e){
    var b = $(e.relatedTarget);
    if (b.data("id")) {
        var birthdate = b.data("birthdate") || '';
        var parts = birthdate.split('-');
        $("#fAct").val("edit"); $("#fId").val(b.data("id")); $("#fNama").val(b.data("nama")); $("#fEmail").val(b.data("email")); $("#fHp").val(b.data("hp")); $("#fAlamat").val(b.data("alamat")); $("#fRole").val(b.data("role"));
        $("#fBirthDay").val(parts[2] || ''); $("#fBirthMonth").val(parts[1] || ''); $("#fBirthYear").val(parts[0] || '');
        $(this).find(".modal-title").text("Edit User");
    } else {
        $("#fAct").val("tambah"); $("#fId").val(""); $("#fNama").val(""); $("#fEmail").val(""); $("#fHp").val(""); $("#fAlamat").val(""); $("#fRole").val("customer");
        $("#fBirthDay").val(''); $("#fBirthMonth").val(''); $("#fBirthYear").val('');
        $(this).find(".modal-title").text("Tambah User");
    }
});
$("#modalDetail").on("show.bs.modal", function(e){
    var b = $(e.relatedTarget);
    var birthdate = b.data("birthdate") || '-';
    var ageText = '-';
    if (birthdate) {
        var d = new Date(birthdate);
        if (!isNaN(d)) {
            var today = new Date();
            var age = today.getFullYear() - d.getFullYear();
            var m = today.getMonth() - d.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < d.getDate())) age--;
            ageText = age >= 0 ? age + ' thn' : '-';
        }
    }
    $("#dNama").text(b.data("nama")); $("#dEmail").text(b.data("email")); $("#dHp").text(b.data("hp")); $("#dRole").text(b.data("role")); $("#dBirthdate").text(birthdate);
    $("#dAge").text(ageText); $("#dAlamat").text(b.data("alamat")); $("#dCreated").text(b.data("created"));
});
</script>
EOT;
require_once '../../includes/footer_admin.php';
