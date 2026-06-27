</main><!-- /.tb-main -->
</div><!-- /.tb-layout -->

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script utama panel Admin -->
<script src="<?= SITE_URL ?>/admin_panel/script.js"></script>

<?php if (!empty($extraJs)): ?>
<!-- Script tambahan halaman ini -->
<?php foreach ((array) $extraJs as $jsFile): ?>
<script src="<?= SITE_URL ?>/admin_panel/js/<?= htmlspecialchars($jsFile) ?>"></script>
<?php endforeach; ?>
<?php endif; ?>

</body>
</html>