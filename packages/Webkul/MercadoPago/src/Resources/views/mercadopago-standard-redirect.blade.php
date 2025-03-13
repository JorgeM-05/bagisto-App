<?php $mercadoPago = app('Webkul\MercadoPago\Payment\MercadoPago') ?>

<body data-gr-c-s-loaded="true" cz-shortcut-listen="true">
    You will be redirected to the Mercado Pago website in a few seconds.

    <form action="{{ $mercadoPago->getMercadoPagoUrl() }}" id="mercadopago_standard_checkout" method="POST">
        <input value="Click here if you are not redirected within 10 seconds..." type="submit">

        @foreach ($mercadoPago->getFormFields() as $name => $value)
            <input
                type="hidden"
                name="{{ $name }}"
                value="{{ $value }}"
            />
        @endforeach
    </form>

    <script type="text/javascript">
        document.getElementById("mercadopago_standard_checkout").submit();
    </script>
</body>
