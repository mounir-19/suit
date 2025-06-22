// Sample articles data for offer selection
const articlesData = [
    {
        id: 1,
        name: "Costume Mariage Classic",
        price: 25000,
        image: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Crect width='40' height='40' fill='%23D4AE6A'/%3E%3Ctext x='20' y='25' text-anchor='middle' fill='%23fff' font-size='10'%3EPhoto%3C/text%3E%3C/svg%3E"
    },
    {
        id: 2,
        name: "Costume Bureau Élégant",
        price: 18500,
        image: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Crect width='40' height='40' fill='%23D4AE6A'/%3E%3Ctext x='20' y='25' text-anchor='middle' fill='%23fff' font-size='10'%3EPhoto%3C/text%3E%3C/svg%3E"
    },
    {
        id: 3,
        name: "Smoking Noir Premium",
        price: 35000,
        image: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Crect width='40' height='40' fill='%23D4AE6A'/%3E%3Ctext x='20' y='25' text-anchor='middle' fill='%23fff' font-size='10'%3EPhoto%3C/text%3E%3C/svg%3E"
    },
    {
        id: 4,
        name: "Veste Casual Moderne",
        price: 12500,
        image: "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Crect width='40' height='40' fill='%23D4AE6A'/%3E%3Ctext x='20' y='25' text-anchor='middle' fill='%23fff' font-size='10'%3EPhoto%3C/text%3E%3C/svg%3E"
    }
];

// Wait for DOM to load before executing JavaScript
// Enhanced admin panel functionality
// Utility functions
const showNotification = (message, type = 'info') => {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    // Trigger animation
    setTimeout(() => notification.classList.add('show'), 10);

    // Auto-hide after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
};

const setLoading = (element, isLoading) => {
    if (isLoading) {
        element.classList.add('loading');
        element.disabled = true;
    } else {
        element.classList.remove('loading');
        element.disabled = false;
    }
};

const handleError = (error) => {
    console.error('Error:', error);
    showNotification(error.message || 'An error occurred', 'error');
};

// Mobile menu functionality
const initMobileMenu = () => {
    const menuToggle = document.querySelector('.menu-toggle');
    const sidebar = document.querySelector('.sidebar');

    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            menuToggle.classList.toggle('active');
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && !menuToggle.contains(e.target) && sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
                menuToggle.classList.remove('active');
            }
        });
    }
};

// Navigation functionality
const initNavigation = () => {
    const navLinks = document.querySelectorAll('.nav a');
    const adminSections = document.querySelectorAll('.admin-section');

    const showSection = (sectionId) => {
        adminSections.forEach(section => {
            section.style.display = section.id === sectionId ? 'block' : 'none';
        });

        // Update URL without page reload
        const newUrl = `${window.location.pathname}?section=${sectionId}`;
        window.history.pushState({ section: sectionId }, '', newUrl);
    };

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const sectionId = link.getAttribute('href').substring(1);
            
            // Update active state
            navLinks.forEach(l => l.classList.remove('active'));
            link.classList.add('active');

            showSection(sectionId);
        });
    });

    // Handle browser back/forward
    window.addEventListener('popstate', (e) => {
        if (e.state && e.state.section) {
            showSection(e.state.section);
        }
    });
};

// Form handling
const initForms = () => {
    const forms = document.querySelectorAll('form');

    forms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector('[type="submit"]');
            setLoading(submitBtn, true);

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                showNotification(data.message || 'Operation successful', 'success');
                form.reset();

                // Refresh data if needed
                if (typeof loadData === 'function') {
                    loadData();
                }
            } catch (error) {
                handleError(error);
            } finally {
                setLoading(submitBtn, false);
            }
        });
    });
};

// Modal functionality
const initModals = () => {
    const modals = document.querySelectorAll('.modal');
    const modalTriggers = document.querySelectorAll('[data-modal]');

    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', () => {
            const modalId = trigger.getAttribute('data-modal');
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('show'), 10);
            }
        });
    });

    modals.forEach(modal => {
        const closeBtn = modal.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                modal.classList.remove('show');
                setTimeout(() => modal.style.display = 'none', 300);
            });
        }

        // Close modal when clicking outside
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('show');
                setTimeout(() => modal.style.display = 'none', 300);
            }
        });
    });
};

// Article management
const initArticleManagement = () => {
    const deleteButtons = document.querySelectorAll('.delete-article');
    const editButtons = document.querySelectorAll('.edit-article');

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            if (!confirm('Are you sure you want to delete this article?')) return;

            setLoading(btn, true);
            const articleId = btn.getAttribute('data-id');

            try {
                const response = await fetch(`admin_api.php?action=delete_article&id=${articleId}`, {
                    method: 'DELETE'
                });

                if (!response.ok) throw new Error('Failed to delete article');
                
                const data = await response.json();
                showNotification(data.message || 'Article deleted successfully', 'success');
                btn.closest('tr').remove();
            } catch (error) {
                handleError(error);
            } finally {
                setLoading(btn, false);
            }
        });
    });

    editButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const articleId = btn.getAttribute('data-id');
            setLoading(btn, true);

            try {
                const response = await fetch(`admin_api.php?action=get_article&id=${articleId}`);
                if (!response.ok) throw new Error('Failed to fetch article data');

                const data = await response.json();
                // Populate edit form
                const form = document.getElementById('edit-article-form');
                Object.keys(data).forEach(key => {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) input.value = data[key];
                });

                // Show edit modal
                const modal = document.getElementById('edit-modal');
                modal.style.display = 'block';
                setTimeout(() => modal.classList.add('show'), 10);
            } catch (error) {
                handleError(error);
            } finally {
                setLoading(btn, false);
            }
        });
    });
};

// Order management
const initOrderManagement = () => {
    const statusSelects = document.querySelectorAll('.order-status-select');

    statusSelects.forEach(select => {
        select.addEventListener('change', async () => {
            const orderId = select.getAttribute('data-order-id');
            const newStatus = select.value;
            setLoading(select, true);

            try {
                const response = await fetch('admin_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update_order_status&order_id=${orderId}&status=${newStatus}`
                });

                if (!response.ok) throw new Error('Failed to update order status');

                const data = await response.json();
                showNotification(data.message || 'Order status updated successfully', 'success');
            } catch (error) {
                handleError(error);
                // Revert select to previous value
                select.value = select.getAttribute('data-previous-value');
            } finally {
                setLoading(select, false);
            }
        });

        // Store initial value
        select.setAttribute('data-previous-value', select.value);
    });
};

// Initialize all functionality
document.addEventListener('DOMContentLoaded', () => {
    initMobileMenu();
    initNavigation();
    initForms();
    initModals();
    initArticleManagement();
    initOrderManagement();
});

// Handle scrollable main content layout
function setScrollableLayout() {
const header = document.querySelector('.header');
const bar = document.querySelector('.bar');
const adminContainer = document.querySelector('.admin-container');

if (header && bar && adminContainer) {
const totalOffset = header.offsetHeight + bar.offsetHeight;
const availableHeight = window.innerHeight - totalOffset;
adminContainer.style.height = availableHeight + 'px';
adminContainer.style.overflowY = 'auto';
}
}

window.addEventListener('load', setScrollableLayout);
window.addEventListener('resize', setScrollableLayout);
