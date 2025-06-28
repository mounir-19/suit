// Wait for page to load
document.addEventListener('DOMContentLoaded', function() {
    
    // Mobile Menu Toggle - Fixed Implementation
    const menuToggle = document.getElementById('menuToggle');
    const nav = document.getElementById('nav');
    
    if (menuToggle && nav) {
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle the active class
            nav.classList.toggle('active');
            menuToggle.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!menuToggle.contains(e.target) && !nav.contains(e.target)) {
                nav.classList.remove('active');
                menuToggle.classList.remove('active');
            }
        });
        
        // Close menu when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                nav.classList.remove('active');
                menuToggle.classList.remove('active');
            }
        });
    }
    
    // Language Switcher
    const langButtons = document.querySelectorAll('.lang-btn');
    langButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            langButtons.forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            // Here you would implement the actual language switching logic
            const selectedLang = this.getAttribute('data-lang');
            console.log('Selected language:', selectedLang);
        });
    });
    
    // Smooth scrolling for anchor links
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const target = document.querySelector(targetId);
            
            if (target) {
                // Calculate offset for fixed header
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile menu after clicking
                if (nav && nav.classList.contains('active')) {
                    nav.classList.remove('active');
                    menuToggle.classList.remove('active');
                }
            }
        });
    });
    
    // Contact form enhancement
    const form = document.querySelector('.contact-form-element');
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('.submit-btn');
            if (submitBtn) {
                submitBtn.innerHTML = '<span>Envoi en cours...</span> <i class="fas fa-spinner fa-spin"></i>';
                submitBtn.disabled = true;
            }
        });
    }
    
    // Add active state to current navigation item
    const currentLocation = location.pathname;
    const menuItems = document.querySelectorAll('.nav-link');
    menuItems.forEach(item => {
        if(item.getAttribute('href') === currentLocation){
            item.classList.add('active');
        }
    });
    
    // Header scroll effect
    let lastScroll = 0;
    const header = document.querySelector('.header');
    
    window.addEventListener('scroll', function() {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
        } else {
            header.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
        }
        
        lastScroll = currentScroll;
    });
});

// Hero Carousel - Enhanced Version
let currentSlide = 0;
let autoPlayInterval;

const slides = [
    {
        image: 'img1.jpg',
        title: 'Bienvenue chez',
        brand: 'MSH ISTANBUL',
        subtitle: 'L\'élégance, à l\'algérienne.',
        discount: '-20%',
        text: 'Collection Mariage'
    },
    {
        image: 'img2.jpg', 
        title: 'Découvrez notre',
        brand: 'NOUVELLE COLLECTION',
        subtitle: 'Des costumes qui racontent votre histoire.',
        discount: '-25%',
        text: 'Costumes Business'
    },
    {
        image: 'img3.jpg',
        title: 'L\'art de',
        brand: 'LA TRADITION', 
        subtitle: 'Où le style rencontre l\'authenticité.',
        discount: '-15%',
        text: 'Collection Premium'
    }
];

function updateHero() {
    const slide = slides[currentSlide];
    
    // Update desktop version
    updateHeroVersion('.hero-content', slide);
    
    // Update mobile version
    updateHeroVersion('.hero-content-mobile', slide);
    
    // Update indicators
    updateIndicators();
}

function updateHeroVersion(containerSelector, slide) {
    const container = document.querySelector(containerSelector);
    if (!container) return;
    
    // Fade out effect
    const heroImg = container.querySelector('.hero-img');
    const titleLine = container.querySelector('.title-line');
    const titleBrand = container.querySelector('.title-brand');
    const subtitle = container.querySelector('.hero-subtitle');
    const dealPercent = container.querySelector('.deal-percent');
    const dealText = container.querySelector('.deal-text');
    
    // Add transition class
    if (heroImg) {
        heroImg.style.opacity = '0.5';
        setTimeout(() => {
            heroImg.src = slide.image;
            heroImg.style.opacity = '1';
        }, 300);
    }
    
    // Update text with fade effect
    if (titleLine) {
        titleLine.style.opacity = '0';
        setTimeout(() => {
            titleLine.textContent = slide.title;
            titleLine.style.opacity = '1';
        }, 300);
    }
    
    if (titleBrand) {
        titleBrand.style.opacity = '0';
        setTimeout(() => {
            titleBrand.textContent = slide.brand;
            titleBrand.style.opacity = '1';
        }, 300);
    }
    
    if (subtitle) {
        subtitle.style.opacity = '0';
        setTimeout(() => {
            subtitle.textContent = slide.subtitle;
            subtitle.style.opacity = '1';
        }, 300);
    }
    
    // Update badge
    if (dealPercent) dealPercent.textContent = slide.discount;
    if (dealText) dealText.textContent = slide.text;
}

