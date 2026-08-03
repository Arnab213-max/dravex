document.addEventListener('DOMContentLoaded', function() {
    // Theme Selector - Desktop
    var themeSelect = document.getElementById('themeSelect');
    if (themeSelect) {
        var savedTheme = localStorage.getItem('selectedTheme') || 'default';
        themeSelect.value = savedTheme;
        document.documentElement.setAttribute('data-theme', savedTheme);

        themeSelect.addEventListener('change', function() {
            var theme = this.value;
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('selectedTheme', theme);
            document.cookie = "theme=" + theme + "; path=/; max-age=31536000";
            var mobileSelect = document.getElementById('themeSelectMobile');
            if (mobileSelect) {
                mobileSelect.value = theme;
            }
        });
    }

    // Theme Selector - Mobile
    var themeSelectMobile = document.getElementById('themeSelectMobile');
    if (themeSelectMobile) {
        var savedThemeMobile = localStorage.getItem('selectedTheme') || 'default';
        themeSelectMobile.value = savedThemeMobile;

        themeSelectMobile.addEventListener('change', function() {
            var theme = this.value;
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('selectedTheme', theme);
            document.cookie = "theme=" + theme + "; path=/; max-age=31536000";
            var desktopSelect = document.getElementById('themeSelect');
            if (desktopSelect) {
                desktopSelect.value = theme;
            }
        });
    }

    // Particles
    var particles = document.getElementById('particles');
    if (particles) {
        for (var i = 0; i < 25; i++) {
            var p = document.createElement('div');
            p.className = 'particle';
            var s = Math.random() * 4 + 2;
            p.style.width = s + 'px';
            p.style.height = s + 'px';
            p.style.left = Math.random() * 100 + '%';
            p.style.animationDuration = Math.random() * 20 + 10 + 's';
            p.style.animationDelay = Math.random() * 10 + 's';
            p.style.background = Math.random() > 0.5 ? '#d4af37' : '#8b5cf6';
            particles.appendChild(p);
        }
    }

    // Mobile Menu
    var toggle = document.getElementById('menuToggle');
    var menu = document.getElementById('mobileMenu');
    if (toggle && menu) {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('active');
            menu.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (menu.classList.contains('active') && !menu.contains(e.target) && !toggle.contains(e.target)) {
                menu.classList.remove('active');
                toggle.classList.remove('active');
            }
        });

        var menuLinks = menu.querySelectorAll('a');
        menuLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                menu.classList.remove('active');
                toggle.classList.remove('active');
            });
        });
    }

    // Typing Effect
    var words = ['Premium', 'Bold', 'Streetwear', 'Luxury', 'Urban', 'Style', 'DRAVEX'];
    var el = document.getElementById('typingText');
    if (el) {
        var wordIndex = 0, charIndex = 0, isDeleting = false;
        function typeEffect() {
            var currentWord = words[wordIndex];
            if (isDeleting) {
                charIndex--;
                el.textContent = currentWord.substring(0, charIndex);
            } else {
                charIndex++;
                el.textContent = currentWord.substring(0, charIndex);
            }
            if (!isDeleting && charIndex === currentWord.length) {
                setTimeout(function() { isDeleting = true; }, 2000);
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                wordIndex = (wordIndex + 1) % words.length;
            }
            setTimeout(typeEffect, isDeleting ? 100 : 150);
        }
        typeEffect();
    }

    // Stats Counter
    var statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(function(el) {
        var target = parseInt(el.getAttribute('data-count'));
        if (isNaN(target)) target = 0;
        var current = 0;
        var increment = Math.ceil(target / 50);
        var interval = setInterval(function() {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(interval);
            }
            el.textContent = current;
        }, 40);
    });

    // Toast
    window.showToast = function(msg, type) {
        if (!type) type = 'success';
        var container = document.getElementById('toastContainer');
        if (!container) return;
        var toast = document.createElement('div');
        toast.className = 'toast toast-' + type;
        toast.innerHTML = '<span>' + msg + '</span><button class="toast-close">&times;</button>';
        container.appendChild(toast);
        setTimeout(function() {
            toast.classList.add('fade-out');
            setTimeout(function() { if (toast.parentNode) toast.remove(); }, 300);
        }, 3000);
        toast.querySelector('.toast-close').onclick = function() {
            toast.classList.add('fade-out');
            setTimeout(function() { if (toast.parentNode) toast.remove(); }, 300);
        };
    };

    // Password Toggle
    window.togglePassword = function(fieldId) {
        var password = document.getElementById(fieldId);
        var icon = document.getElementById(fieldId + 'Icon');
        if (!password) return;
        if (password.type === 'password') {
            password.type = 'text';
            if (icon) icon.className = 'fas fa-eye-slash';
        } else {
            password.type = 'password';
            if (icon) icon.className = 'fas fa-eye';
        }
    };
});