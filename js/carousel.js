 document.addEventListener('DOMContentLoaded', () => {
            const carousels = document.querySelectorAll('[data-carousel]');

            carousels.forEach(carousel => {
                const track = carousel.querySelector('.carousel-slides');
                const nextBtn = carousel.querySelector('.carousel-next');
                const prevBtn = carousel.querySelector('.carousel-prev');
                
                // Si no encuentra elementos, saltar este carrusel para evitar errores
                if (!track || !nextBtn || !prevBtn) return;

                const slides = track.children;
                const totalSlides = slides.length;
                let currentIndex = 0;

                const updateCarouselPosition = () => {
                    const amountToMove = currentIndex * 100;
                    track.style.transform = `translateX(-${amountToMove}%)`;
                };

                nextBtn.addEventListener('click', () => {
                    if (currentIndex === totalSlides - 1) {
                        currentIndex = 0; 
                    } else {
                        currentIndex++;
                    }
                    updateCarouselPosition();
                });

                prevBtn.addEventListener('click', () => {
                    if (currentIndex === 0) {
                        currentIndex = totalSlides - 1; 
                    } else {
                        currentIndex--;
                    }
                    updateCarouselPosition();
                });
            });
        });