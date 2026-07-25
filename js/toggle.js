

document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('themeToggleCheckbox');
    const ball = document.getElementById('toggleBall');
    const html = document.documentElement;

    if (!checkbox || !ball) return;

    // Function to set theme
    function setTheme(theme) {
        html.setAttribute('data-theme', theme);
        
        // Save to localStorage
        localStorage.setItem('theme', theme);
        
        // Save to cookie for PHP
        document.cookie = "theme=" + theme + "; path=/; max-age=31536000";
        
        // Update toggle state
        if (theme === 'light') {
            checkbox.checked = true;
            ball.classList.remove('dark');
        } else {
            checkbox.checked = false;
            ball.classList.add('dark');
        }
    }

    // Load saved theme
    const savedTheme = localStorage.getItem('theme') || 'dark';
    setTheme(savedTheme);

    // Toggle on checkbox change
    checkbox.addEventListener('change', function() {
        const theme = this.checked ? 'light' : 'dark';
        setTheme(theme);
    });

    // Click on label toggles too
    document.getElementById('themeToggleLabel').addEventListener('click', function(e) {
        // Don't trigger if clicking directly on the checkbox (it handles itself)
        if (e.target.tagName !== 'INPUT') {
            checkbox.checked = !checkbox.checked;
            checkbox.dispatchEvent(new Event('change'));
        }
    });

    // Keyboard support
    checkbox.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            this.checked = !this.checked;
            this.dispatchEvent(new Event('change'));
        }
    });
});