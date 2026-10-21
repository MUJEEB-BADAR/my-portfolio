
<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/db.php';

$pageTitle = 'Projects | ' . OWNER_NAME;
$pageDescription = 'Selected web development projects by Mujeeb Ahmed Badar.';

$projects = [];
$projectsError = false;

$sql = "SELECT title, slug, description, technologies, category, image
        FROM projects
        ORDER BY is_featured DESC, created_at DESC, id DESC";

$result = mysqli_query($conn, $sql);

if ($result === false) {
    error_log('Projects page query failed: ' . mysqli_error($conn));
    $projectsError = true;
} else {
    while ($row = mysqli_fetch_assoc($result)) {
        $row['stack'] = array_values(
            array_filter(
                array_map(
                    'trim',
                    explode(',', (string) ($row['technologies'] ?? ''))
                ),
                static fn($tech) => $tech !== ''
            )
        );

        $imagePath = $row['image'] ?? '';
        $row['image_url'] = '';

        if (
            is_string($imagePath) &&
            preg_match(
                '#\Auploads/[a-f0-9]{32}\.(?:jpe?g|png|webp)\z#i',
                $imagePath
            ) &&
            is_file(__DIR__ . '/' . $imagePath)
        ) {
            $row['image_url'] = $imagePath;
        }

        $projects[] = $row;
    }

    mysqli_free_result($result);
}

require __DIR__ . '/includes/header.php';
?>

<style>
    /* Projects Page */
    .projects-page-section {
        position: relative;
        overflow: hidden;
        background: radial-gradient(
            ellipse at 50% 0%,
            rgba(124, 58, 237, 0.10),
            transparent 65%
        );
    }

    .projects-page-heading {
        max-width: 700px;
        margin: 0 auto 42px;
    }

    .projects-page-heading h2 {
        color: #f8fafc;
        font-size: clamp(1.8rem, 4vw, 2.5rem);
        font-weight: 800;
        letter-spacing: -0.8px;
        margin-bottom: 12px;
    }

    .projects-page-heading p {
        color: #a5b4cc;
        line-height: 1.8;
        margin-bottom: 0;
    }

    /* Category Filters */
    .projects-page-section .filter-bar {
        justify-content: center;
        margin-bottom: 38px !important;
    }

    .projects-page-section .filter-btn {
        padding: 10px 19px;
        border: 1px solid rgba(148, 163, 184, 0.22);
        border-radius: 9px;
        background: rgba(15, 23, 42, 0.75);
        color: #cbd5e1;
        font-size: 0.9rem;
        font-weight: 600;
        transition:
            background 0.25s ease,
            border-color 0.25s ease,
            color 0.25s ease,
            transform 0.25s ease;
    }

    .projects-page-section .filter-btn:hover {
        border-color: #a78bfa;
        color: #ffffff;
        transform: translateY(-2px);
    }

    .projects-page-section .filter-btn.active {
        border-color: #8b5cf6;
        background: linear-gradient(135deg, #7c3aed, #6366f1);
        color: #ffffff;
        box-shadow: 0 5px 18px rgba(124, 58, 237, 0.22);
    }

    .projects-page-section .filter-btn:focus-visible {
        outline: 2px solid #c4b5fd;
        outline-offset: 3px;
    }

    /* Project Cards */
    .projects-page-section .project-card {
        height: 100%;
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, 0.17);
        border-radius: 18px;
        background: linear-gradient(145deg, #141d32, #0d1527);
        transition:
            transform 0.3s ease,
            border-color 0.3s ease,
            box-shadow 0.3s ease;
    }

    .projects-page-section .project-card:hover {
        transform: translateY(-7px);
        border-color: rgba(167, 139, 250, 0.65);
        box-shadow: 0 18px 40px rgba(0, 0, 0, 0.25);
    }

    /* Project Images */
    .projects-page-section .project-visual {
        position: relative;
        display: flex;
        height: 220px;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: linear-gradient(
            135deg,
            rgba(124, 58, 237, 0.15),
            #111a2d
        );
    }

    .projects-page-section .project-image {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        transition: transform 0.45s ease;
    }

    .projects-page-section .project-card:hover .project-image {
        transform: scale(1.045);
    }

    .projects-page-section .project-visual-placeholder {
        display: flex;
        width: 100%;
        height: 100%;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 12px;
        color: #c4b5fd;
    }

    .projects-page-section .project-visual-placeholder i {
        font-size: 2.8rem;
    }

    .projects-page-section .project-visual-placeholder span {
        font-size: 0.85rem;
        font-weight: 600;
    }

    /* Image Category Label */
    .projects-page-section .project-visual-label {
        position: absolute;
        right: 14px;
        bottom: 14px;
        padding: 7px 12px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 8px;
        background: rgba(11, 18, 36, 0.88);
        color: #e9d5ff;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: capitalize;
        backdrop-filter: blur(8px);
    }

    /* Project Information */
    .projects-page-section .project-content {
        display: flex;
        min-height: 280px;
        flex-direction: column;
        padding: 24px;
    }

    .projects-page-section .project-label {
        display: inline-flex;
        align-self: flex-start;
        align-items: center;
        gap: 7px;
        margin-bottom: 14px;
        padding: 6px 10px;
        border: 1px solid rgba(167, 139, 250, 0.25);
        border-radius: 50px;
        background: rgba(124, 58, 237, 0.12);
        color: #c4b5fd;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: capitalize;
    }

    .projects-page-section .project-content h2 {
        margin-bottom: 12px;
        color: #f8fafc;
        font-size: 1.25rem;
        font-weight: 700;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .projects-page-section .project-content > p {
        margin-bottom: 22px;
        color: #a5b4cc;
        font-size: 0.92rem;
        line-height: 1.8;
        overflow-wrap: anywhere;
        white-space: normal;
    }

    /* Technology Badges */
    .projects-page-section .tech-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: auto;
        padding-top: 4px;
    }

    .projects-page-section .tech-tags span {
        display: inline-block;
        max-width: 100%;
        padding: 6px 10px;
        border: 1px solid rgba(129, 140, 248, 0.22);
        border-radius: 7px;
        background: rgba(99, 102, 241, 0.10);
        color: #c7d2fe;
        font-size: 0.76rem;
        overflow-wrap: anywhere;
    }

    .projects-page-section .project-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-top: 22px;
        padding-top: 17px;
        border-top: 1px solid rgba(148, 163, 184, 0.14);
    }

    
