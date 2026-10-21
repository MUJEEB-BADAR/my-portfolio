$(function () {
    // Smooth scrolling for same-page anchors.
    $('a[href*="#"]').on('click', function (event) {
        const url = new URL(this.href, window.location.href);

        if (
            url.pathname === window.location.pathname &&
            url.hash &&
            $(url.hash).length
        ) {
            event.preventDefault();

            $('html, body').animate({
                scrollTop: $(url.hash).offset().top - 78
            }, 450);
        }

        // Close the mobile navigation after choosing a link.
        if ($(this).hasClass('nav-link')) {
            const nav = document.getElementById('mainNav');

            if (nav && nav.classList.contains('show')
                && window.bootstrap) {
                bootstrap.Collapse.getOrCreateInstance(nav).hide();
            }
        }
    });

    // Simple rotating role text.
    const $typing = $('#typing-text');

    if ($typing.length) {
        const roles = [
            'Web Developer',
            'PHP Developer',
            'Frontend Developer'
        ];

        let index = 0;

        window.setInterval(function () {
            index = (index + 1) % roles.length;

            $typing.fadeOut(180, function () {
                $typing.text(roles[index]).fadeIn(180);
            });
        }, 2600);
    }

    // Project filtering.
    $('.filter-btn').on('click', function () {
        const filter = $(this).data('filter');

        $('.filter-btn')
            .removeClass('active')
            .attr('aria-pressed', 'false');

        $(this)
            .addClass('active')
            .attr('aria-pressed', 'true');

        $('.project-filter-item').each(function () {
            const category = $(this).data('category');
            const show = filter === 'all' || category === filter;

            if (show) {
                $(this).stop(true, true).fadeIn(200);
            } else {
                $(this).stop(true, true).fadeOut(150);
            }
        });
    });

    // Reveal animations for content entering the viewport.
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.12
        });

        document.querySelectorAll('.skill-card, .project-card, .glass-card')
            .forEach(function (element) {
                element.classList.add('reveal');
                observer.observe(element);
            });
    }
});