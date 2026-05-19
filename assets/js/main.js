// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth'
            });
        }
    });
});

// Add shadow to header on scroll
window.addEventListener('scroll', () => {
    const header = document.querySelector('.main-header');
    if (window.scrollY > 10) {
        header.style.boxShadow = '0 4px 20px rgba(0,0,0,0.1)';
    } else {
        header.style.boxShadow = '0 2px 10px rgba(0,0,0,0.05)';
    }
});

// Hero Carousel Logic
const slides = document.querySelectorAll('.hero-bg.slide');
const dots = document.querySelectorAll('.carousel-dots .dot');
const prevBtn = document.querySelector('.prev-btn');
const nextBtn = document.querySelector('.next-btn');

if(slides.length > 1) {
    let currentSlide = 0;
    let interval;

    const goToSlide = (n) => {
        slides[currentSlide].classList.remove('active');
        if(dots.length) dots[currentSlide].classList.remove('active');
        
        currentSlide = (n + slides.length) % slides.length;
        
        slides[currentSlide].classList.add('active');
        if(dots.length) dots[currentSlide].classList.add('active');
    };

    const nextSlide = () => goToSlide(currentSlide + 1);
    const prevSlide = () => goToSlide(currentSlide - 1);

    const startInterval = () => {
        clearInterval(interval);
        interval = setInterval(nextSlide, 5000); // Auto change every 5 seconds
    };

    if(prevBtn) prevBtn.addEventListener('click', () => { prevSlide(); startInterval(); });
    if(nextBtn) nextBtn.addEventListener('click', () => { nextSlide(); startInterval(); });
    
    if(dots) {
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                goToSlide(index);
                startInterval();
            });
        });
    }

    startInterval();
}

