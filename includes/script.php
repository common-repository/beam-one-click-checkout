<?php
function beam_checkout_create_script() {
    ?>
    <script>
        const FETCH_TIMEOUT_IN_MS = 300000;
        const FETCH_INTERVAL_IN_MS = 1000;

        const handleClick = async (lineItems, coupons, shouldUpdateQuantity, params) => {
            const checkoutButton = document.querySelector('button#beam-checkout-btn');
            const productInputs = document.querySelectorAll('.quantity > input');
            const variationId = document.querySelector('input.variation_id')?.value ?? null;

            if (checkoutButton) {
                checkoutButton.disabled = 'disabled';
            }

            let body = { lineItems, coupons, params };

            if (shouldUpdateQuantity) {
                let quantity = 1;
                productInputs.forEach(el => {
                    if (el.type === 'number') {
                        quantity = el.value;
                    }
                });

                body = {
                    lineItems: [{
                        ...lineItems[0],
                        variation_id: variationId,
                        quantity: Number(quantity),
                    }],
                }
            }

            try {
                const res = await fetch(
                    '<?php echo esc_url_raw(get_site_url()) ?>/?rest_route=/beamcheckout/v1/checkout',
                    {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(body)
                    }
                )

                const { paymentLink, unavailableProduct } = await res.json();

                if (unavailableProduct && unavailableProduct.length > 0) {
                    const unavailableList = unavailableProduct.map(product => product.name)

                    alert(`Unavailable to checkout "${unavailableList.join(', ')}"`)
                }

                if (paymentLink) {
                    window.location.href = paymentLink
                } else {
                    console.log('paymentLink is not available')
                    if (checkoutButton) {
                        checkoutButton.disabled = '';
                    }
                }
            } catch (e) {
                console.log(e);
                if (checkoutButton) {
                    checkoutButton.disabled = '';
                }
            }
        }
    </script>
    <?php
}