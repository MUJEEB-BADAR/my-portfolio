<?php
require_once __DIR__ . '/includes/db.php';
// Get total number of projects.
$countSql = "SELECT COUNT(*) AS total_projects FROM projects";
$countResult = mysqli_query($conn, $countSql);

$totalProjects = 0;

if ($countResult) {
    $countRow = mysqli_fetch_assoc($countResult);
    $totalProjects = (int) $countRow['total_projects'];
}
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

$pageTitle = OWNER_NAME . ' | Software Engineer & Web Developer';
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
                    <a href="https://github.com/MUJEEB-BADAR"
   target="_blank"
   rel="noopener noreferrer"
   aria-label="GitHub">
    <i class="bi bi-github"></i>
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
                    <h3><?= $totalProjects; ?></h3>
                    <p>Projects</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <h3>08</h3>
                    <p>Core Technologies</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <h3>BS</h3>
                    <p>Software Engineering</p>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <h3>PHP</h3>
                    <p>Backend Development</p>
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
<div class="row justify-content-center"><div class="col-lg-9"><div class="glass-card p-4 p-md-5">
    <p class="lead">
        I'm an aspiring Software Engineer and Web Developer passionate
        about creating responsive, user-friendly, and functional web
        applications. I enjoy learning new technologies and applying my
        skills to practical projects.
    </p>

    <p class="muted-text">
        My technical skills include HTML, CSS, Bootstrap, JavaScript,
        jQuery, AJAX, PHP, and MySQL. I am continuously improving my
        development skills through hands-on projects and practical
        learning, with a focus on writing clean code and building
        responsive web experiences.
    </p>

    <a href="about.php" class="text-link">
        Learn More About Me
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
    ['HTML5', 'bi-filetype-html', 'Semantic and structured web pages'],
    ['CSS3', 'bi-filetype-css', 'Styling and responsive layouts'],
    ['Bootstrap 5', 'bi-bootstrap', 'Responsive UI components and grids'],
    ['JavaScript', 'bi-filetype-js', 'Interactive web functionality'],
    ['jQuery', 'bi-braces', 'DOM manipulation and event handling'],
    ['AJAX', 'bi-arrow-repeat', 'Asynchronous server communication'],
    ['PHP', 'bi-filetype-php', 'Server-side scripting and logic'],
    ['MySQL', 'bi-database', 'Relational database operations']
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
    display: flex;
    flex-direction: column;
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
    flex: 1;
    padding: 24px;
    min-height: 340px;
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

.featured-project-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 22px;
    padding-top: 17px;
    border-top: 1px solid rgba(148, 163, 184, 0.14);
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
        min-height: 0;
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
    /* Education & Experience — Journey Section */
#journey .journey-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 26px;
    margin-top: 42px;
}

#journey .journey-card {
    position: relative;
    height: 100%;
    padding: 30px;
    overflow: hidden;
    background: linear-gradient(145deg, #141d32, #0d1527);
    border: 1px solid rgba(148, 163, 184, 0.17);
    border-radius: 20px;
    transition: transform 0.3s ease,
                border-color 0.3s ease,
                box-shadow 0.3s ease;
}

#journey .journey-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 30px;
    right: 30px;
    height: 2px;
    background: linear-gradient(90deg, #5b8cff, #a855f7);
    opacity: 0.8;
}

#journey .journey-card:hover {
    transform: translateY(-5px);
    border-color: rgba(167, 139, 250, 0.5);
    box-shadow: 0 18px 38px rgba(0, 0, 0, 0.22);
}

#journey .journey-card-top {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 30px;
}

#journey .journey-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 54px;
    height: 54px;
    border: 1px solid rgba(167, 139, 250, 0.3);
    border-radius: 15px;
    background: rgba(124, 58, 237, 0.13);
    color: #c4b5fd;
    font-size: 1.55rem;
}

#journey .journey-label {
    color: #a5b4cc;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 1.5px;
}

