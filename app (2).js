    // Disable right-click context menu
document.addEventListener('contextmenu', function(e) {
    e.preventDefault();
    return false;
});

// Disable F12, Ctrl+Shift+I, Ctrl+Shift+J, Ctrl+U, Ctrl+A, Ctrl+S, Ctrl+C, Ctrl+V
document.addEventListener('keydown', function(e) {
    // F12
    if (e.keyCode === 123) {
        e.preventDefault();
        return false;
    }
    
    // Ctrl+Shift+I (Dev Tools)
    if (e.ctrlKey && e.shiftKey && e.keyCode === 73) {
        e.preventDefault();
        return false;
    }
    
    // Ctrl+Shift+J (Console)
    if (e.ctrlKey && e.shiftKey && e.keyCode === 74) {
        e.preventDefault();
        return false;
    }
    
    // Ctrl+U (View Source)
    if (e.ctrlKey && e.keyCode === 85) {
        e.preventDefault();
        return false;
    }
});

// Disable text selection
document.addEventListener('selectstart', function(e) {
    e.preventDefault();
    return false;
});

// Disable drag
document.addEventListener('dragstart', function(e) {
    e.preventDefault();
    return false;
});

// Allow form inputs to be selectable
document.addEventListener('DOMContentLoaded', function() {
    const formInputs = document.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        input.addEventListener('selectstart', function(e) {
            e.stopPropagation();
        });
    });
});

