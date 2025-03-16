<?php $mercadoPago = app('Webkul\MercadoPago\Payment\MercadoPagoStandard') ?>

<body onload="document.getElementById('mercadopago_standard_checkout').submit();">
    <p>Serás redirigido a Mercado Pago en unos segundos...</p>

    <form action="{{ $mercadoPago->getRedirectUrl() }}" id="mercadopago_standard_checkout" method="POST">
        @csrf
        <noscript>
            <p>Si no eres redirigido automáticamente, haz clic en el siguiente botón:</p>
            <input value="Ir a Mercado Pago" type="submit">
        </noscript>

        @foreach ($mercadoPago->getFormFields() as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
    </form>

    <script type="text/javascript">
        document.getElementById("mercadopago_standard_checkout").submit();
    </script>
</body>
