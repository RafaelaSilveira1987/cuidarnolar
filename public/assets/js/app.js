document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
        return;
    }

    if (target.matches('.alert-close')) {
        target.closest('.alert')?.remove();
    }
});