#journey .journey-timeline {
    position: relative;
    padding-left: 25px;
}

#journey .journey-timeline::before {
    content: "";
    position: absolute;
    top: 8px;
    bottom: 8px;
    left: 5px;
    width: 1px;
    background: linear-gradient(
        to bottom,
        rgba(167, 139, 250, 0.75),
        rgba(91, 140, 255, 0.12)
    );
}

#journey .journey-dot {
    position: absolute;
    top: 6px;
    left: 0;
    width: 11px;
    height: 11px;
    border: 2px solid #c4b5fd;
    border-radius: 50%;
    background: #141d32;
    box-shadow: 0 0 12px rgba(167, 139, 250, 0.35);
}

#journey .journey-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 18px;
    padding: 7px 11px;
    border: 1px solid rgba(91, 140, 255, 0.2);
    border-radius: 30px;
    background: rgba(91, 140, 255, 0.08);
    color: #c7d2fe;
    font-size: 0.75rem;
    font-weight: 600;
}

#journey .journey-status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #8b9eff;
}

#journey .journey-content h3 {
    margin: 0 0 6px;
    color: #f8fafc;
    font-size: 1.4rem;
    font-weight: 750;
    line-height: 1.4;
    overflow-wrap: anywhere;
}

#journey .journey-content h4 {
    margin: 0 0 18px;
    color: #c4b5fd;
    font-size: 1rem;
    font-weight: 600;
    line-height: 1.6;
}

#journey .journey-institution {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin-bottom: 16px;
    color: #d1d9e8;
    font-size: 0.9rem;
    line-height: 1.7;
}

#journey .journey-institution i {
    margin-top: 3px;
    color: #a78bfa;
}

#journey .journey-description {
    margin-bottom: 22px;
    color: #a5b4cc;
    font-size: 0.92rem;
    line-height: 1.85;
}

#journey .journey-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #c4b5fd;
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    transition: color 0.2s ease, gap 0.2s ease;
}

#journey .journey-link:hover {
    gap: 12px;
    color: #ffffff;
}

@media (max-width: 767.98px) {
    #journey .journey-grid {
        grid-template-columns: 1fr;
        gap: 20px;
        margin-top: 30px;
    }

    #journey .journey-card {
        padding: 24px;
    }

    #journey .journey-card::before {
        left: 24px;
        right: 24px;
    }
}

@media (prefers-reduced-motion: reduce) {
    #journey .journey-card,
    #journey .journey-link {
        transition: none;
    }
}


/* Professional Contact CTA Section */
#contact-cta .cta-card {
    position: relative;
    overflow: hidden;
    padding: 58px 30px;
    border: 1px solid rgba(139, 92, 246, 0.3);
    border-radius: 24px;
    background:
        radial-gradient(
            circle at 10% 10%,
            rgba(91, 140, 255, 0.16),
            transparent 38%
        ),
        radial-gradient(
            circle at 90% 90%,
            rgba(168, 85, 247, 0.16),
            transparent 38%
        ),
        linear-gradient(135deg, #131b31, #0b1120);
    box-shadow: 0 20px 55px rgba(0, 0, 0, 0.2);
}

#contact-cta .cta-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 62px;
    height: 62px;
    margin: 0 auto 24px;
    border: 1px solid rgba(167, 139, 250, 0.35);
    border-radius: 18px;
    background: rgba(124, 58, 237, 0.14);
    color: #c4b5fd;
    font-size: 1.7rem;
}

#contact-cta .cta-card h2 {
    margin: 18px 0;
    color: #f8fafc;
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 800;
    line-height: 1.35;
}

#contact-cta .cta-description {
    max-width: 570px;
    margin: 0 auto;
    font-size: 1rem;
    line-height: 1.9;
}

#contact-cta .cta-button {
    margin-top: 28px;
    padding: 13px 25px;
    border-radius: 12px;
    font-weight: 700;
    transition: transform 0.25s ease,
                box-shadow 0.25s ease;
}

