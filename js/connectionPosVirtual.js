jQuery(document).ready(function ($){
    let idWpWrp = $("#wpcontent");
    idWpWrp.css({
        'padding-left':0,
    })

    let botton = $("#btn-test-connection");
    let bottonModalDeconection = $("#btn-desconnection");
    let bottonDeconection = $("#btndesactivar");

    function testConnection() {
        botton.text("Validando credenciales...");
        botton.prop('disabled', true)
        let cntCredentialsConnection = $("#cnt-form-test-connection");
        let cntCredentials = $("#cnt-form-credentials");


        let userposvirtual = $('input[name="user_posvirtual_s1"]').val();
        let passwordposvirtual = $('input[name="password_posvirtual_s1"]').val();
        let tokenposvirtual = $('input[name="token_posvirtual_s1"]').val();

        let midposvirtual = $('input[name="mid_posvirtual_s1"]').val();
        let cybsposvirtual = $('select[name="cybs_posvirtual_s1"]').val();

        let prefixTable = $('input[name="prefixTable"]').val();

        var formData = new FormData();
        formData.append('user_posvirtual', userposvirtual);
        formData.append('password_posvirtual', passwordposvirtual);
        formData.append('token_posvirtual', tokenposvirtual);
        formData.append('mid_posvirtual', midposvirtual);
        formData.append('cybs_posvirtual', cybsposvirtual);
        formData.append('prefixTable', prefixTable);

        $.ajax({
            url: SolicitudesAjaxPosVirtual.url,
            type: "post",
            dataType: 'json',
            data: {
                action: "PosVirtual_connect",
                nonce: SolicitudesAjaxPosVirtual.seguridad,
                user_posvirtual: userposvirtual,
                password_posvirtual: passwordposvirtual,
                token_posvirtual: tokenposvirtual,
                mid_posvirtual: midposvirtual,
                cybs_posvirtual: cybsposvirtual,
                prefixTable: prefixTable
            },
            success: function (data) {
                if (data) {
                    if (data.result === 'success') {
                        $(document).ready(function(){
                            $('#conexionact').foundation('open');
                        });

                        setTimeout(function () {
                            location.reload();
                        }, 1000 * 3)

                    } else {

                        $('#titleErrorResponse').text(data.title);
                        $('#messageErrorResponse').text(data.message);

                        if (data.code) {
                            $('#cnt-code-error').show('fast');
                            $('#codeError').text(data.code);
                        }

                        $(document).ready(function(){
                            $('#errorlogin').foundation('open');
                        });
                        botton.text("Probar Conexión");
                        botton.prop('disabled', false)
                    }
                }
            },
            error: function (error) {
                console.log(error.data, 'entro en el ERRO')
                $(document).ready(function () {
                    $('#errorlogin').foundation('open');
                });
                botton.text("Probar Conexión");
                botton.prop('disabled', false)

            }
        })
    }

    botton.click(function () {
        testConnection();
    });

    /**Eliminar los datos del usuario*/



    function disconnect(){
        var tableOptions = $('input[name="prefixTable"]').val();
        var formData = new FormData();
        formData.append('prefixTable', tableOptions);

        bottonDeconection.text("Eliminando datos...");
        bottonDeconection.prop('disabled', true)

        bottonModalDeconection.text("Eliminando datos...");
        bottonModalDeconection.prop('disabled', true)

        $.ajax({
            url: SolicitudesAjaxPosVirtual.url,
            type: "post",
            dataType : 'json',
            data: {
                action : "PosVirtual_disconnect",
                nonce : SolicitudesAjaxPosVirtual.seguridad,
                prefixTable : tableOptions
            },
            success: function (data) {
                if (data) {
                    if (data.result === 'success') {
                        location.reload();
                    } else if (data.result == 'error') {
                        bottonDeconection.text("Eliminar Conexión");
                        bottonDeconection.prop('disabled', false)
                        bottonModalDeconection.text("Eliminar Conexión");
                        bottonModalDeconection.prop('disabled', false)

                    }
                }
            },
            error: function (error){
                console.log(error.data, 'entro en el ERRO')
                $(document).ready(function(){
                    $('#errorlogin').foundation('open');
                });
                botton.text("Probar Conexión");
                botton.prop('disabled', false)

            }
        })
    }

    bottonDeconection.click(function () {
        disconnect();
    });

    bottonModalDeconection.click(function () {
        $(document).ready(function(){
            $('#salidasesion').foundation('open');
        });
    });

});
function showContent() {
    element = document.getElementById("showcuotas");
    check = document.getElementById("check");
    if (check.checked) {
        element.style.display='block';
    }
    else {
        element.style.display='none';
    }
}