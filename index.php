<?php
require_once __DIR__ . '/includes/db.php';

$sql = "SELECT title, description, technologies, category, image
        FROM projects
        WHERE is_featured = 1
        ORDER BY created_at DESC
        LIMIT 3";

$result = mysqli_query($conn, $sql);

if (!$result) {
    error_log("Featured projects query failed: " . mysqli_error($conn));
    $result = false;
}
?>
<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'MUJEEB AHMED BADAR | Software Engineer | Web Developer';
require __DIR__ . '/includes/header.php';
?>

<section class="hero-section section-pad" id="home">
    <div class="container">
        <div class="row align-items-center gy-5">
            <div class="col-lg-7">
                <span class="eyebrow">
                    <span class="status-dot"></span>
                    AVAILABLE FOR OPPORTUNITIES
                </span>

                <p class="hero-intro mt-4 mb-2">Hello, I'm</p>

                <h1 class="hero-title">
                    <span class="text-gradient">MUJEEB AHMED BADAR</span>
                </h1>

                <h2 class="hero-role">
                    SOFTWARE ENGINEER | <span id="typing-text">Web Developer</span>
                </h2>

                <p class="hero-description mt-4">
                    I build responsive, user-friendly websites using
                    HTML, CSS, Bootstrap, JavaScript, jQuery, AJAX, PHP and MySQL. I enjoy turning ideas into practical
                    web experiences.
                </p>

                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="projects.php" class="btn btn-accent btn-lg">
                        Explore My Work
                        <i class="bi bi-arrow-up-right ms-2"></i>
                    </a>

                    <a href="contact.php"
                       class="btn btn-outline-light btn-lg">
                        Contact Me
                    </a>
                </div>

                <div class="hero-socials mt-4">

                    <a href="https://www.linkedin.com/in/mujeeb-ahmed-badar-60661a248/"
                       target="_blank" rel="noopener noreferrer"
                       aria-label="LinkedIn">
                        <i class="bi bi-linkedin"></i>
                    </a>
                    <a href="mailto:mujeebmughal458@gmail.com"
                       aria-label="Email">
                        <i class="bi bi-envelope"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-5 text-center">
                <div class="profile-frame mx-auto">
                    <div class="profile-glow"></div>
                    <img
                        src="assets/images/Mujeeb.jpeg"
                        alt="Mujeeb Ahmed Badar"
                        class="profile-image">
                    <div class="floating-tag tag-top">
                        
                        Clean Code
                    </div>
                    <div class="floating-tag tag-bottom">
                        
                        Responsive UI
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="stats-strip">
    <div class="container">
        <div class="row text-center g-3">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <h3>8</h3>
                    <p>Core Technologies</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <h3>100%</h3>
                    <p>Learning Mindset</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <h3>Web</h3>
                    <p>Development Focus</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <h3>PHP</h3>
                    <p>Backend Technology</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-pad" id="about">
    <div class="container">
        <div class="section-heading text-center">
            <span class="section-kicker">GET TO KNOW ME</span>
            <h2>About <span class="text-gradient">Me</span></h2>
            <p>A short introduction to my development journey.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="glass-card p-4 p-md-5">
                    <p class="lead">
                        I'm an aspiring Software Engineering Student and Web Developer
                        focused on building responsive and functional
                        websites.
                    </p>
                    <p class="muted-text">
                        My toolkit includes front-end technologies
                        such as HTML, CSS, Bootstrap and JavaScript,
                        along with jQuery, AJAX, PHP and MySQL for interactive
                        web applications. I'm continuously improving
                        my skills through hands-on projects.
                    </p>
                    <a href="about.php" class="text-link">
                        More About Me
                        <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-pad section-muted" id="skills">
    <div class="container">
        <div class="section-heading text-center">
            <span class="section-kicker">MY TOOLKIT</span>
            <h2>Technical <span class="text-gradient">Skills</span></h2>
            <p>Technologies I use and practise in web development.</p>
        </div>

        <div class="row g-4">
            <?php
            $skills = [
                ['HTML5', 'bi-filetype-html', 'Structure and semantic markup'],
                ['CSS3', 'bi-filetype-css', 'Styling and responsive layouts'],
                ['Bootstrap 5', 'bi-bootstrap', 'Mobile-first UI components'],
                ['JavaScript', 'bi-filetype-js', 'Client-side interactivity'],
                ['jQuery', 'bi-braces', 'DOM manipulation and events'],
                ['AJAX', 'bi-arrow-repeat', 'Asynchronous HTTP requests'],
                ['PHP', 'bi-filetype-php', 'Server-side application logic'],
                ['MySQL', 'bi-database', 'Relational database management']
            ];
            foreach ($skills as $skill):
            ?>
            <div class="col-sm-6 col-lg-3">
                <div class="skill-card h-100">
                    <i class="bi <?= e($skill[1]) ?> skill-icon"></i>
                    <h3><?= e($skill[0]) ?></h3>
                    <p><?= e($skill[2]) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<style>
    /* Featured Projects Section */
    .featured-projects-section {
        position: relative;
        overflow: hidden;
        background:
            radial-gradient(
                ellipse at 50% 0%,
                rgba(124, 58, 237, 0.12),
                transparent 65%
            );
    }

    .featured-projects-section .projects-heading {
        max-width: 650px;
        margin: 0 auto 48px;
    }

    .featured-projects-section .projects-kicker {
        display: inline-block;
        color: #a78bfa;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 12px;
    }

    .featured-projects-section .projects-title {
        color: #f8fafc;
        font-size: clamp(2rem, 4vw, 2.8rem);
        font-weight: 800;
        letter-spacing: -1px;
        margin-bottom: 14px;
    }

    .featured-projects-section .projects-subtitle {
        color: #a5b4cc;
        line-height: 1.8;
        margin: 0;
    }

    /* Project Cards */
    .featured-project-card {
        height: 100%;
        background: linear-gradient(
            145deg,
            #141d32,
            #0d1527
        );
        border: 1px solid rgba(148, 163, 184, 0.17);
        border-radius: 18px;
        overflow: hidden;
        transition:
            transform 0.3s ease,
            border-color 0.3s ease,
            box-shadow 0.3s ease;
    }

    .featured-project-card:hover {
        transform: translateY(-7px);
        border-color: rgba(167, 139, 250, 0.65);
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.25);
    }

    /* Image */
    .featured-project-image-wrap {
        position: relative;
        height: 220px;
        overflow: hidden;
        background: #111a2d;
    }

    .featured-project-image {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.45s ease;
    }

    .featured-project-card:hover .featured-project-image {
        transform: scale(1.045);
    }

    .featured-project-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 10px;
        color: #a78bfa;
        background: linear-gradient(
            135deg,
            rgba(124, 58, 237, 0.13),
            rgba(30, 41, 59, 0.35)
        );
    }

    .featured-project-placeholder i {
        font-size: 2.5rem;
    }

    .featured-project-placeholder span {
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Card Content */
    .featured-project-body {
        display: flex;
        flex-direction: column;
        padding: 24px;
        min-height: 265px;
    }

    .featured-project-category {
        display: inline-flex;
        align-self: flex-start;
        align-items: center;
        gap: 7px;
        padding: 6px 10px;
        margin-bottom: 15px;
        border: 1px solid rgba(167, 139, 250, 0.25);
        border-radius: 50px;
        background: rgba(124, 58, 237, 0.12);
        color: #c4b5fd;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: capitalize;
    }

    .featured-project-category i {
        font-size: 0.75rem;
    }

    .featured-project-title {
        color: #f8fafc;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.5;
        margin-bottom: 12px;
        overflow-wrap: anywhere;
    }

    .featured-project-description {
        color: #a5b4cc;
        font-size: 0.92rem;
        line-height: 1.8;
        margin-bottom: 22px;
        overflow-wrap: anywhere;
        white-space: normal;
    }

    /* Technology Tags */
    .featured-project-technologies {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: auto;
        padding-top: 4px;
    }

    .featured-project-tech {
        display: inline-block;
        padding: 6px 10px;
        border: 1px solid rgba(129, 140, 248, 0.22);
        border-radius: 7px;
        background: rgba(99, 102, 241, 0.10);
        color: #c7d2fe;
        font-size: 0.76rem;
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    .featured-project-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 22px;
        padding-top: 17px;
        border-top: 1px solid rgba(148, 163, 184, 0.14);
    }

    .featured-project-footer-label {
        color: #94a3b8;
        font-size: 0.78rem;
    }

    .featured-project-footer-label span {
        color: #c4b5fd;
        font-weight: 600;
    }

    .featured-project-link {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: #c4b5fd;
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        transition: color 0.2s ease;
    }

    .featured-project-link:hover {
        color: #ffffff;
    }

    .featured-project-link i {
        transition: transform 0.2s ease;
    }

    .featured-project-link:hover i {
        transform: translate(3px, -3px);
    }

    .featured-projects-cta {
        margin-top: 42px;
        text-align: center;
    }

    .featured-projects-cta .btn {
        border-radius: 10px;
    }

    @media (max-width: 575.98px) {
        .featured-projects-section .projects-heading {
            margin-bottom: 32px;
        }

        .featured-project-body {
            padding: 20px;
            min-height: auto;
        }

        .featured-project-image-wrap {
            height: 200px;
        }

        .featured-projects-cta {
            margin-top: 30px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .featured-project-card,
        .featured-project-image,
        .featured-project-link,
        .featured-project-link i {
            transition: none;
        }
    }
</style>

<section id="projects" class="section-pad featured-projects-section">
    <div class="container">

        <div class="projects-heading text-center">
            <span class="projects-kicker">MY RECENT WORK</span>

            <h2 class="projects-title">
                Featured <span class="text-gradient">Projects</span>
            </h2>

            <p class="projects-subtitle">
                A selection of projects showcasing my skills,
                technologies and hands-on development experience.
            </p>
        </div>

        <div class="row g-4">

            <?php if ($result === false): ?>

                <div class="col-12">
                    <p class="text-center text-danger">
                        Projects could not be loaded. Please try again later.
                    </p>
                </div>

            <?php elseif (mysqli_num_rows($result) > 0): ?>

                <?php while ($project = mysqli_fetch_assoc($result)): ?>

                    <?php
                    $imagePath = $project['image'] ?? '';
                    $imageUrl = '';

                    if (
                        is_string($imagePath) &&
                        preg_match(
                            '#\Auploads/[a-f0-9]{32}\.(?:jpe?g|png|webp)\z#i',
                            $imagePath
                        ) &&
                        is_file(__DIR__ . '/' . $imagePath)
                    ) {
                        $imageUrl = $imagePath;
                    }

                    $category = trim((string) ($project['category'] ?? ''));

                    $categoryIcons = [
                        'frontend' => 'bi-window',
                        'backend' => 'bi-database',
                        'fullstack' => 'bi-layers'
                    ];

                    $categoryIcon = $categoryIcons[$category] ?? 'bi-code-slash';
                    ?>

                    <div class="col-md-6 col-lg-4">

                        <article class="featured-project-card">

                            <div class="featured-project-image-wrap">

                                <?php if ($imageUrl !== ''): ?>

                                    <img
                                        src="<?= htmlspecialchars(
                                            $imageUrl,
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        alt="<?= htmlspecialchars(
                                            (string) $project['title'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>"
                                        class="featured-project-image"
                                        loading="lazy"
                                        decoding="async"
                                    >

                                <?php else: ?>

                                    <div class="featured-project-placeholder">
                                        <i class="bi bi-image" aria-hidden="true"></i>
                                        <span>Project Preview</span>
                                    </div>

                                <?php endif; ?>

                            </div>

                            <div class="featured-project-body">

                                <span class="featured-project-category">
                                    <i class="bi <?= e($categoryIcon) ?>"
                                       aria-hidden="true"></i>
                                    <?= htmlspecialchars(
                                        $category !== '' ? $category : 'Project',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </span>

                                <h3 class="featured-project-title">
                                    <?= htmlspecialchars(
                                        (string) $project['title'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </h3>

                                <p class="featured-project-description">
                                    <?= htmlspecialchars(
                                        (string) $project['description'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </p>

                                <div class="featured-project-technologies">

                                    <?php
                                    $technologies = explode(
                                        ',',
                                        (string) ($project['technologies'] ?? '')
                                    );

                                    foreach ($technologies as $tech):
                                        $tech = trim($tech);

                                        if ($tech === '') {
                                            continue;
                                        }
                                    ?>

                                        <span class="featured-project-tech">
                                            <?= htmlspecialchars(
                                                $tech,
                                                ENT_QUOTES,
                                                'UTF-8'
                                            ) ?>
                                        </span>

                                    <?php endforeach; ?>

                                </div>

                                <div class="featured-project-footer">

                                    <span class="featured-project-footer-label">
                                        <span>
                                            <i class="bi bi-code-slash"
                                               aria-hidden="true"></i>
                                            Built with care
                                        </span>
                                    </span>

                                    <a href="projects.php"
                                       class="featured-project-link">
                                        View Projects
                                        <i class="bi bi-arrow-up-right"
                                           aria-hidden="true"></i>
                                    </a>

                                </div>

                            </div>

                        </article>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="col-12">
                    <div class="text-center glass-card p-5">
                        <i class="bi bi-folder2-open feature-icon"
                           aria-hidden="true"></i>

                        <h3 class="text-light mt-3">
                            Projects Coming Soon
                        </h3>

                        <p class="muted-text mb-0">
                            Featured projects will appear here soon.
                        </p>
                    </div>
                </div>

            <?php endif; ?>

        </div>

        <div class="featured-projects-cta">
            <a href="projects.php" class="btn btn-accent btn-lg">
                Explore All Projects
                <i class="bi bi-arrow-right ms-2"
                   aria-hidden="true"></i>
            </a>
        </div>

    </div>
</section>


<section class="section-pad section-muted" id="journey">
    <div class="container">
        <div class="section-heading text-center">
            <span class="section-kicker">MY JOURNEY</span>
            <h2>Education &amp; <span class="text-gradient">Experience</span></h2>
        </div>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <i class="bi bi-mortarboard feature-icon"></i>
                    <h3>Education</h3>
                    <p class="muted-text">
                        Bachelor of Science in Software Engineering at Virtual University of Pakistan (degree in progress).
                    </p>
                    <a href="about.php#education" class="text-link">
                        Education details
                    </a>
                </div>
            </div>
            <div class="col-md-6">
                <div class="glass-card p-4 h-100">
                    <i class="bi bi-briefcase feature-icon"></i>
                    <h3>Development Experience</h3>
                    <p class="muted-text">
                        Academic project work includes a Student Management System built with PHP and MySQL. I am continuing to build practical web development experience.
                    </p>
                    <a href="about.php#experience" class="text-link">
                        Experience details
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section-pad">
    <div class="container">
        <div class="cta-card text-center">
            <span class="section-kicker">HAVE A PROJECT IN MIND?</span>
            <h2>Let's build something <span class="text-gradient">great.</span></h2>
            <p class="muted-text">
                I'm interested in discussing web development opportunities.
            </p>
            <a href="contact.php" class="btn btn-accent btn-lg mt-3">
                Get In Touch <i class="bi bi-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>