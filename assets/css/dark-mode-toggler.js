document.addEventListener('DOMContentLoaded', function () {
    // 1. Create and inject the slider component
    const slider = document.createElement('div');
    slider.id = 'darkModeSlider';

    const handle = document.createElement('div');
    handle.id = 'darkModeHandle';
    handle.title = 'Tampilkan/Sembunyikan Pengaturan Mode';
    handle.innerHTML = '<i class="bi bi-palette-fill"></i>';

    const button = document.createElement('div');
    button.id = 'darkModeButton';
    button.title = 'Ganti Mode Terang/Gelap';
    const icon = document.createElement('i');
    icon.className = 'bi'; // Base class
    button.appendChild(icon);

    slider.appendChild(handle);
    slider.appendChild(button);
    document.body.appendChild(slider);

    const body = document.body;
    const themeKey = 'wla_theme';

    // 2. Function to update theme and icon
    function applyTheme(theme) {
      if (theme === 'dark') {
        body.classList.add('dark-mode');
        icon.classList.remove('bi-moon-stars-fill');
        icon.classList.add('bi-sun-fill');
        localStorage.setItem(themeKey, 'dark');
      } else {
        body.classList.remove('dark-mode');
        icon.classList.remove('bi-sun-fill');
        icon.classList.add('bi-moon-stars-fill');
        localStorage.setItem(themeKey, 'light');
      }
    }

    // 3. Check localStorage for saved theme on page load
    const savedTheme = localStorage.getItem(themeKey) || 'light';
    applyTheme(savedTheme);

    // 4. Add click event to the handle to toggle the slider
    handle.addEventListener('click', function (e) {
        e.stopPropagation(); // Prevent click from bubbling to document
        slider.classList.toggle('active');
    });

    // 5. Add click event to the button to toggle the theme
    button.addEventListener('click', function (e) {
        e.stopPropagation();
        const currentTheme = localStorage.getItem(themeKey) === 'dark' ? 'light' : 'dark';
        applyTheme(currentTheme);
    });

    // 6. Hide the slider when clicking anywhere else on the page
    document.addEventListener('click', function (e) {
        if (!slider.contains(e.target) && slider.classList.contains('active')) {
            slider.classList.remove('active');
        }
    });
});