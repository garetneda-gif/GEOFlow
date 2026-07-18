(() => {
    const library = document.querySelector('[data-product-library]');

    if (!library) {
        return;
    }

    const items = [...library.querySelectorAll('[data-product-item]')];
    const categoryButtons = [...library.querySelectorAll('[data-category]')].filter((element) => element.tagName === 'BUTTON');
    const searchInput = library.querySelector('[data-product-search]');
    const clearSearchButton = library.querySelector('[data-clear-search]');
    const resetButton = library.querySelector('[data-reset-filters]');
    const emptyState = library.querySelector('[data-empty-state]');
    const resultLabel = library.querySelector('[data-result-label]');
    let activeCategory = 'all';

    const normalize = (value) => value.trim().toLocaleLowerCase('zh-CN');

    const render = () => {
        const query = normalize(searchInput.value);
        let visibleCount = 0;

        items.forEach((item) => {
            const matchesCategory = activeCategory === 'all' || item.dataset.category === activeCategory;
            const matchesSearch = query === '' || normalize(item.dataset.search || '').includes(query);
            const isVisible = matchesCategory && matchesSearch;

            item.hidden = !isVisible;
            if (isVisible) {
                visibleCount += 1;
            }
        });

        clearSearchButton.hidden = query === '';
        emptyState.hidden = visibleCount !== 0;
        resultLabel.textContent = query === '' && activeCategory === 'all'
            ? `正在展示全部 ${visibleCount} 款产品`
            : `找到 ${visibleCount} 款产品`;
    };

    categoryButtons.forEach((button) => {
        button.addEventListener('click', () => {
            activeCategory = button.dataset.category || 'all';
            categoryButtons.forEach((candidate) => {
                const isActive = candidate === button;
                candidate.classList.toggle('is-active', isActive);
                candidate.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
            render();
        });
    });

    searchInput.addEventListener('input', render);
    clearSearchButton.addEventListener('click', () => {
        searchInput.value = '';
        searchInput.focus();
        render();
    });
    resetButton.addEventListener('click', () => {
        activeCategory = 'all';
        searchInput.value = '';
        categoryButtons.forEach((button) => {
            const isActive = button.dataset.category === 'all';
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
        render();
    });
})();
