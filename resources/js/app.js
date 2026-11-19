//
document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!input) return;
        const visible = input.type === 'text';
        input.type = visible ? 'password' : 'text';
        button.innerHTML = `<iconify-icon icon="lucide:${visible ? 'eye' : 'eye-off'}"></iconify-icon>`;
        button.setAttribute('aria-label', visible ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
    });
});
