@if (
request()->routeIs('shop.checkout.onepage.index')
&& (bool) core()->getConfigData('sales.payment_methods.mercadopago_smart_button.active')
)
@php
$publicKey = core()->getConfigData('sales.payment_methods.mercadopago_smart_button.client_id');
$acceptedCurrency = core()->getConfigData('sales.payment_methods.mercadopago_smart_button.accepted_currencies') ?? 'USD';
@endphp

@pushOnce('scripts')
<script
    src="https://sdk.mercadopago.com/js/v2"
    data-partner-attribution-id="Bagisto_Cart"></script>

<script
    type="text/x-template"
    id="v-mercadopago-smart-button-template">
    <div class="w-full mercadopago-button-container"></div>
        </script>

<script type="module">
    console.log("eRROR >____>zDSFSDF");

    app.component('v-mercadopago-smart-button', {
        template: '#v-mercadopago-smart-button-template',

        mounted() {
            this.register();
        },

        methods: {
            register() {
                if (typeof MercadoPago === 'undefined') {
                    console.log("eRROR >____>zDSFSDF22");

                    this.$emitter.emit('add-flash', { type: 'error', message: '@lang('paypal::app.errors.invalid-configs')' });

                    return;
                }

                console.log("🔹 MercadoPago cargado con Public Key:", "{{ $publicKey }}");
                console.log("🔹 Moneda aceptada:", "{{ $acceptedCurrency }}");

                const mp = new MercadoPago("{{ $publicKey }}", {
                    locale: 'es-AR'
                });

                this.createPreference(mp);
            },

            async createPreference(mp) {
                try {
                    let response = await fetch("{{ route('mercadopago.smart-button.create-order') }}");
                    let data = await response.json();

                    if (data && data.id) {
                        console.log("🔹 Preferencia creada correctamente:", data.id);
                        mp.checkout({
                            preference: {
                                id: data.id
                            },
                            autoOpen: true
                        });
                    } else {
                        console.error("❌ Error: No se recibió una preferencia válida de MercadoPago.");
                    }
                } catch (error) {
                    console.error("❌ Error al crear la preferencia de MercadoPago:", error);
                }
            }
        },
    });
</script>
@endPushOnce
@endif