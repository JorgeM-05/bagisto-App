@if (
    request()->routeIs('shop.checkout.onepage.index')
    && (bool) core()->getConfigData('sales.payment_methods.mercadopago.active')
)
    @php
        $publicKey = core()->getConfigData('sales.payment_methods.mercadopago.public_key');
        $acceptedCurrency = core()->getConfigData('sales.payment_methods.mercadopago.currency') ?: 'USD';
    @endphp

    @pushOnce('scripts')
        <script
            src="https://sdk.mercadopago.com/js/v2"
            data-partner-attribution-id="Bagisto_Cart"
        ></script>

        <script
            type="text/x-template"
            id="v-mercadopago-smart-button-template"
        >
            <div class="w-full mercadopago-button-container"></div>
        </script>

        <script type="module">
            app.component('v-mercadopago-smart-button', {
                template: '#v-mercadopago-smart-button-template',

                mounted() {
                    this.register();
                },

                methods: {
                    register() {
                        if (typeof MercadoPago == 'undefined') {
                            this.$emitter.emit('add-flash', { type: 'error', message: '@lang('mercadopago::app.errors.invalid-configs')' });

                            return;
                        }

                        const mp = new MercadoPago("{{ $publicKey }}", {
                            locale: 'es-AR'
                        });

                        mp.checkout({
                            preference: {
                                id: "{{ route('mercadopago.create-preference') }}"
                            },
                            autoOpen: true,
                        });
                    },
                },
            });
        </script>
    @endPushOnce
@endif
