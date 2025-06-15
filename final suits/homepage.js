function initInfiniteScroll() {
    const scrollContent = document.getElementById('scroll');
    const originalText = scrollContent.querySelector('.text');
    
    // Clone the text element to create a seamless loop
    const clonedText = originalText.cloneNode(true);
    scrollContent.appendChild(clonedText);

    // Get the width of the text content
    let textWidth = originalText.offsetWidth;
    
    // Set initial position to start from the edge of the viewport
    let position = scrollContent.parentElement.offsetWidth;
    
    // Configurable settings
    const settings = {
        speed: 1,        // Base speed (pixels per frame)
        speedMobile: 0.7, // Slower speed for mobile devices
        acceleration: 1.5  // Speed multiplier when user interacts
    };
    
    // Current animation state
    let state = {
        speed: window.innerWidth <= 768 ? settings.speedMobile : settings.speed,
        rafId: null
    };
    
    // Handle window resize to adjust dimensions and speed
    function handleResize() {
        textWidth = originalText.offsetWidth;
        // Adjust speed based on screen size
        state.speed = window.innerWidth <= 768 ? settings.speedMobile : settings.speed;
    }
    
    // Animation function
    function animate() {
        position -= state.speed;
        
        // Reset position when text has scrolled completely
        if (position <= -textWidth) {
            position = 0;
        }
        
        scrollContent.style.transform = `translateX(${position}px)`;
        
        state.rafId = requestAnimationFrame(animate);
    }
    
    // Start the animation
    animate();
    
    // Handle window resize
    window.addEventListener('resize', handleResize);
    
    // Clean up function to remove event listeners and cancel animation
    function cleanup() {
        window.removeEventListener('resize', handleResize);
        if (state.rafId) {
            cancelAnimationFrame(state.rafId);
        }
    }
    
    // Return cleanup function for potential use
    return cleanup;
}

// Initialize the infinite scroll when the page loads
window.addEventListener('load', initInfiniteScroll);

// Keep the rest of the existing code
const menuToggle = document.getElementById('menuToggle');
const nav = document.getElementById('nav');
const user = document.getElementById('user');

menuToggle.addEventListener('click', () => {
    nav.classList.toggle('active');
    user.classList.toggle('active');
    
    const icon = menuToggle.querySelector('i');
    icon.classList.toggle('fa-bars');
    icon.classList.toggle('fa-times');
});

// Smooth scrolling
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });

            nav.classList.remove('active');
            user.classList.remove('active');
            const icon = menuToggle.querySelector('i');
            icon.classList.add('fa-bars');
            icon.classList.remove('fa-times');
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const filterBtn = document.getElementById("filterBtn");
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("overlay");
    const accordionHeaders = document.querySelectorAll(".accordion-header");

    // Open sidebar
    filterBtn.addEventListener("click", function () {
        sidebar.classList.add("show");
        overlay.style.display = "block";
    });

    // Close sidebar when clicking outside
    overlay.addEventListener("click", function () {
        sidebar.classList.remove("show");
        overlay.style.display = "none";
    });

    // Accordion toggle
    accordionHeaders.forEach(header => {
        header.addEventListener("click", function() {
            const content = this.nextElementSibling;
            
            // Toggle active class for rotation animation
            this.classList.toggle("active");
            
            if (content.style.display === "block") {
                content.style.display = "none";
            } else {
                content.style.display = "block";
            }
        });
    });

    // Optional: Apply filters button
    const applyBtn = document.getElementById("applyBtn");
    applyBtn.addEventListener("click", function () {
        alert("Filters applied! (You can add logic here)");
        sidebar.classList.remove("show");
        overlay.style.display = "none";
    });
});
