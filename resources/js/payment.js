export function initPayment() {

    const errorModal = document.getElementById('add-payment-modal');

    if (
        errorModal &&
        errorModal.querySelector('.text-red-400\\/80')
    ) {
        errorModal.classList.add('active');
    }

    window.previewImage = function (input, paymentId) {
        const file = input.files[0];

        if (!file) return;

        const reader = new FileReader();

        reader.onload = function (e) {
            const preview = document.getElementById(
                `preview-img-${paymentId}`
            );

            if (preview) {
                preview.src = e.target.result;
            }
        };

        reader.readAsDataURL(file);
    };
}