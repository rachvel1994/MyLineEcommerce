function initDualScroll() {

    document.querySelectorAll('.dual-scroll').forEach(wrapper => {

        if (wrapper.dataset.dualScrollInit) {
            return;
        }

        wrapper.dataset.dualScrollInit = 'true';

        const topScroll = document.createElement('div');
        topScroll.className = 'dual-scroll-top';

        const topInner = document.createElement('div');
        topInner.className = 'dual-scroll-inner';

        topScroll.appendChild(topInner);

        wrapper.parentNode.insertBefore(topScroll, wrapper);

        const updateWidth = () => {
            topInner.style.width = wrapper.scrollWidth + 'px';
        };

        updateWidth();

        topScroll.addEventListener('scroll', () => {
            wrapper.scrollLeft = topScroll.scrollLeft;
        });

        wrapper.addEventListener('scroll', () => {
            topScroll.scrollLeft = wrapper.scrollLeft;
        });

        window.addEventListener('resize', updateWidth);

        setTimeout(updateWidth, 300);
    });
}

document.addEventListener('DOMContentLoaded', initDualScroll);
document.addEventListener('livewire:navigated', initDualScroll);
document.addEventListener('livewire:update', initDualScroll);