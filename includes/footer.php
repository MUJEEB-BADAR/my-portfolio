</main>

<footer class="site-footer">
    <div class="container py-4">
        <div class="row align-items-center gy-3">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">
                    &copy; <?= date('Y') ?>
                    <?= e(OWNER_NAME) ?>.
                    All rights reserved.
                </p>
            </div>

            <div class="col-md-6 text-center text-md-end">
                <a href="<?= e(LINKEDIN_URL) ?>"
                   target="_blank" rel="noopener noreferrer"
                   aria-label="LinkedIn profile"
                   class="social-link">
                    <i class="bi bi-linkedin"></i>
                </a>
                <a href="contact.php" class="social-link"
                   aria-label="Contact">
                    <i class="bi bi-envelope"></i>
                </a>
            </div>
        </div>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/script.js"></script>
</body>
</html>