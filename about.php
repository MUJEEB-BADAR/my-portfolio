
<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Me | Software Engineer & Web Developer | ' . OWNER_NAME;
$pageDescription = 'Learn about Mujeeb Ahmed Badar, a Software Engineering student and web developer.';
require __DIR__ . '/includes/header.php';
?>

<!-- About Banner -->
<section class="page-banner about-banner">
    <div class="container">
        <span class="section-kicker">GET TO KNOW ME</span>
        <h1>About <span class="text-gradient">Me</span></h1>
        <p>My background, education and journey in web development.</p>
    </div>
</section>

<!-- Profile Section -->
<section class="section-pad about-profile-section">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-5">
                <div class="profile-frame about-profile-frame mx-auto">
    <div class="profile-glow"></div>

    <img
        src="assets/images/Mujeeb.jpeg"
        alt="Mujeeb Ahmed Badar"
        class="profile-image">

    <div class="floating-tag tag-top">
        Software Engineering
    </div>

    <div class="floating-tag tag-bottom">
        Web Developer
    </div>
</div>
            </div>

            <div class="col-lg-7">
                <span class="section-kicker">WHO I AM</span>

                <h2 class="about-profile-title">
                    I'm <?= e(OWNER_NAME) ?>
                </h2>

                <h3 class="hero-role mb-4">
                    Software Engineering Student
                    <span class="about-role-divider">|</span>
                    Web Developer
                </h3>

                <p class="muted-text about-description">
                    I am a Software Engineering student at Virtual University of
                    Pakistan with a strong interest in web development. I enjoy
                    building responsive, user-friendly and database-driven web
                    applications using HTML, CSS, Bootstrap, JavaScript, jQuery,
                    AJAX, PHP and MySQL.
                </p>

                <p class="muted-text about-description">
                    My practical work includes developing a Student Management
                    System with CRUD functionality using PHP and MySQL. I use
                    this portfolio to showcase my technical skills, projects
                    and web development experience. I am continuously improving
                    my programming, problem-solving and application development
                    skills through hands-on practice.
                </p>

                <div class="about-actions">
                    <a href="download-cv.php" class="btn btn-accent">
                        <i class="bi bi-download me-2"></i>Download CV
                    </a>
                </div>

                <div class="about-highlights">
                    <div>
                        <i class="bi bi-mortarboard"></i>
                        <span>Software Engineering</span>
                    </div>
                    <div>
                        <i class="bi bi-laptop"></i>
                        <span>Web Development</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Education Section -->
<section class="section-pad section-muted" id="education">
    <div class="container">

        <div class="section-heading">
            <span class="section-kicker">ACADEMIC BACKGROUND</span>
            <h2>My <span class="text-gradient">Education</span></h2>
            <p class="muted-text">My academic journey and qualifications.</p>
        </div>

        <div class="about-timeline">

            <article class="about-timeline-item">
                <div class="about-timeline-icon">
                    <i class="bi bi-mortarboard"></i>
                </div>
                <div class="glass-card about-info-card">
                    <span class="section-kicker">CURRENT STUDIES</span>
                    <h3>Bachelor of Science in Software Engineering</h3>
                    <p class="muted-text mb-0">
                        Currently pursuing a Bachelor of Science in Software
                        Engineering at Virtual University of Pakistan.
                    </p>
                </div>
            </article>

            <article class="about-timeline-item">
                <div class="about-timeline-icon">
                    <i class="bi bi-book"></i>
                </div>
                <div class="glass-card about-info-card">
                    <span class="section-kicker">2019–2021</span>
                    <h3>Intermediate (FSc Pre-Engineering)</h3>
                    <p class="muted-text mb-0">
                        Punjab Group of Colleges, Ali Pur Chattha — 950/1100.
                    </p>
                </div>
            </article>

            <article class="about-timeline-item">
                <div class="about-timeline-icon">
                    <i class="bi bi-journal-bookmark"></i>
                </div>
                <div class="glass-card about-info-card">
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

<!-- Certifications -->
<section class="section-pad section-muted" id="certifications">
    <div class="container">

        <div class="section-heading">
            <span class="section-kicker">CONTINUOUS LEARNING</span>
            <h2>Courses &amp; <span class="text-gradient">Certifications</span></h2>
            <p class="muted-text">
                Courses and training completed to strengthen my skills.
            </p>
        </div>

        <div class="row g-4">

            <div class="col-md-6">
                <div class="skill-card about-cert-card h-100">
                    <div class="about-cert-icon">
                        <i class="bi bi-code-square"></i>
                    </div>
                    <h3>Advanced Web Application Development</h3>
                    <p class="muted-text mb-0">
                        NAVTTC — Progressive Multimedia Systems Technical
                        Training Center
                    </p>
                    <span class="about-cert-date">May–August 2023</span>
                </div>
            </div>

            <div class="col-md-6">
                <div class="skill-card about-cert-card h-100">
                    <div class="about-cert-icon">
                        <i class="bi bi-wordpress"></i>
                    </div>
                    <h3>WordPress</h3>
                    <p class="muted-text mb-0">DigiSkills.pk</p>
                    <span class="about-cert-date">November 2022–January 2023</span>
                </div>
            </div>

            <div class="col-md-6">
                <div class="skill-card about-cert-card h-100">
                    <div class="about-cert-icon">
                        <i class="bi bi-briefcase"></i>
                    </div>
                    <h3>Freelancing</h3>
                    <p class="muted-text mb-0">DigiSkills.pk</p>
                    <span class="about-cert-date">July–October 2022</span>
                </div>
            </div>

            <div class="col-md-6">
                <div class="skill-card about-cert-card h-100">
                    <div class="about-cert-icon">
                        <i class="bi bi-pc-display"></i>
                    </div>
                    <h3>Digital Literacy</h3>
                    <p class="muted-text mb-0">DigiSkills.pk</p>
                    <span class="about-cert-date">March–June 2023</span>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Project Experience -->
<section class="section-pad" id="experience">
    <div class="container">

        <div class="section-heading">
            <span class="section-kicker">PRACTICAL WORK</span>
            <h2>Project <span class="text-gradient">Experience</span></h2>
            <p class="muted-text">A look at my practical development work.</p>
        </div>

        <div class="glass-card about-project-card">
            <div class="about-project-icon">
                <i class="bi bi-window-stack"></i>
            </div>

            <div class="about-project-content">
                <span class="section-kicker">ACADEMIC PROJECT</span>
                <h3>Student Management System</h3>

                <p class="muted-text">
                    Developed a database-driven application for creating,
                    viewing, searching, updating and deleting student records
                    using PHP and MySQL, with responsive interfaces built
                    using HTML, CSS, Bootstrap and JavaScript.
                </p>

                <div class="about-tech-list">
                    <span>HTML</span>
                    <span>CSS</span>
                    <span>Bootstrap</span>
                    <span>JavaScript</span>
                    <span>PHP</span>
                    <span>MySQL</span>
                </div>
            </div>
        </div>

    </div>
</section>



<?php require __DIR__ . '/includes/footer.php'; ?>
