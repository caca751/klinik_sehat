<?php
/* includes/footer_admin.php
   Menutup content + footer + script */
global $pdo;
?>
        </main>
        <footer class="footer">
            &copy; <?= date('Y') ?> Klinik Sehat &middot; Sistem Pemesanan Apotek &middot;
        </footer>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
<?php if (!empty($extra_js)): ?>
<?= $extra_js ?>
<?php endif; ?>
</body>
</html>