function nextSlide() {
    currentSlide = (currentSlide + 1) % slides.length;
    updateHero();
}

function prevSlide() {
    currentSlide = (currentSlide - 1 + slides.length) % slides.length;
    updateHero();
}

function startAutoPlay() {
    stopAutoPlay();
    autoPlayInterval = setInterval(nextSlide, 5000);
}

function stopAutoPlay() {
    if (autoPlayInterval) {
        clearInterval(autoPlayInterval);
    }
}

function updateIndicators() {
    const indicators = document.querySelectorAll('.hero-indicator');
    indicators.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
    });
}

// Initialize hero carousel after page loads
window.addEventListener('load', function() {
    const hero = document.querySelector('.hero');
    if (hero && slides.length > 1) {
        // Add navigation arrows (only show on desktop)
        const leftArrow = document.createElement('button');
        leftArrow.className = 'hero-nav hero-nav-left';
        leftArrow.innerHTML = '‹';
        leftArrow.onclick = function() {
            prevSlide();
            stopAutoPlay();
            setTimeout(startAutoPlay, 5000);
        };
        hero.appendChild(leftArrow);
        
        const rightArrow = document.createElement('button');
        rightArrow.className = 'hero-nav hero-nav-right';
        rightArrow.innerHTML = '›';
        rightArrow.onclick = function() {
            nextSlide();
            stopAutoPlay();
            setTimeout(startAutoPlay, 5000);
        };
        hero.appendChild(rightArrow);
        
        // Add indicators
        const indicators = document.createElement('div');
        indicators.className = 'hero-indicators';
        
        slides.forEach((_, index) => {
            const dot = document.createElement('button');
            dot.className = 'hero-indicator';
            if (index === 0) dot.classList.add('active');
            dot.onclick = function() {
                currentSlide = index;
                updateHero();
                stopAutoPlay();
                setTimeout(startAutoPlay, 5000);
            };
            indicators.appendChild(dot);
        });
        
        hero.appendChild(indicators);
        
        // Add smooth transition styles for both desktop and mobile versions
        const allHeroImgs = document.querySelectorAll('.hero-img');
        const allTitleLines = document.querySelectorAll('.title-line');
        const allTitleBrands = document.querySelectorAll('.title-brand');
        const allSubtitles = document.querySelectorAll('.hero-subtitle');
        
        allHeroImgs.forEach(img => {
            if (img) img.style.transition = 'opacity 0.3s ease-in-out';
        });
        allTitleLines.forEach(line => {
            if (line) line.style.transition = 'opacity 0.3s ease-in-out';
        });
        allTitleBrands.forEach(brand => {
            if (brand) brand.style.transition = 'opacity 0.3s ease-in-out';
        });
        allSubtitles.forEach(subtitle => {
            if (subtitle) subtitle.style.transition = 'opacity 0.3s ease-in-out';
        });
        
        // Start auto-play
        startAutoPlay();
        
        // Pause on hover
        hero.addEventListener('mouseenter', stopAutoPlay);
        hero.addEventListener('mouseleave', startAutoPlay);
        
        // Add touch swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;
        
        hero.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        hero.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });
        
        function handleSwipe() {
            if (touchEndX < touchStartX - 50) {
                // Swipe left - next slide
                nextSlide();
                stopAutoPlay();
                setTimeout(startAutoPlay, 5000);
            }
            if (touchEndX > touchStartX + 50) {
                // Swipe right - previous slide
                prevSlide();
                stopAutoPlay();
                setTimeout(startAutoPlay, 5000);
            }
        }
    }
});

// Update cart count (example function - replace with actual implementation)
function updateCartCount() {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        // This would typically fetch from your backend
        // For now, just a placeholder
        const count = localStorage.getItem('cartCount') || 0;
        cartCount.textContent = count;
    }
}

// Call on page load
document.addEventListener('DOMContentLoaded', updateCartCount);