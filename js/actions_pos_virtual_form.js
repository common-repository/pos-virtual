function valid_credit_card_pos_virtual(value) {
    // Accept only digits, dashes or spaces
    if (/[^0-9-\s]+/.test(value)) return false;

    // The Luhn Algorithm. It's so pretty.
    let nCheck = 0, bEven = false;
    value = value.replace(/\D/g, "");

    for (var n = value.length - 1; n >= 0; n--) {
        var cDigit = value.charAt(n),
            nDigit = parseInt(cDigit, 10);

        if (bEven && (nDigit *= 2) > 9) nDigit -= 9;

        nCheck += nDigit;
        bEven = !bEven;
    }

    let finalVal = (nCheck % 10) == 0
    return finalVal;
}

jQuery(document).ready(function ($) {
    let validLunhFormEbiPayForm = false;
    let isCheckedEbiPay = false;
    let btnPlaceOrder = $('#place_order');
    let cleanFields = function () {
        $('input[name="PLUGIN_gateway_posvirtual-card-name"]').val(null)
        $('input[name="PLUGIN_gateway_posvirtual-card-number"]').val(null)
        $('input[name="PLUGIN_gateway_posvirtual-card-expiry"]').val(null)
        $('input[name="PLUGIN_gateway_posvirtual-card-cvc"]').val(null)
    }

    $('#PLUGIN_gateway_posvirtual-card-number').keyup(function (event) {
        let luhnValid = valid_credit_card_pos_virtual($(this).val())
        let btnWoocommerceEbiPay = $('#place_order'),
            cardNumberParagraph = $('#card-number-paragraph');

        if (luhnValid && $(this).val().length > 0) {
            // btnWoocommerceEbiPay.removeAttr('disabled')
            cardNumberParagraph.removeClass('woocommerce-invalid woocommerce-invalid-required-field')
            $(this).css('border-color', '#6dc22e')
            validLunhFormEbiPayForm = true;
        } else {
            cardNumberParagraph.addClass('woocommerce-invalid woocommerce-invalid-required-field')
            $(this).css('border-color', '#a00')
            validLunhFormEbiPayForm = false;
            // btnWoocommerceEbiPay.attr('disabled', 'disabled')
        }

    })


    setInterval(() => {
        let check = $('input:radio[name="payment_method"]:checked').val()
        if (check === 'PLUGIN_gateway_posvirtual') {
            if (validLunhFormEbiPayForm) {
                btnPlaceOrder.prop('disabled', false)
            } else {
                btnPlaceOrder.prop('disabled', true)
            }
        } else {
            btnPlaceOrder.prop('disabled', false)
        }

    }, 1000)


    btnPlaceOrder.click(function () {
        setTimeout(() => {
            cleanFields();
        }, 4 * 1000);
    })

});
