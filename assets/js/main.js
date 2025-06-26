
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a.delete').forEach(function(link) {
        link.addEventListener('click', function(e) {
            if(!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    document.querySelectorAll('a').forEach(function(link) {
        if (link.textContent.trim().toLowerCase() === 'remove') {
            link.addEventListener('click', function(e) {
                if(!confirm('Remove this item?')) {
                    e.preventDefault();
                }
            });
        }
    });

    var navLinks = document.querySelectorAll('nav a');
    var current = window.location.pathname.split('/').pop();
    navLinks.forEach(function(link) {
        if(link.getAttribute('href') && link.getAttribute('href').split('/').pop() === current) {
            link.classList.add('active');
        }
    });

    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function (e) {
            var hash = this.getAttribute('href');
            if (hash.length > 1 && document.querySelector(hash)) {
                e.preventDefault();
                document.querySelector(hash).scrollIntoView({ behavior: 'smooth' });
            }
        });
    });

    setTimeout(function() {
        document.querySelectorAll('.error, .success').forEach(function(msg) {
            msg.style.transition = 'opacity 0.5s';
            msg.style.opacity = 0;
            setTimeout(function() { msg.style.display = 'none'; }, 500);
        });
    }, 4000);
});
