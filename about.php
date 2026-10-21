<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Me | ' . OWNER_NAME;
$pageDescription = 'Learn about Mujeeb Ahmed Badar, a Software Engineering student and web developer.';
require __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <span class="section-kicker">MY BACKGROUND</span>
        <h1>About <span class="text-gradient">Me</span></h1>
        <p>My education, technical interests and development goals.</p>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-4 text-center">
                <img src="assets/images/Mujeeb.jpeg"
                     class="about-avatar"
                     alt="Mujeeb Ahmed Badar">
            </div>

            <div class="col-lg-8">
                <h2 class="mb-3">I'm <?= e(OWNER_NAME) ?></h2>
                <h3 class="hero-role mb-3">Software Engineering Student | Web Developer</h3>

                <p class="muted-text">
                    I am studying Software Engineering at Virtual University of Pakistan
                    and focus on building responsive, database-driven web applications.
                    My current toolkit includes HTML, CSS, Bootstrap, JavaScript,
                    jQuery, AJAX, PHP and MySQL.
                </p>

                <p class="muted-text">
                    My academic project work includes a Student Management System with
                    create, view, search, update and delete functionality backed by a
                    MySQL database. I am continuing to strengthen my practical skills
                    and looking for opportunities to learn from real development work.
                </p>

                <a href="download-cv.php" class="btn btn-accent mt-2">
                    <i class="bi bi-download me-2"></i>Download CV
                </a>
            </div>
        </div>
    </div>
</section>

<section class="section-pad section-muted" id="education">
    <div class="container">
        <div class="section-heading">
            <span class="section-kicker">ACADEMIC BACKGROUND</span>
            <h2>My <span class="text-gradient">Education</span></h2>
        </div>

        <div class="timeline">
            <article class="timeline-item">
                <span class="timeline-dot"></span>
                <div class="glass-card p-4">
                    <span class="section-kicker">CURRENT STUDIES</span>
                    <h3>Bachelor of Science in Software Engineering</h3>
                    <p class="muted-text mb-0">
                        Virtual University of Pakistan. Degree in progress.
                    </p>
                </div>
            </article>
            <article class="timeline-item">
                <span class="timeline-dot"></span>
                <div class="glass-card p-4">
                    <span class="section-kicker">2019–2021</span>
                    <h3>Intermediate (FSc Pre-Engineering)</h3>
                    <p class="muted-text mb-0">
                        Punjab Group of Colleges, Ali Pur Chattha — 950/1100.
                    </p>
                </div>
            </article>
            <article class="timeline-item">
                <span class="timeline-dot"></span>
                <div class="glass-card p-4">
                    <span class="section-kicker">2017–2019</span>
                    <h3>Matriculation (Science)</h3>
                    <p class="muted-text mb-0">
                        Hussain High School, Ali Pur Chattha — 1000/1100.
                    </p>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="section-pad" id="experience">
    <div class="container">
        <div class="section-heading">
            <span class="section-kicker">PRACTICAL WORK</span>
            <h2>Project <span class="text-gradient">Experience</span></h2>
        </div>

        <div class="timeline">
            <article class="timeline-item">
                <span class="timeline-dot"></span>
                <div class="glass-card p-4">
                    <span class="section-kicker">ACADEMIC PROJECT</span>
                    <h3>Student Management System</h3>
                    <p class="muted-text mb-0">
                        Developed a database-driven application for creating, viewing,
                        searching, updating and deleting student records using PHP and
                        MySQL, with responsive interfaces built using HTML, CSS,
                        Bootstrap and JavaScript.
                    </p>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="section-pad section-muted" id="certifications">
    <div class="container">
        <div class="section-heading">
            <span class="section-kicker">CONTINUOUS LEARNING</span>
            <h2>Courses &amp; <span class="text-gradient">Certifications</span></h2>
        </div>
        <div class="row g-4">
            <div class="col-md-6"><div class="skill-card h-100"><h3>Advanced Web Application Development</h3><p class="mb-0">NAVTTC — Progressive Multimedia Systems Technical Training Center, May–August 2023</p></div></div>
            <div class="col-md-6"><div class="skill-card h-100"><h3>WordPress</h3><p class="mb-0">DigiSkills.pk, November 2022–January 2023</p></div></div>
            <div class="col-md-6"><div class="skill-card h-100"><h3>Freelancing</h3><p class="mb-0">DigiSkills.pk, July–October 2022</p></div></div>
            <div class="col-md-6"><div class="skill-card h-100"><h3>Digital Literacy</h3><p class="mb-0">DigiSkills.pk, March–June 2023</p></div></div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
