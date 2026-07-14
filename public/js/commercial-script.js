/* ==========================================================================
   Braga8 Commercial Center — script.js
   Vanilla JS only. No frameworks, no jQuery.
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    initLoadingScreen();
    initScrollProgress();
    initStickyNavbar();
    initMobileMenu();
    initSmoothScroll();
    initScrollReveal();
    initCounters();
    initTenantDirectory();
    initGalleryFilterAndLightbox();
    initFaqAccordion();
    initBackToTop();
    initParallaxHero();
    initActiveNavLink();
});

function initLoadingScreen() {
    const screen = document.getElementById('loading-screen');
    const bar = document.getElementById('loading-bar');
    if (!screen) return;

    requestAnimationFrame(() => {
        if (bar) bar.style.width = '100%';
    });

    window.addEventListener('load', () => {
        setTimeout(() => {
            screen.classList.add('fade-out');
            setTimeout(() => screen.remove(), 700);
        }, 400);
    });

    setTimeout(() => {
        if (document.body.contains(screen) && !screen.classList.contains('fade-out')) {
            screen.classList.add('fade-out');
            setTimeout(() => screen.remove(), 700);
        }
    }, 2500);
}

function initScrollProgress() {
    const bar = document.getElementById('scroll-progress');
    if (!bar) return;

    const update = () => {
        const scrollTop = window.scrollY;
        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
        const progress = docHeight > 0 ? (scrollTop / docHeight) * 100 : 0;
        bar.style.width = progress + '%';
    };

    window.addEventListener('scroll', update, { passive: true });
    update();
}

function initStickyNavbar() {
    const inner = document.getElementById('navbar-inner');
    if (!inner) return;

    const update = () => {
        if (window.scrollY > 24) {
            inner.classList.add('scrolled');
        } else {
            inner.classList.remove('scrolled');
        }
    };

    window.addEventListener('scroll', update, { passive: true });
    update();
}

function initMobileMenu() {
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    if (!btn || !menu) return;

    btn.addEventListener('click', () => {
        menu.classList.toggle('open');
    });

    menu.querySelectorAll('.mobile-nav-link').forEach(link => {
        link.addEventListener('click', () => menu.classList.remove('open'));
    });
}

function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', (e) => {
            const targetId = link.getAttribute('href');
            if (targetId.length <= 1) return;
            const target = document.querySelector(targetId);
            if (!target) return;
            e.preventDefault();
            const offset = 100;
            const top = target.getBoundingClientRect().top + window.scrollY - offset;
            window.scrollTo({ top, behavior: 'smooth' });
        });
    });
}

function initScrollReveal() {
    const items = document.querySelectorAll('.reveal-up');
    if (!items.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

    items.forEach(item => observer.observe(item));
}

function initCounters() {
    const counters = document.querySelectorAll('.counter');
    if (!counters.length) return;

    const animate = (el) => {
        const target = parseInt(el.getAttribute('data-target'), 10) || 0;
        const duration = 1600;
        const start = performance.now();

        const step = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const value = Math.floor(eased * target);
            el.textContent = value.toLocaleString('en-US');
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = target.toLocaleString('en-US');
            }
        };
        requestAnimationFrame(step);
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animate(entry.target);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counters.forEach(counter => observer.observe(counter));
}

function initTenantDirectory() {
    const searchInput = document.getElementById('tenant-search');
    const categoryFilter = document.getElementById('category-filter');
    const floorFilter = document.getElementById('floor-filter');
    const cards = document.querySelectorAll('.tenant-card');
    const noResults = document.getElementById('no-results');

    if (!searchInput || !cards.length) return;

    const applyFilters = () => {
        const query = searchInput.value.trim().toLowerCase();
        const category = categoryFilter.value;
        const floor = floorFilter.value;
        let visibleCount = 0;

        cards.forEach(card => {
            const matchesQuery = card.dataset.name.includes(query);
            const matchesCategory = category === 'All' || card.dataset.category === category;
            const matchesFloor = floor === 'All Floors' || card.dataset.floor === floor;
            const isVisible = matchesQuery && matchesCategory && matchesFloor;

            card.classList.toggle('hidden-by-filter', !isVisible);
            if (isVisible) visibleCount++;
        });

        if (noResults) {
            noResults.classList.toggle('hidden', visibleCount !== 0);
        }
    };

    searchInput.addEventListener('input', applyFilters);
    categoryFilter.addEventListener('change', applyFilters);
    floorFilter.addEventListener('change', applyFilters);
}

function initGalleryFilterAndLightbox() {
    const filterBtns = document.querySelectorAll('.gallery-filter-btn');
    const items = document.querySelectorAll('.gallery-item');
    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const lightboxClose = document.getElementById('lightbox-close');

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const cat = btn.dataset.cat;

            filterBtns.forEach(b => {
                b.classList.remove('is-active', 'bg-accent', 'text-white', 'border-accent');
                b.classList.add('text-ink-soft');
            });
            btn.classList.add('is-active', 'bg-accent', 'text-white', 'border-accent');
            btn.classList.remove('text-ink-soft');

            items.forEach(item => {
                const match = cat === 'All' || item.dataset.cat === cat;
                item.classList.toggle('hidden-by-filter', !match);
            });
        });
    });

    if (!lightbox || !lightboxImg) return;

    items.forEach(item => {
        item.addEventListener('click', () => {
            lightboxImg.src = item.dataset.src;
            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');
            requestAnimationFrame(() => lightbox.classList.add('open'));
            document.body.style.overflow = 'hidden';
        });
    });

    const closeLightbox = () => {
        lightbox.classList.remove('open');
        document.body.style.overflow = '';
        setTimeout(() => {
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex');
        }, 300);
    };

    lightboxClose?.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
    });
}

function initFaqAccordion() {
    const items = document.querySelectorAll('.faq-item');
    if (!items.length) return;

    items.forEach(item => {
        const toggle = item.querySelector('.faq-toggle');
        const content = item.querySelector('.faq-content');

        toggle.addEventListener('click', () => {
            const isOpen = item.classList.contains('open');

            items.forEach(other => {
                other.classList.remove('open');
                other.querySelector('.faq-content').style.maxHeight = null;
            });

            if (!isOpen) {
                item.classList.add('open');
                content.style.maxHeight = content.scrollHeight + 'px';
            }
        });
    });
}

function initBackToTop() {
    const btn = document.getElementById('back-to-top');
    if (!btn) return;

    const update = () => {
        if (window.scrollY > 480) {
            btn.classList.remove('opacity-0', 'pointer-events-none', 'translate-y-4');
        } else {
            btn.classList.add('opacity-0', 'pointer-events-none', 'translate-y-4');
        }
    };

    window.addEventListener('scroll', update, { passive: true });
    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
    update();
}

function initParallaxHero() {
    const img = document.querySelector('.parallax-img img');
    if (!img) return;

    const update = () => {
        const scrollY = window.scrollY;
        if (scrollY < window.innerHeight) {
            const translate = scrollY * 0.08;
            img.style.transform = `translateY(${translate}px) scale(1.1)`;
        }
    };

    window.addEventListener('scroll', update, { passive: true });
}

function initActiveNavLink() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-link');
    if (!sections.length || !navLinks.length) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id = entry.target.getAttribute('id');
                navLinks.forEach(link => {
                    link.classList.toggle('is-active', link.getAttribute('href') === `#${id}`);
                });
            }
        });
    }, { threshold: 0.4, rootMargin: '-100px 0px -60% 0px' });

    sections.forEach(section => observer.observe(section));
}
