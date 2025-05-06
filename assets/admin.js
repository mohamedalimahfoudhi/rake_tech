import './bootstrap.js';
/*
 * Admin dashboard JavaScript file
 */
import './styles/admin-auth.css';

console.log('Admin dashboard assets loaded successfully');

// Handle any admin-specific JavaScript functionality
document.addEventListener('DOMContentLoaded', () => {
    // Mobile sidebar toggle functionality
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    }
}); 