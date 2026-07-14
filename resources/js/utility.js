export function initUtilityFeatures() {
    document.addEventListener('click', (e) => {
        const lockBtn = e.target.closest('.lock-btn-small');
        if (!lockBtn) return;

        const password = prompt("Masukkan Password Admin:");
        if (password === "BRAGA8ADMIN") {
            const meterId = lockBtn.getAttribute('data-meter-id') || '';
            const input = document.getElementById('multiplierInput' + meterId);
            const icon = document.getElementById('lockIcon' + meterId);

            if (input) {
                input.readOnly = false;
                input.classList.add('unlocked');
                if (icon) icon.classList.replace('fa-lock', 'fa-lock-open');
                lockBtn.style.opacity = "0.5";
                lockBtn.disabled = true;
                alert("Akses dibuka!");
            }
        } else if (password !== null) {
            alert("Password salah!");
        }
    });

    let idToDelete = null;
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-popup="delete-tariff"]');
        if (trigger) {
            idToDelete = trigger.getAttribute('data-id');
        }
    });

    document.addEventListener('click', (e) => {
        if (e.target.closest('#confirm-delete-btn')) {
            if (idToDelete) {
                document.getElementById('delete-form-' + idToDelete).submit();
            }
        }
    });
}