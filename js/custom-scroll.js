// Function to handle horizontal scrolling
function initializeScrollButtons(containerClass) {
    const containers = document.querySelectorAll(containerClass);
    
    containers.forEach(container => {
        const wrapper = container.querySelector(`${containerClass}-wrapper`);
        const leftBtn = container.querySelector('.scroll-button-left');
        const rightBtn = container.querySelector('.scroll-button-right');
        
        if (!wrapper || !leftBtn || !rightBtn) return;
        
        // Scroll left
        leftBtn.addEventListener('click', () => {
            wrapper.scrollBy({
                left: -wrapper.offsetWidth * 0.8,
                behavior: 'smooth'
            });
        });
        
        // Scroll right
        rightBtn.addEventListener('click', () => {
            wrapper.scrollBy({
                left: wrapper.offsetWidth * 0.8,
                behavior: 'smooth'
            });
        });
        
        // Show/hide scroll buttons based on scroll position
        wrapper.addEventListener('scroll', () => {
            leftBtn.style.display = wrapper.scrollLeft > 0 ? 'flex' : 'none';
            rightBtn.style.display = 
                wrapper.scrollLeft < wrapper.scrollWidth - wrapper.offsetWidth 
                ? 'flex' : 'none';
        });
        
        // Initial button visibility
        leftBtn.style.display = 'none';
        rightBtn.style.display = 
            wrapper.scrollWidth > wrapper.offsetWidth ? 'flex' : 'none';
    });
}

// Function to handle vendor tabs
function initializeVendorTabs() {
    const sellersTab = document.getElementById('top-sellers-tab');
    const suppliersTab = document.getElementById('top-suppliers-tab');
    const sellersContainer = document.getElementById('top-sellers-container');
    const suppliersContainer = document.getElementById('top-suppliers-container');
    
    if (!sellersTab || !suppliersTab || !sellersContainer || !suppliersContainer) return;
    
    sellersTab.addEventListener('click', () => {
        sellersTab.classList.add('active');
        suppliersTab.classList.remove('active');
        sellersContainer.style.display = 'block';
        suppliersContainer.style.display = 'none';
    });
    
    suppliersTab.addEventListener('click', () => {
        suppliersTab.classList.add('active');
        sellersTab.classList.remove('active');
        suppliersContainer.style.display = 'block';
        sellersContainer.style.display = 'none';
    });
}

// Initialize all scroll containers when the DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize scroll functionality for all scroll containers
    const scrollContainers = document.querySelectorAll('.deals-scroll-container, .vendors-scroll-container');
    
    scrollContainers.forEach(container => {
        const wrapper = container.querySelector('.deals-scroll-wrapper, .vendors-scroll-wrapper');
        const prevBtn = container.querySelector('.prev-btn');
        const nextBtn = container.querySelector('.next-btn');
        
        if (!wrapper || !prevBtn || !nextBtn) return;
        
        // Handle button clicks
        prevBtn.addEventListener('click', () => {
            wrapper.scrollBy({
                left: -wrapper.offsetWidth * 0.8,
                behavior: 'smooth'
            });
        });
        
        nextBtn.addEventListener('click', () => {
            wrapper.scrollBy({
                left: wrapper.offsetWidth * 0.8,
                behavior: 'smooth'
            });
        });
        
        // Handle scroll position for button visibility
        wrapper.addEventListener('scroll', () => {
            prevBtn.style.display = wrapper.scrollLeft > 0 ? 'flex' : 'none';
            nextBtn.style.display = 
                wrapper.scrollLeft < (wrapper.scrollWidth - wrapper.offsetWidth - 5)
                ? 'flex' : 'none';
        });
        
        // Initial button visibility
        prevBtn.style.display = 'none';
        nextBtn.style.display = 
            wrapper.scrollWidth > wrapper.offsetWidth ? 'flex' : 'none';
        
        // Touch scroll functionality
        let isDown = false;
        let startX;
        let scrollLeft;
        
        wrapper.addEventListener('mousedown', (e) => {
            isDown = true;
            wrapper.style.cursor = 'grabbing';
            startX = e.pageX - wrapper.offsetLeft;
            scrollLeft = wrapper.scrollLeft;
        });
        
        wrapper.addEventListener('mouseleave', () => {
            isDown = false;
            wrapper.style.cursor = 'grab';
        });
        
        wrapper.addEventListener('mouseup', () => {
            isDown = false;
            wrapper.style.cursor = 'grab';
        });
        
        wrapper.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - wrapper.offsetLeft;
            const walk = (x - startX) * 2;
            wrapper.scrollLeft = scrollLeft - walk;
        });
        
        // Touch events for mobile
        wrapper.addEventListener('touchstart', (e) => {
            isDown = true;
            startX = e.touches[0].pageX - wrapper.offsetLeft;
            scrollLeft = wrapper.scrollLeft;
        });
        
        wrapper.addEventListener('touchend', () => {
            isDown = false;
        });
        
        wrapper.addEventListener('touchmove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.touches[0].pageX - wrapper.offsetLeft;
            const walk = (x - startX) * 2;
            wrapper.scrollLeft = scrollLeft - walk;
        });
    });
    
    // Initialize vendor tabs
    initializeVendorTabs();
}); 