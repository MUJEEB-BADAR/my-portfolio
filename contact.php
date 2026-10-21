<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Contact | ' . OWNER_NAME;
require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <span class="section-kicker">GET IN TOUCH</span>
        <h1>Let's <span class="text-gradient">Connect</span></h1>
        <p>Have an opportunity or a web development project in mind?</p>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-5">
                <div class="glass-card p-4 p-lg-5 h-100">
                    <h2>Contact Information</h2>
                    <p class="muted-text mt-3">
                        Send me a message using the form. I'll review
                        your enquiry and respond if email delivery is
                        configured correctly.
                    </p>

                    <div class="contact-detail mt-4">
                        <i class="bi bi-envelope"></i>
                        <div>
                            <h3>Email</h3>
                            <p><?= e(OWNER_EMAIL) ?></p>
                        </div>
                    </div>

                    <div class="contact-detail mt-3">
                        <i class="bi bi-geo-alt"></i>
                        <div>
                            <h3>Location</h3>
                            <p><?= e(OWNER_LOCATION) ?></p>
                        </div>
                    </div>

                    <div class="contact-detail mt-3">
                        <i class="bi bi-linkedin"></i>
                        <div>
                            <h3>LinkedIn</h3>
                            <p><a href="<?= e(LINKEDIN_URL) ?>" target="_blank" rel="noopener noreferrer">View my LinkedIn profile</a></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="glass-card p-4 p-lg-5">
                    <h2>Send a Message</h2>
                    <p class="muted-text">
                        Fields marked * are required.
                    </p>

                    <div id="form-message" class="alert d-none"
                         role="status" aria-live="polite"></div>

                    <form id="contact-form" novalidate>
                        <input type="hidden" name="csrf_token"
                               value="<?= e($_SESSION['csrf_token']) ?>">

                        <!-- Spam honeypot: real users should leave this empty -->
                        <div class="honeypot" aria-hidden="true">
                            <label for="website">Leave this field empty</label>
                            <input type="text" id="website"
                                   name="website" tabindex="-1"
                                   autocomplete="off">
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Full Name *
                                </label>
                                <input class="form-control" id="name"
                                       name="name" type="text"
                                       maxlength="80" required
                                       autocomplete="name">
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    Email Address *
                                </label>
                                <input class="form-control" id="email"
                                       name="email" type="email"
                                       maxlength="254" required
                                       autocomplete="email">
                            </div>

                            <div class="col-12">
                                <label for="subject" class="form-label">
                                    Subject *
                                </label>
                                <input class="form-control" id="subject"
                                       name="subject" type="text"
                                       maxlength="120" required>
                            </div>

                            <div class="col-12">
                                <label for="message" class="form-label">
                                    Message *
                                </label>
                                <textarea class="form-control"
                                          id="message" name="message"
                                          rows="6" maxlength="5000"
                                          required></textarea>
                            </div>

                            <div class="col-12">
                                <button class="btn btn-accent btn-lg"
                                        type="submit" id="submit-btn">
                                    <span id="submit-text">Send Message</span>
                                    <i class="bi bi-send ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="js/contact.js"></script>
<?php require __DIR__ . '/includes/footer.php'; ?>