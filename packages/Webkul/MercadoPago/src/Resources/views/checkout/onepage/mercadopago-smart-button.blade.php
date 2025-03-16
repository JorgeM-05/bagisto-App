@if (
    request()->routeIs('shop.checkout.onepage.index')
    && (bool) core()->getConfigData('sales.payment_methods.mercadopago_smart_button.active')
)
    @php
        $clientId = core()->getConfigData('sales.payment_methods.mercadopago_smart_button.client_id');

        $acceptedCurrency = core()->getConfigData('sales.payment_methods.mercadopago_smart_button.accepted_currencies');
    @endphp

    @pushOnce('scripts')
        <script
            src="https://www.paypal.com/sdk/js?client-id={{ $clientId }}&currency={{ $acceptedCurrency }}"
            data-partner-attribution-id="Bagisto_Cart"
        >
        </script>

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
                        if (typeof mercadopago == 'undefined') {
                            this.$emitter.emit('add-flash', { type: 'error', message: '@lang('mercadopago::app.errors.invalid-configs')' });

                            return;
                        }

                        mercadopago.Buttons(this.getOptions()).render('.mercadopago-button-container');
                    },

                    getOptions() {
                        let options = {
                            style: {
                                layout: 'vertical',
                                shape: 'rect',
                            },

                            authorizationFailed: false,

                            enableStandardCardFields: false,

                            alertBox: (message) => {
                                this.$emitter.emit('add-flash', { type: 'error', message: message });
                            },

                            createOrder: (data, actions) => {
                                return this.$axios.get("{{ route('mercadopago.smart-button.create-order') }}")
                                    .then(response => response.data.result)
                                    .then(order => order.id)
                                    .catch(error => {
                                        if (error.response.data.error === 'invalid_client') {
                                            options.authorizationFailed = true;

                                            options.alertBox('@lang('mercadopago::app.errors.invalid-configs')');
                                        }

                                        return error;
                                    });
                            },

                            onApprove: (data, actions) => {
                                this.$axios.post("{{ route('mercadopago.smart-button.capture-order') }}", {
                                    _token: "{{ csrf_token() }}",
                                    orderData: data
                                })
                                .then(response => {
                                    if (response.data.success) {
                                        if (response.data.redirect_url) {
                                            window.location.href = response.data.redirect_url;
                                        } else {
                                            window.location.href = "{{ route('shop.checkout.onepage.success') }}";
                                        }
                                    }
                                })
                                .catch(error => window.location.href = "{{ route('shop.checkout.cart.index') }}");
                            },

                            onError: (error) => {
                                if (! options.authorizationFailed) {
                                    options.alertBox('@lang('mercadopago::app.errors.something-went-wrong')');
                                }
                            },
                        };

                        return options;
                    },
                },
            });
        </script>
    @endPushOnce
@endif
