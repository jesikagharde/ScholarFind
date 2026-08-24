/**
 * ScholarFind - Main JavaScript
 * Handles Theme Customizer, Glowing Toast Notices, Live Filters & Bookmarks
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Theme & Color Customizer Initialization
    const savedTheme = localStorage.getItem('scholarfind_theme') || 'light';
    const savedColor = localStorage.getItem('scholarfind_color') || 'indigo';

    document.documentElement.setAttribute('data-theme', savedTheme);
    document.documentElement.setAttribute('data-color', savedColor);

    // Update active color dot
    document.querySelectorAll('.color-dot').forEach(dot => {
        if (dot.getAttribute('data-color') === savedColor) {
            dot.classList.add('active');
        }
        dot.addEventListener('click', () => {
            const color = dot.getAttribute('data-color');
            document.documentElement.setAttribute('data-color', color);
            localStorage.setItem('scholarfind_color', color);

            document.querySelectorAll('.color-dot').forEach(d => d.classList.remove('active'));
            dot.classList.add('active');
            showToast(`Theme color updated to ${color}!`, 'success');
        });
    });

    // Theme Toggle Button (☀️ / 🌙)
    const themeBtn = document.getElementById('theme-toggle-btn');
    if (themeBtn) {
        updateThemeIcon(themeBtn, savedTheme);
        themeBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const nextTheme = currentTheme === 'light' ? 'dark' : 'light';

            document.documentElement.setAttribute('data-theme', nextTheme);
            localStorage.setItem('scholarfind_theme', nextTheme);
            updateThemeIcon(themeBtn, nextTheme);
            showToast(`Switched to ${nextTheme} mode!`, 'info');
        });
    }

    function updateThemeIcon(btn, theme) {
        btn.innerText = theme === 'dark' ? '☀️' : '🌙';
        btn.title = theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode';
    }

    // 2. Auto-Dismiss Alert Messages
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.4s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 400);
        }, 5000);
    });

    // 3. Bookmark / Save Scholarship Handlers
    document.querySelectorAll('.btn-save-scholarship').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const scholarshipId = btn.getAttribute('data-id');
            
            try {
                const res = await fetch('api/toggle_save.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ scholarship_id: scholarshipId })
                });
                const data = await res.json();
                
                if (data.status === 'unauthorized') {
                    window.location.href = 'login.php';
                    return;
                }
                
                const counterBadge = document.getElementById('nav-saved-count');
                let count = counterBadge ? parseInt(counterBadge.innerText) || 0 : 0;

                if (data.status === 'saved') {
                    btn.classList.add('btn-primary');
                    btn.classList.remove('btn-outline');
                    btn.innerText = '★ Saved';
                    if (counterBadge) counterBadge.innerText = count + 1;
                    showToast('Scholarship saved to your bookmarks!', 'success');
                } else if (data.status === 'removed') {
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-outline');
                    btn.innerText = '☆ Save';
                    if (counterBadge && count > 0) counterBadge.innerText = Math.max(0, count - 1);
                    showToast('Scholarship removed from bookmarks.', 'info');
                }
            } catch (err) {
                console.error(err);
            }
        });
    });
});

// Glowing High-End Toast Notification Helper
function showToast(message, type = 'success') {
    // Remove existing toast
    const existing = document.querySelector('.scholarfind-toast');
    if (existing) existing.remove();

    const toast = document.createElement('div');
    toast.className = 'scholarfind-toast';
    
    const icon = type === 'success' ? '🎉' : (type === 'warning' ? '⚠️' : 'ℹ️');
    const bg = type === 'success' 
        ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)' 
        : (type === 'warning' ? 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)' : 'linear-gradient(135deg, #4f46e5 0%, #3730a3 100%)');

    toast.style.position = 'fixed';
    toast.style.bottom = '28px';
    toast.style.right = '28px';
    toast.style.zIndex = '999999';
    toast.style.background = bg;
    toast.style.color = '#ffffff';
    toast.style.padding = '14px 22px';
    toast.style.borderRadius = '9999px';
    toast.style.fontWeight = '700';
    toast.style.fontSize = '0.92rem';
    toast.style.boxShadow = '0 15px 35px -5px rgba(0, 0, 0, 0.35)';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '10px';
    toast.style.transform = 'translateY(50px)';
    toast.style.opacity = '0';
    toast.style.transition = 'all 0.35s cubic-bezier(0.4, 0, 0.2, 1)';
    toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;
    
    document.body.appendChild(toast);
    
    requestAnimationFrame(() => {
        toast.style.transform = 'translateY(0)';
        toast.style.opacity = '1';
    });
    
    setTimeout(() => {
        toast.style.transform = 'translateY(30px)';
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 350);
    }, 3500);
}
