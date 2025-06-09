        function initInfiniteScroll() {
            const scrollContent = document.getElementById('scroll');
            const originalText = scrollContent.querySelector('.text');
            
            const clonedText = originalText.cloneNode(true);
            scrollContent.appendChild(clonedText);

            const textWidth = originalText.offsetWidth;

            let position = scrollContent.parentElement.offsetWidth;

            const speed = 1;
            
            function animate() {
                position -= speed;

                if (position <= -textWidth) {
                    position = 0;
                }

                scrollContent.style.transform = `translateX(${position}px)`;

                requestAnimationFrame(animate);
            }

            animate();
        }
        
        window.addEventListener('load', initInfiniteScroll);
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