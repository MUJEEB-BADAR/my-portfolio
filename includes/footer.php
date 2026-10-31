</main>


<footer class="site-footer">
    <div class="container py-4">
        <div class="footer-content">
            <div class="footer-copyright">
                <p class="mb-1">
                    &copy; <?= date('Y') ?>
                    <?= e(OWNER_NAME) ?>.
                    All rights reserved.
                </p>
                <span>Designed &amp; developed with care.</span>
            </div>

            <div class="footer-socials">
                <a href="<?= e(LINKEDIN_URL) ?>"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="LinkedIn profile"
                   class="social-link">
                    <i class="bi bi-linkedin"></i>
                </a>

                <a href="https://github.com/MUJEEB-BADAR"
                   target="_blank"
                   rel="noopener noreferrer"
                   aria-label="GitHub profile"
                   class="social-link">
                    <i class="bi bi-github"></i>
                </a>

                <a href="contact.php"
                   aria-label="Contact"
                   class="social-link">
                    <i class="bi bi-envelope"></i>
                </a>
            </div>
        </div>
    </div>
</footer>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="js/contact.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>