
<?php
require_once __DIR__ . '/includes/db.php';

function projectDetailsEscape($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$slug = trim($_GET['slug'] ?? '');
$project = null;

if ($slug !== '' && strlen($slug) <= 200) {
    $sql = "SELECT title, slug, description, technologies, category, image,
               github_url, created_at
        FROM projects
        WHERE slug = ?
        LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 's', $slug);

        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);

            if ($result) {
                $project = mysqli_fetch_assoc($result) ?: null;
                mysqli_free_result($result);
            }
        } else {
            error_log('Project details query failed.');
        }

        mysqli_stmt_close($stmt);
    } else {
        error_log('Project details statement preparation failed.');
    }
}

if (!$project) {
    http_response_code(404);
}

$imagePath = $project['image'] ?? '';
$githubUrl = trim((string) ($project['github_url'] ?? ''));

$validGithubUrl = (
    $githubUrl !== '' &&
    filter_var($githubUrl, FILTER_VALIDATE_URL) !== false &&
    in_array(
        strtolower((string) parse_url($githubUrl, PHP_URL_SCHEME)),
        ['http', 'https'],
        true
    ) &&
    strtolower((string) parse_url($githubUrl, PHP_URL_HOST)) === 'github.com'
);

$validImage = (
    is_string($imagePath) &&
    preg_match(
        '#\Auploads/[a-f0-9]{32}\.(?:jpg|jpeg|png|webp)\z#i',
        $imagePath
    ) === 1 &&
    is_file(__DIR__ . '/' . $imagePath)
);

$category = ucwords(
    str_replace('_', ' ', $project['category'] ?? '')
);

$technologies = array_filter(
    array_map(
        'trim',
        explode(',', (string) ($project['technologies'] ?? ''))
    ),
    static fn($technology) => $technology !== ''
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?= $project
            ? projectDetailsEscape($project['title']) . ' | My Portfolio'
            : 'Project Not Found | My Portfolio' ?>
    </title>

    <meta name="description"
          content="<?= projectDetailsEscape(
              $project
                  ? mb_substr(
                      trim(strip_tags((string) $project['description'])),
                      0,
                      155
                  )
                  : 'The requested portfolio project could not be found.'
          ) ?>">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        :root {
            --bg: #080d1b;
            --card: #131b31;
            --text: #f1f5ff;
            --muted: #a6b1ca;
            --blue: #5b8cff;
            --purple: #a855f7;
            --border: rgba(255, 255, 255, .10);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            color: var(--text);
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                radial-gradient(circle at 10% 0%, #5b8cff25, transparent 32%),
                radial-gradient(circle at 90% 10%, #a855f720, transparent 30%),
                var(--bg);
        }

        .details-container {
            width: min(100% - 36px, 1050px);
            margin: 0 auto;
            padding: 45px 0 65px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 28px;
            color: #c5d4ff;
            text-decoration: none;
        }

        .back-link:hover {
            color: #fff;
        }

        .details-card {
            overflow: hidden;
            border: 1px solid var(--border);
            border-radius: 22px;
            background: rgba(19, 27, 49, .92);
        }

        .project-image {
    display: block;
    width: 100%;
    height: clamp(240px, 48vw, 540px);
    object-fit: contain;
    object-position: center;
    background: #0b1224;
}

        .image-placeholder {
            display: grid;
            min-height: 240px;
            place-items: center;
            color: var(--muted);
            background: #0b1224;
            font-size: 1rem;
        }

        .details-content {
            padding: clamp(23px, 5vw, 48px);
        }

        .project-category {
            display: inline-block;
            margin-bottom: 16px;
            padding: 7px 13px;
            border: 1px solid #5b8cff55;
            border-radius: 30px;
            color: #c5d4ff;
            background: #5b8cff18;
            font-size: .8rem;
            font-weight: 700;
        }

        h1 {
            margin: 0 0 22px;
            overflow-wrap: anywhere;
            font-size: clamp(2rem, 5vw, 3.2rem);
            font-weight: 800;
            line-height: 1.2;
        }

        .description {
            color: #c4cde0;
            font-size: 1rem;
            line-height: 1.9;
            overflow-wrap: anywhere;
        }

        .subheading {
            margin: 30px 0 15px;
            font-size: 1.15rem;
            font-weight: 750;
        }

        .technology-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .technology-tag {
            padding: 8px 13px;
            border: 1px solid var(--border);
            border-radius: 9px;
            color: #dbe5ff;
            background: #ffffff08;
            font-size: .88rem;
        }

        .project-date {
            margin-top: 30px;
            color: var(--muted);
            font-size: .85rem;
        }

        .primary-link {
            display: inline-block;
            margin-top: 28px;
            padding: 12px 18px;
            border-radius: 10px;
            color: white;
            background: linear-gradient(110deg, var(--blue), var(--purple));
            font-weight: 650;
            text-decoration: none;
        }

        .primary-link:hover {
            color: white;
            filter: brightness(1.1);
        }

        
.github-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    margin-top: 28px;
    margin-left: 10px;
    padding: 12px 18px;
    border: 1px solid rgba(167, 139, 250, .45);
    border-radius: 10px;
    color: #e2dcff;
    background: rgba(168, 85, 247, .10);
    font-weight: 650;
    text-decoration: none;
    transition: background .2s ease, border-color .2s ease;
}

