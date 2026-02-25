<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h4>Potřebuješ pomoc?</h4>
            <ul>
                <li><a href="napoveda.php"><i class="fas fa-question-circle"></i> Jak nahrávat fotky</a></li>
                <li><a href="napoveda.php#hledani"><i class="fas fa-search"></i> Jak vyhledávat</a></li>
            </ul>
        </div>

        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <div class="footer-section">
            <h4>Správa systému</h4>
            <ul>
                <li><a href="admin.php"><i class="fas fa-user-shield"></i> Admin Panel</a></li>
                <li><i class="fas fa-database"></i> DB: Připojeno</li>
            </ul>
        </div>
        <?php endif; ?>

        <div class="footer-section">
            <p>&copy; <?= date("Y") ?> Rodinný Web</p>
            <span class="version-tag">Verze 1.2.5</span>
        </div>
    </div>
</footer>