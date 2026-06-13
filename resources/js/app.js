'use strict';

document.addEventListener('DOMContentLoaded', function () {
    var body = document.body;
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* -------------------------------------------------------
     * Mobile Navigation
     * ------------------------------------------------------- */
    var mobileNav = document.querySelector('[data-mobile-nav]');
    var mobileNavOverlay = document.querySelector('[data-mobile-nav-overlay]');
    var mobileNavToggles = Array.prototype.slice.call(document.querySelectorAll('[data-mobile-nav-toggle]'));
    var mobileNavCloseButtons = Array.prototype.slice.call(document.querySelectorAll('[data-mobile-nav-close], [data-mobile-nav-link], [data-mobile-nav-overlay]'));
    var lastFocusedElement = null;

    var setMobileNavState = function (open) {
        if (! mobileNav) {
            return;
        }

        mobileNav.classList.toggle('hidden', ! open);
        mobileNav.classList.toggle('translate-x-full', ! open);
        mobileNav.setAttribute('aria-hidden', open ? 'false' : 'true');

        if (mobileNavOverlay) {
            mobileNavOverlay.classList.toggle('hidden', ! open);
            mobileNavOverlay.classList.toggle('pointer-events-none', ! open);
            mobileNavOverlay.classList.toggle('opacity-0', ! open);
            mobileNavOverlay.tabIndex = open ? 0 : -1;
        }

        mobileNavToggles.forEach(function (toggle) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        body.classList.toggle('overflow-hidden', open);

        if (open) {
            lastFocusedElement = document.activeElement;
            window.setTimeout(function () {
                mobileNav.focus();
            }, reduceMotion ? 0 : 20);
            return;
        }

        if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus();
        }
    };

    mobileNavToggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            setMobileNavState(true);
        });
    });

    mobileNavCloseButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            setMobileNavState(false);
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && mobileNav && mobileNav.getAttribute('aria-hidden') === 'false') {
            setMobileNavState(false);
        }
    });

    /* -------------------------------------------------------
     * Flash Messages
     * ------------------------------------------------------- */
    document.querySelectorAll('[data-flash-close]').forEach(function (button) {
        button.addEventListener('click', function () {
            var flash = button.closest('[data-flash]');
            if (! flash) {
                return;
            }

            flash.style.transition = 'opacity 180ms ease';
            flash.style.opacity = '0';

            window.setTimeout(function () {
                flash.remove();
            }, 180);
        });
    });

    document.querySelectorAll('[data-flash][data-flash-autohide="true"]').forEach(function (flash) {
        window.setTimeout(function () {
            if (! flash.isConnected) {
                return;
            }

            flash.style.transition = reduceMotion ? 'none' : 'opacity 180ms ease';
            flash.style.opacity = '0';

            window.setTimeout(function () {
                if (flash.isConnected) {
                    flash.remove();
                }
            }, reduceMotion ? 0 : 180);
        }, 4500);
    });

    /* -------------------------------------------------------
     * Header Scroll – transparent → solid
     * ------------------------------------------------------- */
    var header = document.querySelector('[data-header]');
    if (header) {
        var headerMode = header.getAttribute('data-header-mode') || (header.classList.contains('header-transparent') ? 'home' : 'inner');

        if (headerMode === 'home') {
            var scrollThreshold = 60;
            var lastHomeScrollY = window.scrollY;

            var updateHomeHeaderState = function () {
                var currentScrollY = window.scrollY;
                var isScrollingUp = currentScrollY < lastHomeScrollY;

                if (currentScrollY > scrollThreshold || (isScrollingUp && currentScrollY > 8)) {
                    header.classList.add('header-scrolled');
                } else {
                    header.classList.remove('header-scrolled');
                }

                lastHomeScrollY = currentScrollY;
            };

            updateHomeHeaderState();
            window.addEventListener('scroll', updateHomeHeaderState, { passive: true });
        } else {
            var innerThreshold = 24;
            var lastScrollY = window.scrollY;

            var updateInnerHeaderState = function () {
                var currentScrollY = window.scrollY;
                var isScrollingUp = currentScrollY < lastScrollY;

                if (currentScrollY > innerThreshold && isScrollingUp) {
                    header.classList.add('header-scrolled');
                } else {
                    header.classList.remove('header-scrolled');
                }

                lastScrollY = currentScrollY;
            };

            updateInnerHeaderState();
            window.addEventListener('scroll', updateInnerHeaderState, { passive: true });
        }
    }

    /* -------------------------------------------------------
     * Scroll Reveal – IntersectionObserver
     * ------------------------------------------------------- */
    if (!reduceMotion && 'IntersectionObserver' in window) {
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('.reveal, .reveal-scale').forEach(function (el) {
            revealObserver.observe(el);
        });
    } else {
        // Reduced motion or no observer – show everything immediately
        document.querySelectorAll('.reveal, .reveal-scale').forEach(function (el) {
            el.classList.add('revealed');
        });
    }

    /* -------------------------------------------------------
     * Hero Slider
     * ------------------------------------------------------- */
    var slider = document.querySelector('.hero-slider');
    if (slider) {
        var slides = Array.prototype.slice.call(slider.querySelectorAll('.hero-slide'));
        var dots = Array.prototype.slice.call(slider.querySelectorAll('.hero-slider-dot'));
        var prevBtn = slider.querySelector('[data-slider-prev]');
        var nextBtn = slider.querySelector('[data-slider-next]');
        var progressBar = slider.querySelector('.hero-slider-progress');
        var counterCurrent = slider.querySelector('.slide-counter-current');

        var slideCount = slides.length;
        var currentIndex = 0;
        var autoplay = slider.getAttribute('data-autoplay') !== 'false';
        var autoplayDelay = parseInt(slider.getAttribute('data-autoplay-delay'), 10) || 6000;
        var isTransitioning = false;
        var autoplayTimer = null;
        var progressTimer = null;
        var isPaused = false;

        var goToSlide = function (index) {
            if (isTransitioning || index === currentIndex || slideCount <= 1) {
                return;
            }
            isTransitioning = true;

            slides[currentIndex].classList.remove('active');
            if (dots[currentIndex]) {
                dots[currentIndex].classList.remove('active');
                dots[currentIndex].setAttribute('aria-selected', 'false');
            }

            currentIndex = ((index % slideCount) + slideCount) % slideCount;

            slides[currentIndex].classList.add('active');
            if (dots[currentIndex]) {
                dots[currentIndex].classList.add('active');
                dots[currentIndex].setAttribute('aria-selected', 'true');
            }

            if (counterCurrent) {
                counterCurrent.textContent = String(currentIndex + 1).padStart(2, '0');
            }

            // Allow transitions to settle
            window.setTimeout(function () {
                isTransitioning = false;
            }, 1100);

            resetAutoplay();
        };

        var nextSlide = function () {
            goToSlide(currentIndex + 1);
        };

        var prevSlide = function () {
            goToSlide(currentIndex - 1);
        };

        /* Progress bar animation */
        var startProgress = function () {
            if (!progressBar || !autoplay || reduceMotion) return;
            progressBar.style.transition = 'none';
            progressBar.style.width = '0%';

            // Force reflow
            void progressBar.offsetWidth;

            progressBar.style.transition = 'width ' + autoplayDelay + 'ms linear';
            progressBar.style.width = '100%';
        };

        /* Autoplay control */
        var resetAutoplay = function () {
            clearTimeout(autoplayTimer);
            clearTimeout(progressTimer);

            if (!autoplay || isPaused || slideCount <= 1) return;

            startProgress();
            autoplayTimer = window.setTimeout(nextSlide, autoplayDelay);
        };

        /* Dot navigation */
        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () {
                goToSlide(i);
            });
        });

        /* Arrow navigation */
        if (prevBtn) {
            prevBtn.addEventListener('click', prevSlide);
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', nextSlide);
        }

        /* Keyboard navigation */
        slider.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') {
                prevSlide();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
            }
        });

        /* Pause on hover */
        slider.addEventListener('mouseenter', function () {
            isPaused = true;
            clearTimeout(autoplayTimer);
            if (progressBar) {
                progressBar.style.transitionPlayState = 'paused';
            }
        });

        slider.addEventListener('mouseleave', function () {
            isPaused = false;
            resetAutoplay();
            if (progressBar) {
                progressBar.style.transitionPlayState = 'running';
            }
        });

        /* Touch swipe support */
        var touchStartX = 0;
        var touchEndX = 0;

        slider.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        slider.addEventListener('touchend', function (e) {
            touchEndX = e.changedTouches[0].screenX;
            var diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    nextSlide();
                } else {
                    prevSlide();
                }
            }
        }, { passive: true });

        // Initialize first slide and start autoplay
        if (slides[0]) {
            slides[0].classList.add('active');
        }
        if (dots[0]) {
            dots[0].classList.add('active');
            dots[0].setAttribute('aria-selected', 'true');
        }
        resetAutoplay();
    }

    /* -------------------------------------------------------
     * Scroll Indicator (hero)
     * ------------------------------------------------------- */
    var scrollIndicator = document.querySelector('.hero-slider + section, .hero-slider ~ section');
    var heroSection = document.querySelector('.hero-slider');
    if (heroSection) {
        var scrollTarget = heroSection.nextElementSibling;
        var indicator = heroSection.querySelector('[data-scroll-indicator]');
        if (indicator) {
            indicator.style.cursor = 'pointer';
            indicator.addEventListener('click', function () {
                if (scrollTarget) {
                    scrollTarget.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth' });
                }
            });
        }
    }
});