/* View Project Details Button */
.projects-page-section .project-details-btn {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: 9px;
    margin-top: 18px;
    padding: 12px 16px;
    border: 1px solid rgba(167, 139, 250, 0.4);
    border-radius: 10px;
    background: linear-gradient(
        110deg,
        rgba(91, 140, 255, 0.15),
        rgba(168, 85, 247, 0.15)
    );
    color: #dcd2ff;
    font-size: 0.9rem;
    font-weight: 700;
    text-decoration: none;
    transition:
        background 0.25s ease,
        border-color 0.25s ease,
        transform 0.25s ease;
}

.projects-page-section .project-details-btn:hover {
    border-color: #a78bfa;
    background: linear-gradient(110deg, #5b8cff, #a855f7);
    color: #ffffff;
    transform: translateY(-2px);
}

.projects-page-section .project-details-btn:focus-visible {
    outline: 2px solid #c4b5fd;
    outline-offset: 3px;
}

@media (prefers-reduced-motion: reduce) {
    .projects-page-section .project-details-btn {
        transition: none;
    }
}


    .projects-page-section .project-stack-label {
        color: #a5b4cc;
        font-size: 0.78rem;
    }

    .projects-page-section .project-stack-label i {
        margin-right: 5px;
        color: #a78bfa;
    }

    .projects-page-section .project-card-footer .project-card-category {
        color: #c4b5fd;
        font-size: 0.78rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .projects-page-section .projects-empty-state {
        border: 1px solid rgba(148, 163, 184, 0.17);
        border-radius: 18px;
        background: linear-gradient(145deg, #141d32, #0d1527);
        color: #f8fafc;
    }

    .projects-page-section .projects-empty-state > i {
        color: #a78bfa;
        font-size: 2.5rem;
    }

    @media (max-width: 575.98px) {
        .projects-page-heading {
            margin-bottom: 30px;
        }

        .projects-page-section .filter-bar {
            gap: 8px !important;
            margin-bottom: 28px !important;
        }

        .projects-page-section .filter-btn {
            padding: 9px 13px;
            font-size: 0.82rem;
        }

        .projects-page-section .project-visual {
            height: 200px;
        }

        .projects-page-section .project-content {
            min-height: auto;
            padding: 20px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .projects-page-section .project-card,
        .projects-page-section .project-image,
        .projects-page-section .filter-btn {
            transition: none;
        }
    }
</style>

<section class="page-banner">
    <div class="container">
        <span class="section-kicker">MY WORK</span>
        <h1>My <span class="text-gradient">Projects</span></h1>
        <p>Academic and practical web development projects.</p>
    </div>
</section>

<section class="section-pad projects-page-section">
    <div class="container">

        <div class="projects-page-heading text-center">
            <h2>Explore My <span class="text-gradient">Work</span></h2>
            <p>
                Discover projects I've built while developing my skills
                in frontend, backend and full-stack web development.
            </p>
        </div>

        <?php if (!$projectsError && count($projects) > 0): ?>

            <div class="filter-bar d-flex flex-wrap gap-2"
                 role="group"
                 aria-label="Filter projects">

                <button type="button"
                        class="filter-btn active"
                        data-filter="all"
                        aria-pressed="true">
                    All Projects
                </button>

                <button type="button"
                        class="filter-btn"
                        data-filter="frontend"
                        aria-pressed="false">
                    Frontend
                </button>

                <button type="button"
                        class="filter-btn"
                        data-filter="backend"
                        aria-pressed="false">
                    Backend
                </button>

                <button type="button"
                        class="filter-btn"
                        data-filter="fullstack"
                        aria-pressed="false">
                    Full Stack
                </button>

            </div>

            <div class="row g-4" id="project-list">

                <?php foreach ($projects as $project): ?>

                    <?php
                    $category = strtolower(
                        trim((string) ($project['category'] ?? ''))
                    );

                    $categoryLabel = ucwords(
                        str_replace('-', ' ', $category)
                    );

                    $categoryIcons = [
                        'frontend' => 'bi-window',
                        'backend' => 'bi-database',
                        'fullstack' => 'bi-layers'
                    ];

                    $categoryIcon = $categoryIcons[$category]
                        ?? 'bi-code-slash';
                    ?>

                    <div class="col-md-6 col-lg-4 project-filter-item"
                         data-category="<?= e($category) ?>">

                        <article class="project-card">

                            <div class="project-visual">

                                <?php if (!empty($project['image_url'])): ?>

                                    <img
                                        src="<?= e($project['image_url']) ?>"
                                        alt="<?= e($project['title']) ?>"
                                        class="project-image"
                                        loading="lazy"
                                        decoding="async"
                                    >

                                <?php else: ?>

                                    <div class="project-visual-placeholder">
                                        <i class="bi bi-image"
                                           aria-hidden="true"></i>
                                        <span>Project Preview</span>
                                    </div>

                                <?php endif; ?>

                                <span class="project-visual-label">
                                    <?= e($categoryLabel ?: 'Project') ?>
                                </span>

                            </div>

                            <div class="project-content">

                                <span class="project-label">
                                    <i class="bi <?= e($categoryIcon) ?>"
                                       aria-hidden="true"></i>
                                    <?= e($categoryLabel ?: 'Project') ?>
                                </span>

                                <h2><?= e($project['title']) ?></h2>

                                <p><?= e($project['description']) ?></p>

                                <?php if (!empty($project['stack'])): ?>

                                    <div class="tech-tags">

                                        <?php foreach ($project['stack'] as $tech): ?>

                                            <span><?= e($tech) ?></span>

                                        <?php endforeach; ?>

                                    </div>

                                <?php endif; ?>

                                
                                <div class="project-card-footer">

                                    <span class="project-stack-label">
                                        <i class="bi bi-code-slash"
                                           aria-hidden="true"></i>
                                        Technologies
                                    </span>

                                    <span class="project-card-category">
                                        <?= e($categoryLabel ?: 'Project') ?>
                                    </span>

                                </div>

                                <a
                                    href="project-details.php?slug=<?= e(rawurlencode((string) ($project['slug'] ?? ''))) ?>"
                                    class="project-details-btn"
                                    aria-label="View details for <?= e($project['title']) ?>"
                                >
                                    View Project Details
                                    <i class="bi bi-arrow-right"
                                       aria-hidden="true"></i>
                                </a>


                            </div>

                        </article>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php elseif ($projectsError): ?>

            <div class="projects-empty-state p-4 p-md-5">
                <h2>Projects are temporarily unavailable</h2>
                <p class="muted-text mb-0">
                    Please check that MySQL is running and the projects
                    database is available.
                </p>
            </div>

        <?php else: ?>

            <div class="projects-empty-state p-4 p-md-5 text-center">
                <i class="bi bi-folder2-open"
                   aria-hidden="true"></i>

                <h2 class="mt-3">Projects Coming Soon</h2>

                <p class="muted-text mb-0">
                    Projects added to your portfolio will appear here.
                </p>
            </div>

        <?php endif; ?>

    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
