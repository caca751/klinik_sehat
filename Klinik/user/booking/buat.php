<?php
require_once '../../config/koneksi.php';
require_login();
$page_title = 'Buat Booking';
$active = 'booking';
$user = current_user();
$spesialis_list = $pdo->query("SELECT * FROM spesialis ORDER BY nama_spesialis")->fetchAll();
$kotas = $pdo->query("SELECT * FROM kota ORDER BY nama_kota")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valid()) { set_flash('error', 'Token CSRF tidak valid.'); redirect(''); }
    $id_dokter = (int)$_POST['id_dokter'];
    $id_klinik = (int)$_POST['id_klinik'];
    $id_jadwal = !empty($_POST['id_jadwal']) ? (int)$_POST['id_jadwal'] : null;
    $tanggal = clean($_POST['tanggal_booking']);
    $keluhan = clean($_POST['keluhan'] ?? '');
    if (!$id_dokter || !$id_klinik || !$tanggal) {
        set_flash('error', 'Data tidak lengkap.');
    } else {
        $error = false;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM dokter_klinik WHERE id_dokter=? AND id_klinik=?");
        $stmt->execute([$id_dokter, $id_klinik]);
        if ($stmt->fetchColumn() == 0) {
            set_flash('error', 'Klinik tidak tersedia untuk dokter yang dipilih.');
            $error = true;
        }
        if ($id_jadwal && !$error) {
            $stmt = $pdo->prepare("SELECT id_jadwal, kuota, hari, id_dokter, id_klinik FROM jadwal_praktik WHERE id_jadwal=?");
            $stmt->execute([$id_jadwal]);
            $jadwal = $stmt->fetch();
            if (!$jadwal) {
                set_flash('error', 'Jadwal tidak ditemukan.');
                $error = true;
            } elseif ((int)$jadwal['id_dokter'] !== $id_dokter || (int)$jadwal['id_klinik'] !== $id_klinik) {
                set_flash('error', 'Jadwal praktik tidak sesuai dengan dokter dan klinik yang dipilih.');
                $error = true;
            } else {
                $hari_map = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
                $hari_tanggal = $hari_map[date('l', strtotime($tanggal))];
                if ($jadwal['hari'] !== $hari_tanggal) {
                    set_flash('error', 'Jadwal tersedia hari ' . $jadwal['hari'] . ', tetapi tanggal yang dipilih adalah hari ' . $hari_tanggal . '.');
                    $error = true;
                } else {
                    $aktif = $pdo->prepare("SELECT COUNT(*) FROM booking WHERE id_jadwal=? AND tanggal_booking=? AND status IN ('Menunggu','Selesai')");
                    $aktif->execute([$id_jadwal, $tanggal]);
                    if ($aktif->fetchColumn() >= $jadwal['kuota']) {
                        set_flash('error', 'Kuota jadwal sudah penuh untuk tanggal tersebut.');
                        $error = true;
                    }
                }
            }
        }
        if (!$error) {
            $kode = 'BKG-' . date('Ymd') . '-' . str_pad((int)$pdo->query("SELECT COUNT(*) FROM booking WHERE DATE(created_at)=CURDATE()")->fetchColumn() + 1, 4, '0', STR_PAD_LEFT);
            $stmt = $pdo->prepare("INSERT INTO booking (kode_booking,id_user,id_dokter,id_klinik,id_jadwal,tanggal_booking,keluhan,status,created_at) VALUES (?,?,?,?,?,?,?,'Menunggu',NOW())");
            if ($stmt->execute([$kode, $user['id_user'], $id_dokter, $id_klinik, $id_jadwal, $tanggal, $keluhan])) {
                send_notif($pdo, $user['id_user'], 'Booking Diterima', "Booking $kode berhasil dibuat.");
                set_flash('success', "Booking $kode berhasil dibuat.");
                redirect(BASE_URL . 'user/booking/');
            } else {
                set_flash('error', 'Gagal membuat booking.');
            }
        }
    }
}

require_once '../../includes/header_user.php';
?>
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="card">
            <div class="card-header"><i class="fas fa-calendar-plus me-2"></i>Form Booking Dokter</div>
            <div class="card-body">
                <form method="post">
                    <?= csrf_field() ?>
                    <div class="mb-3"><label class="form-label">Spesialis</label>
                        <select id="filterSpesialis" class="form-select"><option value="">Pilih Spesialis</option><?php foreach($spesialis_list as $s): ?><option value="<?= e($s['id_spesialis']) ?>"><?= e($s['nama_spesialis']) ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label">Kota</label>
                        <select id="filterKota" class="form-select"><option value="">Pilih Kota</option><?php foreach($kotas as $k): ?><option value="<?= e($k['id_kota']) ?>"><?= e($k['nama_kota']) ?></option><?php endforeach; ?></select></div>
                    <div class="mb-3"><label class="form-label">Dokter</label>
                        <select name="id_dokter" id="selectDokter" class="form-select" required><option value="">Pilih Dokter</option></select></div>
                    <div class="mb-3"><label class="form-label">Klinik</label>
                        <select name="id_klinik" id="selectKlinik" class="form-select" required><option value="">Pilih Klinik</option></select></div>
                    <div class="mb-3"><label class="form-label">Jadwal Praktik <small class="text-muted">(opsional)</small></label>
                        <select name="id_jadwal" id="selectJadwal" class="form-select"><option value="">Tidak pilih jadwal khusus</option></select></div>
                    <div class="mb-3"><label class="form-label">Tanggal Booking</label><input type="date" name="tanggal_booking" class="form-control" required min="<?= date('Y-m-d') ?>"></div>
                    <div class="mb-3"><label class="form-label">Keluhan</label><textarea name="keluhan" class="form-control" rows="3" placeholder="Jelaskan keluhan Anda..."></textarea></div>
                    <button type="submit" class="btn btn-primary">Buat Booking</button>
                    <a href="<?= BASE_URL ?>user/booking/" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