.github-link:hover {
    color: #fff;
    background: rgba(168, 85, 247, .25);
    border-color: #a78bfa;
}

.github-link:focus-visible {
    outline: 2px solid #c4b5fd;
    outline-offset: 3px;
}

@media (max-width: 576px) {
    .github-link {
        margin-left: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .github-link {
        transition: none;
    }
}


        .not-found {
            padding: 55px 25px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: var(--card);
            text-align: center;
        }

        .not-found h1 {
            font-size: 2rem;
        }

        footer {
            padding-top: 25px;
            color: #8490aa;
            text-align: center;
            font-size: .85rem;
        }

        @media (max-width: 576px) {
    .details-container {
        width: min(100% - 24px, 1050px);
        padding-top: 28px;
    }

    .details-card {
        border-radius: 16px;
    }

    .project-image {
        height: 280px;
    }
}
        
    </style>
</head>
<body>

<main class="details-container">

    <a href="projects.php" class="back-link">
        &larr; Back to All Projects
    </a>

    <?php if ($project): ?>

        <article class="details-card">

            <?php if ($validImage): ?>
                <img
                    class="project-image"
                    src="<?= projectDetailsEscape($imagePath) ?>"
                    alt="<?= projectDetailsEscape($project['title']) ?>"
                >
            <?php else: ?>
                <div class="image-placeholder">
                    Project image is not available
                </div>
            <?php endif; ?>

            <div class="details-content">

                <span class="project-category">
                    <?= projectDetailsEscape($category) ?>
                </span>

                <h1>
                    <?= projectDetailsEscape($project['title']) ?>
                </h1>

                <div class="description"><?=
                    nl2br(projectDetailsEscape($project['description']))
                ?></div>

                <?php if (!empty($technologies)): ?>
                    <h2 class="subheading">Technologies Used</h2>

                    <div class="technology-list">
                        <?php foreach ($technologies as $technology): ?>
                            <span class="technology-tag">
                                <?= projectDetailsEscape($technology) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php
                $createdAt = $project['created_at'] ?? '';
                $timestamp = is_string($createdAt)
                    ? strtotime($createdAt)
                    : false;
                ?>

                <?php if ($timestamp !== false): ?>
                    <p class="project-date">
                        Added on <?= date('d M Y', $timestamp) ?>
                    </p>
                <?php endif; ?>

                
<div class="d-flex flex-wrap align-items-center gap-2">

    <a href="projects.php" class="primary-link">
        Explore More Projects
    </a>

    <?php if ($validGithubUrl): ?>
        <a
            href="<?= projectDetailsEscape($githubUrl) ?>"
            class="github-link"
            target="_blank"
            rel="noopener noreferrer"
            aria-label="View <?= projectDetailsEscape($project['title']) ?> source code on GitHub"
        >
            <span aria-hidden="true">&#128187;</span>
            View on GitHub
            <span aria-hidden="true">&#8599;</span>
        </a>
    <?php endif; ?>

</div>


            </div>
        </article>

    <?php else: ?>

        <section class="not-found">
            <h1>Project Not Found</h1>
            <p class="description">
                This project may have been removed or the link may be incorrect.
            </p>
            <a href="projects.php" class="primary-link">
                Browse All Projects
            </a>
        </section>

    <?php endif; ?>

    <footer>
        &copy; <?= date('Y') ?> Mujeeb Ahmed Badar. All rights reserved.
    </footer>

</main>

</body>
</html>