// Main application logic
document.addEventListener('DOMContentLoaded', function() {
    
    // Smooth scroll navigation - Fixed
    const navLinks = document.querySelectorAll('a[href^="#"]');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const headerHeight = document.querySelector('.header').offsetHeight || 80;
                const targetPosition = targetSection.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                
                // Trigger skills animation
                if (entry.target.classList.contains('projects')) {
                    setTimeout(animateSkillBars, 300);
                }
                
                entry.target.classList.add('fade-in');
            }
        });
    }, observerOptions);

    // Observe sections for fade-in animation
    const sections = document.querySelectorAll('.portfolio, .achievements, .projects, .about, .contact');
    sections.forEach(section => {
        section.style.opacity = '0';
        section.style.transform = 'translateY(30px)';
        section.style.transition = 'all 0.8s ease';
        observer.observe(section);
    });

    // Portfolio card tilt effect
    const portfolioCards = document.querySelectorAll('.portfolio__card');
    portfolioCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'perspective(1000px) rotateX(5deg) rotateY(5deg) scale(1.02)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale(1)';
        });
        
        card.addEventListener('mousemove', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 10;
            const rotateY = (centerX - x) / 10;
            
            this.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
        });
    });

    // Skills bar animation - Fixed
    function animateSkillBars() {
        const skillBars = document.querySelectorAll('.skill-progress');
        skillBars.forEach((bar, index) => {
            const percentage = bar.getAttribute('data-percentage');
            if (percentage) {
                setTimeout(() => {
                    bar.style.width = percentage + '%';
                    bar.classList.add('animate');
                }, index * 100);
            }
        });
    }

    // Header background change on scroll
    window.addEventListener('scroll', function() {
        const header = document.querySelector('.header');
        const scrollTop = window.pageYOffset;
        
        if (scrollTop > 100) {
            header.style.background = 'rgba(10, 10, 10, 0.98)';
            header.style.backdropFilter = 'blur(15px)';
        } else {
            header.style.background = 'rgba(10, 10, 10, 0.95)';
            header.style.backdropFilter = 'blur(10px)';
        }
    });

    // Typewriter effect - Fixed
    function typewriterEffect() {
        const headline = document.querySelector('.hero__headline');
        if (headline && headline.classList.contains('typewriter')) {
            const text = headline.textContent;
            headline.textContent = '';
            headline.style.borderRight = '3px solid #00ff00';
            headline.style.whiteSpace = 'wrap';
            headline.style.overflow = 'hidden';
            
            let i = 0;
            const typeInterval = setInterval(() => {
                if (i < text.length) {
                    headline.textContent += text.charAt(i);
                    i++;
                } else {
                    clearInterval(typeInterval);
                    setTimeout(() => {
                        headline.style.borderRight = 'none';
                        headline.style.whiteSpace = 'normal';
                        headline.style.overflow = 'visible';
                    }, 1000);
                }
            }, 50);
        }
    }

    // Start typewriter effect after a short delay
    setTimeout(typewriterEffect, 1000);

    // Notification system - Fixed
    function showNotification(message, type = 'success') {
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notif => notif.remove());
        
        const notification = document.createElement('div');
        notification.className = `notification notification--${type}`;
        notification.innerHTML = `
            <div class="notification__content">
                <span class="notification__message">${message}</span>
                <button class="notification__close">&times;</button>
            </div>
        `;
        
        notification.style.cssText = `
            position: fixed;
            top: 100px;
            right: 20px;
            background: #1a1a1a;
            border: 2px solid #00ff00;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            color: #ffffff;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            max-width: 300px;
            font-family: 'Rajdhani', sans-serif;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        const closeBtn = notification.querySelector('.notification__close');
        closeBtn.style.cssText = `
            background: none;
            border: none;
            color: #00ff00;
            font-size: 1.5rem;
            cursor: pointer;
            margin-left: 10px;
            padding: 0;
        `;
        
        closeBtn.addEventListener('click', () => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        });
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    // Form submission - Fixed
    const contactForm = document.querySelector('.contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                showNotification('Thank you for your message! I\'ll get back to you soon.', 'success');
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                this.reset();
            }, 1000);
        });
    }

    // Add glow effects to interactive elements
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(btn => {
        btn.addEventListener('mouseenter', function() {
            if (this.classList.contains('btn--primary')) {
                this.style.boxShadow = '0 0 30px rgba(0, 255, 0, 0.8)';
            }
        });
        
        btn.addEventListener('mouseleave', function() {
            if (this.classList.contains('btn--primary')) {
                this.style.boxShadow = '0 0 5px #00ff00, 0 0 10px #00ff00, 0 0 15px #00ff00, 0 0 20px #00ff00';
            }
        });
    });

    // Social icons glow effect
    const socialIcons = document.querySelectorAll('.social-icon');
    socialIcons.forEach(icon => {
        icon.addEventListener('mouseenter', function() {
            this.style.boxShadow = '0 0 15px rgba(0, 255, 0, 0.8)';
            this.style.borderColor = '#39ff14';
        });
        
        icon.addEventListener('mouseleave', function() {
            this.style.boxShadow = 'none';
            this.style.borderColor = 'transparent';
        });
    });

    // Parallax effect for geometric shapes
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const shapes = document.querySelectorAll('.geometric-shape');
        
        shapes.forEach((shape, index) => {
            const speed = 0.3 + (index * 0.1);
            const yPos = -(scrolled * speed);
            shape.style.transform = `translateY(${yPos}px)`;
        });
    });

    // Certificate carousel functionality - Fixed
        const carousel = document.querySelector('.carousel-track');
    const certificates = document.querySelectorAll('.certificate');
    
    if (carousel && certificates.length > 0) {
        // Fix: Clone certificates multiple times for a true infinite loop
        const clonesNeeded = 30; // Clone the entire set of certificates 3 times

        for (let i = 0; i < clonesNeeded; i++) {
            certificates.forEach(cert => {
                const clone = cert.cloneNode(true);
                carousel.appendChild(clone);
            });
        }
        
        // The rest of the logic remains the same
        
        // Update animation duration based on number of certificates
        const animationDuration = (certificates.length * (clonesNeeded + 1)) * 0.60; // Adjust duration for all cloned certificates
        carousel.style.animationDuration = `${animationDuration}s`;
        
        // Pause on hover functionality
        // Note: The selector here should be updated to get all certificates, including the clones
        const allCertificates = document.querySelectorAll('.certificate');
        allCertificates.forEach(cert => {
            cert.addEventListener('mouseenter', function() {
                carousel.style.animationPlayState = 'paused';
            });
            
            cert.addEventListener('mouseleave', function() {
                carousel.style.animationPlayState = 'running';
            });
        });
    }

    // Mobile menu functionality
    function setupMobileMenu() {
        const nav = document.querySelector('.header__nav');
        const navList = nav.querySelector('.nav-list');
        
        if (window.innerWidth <= 768) {
            // Create mobile menu button if it doesn't exist
            let menuBtn = document.querySelector('.mobile-menu-btn');
            if (!menuBtn) {
                menuBtn = document.createElement('button');
                menuBtn.className = 'mobile-menu-btn';
                menuBtn.innerHTML = '☰';
                menuBtn.style.cssText = `
                    background: transparent;
                    border: 1px solid #00ff00;
                    color: #00ff00;
                    font-size: 1.5rem;
                    padding: 8px 12px;
                    border-radius: 5px;
                    cursor: pointer;
                    transition: all 0.3s ease;
                    display: block;
                `;
                
                document.querySelector('.header__content').appendChild(menuBtn);
            }
            
            // Hide nav list by default on mobile
            navList.style.display = 'none';
            
            // Toggle functionality
            menuBtn.addEventListener('click', function() {
                if (navList.style.display === 'none' || navList.style.display === '') {
                    navList.style.display = 'block';
                    navList.style.position = 'absolute';
                    navList.style.top = '100%';
                    navList.style.left = '0';
                    navList.style.width = '100%';
                    navList.style.background = 'rgba(10, 10, 10, 0.98)';
                    navList.style.padding = '1rem';
                    navList.style.borderTop = '1px solid #00ff00';
                    navList.style.flexDirection = 'column';
                    navList.style.gap = '1rem';
                } else {
                    navList.style.display = 'none';
                }
            });
        } else {
            // Desktop - ensure nav is visible
            navList.style.display = 'flex';
            navList.style.position = 'static';
            navList.style.background = 'transparent';
            navList.style.padding = '0';
            navList.style.border = 'none';
            navList.style.flexDirection = 'row';
            navList.style.gap = '2rem';
            
            const existingBtn = document.querySelector('.mobile-menu-btn');
            if (existingBtn) {
                existingBtn.remove();
            }
        }
    }

    // Initialize mobile menu
    setupMobileMenu();
    
    // Reinitialize on window resize
    window.addEventListener('resize', setupMobileMenu);

    // Loading animation
    function showLoader() {
        const loader = document.createElement('div');
        loader.className = 'page-loader';
        loader.innerHTML = `
            <div class="loader__content">
                <div class="loader__spinner"></div>
                <div class="loader__text">SM RAFI</div>
                <div class="loader__subtext">Loading Portfolio...</div>
            </div>
        `;
        
        loader.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #0a0a0a;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            opacity: 1;
            transition: opacity 0.5s ease;
        `;
        
        const content = loader.querySelector('.loader__content');
        content.style.cssText = `
            text-align: center;
        `;
        
        const spinner = loader.querySelector('.loader__spinner');
        spinner.style.cssText = `
            width: 60px;
            height: 60px;
            border: 3px solid #333;
            border-top: 3px solid #00ff00;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 1.5rem auto;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);
        `;
        
        const text = loader.querySelector('.loader__text');
        text.style.cssText = `
            color: #00ff00;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.8rem;
            font-weight: 800;
            text-shadow: 0 0 10px rgba(0, 255, 0, 0.8);
            margin-bottom: 0.5rem;
        `;
        
        const subtext = loader.querySelector('.loader__subtext');
        subtext.style.cssText = `
            color: #cccccc;
            font-family: 'Rajdhani', sans-serif;
            font-size: 1rem;
        `;
        
        // Add spinner animation
        if (!document.querySelector('#spinner-style')) {
            const style = document.createElement('style');
            style.id = 'spinner-style';
            style.textContent = `
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(loader);
        
        // Remove loader
        setTimeout(() => {
            loader.style.opacity = '0';
            setTimeout(() => {
                if (loader.parentNode) {
                    loader.remove();
                }
            }, 500);
        }, 2000);
    }

    // Show loader
    showLoader();

    // Image error handling
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('error', function() {
            this.style.opacity = '0.5';
            this.style.border = '2px dashed #00ff00';
        });
    });

    console.log('SM Rafi Portfolio - All systems operational');
});

// Disable drag and drop
document.addEventListener('drop', function(e) {
    e.preventDefault();
    return false;
});

document.addEventListener('dragover', function(e) {
    e.preventDefault();
    return false;
});

// Additional protection layers
(function() {
    'use strict';
    
    // Monitor developer tools
    let devtools = false;
    setInterval(function() {
        if (window.outerHeight - window.innerHeight > 200 || window.outerWidth - window.innerWidth > 200) {
            if (!devtools) {
                devtools = true;
                console.clear();
                console.log('%c⚠️ PROTECTED CONTENT', 'color: #ff0000; font-size: 24px; font-weight: bold;');
                console.log('%cThis website belongs to SM Rafi. Unauthorized access is prohibited.', 'color: #ff6600; font-size: 14px;');
            }
        } else {
            devtools = false;
        }
    }, 1000);
})();


(function(){if(!window.chatbase||window.chatbase("getState")!=="initialized"){window.chatbase=(...arguments)=>{if(!window.chatbase.q){window.chatbase.q=[]}window.chatbase.q.push(arguments)};window.chatbase=new Proxy(window.chatbase,{get(target,prop){if(prop==="q"){return target.q}return(...args)=>target(prop,...args)}})}const onLoad=function(){const script=document.createElement("script");script.src="https://www.chatbase.co/embed.min.js";script.id="UQrNUrBfY7eJ71xi8Xrwb";script.domain="www.chatbase.co";document.body.appendChild(script)};if(document.readyState==="complete"){onLoad()}else{window.addEventListener("load",onLoad)}})();
//chatbase link: https://www.chatbase.co/dashboard/rafi699s-workspace/chatbot/UQrNUrBfY7eJ71xi8Xrwb/playground
