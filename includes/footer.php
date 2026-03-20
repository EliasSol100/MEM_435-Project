<?php
?>
    </div>
</main>

<footer class="site-footer">
    <div class="container footer-grid">
        <div class="footer-brand">
            <h3>UniTrade CY</h3>
            <p>The student marketplace of Cyprus, built for faster exchanges, safer profiles, and better campus selling.</p>
        </div>

        <div class="footer-links-block">
            <p class="footer-label">Explore</p>
            <div class="footer-links">
                <a href="about.php">About UniTrade CY</a>
                <a href="trust-safety.php">Trust & Safety</a>
                <a href="contact.php">Contact & Partnerships</a>
                <a href="browse.php">Marketplace</a>
                <a href="<?= is_logged_in() ? 'create-listing.php' : 'register.php'; ?>">
                    <?= is_logged_in() ? 'Post a Listing' : 'Join UniTrade CY'; ?>
                </a>
                <a href="<?= is_logged_in() ? 'profile.php' : 'login.php'; ?>">
                    <?= is_logged_in() ? 'Your Profile' : 'Member Login'; ?>
                </a>
            </div>
        </div>

        <div class="footer-meta">
            <p><strong>Built for:</strong> Business Plan Light project</p>
            <p><strong>Stack:</strong> PHP, MySQL, HTML, CSS, JavaScript</p>
            <p><strong>Focus:</strong> student essentials, local trust, and simple marketplace flows</p>
        </div>
    </div>
</footer>
</body>
</html>
