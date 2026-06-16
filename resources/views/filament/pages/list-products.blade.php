{{-- resources/views/filament/resources/your-resource/pages/custom-page.blade.php --}}

<x-filament-panels::page>
    <div class="filament-thead-scroll">
        {{ $this->content }}
    </div>

    <style>
        .filament-thead-scroll-row > th {
            padding: 0 !important;
            height: 18px;
            border: 0 !important;
        }

        .filament-thead-scroll-bar {
            position: sticky;
            left: 0;
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            height: 18px;
            min-height: 18px;
            background: inherit;
        }

        .filament-thead-scroll-inner {
            height: 1px;
            min-height: 1px;
        }
    </style>

    <script>
        (() => {
            function getColumnCount(thead) {
                const headerRow = [...thead.rows].find((row) => {
                    return !row.classList.contains('filament-thead-scroll-row');
                });

                if (!headerRow) {
                    return 1;
                }

                return [...headerRow.cells].reduce((total, cell) => {
                    return total + (cell.colSpan || 1);
                }, 0);
            }

            function findHorizontalScroller(root, table) {
                let element = table.parentElement;

                while (element && element !== root.parentElement) {
                    const style = window.getComputedStyle(element);

                    const hasHorizontalOverflow =
                        style.overflowX === 'auto' ||
                        style.overflowX === 'scroll' ||
                        style.overflowX === 'overlay';

                    if (hasHorizontalOverflow && element.scrollWidth > element.clientWidth + 2) {
                        return element;
                    }

                    element = element.parentElement;
                }

                return table.parentElement;
            }

            function initFilamentTheadScroll() {
                document.querySelectorAll('.filament-thead-scroll').forEach((root) => {
                    const table = root.querySelector('table');

                    if (!table) {
                        return;
                    }

                    const thead = table.querySelector('thead');

                    if (!thead) {
                        return;
                    }

                    const realScroller = findHorizontalScroller(root, table);

                    if (!realScroller) {
                        return;
                    }

                    let scrollRow = thead.querySelector('.filament-thead-scroll-row');

                    if (!scrollRow) {
                        scrollRow = document.createElement('tr');
                        scrollRow.className = 'filament-thead-scroll-row';

                        const th = document.createElement('th');
                        th.className = 'filament-thead-scroll-cell';

                        const topScroll = document.createElement('div');
                        topScroll.className = 'filament-thead-scroll-bar';

                        const topInner = document.createElement('div');
                        topInner.className = 'filament-thead-scroll-inner';

                        topScroll.appendChild(topInner);
                        th.appendChild(topScroll);
                        scrollRow.appendChild(th);

                        thead.appendChild(scrollRow);
                    }

                    const scrollCell = scrollRow.querySelector('th');
                    const topScroll = scrollRow.querySelector('.filament-thead-scroll-bar');
                    const topInner = scrollRow.querySelector('.filament-thead-scroll-inner');

                    if (!scrollCell || !topScroll || !topInner) {
                        return;
                    }

                    function updateScrollbar() {
                        scrollCell.colSpan = getColumnCount(thead);

                        const visibleWidth = realScroller.clientWidth;
                        const fullWidth = realScroller.scrollWidth;

                        topScroll.style.width = visibleWidth + 'px';
                        topInner.style.width = fullWidth + 'px';

                        if (fullWidth > visibleWidth + 2) {
                            scrollRow.style.display = '';
                        } else {
                            scrollRow.style.display = 'none';
                        }

                        topScroll.scrollLeft = realScroller.scrollLeft;
                    }

                    updateScrollbar();

                    if (
                        root._theadScrollScroller === realScroller &&
                        root._theadScrollBar === topScroll
                    ) {
                        return;
                    }

                    if (root._theadScrollAbortController) {
                        root._theadScrollAbortController.abort();
                    }

                    root._theadScrollScroller = realScroller;
                    root._theadScrollBar = topScroll;

                    const abortController = new AbortController();
                    root._theadScrollAbortController = abortController;

                    let syncing = false;

                    topScroll.addEventListener('scroll', () => {
                        if (syncing) {
                            return;
                        }

                        syncing = true;
                        realScroller.scrollLeft = topScroll.scrollLeft;

                        requestAnimationFrame(() => {
                            syncing = false;
                        });
                    }, {
                        signal: abortController.signal,
                    });

                    realScroller.addEventListener('scroll', () => {
                        if (syncing) {
                            return;
                        }

                        syncing = true;
                        topScroll.scrollLeft = realScroller.scrollLeft;

                        requestAnimationFrame(() => {
                            syncing = false;
                        });
                    }, {
                        signal: abortController.signal,
                    });

                    const resizeObserver = new ResizeObserver(updateScrollbar);

                    resizeObserver.observe(realScroller);
                    resizeObserver.observe(table);

                    abortController.signal.addEventListener('abort', () => {
                        resizeObserver.disconnect();
                    });

                    setTimeout(updateScrollbar, 300);
                    setTimeout(updateScrollbar, 1000);
                });
            }

            document.addEventListener('DOMContentLoaded', initFilamentTheadScroll);
            document.addEventListener('livewire:navigated', initFilamentTheadScroll);
            document.addEventListener('livewire:updated', initFilamentTheadScroll);

            if (window.Livewire) {
                document.addEventListener('livewire:init', () => {
                    Livewire.hook('morph.updated', () => {
                        initFilamentTheadScroll();
                    });
                });
            }

            new MutationObserver(() => {
                requestAnimationFrame(initFilamentTheadScroll);
            }).observe(document.body, {
                childList: true,
                subtree: true,
            });
        })();
    </script>
</x-filament-panels::page>
