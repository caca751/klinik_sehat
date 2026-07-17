<?php
require_once '../../config/koneksi.php';
require_login();
if (is_admin()) redirect(BASE_URL . 'admin/');
$page_title = 'Cari Dokter';
$active = 'dokter';
$spesialis_list = $pdo->query("SELECT * FROM spesialis ORDER BY nama_spesialis")->fetchAll();
$kotas = $pdo->query("SELECT * FROM kota ORDER BY nama_kota")->fetchAll();
require_once '../../includes/header_user.php';
?>
<div class="card">
    <div class="card-header"><i class="fas fa-user-md me-2"></i>Cari Dokter</div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4"><input type="text" id="searchNama" class="form-control" placeholder="Cari nama dokter..."></div>
            <div class="col-md-3">
                <select id="searchSpesialis" class="form-select">
                    <option value="">Semua Spesialis</option>
                    <?php foreach($spesialis_list as $s): ?><option value="<?= e($s['id_spesialis']) ?>"><?= e($s['nama_spesialis']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select id="searchKota" class="form-select">
                    <option value="">Semua Kota</option>
                    <?php foreach($kotas as $k): ?><option value="<?= e($k['id_kota']) ?>"><?= e($k['nama_kota']) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2"><button class="btn btn-primary w-100" onclick="loadDokter()">Cari</button></div>
        </div>
        <div id="hasil"><p class="text-muted">Masukkan kata kunci dan klik Cari.</p></div>
    </div>
</div>
<script>
function loadDokter(){
    var q=document.getElementById('searchNama').value.trim();
    var sp=document.getElementById('searchSpesialis').value;
    var kt=document.getElementById('searchKota').value;
    fetch('<?= BASE_URL ?>api/dokter_search.php?nama='+encodeURIComponent(q)+'&id_spesialis='+encodeURIComponent(sp)+'&id_kota='+encodeURIComponent(kt))
    .then(r=>r.text()).then(html=>{document.getElementById('hasil').innerHTML=html;});
}
document.getElementById('searchNama').addEventListener('keyup',function(e){if(e.key==='Enter')loadDokter();});
</script>
<?php require_once '../../includes/footer_user.php'; ?>
