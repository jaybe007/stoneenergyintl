<?php
/**
 * STONE ENERGY INT'L LTD - Reusable Admin Footer
 */
?>
        </main>
        
        <footer style="padding: 16px 32px; background: #fff; border-top: 1px solid var(--admin-border); font-size: 0.8rem; color: var(--admin-text-muted); display: flex; justify-content: space-between; align-items: center;">
            <div>
                &copy; <?= date('Y') ?> <?= e(setting('company_name', "STONE ENERGY INT'L LTD")) ?> &mdash; Custom CMS v2.0
            </div>
            <div>
                Multi-Sector Supply Solutions &amp; General Contracting
            </div>
        </footer>
    </div>

    <!-- Admin Vanilla JS Script -->
    <script src="<?= asset('js/admin.js') ?>"></script>
</body>
</html>
