// Invoice-specific behaviour: modals on invoice show page

document.addEventListener('DOMContentLoaded', () => {
    const receiptModal = document.getElementById('receipt-modal');
    const openReceiptButton = document.getElementById('btn-open-receipt-modal');

    // Receipt modal wiring
    if (receiptModal && openReceiptButton) {
        const showReceiptModal = () => {
            receiptModal.style.display = 'flex';
        };

        const hideReceiptModal = () => {
            receiptModal.style.display = 'none';
        };

        openReceiptButton.addEventListener('click', (event) => {
            event.preventDefault();
            showReceiptModal();
        });

        receiptModal.querySelectorAll('[data-receipt-modal-close]').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                hideReceiptModal();
            });
        });

        // Close when clicking on the dark backdrop
        receiptModal.addEventListener('click', (event) => {
            if (event.target === receiptModal) {
                hideReceiptModal();
            }
        });
    }

    // Credit note modal wiring
    const creditModal = document.getElementById('credit-modal');
    const openCreditButton = document.getElementById('btn-open-credit-modal');

    if (creditModal && openCreditButton) {
        const showCreditModal = () => {
            creditModal.style.display = 'flex';
        };

        const hideCreditModal = () => {
            creditModal.style.display = 'none';
        };

        openCreditButton.addEventListener('click', (event) => {
            event.preventDefault();
            showCreditModal();
        });

        creditModal.querySelectorAll('[data-credit-modal-close]').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                hideCreditModal();
            });
        });

        creditModal.addEventListener('click', (event) => {
            if (event.target === creditModal) {
                hideCreditModal();
            }
        });
    }
});
