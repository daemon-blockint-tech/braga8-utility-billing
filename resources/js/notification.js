export function initNotifications() {
    function updateBellStatus() {
        const wrapper = document.querySelector('.notification-wrapper');
        if (!wrapper) return;

        const unreadCount = wrapper.querySelectorAll('.notification.is-unread').length;
        
        if (unreadCount === 0) {
            const bellDot = document.querySelector('.notif-dot');
            if (bellDot) {
                bellDot.remove();
            }

            const bellIcon = document.querySelector('.icon-wrapper .fa-bell');
            if (bellIcon) {
                bellIcon.classList.remove('text-[#FA8327]', 'text-orange-500');
            }
        }
    }

    document.querySelectorAll('.read-notif-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            
            const currentForm = this;
            const notificationItem = currentForm.closest('.notification');
            const icon = notificationItem ? notificationItem.querySelector('.notif-icon') : null;
            const url = currentForm.action;
            const formData = new FormData(currentForm);

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    notificationItem.classList.remove('is-unread');
                    notificationItem.classList.add('is-read');
                    
                    if (icon) {
                        icon.classList.remove('fa-envelope', 'text-[#FA8327]');
                        icon.classList.add('fa-envelope-open', 'text-zinc-500');
                    }
                    
                    currentForm.remove();

                    updateBellStatus();
                } else {
                    console.error('Gagal memperbarui status baca');
                }
            })
            .catch(error => console.error('Error AJAX Read:', error));
        });
    });

    document.querySelectorAll('.delete-notif-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault(); 
            
            const currentForm = this;
            const url = currentForm.action;
            const formData = new FormData(currentForm);
            const notificationItem = currentForm.closest('.notification');

            notificationItem.style.transition = 'all 0.3s ease';
            notificationItem.style.opacity = '0';
            notificationItem.style.transform = 'translateX(20px)';

            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (response.ok) {
                    setTimeout(() => {
                        notificationItem.remove();
                        
                        updateBellStatus();

                        const wrapper = document.querySelector('.notification-wrapper');
                        if (wrapper && wrapper.querySelectorAll('.notification').length === 0) {
                            wrapper.innerHTML = `
                                <div class="py-16 text-center">
                                    <div class="text-zinc-700 mb-3">
                                        <i class="fa-solid fa-bell-slash text-4xl"></i>
                                    </div>
                                    <p class="text-zinc-500 text-sm italic">Belum ada pemberitahuan baru.</p>
                                </div>`;
                        }
                    }, 300);
                } else {
                    notificationItem.style.opacity = '1';
                    notificationItem.style.transform = 'translateX(0)';
                    console.error('Gagal menghapus notifikasi');
                }
            })
            .catch(error => {
                notificationItem.style.opacity = '1';
                notificationItem.style.transform = 'translateX(0)';
                console.error('Error AJAX Delete:', error);
            });
        });
    });
}