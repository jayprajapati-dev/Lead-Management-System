document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.counter');

    const animateCounter = (counter) => {
        const target = +counter.getAttribute('data-target');
        const speed = 200; // Adjust speed as needed
        const increment = target / speed;

        const updateCounter = () => {
            const value = +counter.innerText;
            if (value < target) {
                counter.innerText = Math.ceil(value + increment);
                setTimeout(updateCounter, 1);
            } else {
                counter.innerText = target;
            }
        };

        updateCounter();
    };

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                observer.unobserve(entry.target); // Stop observing once animated
            }
        });
    }, {
        threshold: 0.5 // Trigger when 50% of the element is visible
    });

    counters.forEach(counter => {
        observer.observe(counter);
    });
}); 