function loadDokter(){
    var sp=document.getElementById('filterSpesialis').value;
    var kt=document.getElementById('filterKota').value;
    fetch('<?= BASE_URL ?>api/dokter_search.php?format=json&id_spesialis='+encodeURIComponent(sp)+'&id_kota='+encodeURIComponent(kt))
    .then(r=>r.json()).then(data=>{
        var sel=document.getElementById('selectDokter');sel.innerHTML='<option value="">Pilih Dokter</option>';
        data.forEach(function(d){
            sel.innerHTML+='<option value="'+d.id_dokter+'">'+d.nama_dokter+' - '+d.nama_spesialis+'</option>';
        });
        document.getElementById('selectKlinik').innerHTML='<option value="">Pilih Klinik</option>';
    });
}
function loadKlinik(){
    var dokter=document.getElementById('selectDokter').value;
    if(!dokter){document.getElementById('selectKlinik').innerHTML='<option value="">Pilih Klinik</option>';document.getElementById('selectJadwal').innerHTML='<option value="">Tidak pilih jadwal khusus</option>';return;}
    return fetch('<?= BASE_URL ?>api/dokter_klinik.php?id_dokter='+encodeURIComponent(dokter))
    .then(r=>r.json()).then(data=>{
        var sel=document.getElementById('selectKlinik');sel.innerHTML='<option value="">Pilih Klinik</option>';
        data.forEach(function(k){
            sel.innerHTML+='<option value="'+k.id_klinik+'">'+k.nama_klinik+' ('+k.nama_kota+')</option>';
        });
        if (data.length === 1 && !sel.value) {
            sel.selectedIndex = 1;
        }
        document.getElementById('selectJadwal').innerHTML='<option value="">Tidak pilih jadwal khusus</option>';
        return data;
    });
}
function loadJadwal(){
    var dokter=document.getElementById('selectDokter').value;
    var klinik=document.getElementById('selectKlinik').value;
    if(!dokter||!klinik){document.getElementById('selectJadwal').innerHTML='<option value="">Tidak pilih jadwal khusus</option>';return;}
    return fetch('<?= BASE_URL ?>api/jadwal_by_dokter_klinik.php?id_dokter='+encodeURIComponent(dokter)+'&id_klinik='+encodeURIComponent(klinik))
    .then(r=>r.json()).then(data=>{
        var sel=document.getElementById('selectJadwal');sel.innerHTML='<option value="">Tidak pilih jadwal khusus</option>';
        data.forEach(function(j){
            sel.innerHTML+='<option value="'+j.id_jadwal+'">'+j.hari+' '+j.jam_mulai+'-'+j.jam_selesai+' (kuota '+j.kuota+')</option>';
        });
        if (data.length === 1 && !sel.value) {
            sel.selectedIndex = 1;
        }
        return data;
    });
}
document.getElementById('filterSpesialis').addEventListener('change',loadDokter);
document.getElementById('filterKota').addEventListener('change',loadDokter);
document.getElementById('selectDokter').addEventListener('change',loadKlinik);
document.getElementById('selectKlinik').addEventListener('change',loadJadwal);
// Prefill from URL params when linking from doctor listing
(function prefillFromUrl(){
    try{
        var params=new URLSearchParams(window.location.search);
        var d=params.get('id_dokter');
        var s=params.get('id_spesialis');
        var k=params.get('id_kota');
        var j=params.get('id_jadwal');
        if(d){
            var selSpesialis=document.getElementById('filterSpesialis');
            var selKota=document.getElementById('filterKota');
            var selDokter=document.getElementById('selectDokter');
            if (s) {
                selSpesialis.value = s;
            }
            if (k) {
                selKota.value = k;
            }
            fetch('<?= BASE_URL ?>api/dokter_search.php?format=json&id_dokter='+encodeURIComponent(d))
            .then(r=>r.json()).then(function(data){
                if (Array.isArray(data) && data.length) {
                    var doc=data[0];
                    if (!selSpesialis.value && doc.id_spesialis) {
                        selSpesialis.value = doc.id_spesialis;
                    }
                    if (!selKota.value && doc.kota_ids) {
                        var kotaIds = doc.kota_ids.split(',');
                        if (kotaIds.length && kotaIds[0]) {
                            selKota.value = kotaIds[0];
                        }
                    }
                    selDokter.innerHTML = '';
                    var opt = new Option(doc.nama_dokter + ' - ' + doc.nama_spesialis, d, true, true);
                    selDokter.appendChild(opt);
                    selDokter.value = d;
                }
            }).catch(function(e){console.warn('Prefill booking doctor error',e);})
            .finally(function(){
                loadKlinik().then(function(){
                    var selK=document.getElementById('selectKlinik');
                    if(k){ selK.value=k; }
                    else if(selK.options.length > 1 && !selK.value){ selK.selectedIndex = 1; }
                    return loadJadwal();
                }).then(function(){
                    if(j){
                        var selJ=document.getElementById('selectJadwal');
                        selJ.value=j;
                    }
                }).catch(function(e){console.warn('Prefill booking params error',e);});
            });
        }
    }catch(e){/* ignore */}
})();
</script>
<?php require_once '../../includes/footer_user.php'; ?>
