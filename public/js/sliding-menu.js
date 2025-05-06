document.addEventListener('DOMContentLoaded', function() {
    // Create mobile menu toggle button
    const mobileToggle = document.createElement('button');
    mobileToggle.classList.add('mobile-menu-toggle');
    mobileToggle.innerHTML = '<span class="material-symbols-outlined">menu</span>';
    document.body.appendChild(mobileToggle);
    
    const menu = document.querySelector('.menu');
    
    // Toggle menu on mobile
    mobileToggle.addEventListener('click', function() {
        menu.classList.toggle('menu-active');
        this.classList.toggle('active');
    });
    
    // Close menu when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const isMobile = window.innerWidth <= 767.98;
        if (isMobile && menu.classList.contains('menu-active') && 
            !menu.contains(event.target) && 
            !mobileToggle.contains(event.target)) {
            menu.classList.remove('menu-active');
            mobileToggle.classList.remove('active');
        }
    });
    
    // Adjust on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 767.98) {
            menu.classList.remove('menu-active');
            mobileToggle.classList.remove('active');
        }
    });
}); 