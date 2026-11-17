document.querySelectorAll('[data-quantity-stepper]').forEach((stepper) => {
    const input = stepper.querySelector('input[type="number"]');

    if (!input) {
        return;
    }

    const decrement = stepper.querySelector('[data-stepper-minus]');
    const increment = stepper.querySelector('[data-stepper-plus]');

    const clamp = () => {
        const min = Number(input.min) || 1;
        const max = Number(input.max) || 999;

        input.value = Math.min(Math.max(Number(input.value) || min, min), max);
    };

    decrement?.addEventListener('click', () => {
        input.stepDown();
        clamp();
    });

    increment?.addEventListener('click', () => {
        input.stepUp();
        clamp();
    });

    input.addEventListener('change', clamp);
});

const adminDrawer = document.getElementById('admin-drawer');
const adminDrawerOverlay = document.getElementById('admin-drawer-overlay');
const adminDrawerOpenButton = document.querySelector('[data-drawer-open]');
const adminDrawerCloseButton = document.querySelector('[data-drawer-close]');

const setDrawerState = (open) => {
    adminDrawer?.classList.toggle('-translate-x-full', !open);
    adminDrawerOverlay?.classList.toggle('hidden', !open);
    adminDrawerOpenButton?.setAttribute('aria-expanded', String(open));
};

adminDrawerOpenButton?.addEventListener('click', () => setDrawerState(true));
adminDrawerCloseButton?.addEventListener('click', () => setDrawerState(false));
adminDrawerOverlay?.addEventListener('click', () => setDrawerState(false));

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setDrawerState(false);
    }
});

const periodSelect = document.querySelector('[data-dashboard-period]');

periodSelect?.addEventListener('change', () => {
    const visible = periodSelect.value === 'custom';

    document.querySelectorAll('[data-custom-range]').forEach((container) => {
        container.classList.toggle('hidden', !visible);
    });
});

const checkoutSummary = document.querySelector('[data-checkout-summary]');

if (checkoutSummary) {
    const formatRupiah = (value) => `Rp ${new Intl.NumberFormat('id-ID').format(Math.max(value, 0))}`;
    const feeText = checkoutSummary.querySelector('[data-checkout-fee]');
    const feeRow = checkoutSummary.querySelector('[data-checkout-fee-row]');
    const totalText = checkoutSummary.querySelector('[data-checkout-total]');
    const deliveryFields = document.querySelector('[data-delivery-fields]');
    const newAddressFields = document.querySelector('[data-new-address-fields]');

    const getFulfillment = () => {
        const selected = document.querySelector('[data-checkout-fulfillment]:checked');

        return selected ? selected.value : 'delivery';
    };

    const refreshSummary = () => {
        const fulfillment = getFulfillment();
        const subtotal = Number(checkoutSummary.dataset.subtotal) || 0;
        const discount = Number(checkoutSummary.dataset.discount) || 0;
        const deliveryFee = Number(checkoutSummary.dataset.deliveryFee) || 0;
        const fee = fulfillment === 'delivery' ? deliveryFee : 0;

        feeRow?.classList.toggle('hidden', fulfillment !== 'delivery');
        deliveryFields?.classList.toggle('hidden', fulfillment !== 'delivery');

        if (fulfillment !== 'delivery') {
            newAddressFields?.classList.add('hidden');
        }

        if (feeText) {
            feeText.textContent = formatRupiah(fee);
        }

        if (totalText) {
            totalText.textContent = formatRupiah(subtotal - discount + fee);
        }
    };

    document.querySelectorAll('[data-checkout-fulfillment]').forEach((radio) => {
        radio.addEventListener('change', refreshSummary);
    });

    refreshSummary();
}

const newAddressFields = document.querySelector('[data-new-address-fields]');

if (newAddressFields) {
    const refreshNewAddress = () => {
        const selected = document.querySelector('input[name="address_choice"]:checked');

        newAddressFields.classList.toggle('hidden', selected?.value !== 'new');
    };

    document.querySelectorAll('input[name="address_choice"]').forEach((radio) => {
        radio.addEventListener('change', refreshNewAddress);
    });

    refreshNewAddress();
}
