import './bootstrap';

// Add class "is-sticky" to the header when the page is scrolled
window.addEventListener('scroll', function() {
    var header = document.querySelector('.navbar');
    header.classList.toggle('is-sticky', window.scrollY > 0);
});