#contact-cta .cta-button:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(91, 140, 255, 0.22);
}

#contact-cta .cta-note {
    margin: 25px 0 0;
    color: #94a3b8;
    font-size: 0.85rem;
}

#contact-cta .cta-note i {
    margin-right: 6px;
    color: #a78bfa;
}

@media (max-width: 575.98px) {
    #contact-cta .cta-card {
        padding: 42px 20px;
        border-radius: 18px;
    }

    #contact-cta .cta-card h2 {
        font-size: 1.9rem;
    }

    #contact-cta .cta-description {
        font-size: 0.92rem;
    }
}

@media (prefers-reduced-motion: reduce) {
    #contact-cta .cta-button {
        transition: none;
    }

    #contact-cta .cta-button:hover {
        transform: none;
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
    No Featured Projects Yet
</h3>

<p class="muted-text mb-0">
    Selected projects will be showcased here.
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
            <p class="muted-text">
                My academic background and practical development journey.
            </p>
        </div>

        <div class="journey-grid">

            <!-- Education -->
            <article class="journey-card">
                <div class="journey-card-top">
                    <div class="journey-icon">
                        <i class="bi bi-mortarboard"></i>
                    </div>
                    <span class="journey-label">EDUCATION</span>
                </div>

                <div class="journey-timeline">
                    <span class="journey-dot"></span>

                    <div class="journey-content">
                        <span class="journey-status">
                            <span class="journey-status-dot"></span>
                            Currently Studying
                        </span>

                        <h3>Bachelor of Science</h3>
                        <h4>Software Engineering</h4>

                        <p class="journey-institution">
                            <i class="bi bi-building"></i>
                            Virtual University of Pakistan
                        </p>

                        <p class="journey-description">
                            Pursuing a degree in Software Engineering while
                            developing practical skills in web development,
                            programming and database management.
                        </p>

                        <a href="about.php#education" class="journey-link">
                            Education Details
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>
            </article>

            <!-- Development Experience -->
            <article class="journey-card">
                <div class="journey-card-top">
                    <div class="journey-icon">
                        <i class="bi bi-code-slash"></i>
                    </div>
                    <span class="journey-label">PRACTICAL EXPERIENCE</span>
                </div>

                <div class="journey-timeline">
                    <span class="journey-dot"></span>

                    <div class="journey-content">
                        <span class="journey-status">
                            <span class="journey-status-dot"></span>
                            Project-Based Learning
                        </span>

                        <h3>Web Development</h3>
                        <h4>Academic &amp; Personal Projects</h4>

                        <p class="journey-institution">
                            <i class="bi bi-laptop"></i>
                            PHP, MySQL &amp; Web Technologies
                        </p>
                        <p class="journey-description">
    Developing hands-on web development skills by building
    a Student Management System and a personal portfolio
    website using PHP, MySQL, HTML, CSS, Bootstrap and JavaScript.
    Continuously improving my coding, problem-solving and
    application development skills through practical work.
</p>


                        <a href="about.php#experience" class="journey-link">
                            Experience Details
                            <i class="bi bi-arrow-up-right"></i>
                        </a>
                    </div>
                </div>
            </article>

        </div>
    </div>
</section>



<section class="section-pad" id="contact-cta">
    <div class="container">
        <div class="cta-card text-center">
            <div class="cta-icon">
                <i class="bi bi-chat-square-dots"></i>
            </div>

            <span class="section-kicker">LET'S CONNECT</span>

            <h2>
                Have a project in mind?
                <br>
                Let's build something
                <span class="text-gradient">great.</span>
            </h2>

            <p class="muted-text cta-description">
                I'm open to web development opportunities,
                collaborative projects, and new challenges.
            </p>

            <a href="contact.php" class="btn btn-accent btn-lg cta-button">
                Get In Touch
                <i class="bi bi-arrow-up-right ms-2"></i>
            </a>

            <p class="cta-note">
                <i class="bi bi-code-slash"></i>
                Let's turn ideas into reality.
            </p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>