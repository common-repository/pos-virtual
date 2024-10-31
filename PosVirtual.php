<?php

/**
 * Plugin Name:       POS Virtual
 * Plugin URI:        https://shopshop.com.gt/
 * Description:       Acepta pagos con tarjeta en WooCommerce con POS Virtual.
 * Version:           3.2
 * Requires at least: 6.0
 * Requires PHP:      7.0.0
 * Author:            ShopShop
 * Author URI:        https://shopshop.com.gt
 * License:           GPLv3 or later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see http://www.gnu.org/licenses/
 **/

define('PosVirtual_TRANSACTION_URL', 'https://adm.posvirtual.gt/api/transaction');
define('PosVirtual_LOGIN_URL', 'https://adm.posvirtual.gt/api/transaction/login');
define('PosVirtual_INSTALLMENT_URL', 'https://adm.posvirtual.gt/api/transaction/installment');


add_action('admin_menu', 'credenciales_POSVIRTAULSHOPSHOP_id_menu');

//js
if (!function_exists("loadJsDVPOSVIRTAULSHOPSHOP")) {
    function loadJsDVPOSVIRTAULSHOPSHOP($hook)
    {
//    echo "<script>console.log(`$hook`)</script>";
        if ($hook != "toplevel_page_configuracion-credenciales-PosVirtual") {
            return;
        }

        wp_enqueue_script('jsconnectionPosVirtual', plugins_url('js/connectionPosVirtual.js', __FILE__), array('jquery'));
        wp_enqueue_script('whatInput', plugins_url('js/vendor/what-input.js', __FILE__), array('jquery'));
        wp_enqueue_script('foundationPOS', plugins_url('js/vendor/foundation.js', __FILE__), array('jquery'));
        wp_enqueue_script('headerPOS', plugins_url('js/vendor/header.js', __FILE__), array('jquery'));

        wp_localize_script('jsconnectionPosVirtual',
            'SolicitudesAjaxPosVirtual',
            array(
                'url' => admin_url('admin-ajax.php'),
                'seguridad' => wp_create_nonce('segPosVirtual')
            ));
    }
}


add_action('admin_enqueue_scripts', 'loadJsDVPOSVIRTAULSHOPSHOP');

if (!function_exists("EncolarPOSVIRTAULSHOPSHOPCSS")) {
    function EncolarPOSVIRTAULSHOPSHOPCSS($hook)
    {
        if ($hook != "toplevel_page_configuracion-credenciales-PosVirtual") {
            return;
        }
        wp_enqueue_style('foundationBi', plugins_url('css/foundation.css', __FILE__));
        wp_enqueue_style('appBi', plugins_url('css/app.css', __FILE__));
        wp_enqueue_style('biCSS', plugins_url('css/PosVirtual.css', __FILE__));
        wp_enqueue_style('stylPOS', plugins_url('icon/style.css', __FILE__));
        wp_enqueue_style('stylPOS', plugins_url('fonts/Roboto/Roboto-Bold.ttf', __FILE__));
        wp_enqueue_style('customCss', plugins_url('css/cssCustom.css', __FILE__));
    }
}


add_action('admin_enqueue_scripts', 'EncolarPOSVIRTAULSHOPSHOPCSS');


//ajax
if (!function_exists("apiVcPosVirtualDJV")) {
    function apiVcPosVirtualDJV($jwt)
    {
        $jwtposvirtual = $jwt;

        $responseCurl = wp_remote_post(PosVirtual_INSTALLMENT_URL, array(
                'method' => 'POST',
                'timeout' => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking' => true,
                'headers' => array(
                    'Authorization' => 'Bearer ' . $jwtposvirtual,
                    'Content-Type' => 'multipart/form-data; boundary=---011000010111000001101001'
                ),
                'body' => "",
            )
        );

        if (is_wp_error($responseCurl)) {
            $error_message = $responseCurl->get_error_message();
            $response = [
                'result' => 'error',
                'title' => 'Algo no anda bien',
                'massage' => $error_message,
            ];

        } else {
            $decompress = json_decode($responseCurl['body']);
            $result = $decompress->result;
            $message = $decompress->message;

            if ($result == 'success') {
                $vcJson = json_encode($decompress->data);
                $vc = json_decode($vcJson);

                $response = [
                    'result' => 'success',
                    'vcJson' => $vcJson,
                    'vc' => $vc,
                ];
            } else {
                $response = [
                    'result' => $result,
                    'title' => 'Algo no anda bien',
                    'massage' => $message
                ];
            }
        }

        return $response;
    }
}

if (!function_exists("ConnectPosVirtualAjax")) {
    function ConnectPosVirtualAjax()
    {
        $nonce = $_POST['nonce'];
        if (!wp_verify_nonce($nonce, 'segPosVirtual')) {
            die('No tienes permisos para ejecutar ese ajax');
        }

        global $wpdb;

        $prefixTable = !empty(sanitize_text_field($_POST['prefixTable'])) ? sanitize_text_field($_POST['prefixTable']) : null;
        $userposvirtual = !empty(sanitize_email($_POST['user_posvirtual'])) ? sanitize_email($_POST['user_posvirtual']) : null;
        $passwordposvirtual = !empty(sanitize_text_field($_POST['password_posvirtual'])) ? sanitize_text_field($_POST['password_posvirtual']) : null;
        $passwordposvirtualHas = false;
        if ($passwordposvirtual) {
            $passwordposvirtualHas = sha1($passwordposvirtual);
        }

        $tokenposvirtual = !empty(sanitize_text_field($_POST['token_posvirtual'])) ? sanitize_text_field($_POST['token_posvirtual']) : null;

        $midposvirtual = !empty(sanitize_text_field($_POST['mid_posvirtual'])) ? sanitize_text_field($_POST['mid_posvirtual']) : null;
        $cybsposvirtual = !empty(sanitize_text_field($_POST['cybs_posvirtual'])) ? sanitize_text_field($_POST['cybs_posvirtual']) : null;

        $response = [
            'result' => 'error',
            'title' => 'Algo no anda bien',
        ];

        if ($userposvirtual && $passwordposvirtualHas && $tokenposvirtual) {

            $datosPost = "-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"user\"\r\n\r\n$userposvirtual\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"pass\"\r\n\r\n$passwordposvirtualHas\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"token\"\r\n\r\n$tokenposvirtual\r\n-----011000010111000001101001--\r\n";

            $responseCurl = wp_remote_post(PosVirtual_LOGIN_URL, array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(
                        'Content-Type' => 'multipart/form-data; boundary=---011000010111000001101001'
                    ),
                    'body' => $datosPost,
                )
            );

            if (is_wp_error($responseCurl)) {
                $error_message = $responseCurl->get_error_message();
                $response = [
                    'result' => 'error',
                    'title' => 'Algo no anda bien',
                    'massage' => $error_message,
                ];
            } else {
                $decompress = json_decode($responseCurl['body']);
                $result = $decompress->result;
                $message = $decompress->message;
                if ($result == 'success') {
                    if (!empty(get_option('timezone_string'))) {
                        date_default_timezone_set(get_option('timezone_string'));
                    }

                    $jwtposvirtual = $decompress->jwt;
                    $responseVc = apiVcPosVirtualDJV($jwtposvirtual);

                    $table = $prefixTable . 'options';

                    $enterpriseData = $decompress->data->enterprise;
                    $nameEnterprise = $enterpriseData->name;
                    $currencyCode = $enterpriseData->currencie->code;
                    switch ($currencyCode) {
                        case 'GTQ':
                            $currencyCode = 'Quetzales';
                            break;
                        case 'USD':
                            $currencyCode = 'Dólares';
                            break;
                    }
                    $currencyEnterprise = $enterpriseData->currencie->symbol . ' ' . $currencyCode;
                    $activationDate = date("d/m/Y H:i:s");

                    //validación para saber si ya existe fecha de creación
                    $sqlCreation = "SELECT * FROM $table WHERE option_name LIKE 'creation_date_posvirtual'";
                    $creation = $wpdb->get_results($sqlCreation);

                    if (empty($creation)) {
                        $creationDate = date("d/m/Y H:i:s");

                        $wpdb->insert($table, array(
                            'option_name' => 'creation_date_posvirtual',
                            'option_value' => $creationDate,
                            'autoload' => 'yes'
                        ));
                    }

                    $wpdb->insert($table, array(
                        'option_name' => 'user_posvirtual',
                        'option_value' => $userposvirtual,
                        'autoload' => 'yes'
                    ));

                    $wpdb->insert($table, array(
                        'option_name' => 'password_posvirtual',
                        'option_value' => $passwordposvirtual,
                        'autoload' => 'yes'
                    ));
                    $wpdb->insert($table, array(
                        'option_name' => 'token_posvirtual',
                        'option_value' => $tokenposvirtual,
                        'autoload' => 'yes'
                    ));
                    $wpdb->insert($table, array(
                        'option_name' => 'jwt_posvirtual',
                        'option_value' => $jwtposvirtual,
                        'autoload' => 'yes'
                    ));

                    $wpdb->insert($table, array(
                        'option_name' => 'mid_posvirtual',
                        'option_value' => $midposvirtual,
                        'autoload' => 'yes'
                    ));
                    $wpdb->insert($table, array(
                        'option_name' => 'environment_dev_cyb_posvirtual',
                        'option_value' => $cybsposvirtual,
                        'autoload' => 'yes'
                    ));

                    $wpdb->insert($table, array(
                        'option_name' => 'name_enterprise_posvirtual',
                        'option_value' => $nameEnterprise,
                        'autoload' => 'yes'
                    ));
                    $wpdb->insert($table, array(
                        'option_name' => 'currency_posvirtual',
                        'option_value' => $currencyEnterprise,
                        'autoload' => 'yes'
                    ));
                    $wpdb->insert($table, array(
                        'option_name' => 'activation_date_posvirtual',
                        'option_value' => $activationDate,
                        'autoload' => 'yes'
                    ));

                    if ($responseVc['result'] == 'success') {
                        $wpdb->insert($table, array(
                            'option_name' => 'vc_json_posvirtual',
                            'option_value' => $responseVc['vcJson'],
                            'autoload' => 'yes'
                        ));
                    }

                    $response = [
                        'result' => 'success',
                        'title' => 'Conexión Exitosa',
                        'massage' => $message
                    ];

                } else {

                    $response = [
                        'result' => $result,
                        'title' => 'Algo no anda bien',
                        'massage' => $message
                    ];
                }
            }
        }

        echo json_encode($response);
        die();
    }
}

add_action('wp_ajax_PosVirtual_connect', 'ConnectPosVirtualAjax');

if (!function_exists("DisConnectPosVirtualAjax")) {

    function DisConnectPosVirtualAjax()
    {
        $nonce = $_POST['nonce'];
        if (!wp_verify_nonce($nonce, 'segPosVirtual')) {
            die('No tienes permisos para ejecutar ese ajax');
        }
        global $wpdb;
        $prefixTable = !empty(sanitize_text_field($_POST['prefixTable'])) ? sanitize_text_field($_POST['prefixTable']) : null;

        $table = $prefixTable . 'options';

        $wpdb->delete($table, array('option_name' => 'user_posvirtual'));
        $wpdb->delete($table, array('option_name' => 'password_posvirtual'));
        $wpdb->delete($table, array('option_name' => 'token_posvirtual'));
        $wpdb->delete($table, array('option_name' => 'jwt_posvirtual'));
        $wpdb->delete($table, array('option_name' => 'vc_json_posvirtual'));

        $wpdb->delete($table, array('option_name' => 'mid_posvirtual'));
        $wpdb->delete($table, array('option_name' => 'environment_dev_cyb_posvirtual'));

        $wpdb->delete($table, array('option_name' => 'name_enterprise_posvirtual'));
        $wpdb->delete($table, array('option_name' => 'currency_posvirtual'));
        $wpdb->delete($table, array('option_name' => 'activation_date_posvirtual'));

        $results = [
            'result' => 'success',
            'title' => 'Datos Eliminados'
        ];


        echo json_encode($results);
        die();
    }
}

add_action('wp_ajax_PosVirtual_disconnect', 'DisConnectPosVirtualAjax');

// Crear WordPress admin menu
if (!function_exists("credenciales_POSVIRTAULSHOPSHOP_id_menu")) {
    function credenciales_POSVIRTAULSHOPSHOP_id_menu()
    {

        $page_title = 'POS Virtual';
        $menu_title = 'POS Virtual';
        $capability = 'manage_options';
        $menu_slug = 'configuracion-credenciales-PosVirtual';
        $function = 'credenciales_posvirtual_page';
        $icon_url = 'dashicons-money-alt';
        $position = 100;

        add_menu_page($page_title,
            $menu_title,
            $capability,
            $menu_slug,
            $function,
            $icon_url,
            $position);

        add_action('admin_init', 'update_credenciales_PosVirtual_v2');
    }
}

// Crear funcion para agregar datos a la bd
if (!function_exists("update_credenciales_PosVirtual_v2")) {
    function update_credenciales_PosVirtual_v2()
    {
        global $wpdb;

        $sqlUser = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'user_posvirtual'";
        $user = $wpdb->get_results($sqlUser);
        if (empty($user))
            register_setting('credenciales_id-settings', 'user_posvirtual');

        $sqlPass = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'password_posvirtual'";
        $pass = $wpdb->get_results($sqlPass);
        if (empty($pass))
            register_setting('credenciales_id-settings', 'password_posvirtual');

        $sqlToken = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'token_posvirtual'";
        $token = $wpdb->get_results($sqlToken);
        if (empty($token))
            register_setting('credenciales_id-settings', 'token_posvirtual');

        $sqlJwt = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'jwt_posvirtual'";
        $jwt = $wpdb->get_results($sqlJwt);
        if (empty($jwt))
            register_setting('credenciales_id-settings', 'jwt_posvirtual');

        $sqlMid = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'mid_posvirtual'";
        $mid = $wpdb->get_results($sqlMid);
        if (empty($mid))
            register_setting('credenciales_id-settings', 'mid_posvirtual');

        $sqlCybs = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'environment_dev_cyb_posvirtual'";
        $cybs = $wpdb->get_results($sqlCybs);
        if (empty($cybs))
            register_setting('credenciales_id-settings', 'environment_dev_cyb_posvirtual');

        $sqlVC = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'vc_json_posvirtual'";
        $cv = $wpdb->get_results($sqlVC);
        if (empty($cv))
            register_setting('credenciales_id-settings', 'vc_json_posvirtual');

        $sqlEnterprise = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'name_enterprise_posvirtual'";
        $enterprise = $wpdb->get_results($sqlEnterprise);
        if (empty($enterprise))
            register_setting('credenciales_id-settings', 'name_enterprise_posvirtual');

        $sqlCurrency = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'currency_posvirtual'";
        $currency = $wpdb->get_results($sqlCurrency);
        if (empty($currency))
            register_setting('credenciales_id-settings', 'currency_posvirtual');

        $sqlActivation = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'activation_date_posvirtual'";
        $activation = $wpdb->get_results($sqlActivation);
        if (empty($activation))
            register_setting('credenciales_id-settings', 'activation_date_posvirtual');

        $sqlCreation = "SELECT * FROM $wpdb->options WHERE option_name LIKE 'creation_date_posvirtual'";
        $creation = $wpdb->get_results($sqlCreation);
        if (empty($creation))
            register_setting('credenciales_id-settings', 'creation_date_posvirtual');
    }
}
if (!function_exists('printJsonDJPOS')) {
    function printJsonDJPOS($array, $die = true, $title = '')
    {
        echo "<pre>\n";
        if ($title) echo esc_html($title) . "\n\n";
        print_r(json_decode(json_encode($array)));
        echo "</pre>\n";
        if ($die) die();
    }
}

if (!function_exists('get_product_by_id_POSVIRTAULSHOPSHOP')) {
    function get_product_by_id_POSVIRTAULSHOPSHOP($id)
    {
        global $wpdb;

        $metakey = 'Meta key';
        $metavalue = "Meta Value";

        $posts = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_title, ID FROM $wpdb->posts WHERE ID LIKE '$id'",
                $metakey,
                $metavalue,
                10
            )
        );

        $productSku = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->postmeta WHERE post_id LIKE '$id' and meta_key like '_sku'",
                $metakey,
                $metavalue,
                10
            ));


        $productPrice = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $wpdb->postmeta WHERE post_id LIKE '$id' and meta_key like '_price'",
            $metakey,
            $metavalue,
            10
        ));

        $results = [
            'product' => $posts,
            'productSku' => $productSku,
            'productPrice' => $productPrice,
        ];

        return $results;
    }

}


// Crear pagina plugin
if (!function_exists("credenciales_posvirtual_page")) {

    function credenciales_posvirtual_page()
    {
        global $wpdb;
//        printJsonDJPOS($wpdb->prefix);
        /**DIFINICOIN DE LA TABLA DE OPCITONS*/
        $tableOfOptions = $wpdb->options;
        ?>
        <div class="warp">
            <div class="ebibackground"></div>
            <?php
            /*** CONEXIÓN CON posvirtual ***/

            ?>
            <?php if (!empty(get_option('jwt_posvirtual'))): ?>
                <?php
                //                $urlDomain = $_SERVER['HTTP_ORIGIN'];
                $optionsHTML = '';
                $activeVC = false;

                $optionsVCJson = get_option('vc_json_posvirtual');
                $optionsVC = json_decode($optionsVCJson);

                if (!empty($optionsVC)) {
                    foreach ($optionsVC as $vc) {
                        if ($vc->name != 'Contado') {
                            $activeVC = true;
                            if ($vc->code != 'LU') {
                                $optionsHTML .= substr($vc->name, 0, 2) . " ";
                            } else {
                                $optionsHTML .= $vc->name . " ";
                            }

                        }
                    }
                }
                ?>
                <!--- login ---->
                <div class="row">
                    <div class="large-12">
                        <br><br>
                        <img class="imgprincipallogo" src="<?php echo plugins_url('/img/logo-shopshop.png', __FILE__) ?>">
                        <!-- <h1>PORTALMCIs</h1> -->
                    </div>
                </div>

                <div class="columns large-12">
                    <div class="row">
                        <div class=" columns large-6 large-centered medium-8 medium-centered small-11 small-centered">
                            <div class="backcolorlogin3">
                                <!-- inicar sesion -->

                                <!-- inputs login  -->


                                <div class="form-special">
                                    <div class="row">
                                        <div class="columns large-9 large-centered medium-9 medium-centered small-10 small-centered">
                                            <label class="colortitlesucces"><span class="icon-Cheque"></span> Empresa conectada
                                            </label>
                                        </div>
                                    </div>

                                    <br>
                                    <div class="row">
                                        <div class="columns large-9 large-centered medium-9 medium-centered small-10 small-centered">
                                            <label class="colortitles">Nombre </label>
                                            <label class="colortext"><?php echo esc_html(get_option('name_enterprise_posvirtual')) ?></label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="columns large-9 large-centered medium-9 medium-centered small-10 small-centered">
                                            <label class="colortitles">Usuario</label>
                                            <label class="colortext"><?php echo esc_html(get_option('user_posvirtual')) ?></label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="columns large-9 large-centered medium-9 medium-centered small-10 small-centered">
                                            <label class="colortitles">Moneda</label>
                                            <label class="colortext"><?php echo esc_html(get_option('currency_posvirtual')) ?></label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="columns large-9 large-centered medium-9 medium-centered small-10 small-centered">
                                            <label class="colortitles">Tipo de pago</label>

                                            <div class="columns large-12  medium-12 small-12 ">

                                                <div class="columns large-12 ">
                                                    <label class="checkbox">Contado
                                                        <input type="checkbox" checked disabled>
                                                        <span class="check"></span>
                                                    </label>
                                                </div>

                                                <div class="columns large-12 medium-12 small-12">
                                                    <label class="checkbox">Cuotas
                                                        <input type="checkbox" name="check" id="check" value="1"
                                                               onchange="javascript:showContent()" <?php if ($activeVC): ?> checked <?php endif ?> disabled/>
                                                        <span class="check"></span>
                                                    </label>


                                                    <div <?php if (!$activeVC): ?> id="showcuotas" <?php endif ?>>
                                                        <div class="columns large-9  medium-9 small-12 ">
                                                            <label class="colortextplazos">Plazos disponibles</label>
                                                            <div class="row">
                                                                <div class="columns large-10 medium-12 small-12">
                                                                    <label class="colorcuotasactivas"><?php echo esc_html($optionsHTML); ?></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="columns large-9 large-centered medium-9 medium-centered small-10 small-centered">
                                            <label class="colortitles">Fecha de activación</label>
                                            <label class="colortext"><?php echo esc_html(get_option('activation_date_posvirtual')) ?></label>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="columns large-9 large-centered medium-9 medium-centered small-10 small-centered">
                                            <label class="colortitles">Fecha de creación</label>
                                            <label class="colortext"><?php echo esc_html(get_option('creation_date_posvirtual')) ?></label>
                                        </div>
                                    </div>
                                </div>

                                <!-- button iniciar sesion -->
                                <div class="row">
                                    <div class=" large-6 large-centered medium-6 small-10 small-centered">
                                        <center>
                                            <input type="hidden" name="prefixTable" value="<?php echo esc_html($wpdb->prefix); ?>">
                                            <button class="hollow button expanded desactive" id="btn-desconnection"> Desactivar
                                                Credenciales
                                            </button>
                                        </center>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- texto con link -->
                <div class="row">
                    <div class="columns large-8 large-centered ">

                        <center><label class="derechos"> <?php echo date("Y"); ?>. <a class="links" href="https://shopshop.com.gt/" target="_blank"><b><u>ShopShop</u></b> </a> </label>
                        </center>
                    </div>
                </div>
                <div class="row">
                    <div class="large-12 columns hide-for-small-only">

                        <img class="leftbotom" src="<?php echo plugins_url('/img/fondo-logo.png', __FILE__) ?>">
                    </div>
                </div>

                <!-- MODALES -->
                <!-- <div>
                  <p><button class="button" data-open="exampleModal3">Modal1</button>
                </div> -->

                <div class="reveal" id="salidasesion" data-reveal>
                    <h1 class="modaltitle3">¿Estás seguro que deseas desactivar tus credenciales?</h1>
                    <h2 class="modaltexto3">Al desactivarlas PosVirtual no podrá procesar transacciones en tu página web.</h2>

                    <div class="row">
                        <div class="small-9 small-centered medium-6 medium-centered large-6 large-centered columns ">
                            <button data-close id="btncancelar" class="hollow button expanded">Cancelar</button>

                            <button data-close id="btndesactivar" class="hollow button sinfondo expanded hover">Desactivar
                                credenciales
                            </button>
                        </div>
                    </div>

                    <button class="close-button" data-close aria-label="Close modal" type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>


                <!-- FIN MODALES -->
            <?php else: ?>
                <div id="cnt-form-test-connection">

                    <?php
                    //                $urlDomain = $_SERVER['HTTP_ORIGIN'];
                    $optionsHTML = '';

                    $optionsVCJson = get_option('vc_json_posvirtual');
                    $optionsVC = json_decode($optionsVCJson);

                    if (!empty($optionsVC)) {
                        foreach ($optionsVC as $vc) {
                            $optionsHTML .= "<li>$vc->name</li>";
                        }
                    }
                    ?>
                    <div class="row">
                        <div class="large-12">
                            <br>
                            <img class="imgprincipallogo" src="<?php echo plugins_url('/img/logo-shopshop.png', __FILE__) ?>">
                        </div>
                    </div>

                    <div class="columns large-12">
                        <div class="row">
                            <div class=" columns large-6 large-centered medium-8 medium-centered small-11 small-centered">
                                <div class="backcolorlogin">
                                    <!-- inicar sesion -->
                                    <div class="row">
                                        <div class="large-10 large-centered small-6 small-centered">
                                            <center><label class="titlehelp">¿Necesitas ayuda? Visita nuestro centro de
                                                    soporte <a class="links"
                                                               href="https://shopshop.com.gt/" target="_blank"><b><u class="linktitle"> aquí</u></b>
                                                    </a></label>
                                        </div>
                                    </div>

                                    <div class="backcolorlogin2">
                                        <!-- inputs login  -->
                                        <form data-abide novalidate name="form_connection">
                                            <?php
                                            settings_fields('credenciales_id-settings');
                                            do_settings_sections('credenciales_id-settings');
                                            ?>
                                            <div class="form-special">
                                                <input type="hidden" name="prefixTable" value="<?php echo esc_html($wpdb->prefix); ?>">
                                                <br>
                                                <div class="row">
                                                    <div class="columns large-9 large-centered medium-8 medium-centered small-10 small-centered">

                                                        <label class="colortitles" for="user_posvirtual">Usuario <span data-tooltip class="right" tabindex="2" title="Deberás colocar el usuario (correo electrónico) con el cual te registraste en POS Virtual.">
                                                        <span class="icon-tooltip"></span>
                                                    </span>
                                                            <input type="text" placeholder=" " class="box" required="" id="user_posvirtual" name="user_posvirtual_s1"/>
                                                            <span class="form-error">
                                                        Ingresa tu usuario.
                                                      </span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="columns large-9 large-centered medium-8 medium-centered small-10 small-centered">
                                                        <label class="colortitles" for="password_posvirtual">Password <span data-tooltip class="right" tabindex="2" title="Deberás colocar tu contraseña de tu usuario registrado en POS Virtual.">
                                                        <span class="icon-tooltip"></span>
                                                    </span>
                                                            <input type="password" id="password_posvirtual"
                                                                   name="password_posvirtual_s1" placeholder=" " class="box" required=""/>
                                                            <span class="form-error">
                                                            Ingresa tu password.
                                                          </span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="columns large-9 large-centered medium-8 medium-centered small-10 small-centered">
                                                        <label class="colortitles" for="token_posvirtual">Token <span data-tooltip class="right" tabindex="2" title="Coloca el token que recibiste por correo al momento de afiliarte a POS Virtual.">
                                                        <span class="icon-tooltip"></span>
                                                    </span>
                                                            <input type="text" id="token_posvirtual"
                                                                   name="token_posvirtual_s1" placeholder=" " class="box numero" required=""/>
                                                            <span class="form-error">
                                                            Ingresa tu token.
                                                        </span>
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="columns large-9 large-centered medium-8 medium-centered small-10 small-centered">
                                                        <label class="colortitles" for="mid_posvirtual">Mid <span data-tooltip class="right" tabindex="2" title="Este campo deberás completarlo únicamente si tus credenciales de afiliación son en Cybersource. Nombre de usuario es el ID de la organización. Ejemplo: visanet_nombreempresa">
                                                            <span class="icon-tooltip"></span>
                                                        </span>
                                                            <input type="text" id="mid_posvirtual" name="mid_posvirtual_s1" placeholder=" " class="box numero" required=""/>
                                                            <span class="form-error">
                                                        Ingresa el mid.
                                                      </span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="columns large-9 large-centered medium-8 medium-centered small-10 small-centered">
                                                        <label class="colorloginuspas" for="cybs_posvirtual">Entorno <span data-tooltip class="right" tabindex="2" title="Selecciona el entorno en el que fueron emitidas tus credenciales. Si tienes dudas por favor comunícate con tu ejecutivo a cargo.">
                                                            <span class="icon-tooltip"></span>
                                                        </span>
                                                            <select type="option" id="cybs_posvirtual" name="cybs_posvirtual_s1" required="" aria-describedby="56p77u-abide-error">
                                                                <option type="option" value="" selected>Selecciona</option>
                                                                <option type="option" value="SI">Pruebas</option>
                                                                <option type="option" value="NO">Producción</option>
                                                            </select>
                                                            <span class="form-error">
                                                            Selecciona el entorno.
                                                          </span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- button iniciar sesion -->
                                            <div class="row">
                                                <div class=" large-6 large-centered medium-6 small-6 small-centered">
                                                    <center>
                                                        <button class="hollow button expanded"
                                                                id="btn-test-connection"> Activar Credenciales
                                                        </button>
                                                    </center>
                                                </div>
                                            </div>

                                            <!-- Logo de Tarjeta-->
                                            <div class="row">
                                                <div class=" large-8 large-centered small-10 small-centered">
                                                    <center><img class="logotarjetas"
                                                                 src="<?php echo plugins_url('/img/logos-tarjetas-mayor_fondo.jpeg', __FILE__) ?>">
                                                    </center>
                                                </div>
                                            </div>
                                    </div>
                                    </form>
                                </div>
                                <!---fin login ---->


                                <!-- texto con link -->
                                <div class="row">
                                    <div class="columns large-8 large-centered ">

                                        <center><label class="derechos"> 2022. <a class="links" href="https://shopshop.com.gt/" target="_blank"><b><u>ShopShop</u></b> </a>
                                            </label></center>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="large-12 columns hide-for-small-only">

                                        <img class="leftbotom" src="<?php echo plugins_url('/img/fondo-logo.png', __FILE__) ?>">
                                    </div>
                                </div>

                                <!-- MODALES -->

                                <div class="reveal" id="errorlogin" data-reveal>
                                    <span class="icon-Cancelar iconosalerta1"></span>
                                    <h1 class="modaltitle" id="titleErrorResponse">Credenciales inválidas</h1>
                                    <h2 class="modaltexto" id="messageErrorResponse">Los datos ingresados son
                                        incorrectos</h2>
                                    <h3 class="modalinfo" id="cnt-code-error" style="display: none">Error: <span
                                                id="codeError">12345</span></h3>
                                    <button class="close-button" data-close aria-label="Close modal" type="button">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="reveal" id="conexionact" data-reveal>
                                    <span class="icon-confirmacion iconosalerta2"></span>
                                    <h1 class="modaltitle2">Conexión activada</h1>
                                    <h2 class="modaltexto2">¡Autenticación exitosa! pos virtual ya se encuentra activo en tu
                                        sitio web.</h2>
                                    <button class="close-button" data-close aria-label="Close modal" type="button">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>


                                <!-- FIN MODALES -->

                                <footer>
                                    <div class="row">
                                        <div class="columns large-12 medium-8 large-centered small-8 small-centered ">
                                            <br><br><br>
                                            <center><label class="lblinferior"> ¿Aún no cuentas con credenciales?
                                                    <a class="links"
                                                       href="https://shopshop.com.gt/" target="_blank"><b><u> Solicítalas Aquí.</u></b> </a> </label></center>
                                        </div>
                                    </div>
                                </footer>

                            </div>
                        </div>
                    </div>
                    <br>
                    <br>
                </div>
            <?php endif ?>
        </div>
        <?php

    }
}
// estamos diciendo a WC que exsite nuestra clase
function wc_POSVIRTAULSHOPSHOP_add_to_gateways($gateways)
{
    $gateways[] = 'Shopshop_posvirtual_POSVIRTAULSHOPSHOP';

    return $gateways;
}

add_filter('woocommerce_payment_gateways', 'wc_POSVIRTAULSHOPSHOP_add_to_gateways');


add_action('plugins_loaded', 'wc_POSVIRTAULSHOPSHOP_gateway_init', 11);

function wc_POSVIRTAULSHOPSHOP_gateway_init()
{
    // hacemos que nuestra clase extienda de la clase WC_Payment_Gateway ya que contiene methods impotantes
    class Shopshop_posvirtual_POSVIRTAULSHOPSHOP extends WC_Payment_Gateway
    {
        /**
         * Constructor for the gateway.
         */
        // en nuestro constructor necesitamos definir las siguientes variables
        public function __construct()
        {

            $this->id = 'PLUGIN_gateway_posvirtual';
            $this->icon = apply_filters('woocommerce_PLUGIN_icon', '');
            $this->has_fields = true;
            $this->method_title = __('POS Virtual', 'payment-online');
            $this->method_description = __('Acepta pagos con tarjeta en WooCommerce con POS Virtual', 'payment-online');

            // Load the settings. cargando compos de configuiracion
            $this->init_form_fields();
            $this->init_settings();

            // Define user set variables, estamos establesiendo los datos
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->instructions = $this->get_option('instructions', $this->description);

            // Actions Agregamos un enlace para guardar la configuración
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_thankyou_' . $this->id, array($this, 'thankyou_page'));

            // Customer Emails
            add_action('woocommerce_email_before_order_table', array($this, 'email_instructions'), 10, 3);
        }

        /**
         * Initialize Gateway Admin Area Settings Form Fields
         */
        // establesemos $this->form_fields

        public function init_form_fields()
        {

            $this->form_fields = apply_filters('wc_PLUGIN_form_fields', array(

                'enabled' => array(
                    'title' => __('Habilitar/deshabilitar', 'payment-online'),
                    'type' => 'text',
                    'label' => __('Habilitar pago por Visacuotas', 'payment-online'),
                    'default' => 'yes'
                ),

                'title' => array(
                    'title' => __('Titulo', 'payment-online'),
                    'type' => 'text',
                    'description' => __('Esto controla el título del método de pago que el cliente ve durante el pago.', 'payment-online'),
                    'default' => __('Pago con tarjeta', 'payment-online'),
                    'desc_tip' => true,
                ),

                'description' => array(
                    'title' => __('Descripción', 'payment-online'),
                    'type' => 'textarea',
                    'description' => __('Descripción del método de pago que el cliente verá en su pago.', 'payment-online'),
                    'default' => __('', 'payment-online'),
                    'desc_tip' => true,
                ),

                'instructions' => array(
                    'title' => __('Instrucciones', 'payment-online'),
                    'type' => 'textarea',
                    'description' => __('Instrucciones que se agregarán a la página de agradecimiento y correos electrónicos.', 'payment-online'),
                    'default' => '',
                    'desc_tip' => true,
                ),

            ));
        }


        /**
         * Output for the order received page.
         */
        public function thankyou_page()
        {
            if ($this->instructions) {
                echo wpautop(wptexturize($this->instructions));
            }
        }

        /**
         * Add content to the WC emails.
         *
         * @access public
         *
         * @param WC_Order $order
         * @param bool $sent_to_admin
         * @param bool $plain_text
         */
        public function email_instructions($order, $sent_to_admin, $plain_text = false)
        {
            if ($this->instructions && !$sent_to_admin && $this->id === $order->payment_method && $order->has_status('on-hold')) {
                echo wpautop(wptexturize($this->instructions)) . PHP_EOL;
            }
        }

        /**
         * Process the payment and return the result
         *
         * @param int $order_id
         *
         * @return array
         */

        public function payment_fields()
        {
            if ($this->supports('tokenization') && is_checkout()) {
                $this->tokenization_script();
                $this->saved_payment_methods();
                $this->form();
                $this->save_payment_method_text();
            } else {
                $this->form();
            }
        }

        /**
         * Output field name HTML
         *
         * Gateways which support tokenization do not require names - we don't want the data to post to the server.
         *
         * @param string $name
         *
         * @return string
         * @since  2.6.0
         */
        public function field_name($name)
        {
            return $this->supports('tokenization') ? '' : ' name="' . esc_attr($this->id . '-' . $name) . '" ';
        }

        /**
         * Outputs fields for entering credit card information.
         */

        private function getAutoincrement()
        {
            $use = 500000;
            global $wpdb;
            global $table_prefix;

            $table = $table_prefix . 'options';
            //validamos que exista el campo autoincrement_POS Virtual
            $existAutoIncremental = "SELECT * FROM `$table` WHERE option_name LIKE 'autoincrement_posvirtual'";
            $existAutoIncrementalQuery = $wpdb->get_results($existAutoIncremental);

            if ($existAutoIncrementalQuery) {
                $autoincrementCurrent = (int)$existAutoIncrementalQuery[0]->option_value;
                if ($autoincrementCurrent < 1000000) {
                    $autoincrementCurrent++;
                    $wpdb->update($table, array('option_value' => $autoincrementCurrent), array('option_name' => 'autoincrement_posvirtual'));

                    $newIncremental = "SELECT * FROM $table WHERE option_name LIKE 'autoincrement_posvirtual'";
                    $resultNewIncremental = $wpdb->get_results($newIncremental);
                    $use = $resultNewIncremental[0]->option_value;
                } else {
                    $wpdb->update($table, array('option_value' => $use), array('option_name' => 'autoincrement_posvirtual'));
                }
            } else {
                //si no existe el campo autoincrement_posvirtual
                $wpdb->insert($table, array(
                    'option_name' => 'autoincrement_posvirtual',
                    'option_value' => $use,
                    'autoload' => 'yes'
                ));
            }

            return $use;
        }

        public function form()
        {
//            echo esc_attr($this->description);

            wp_enqueue_script('wc-credit-card-form');

            $fields = array();
            $optionsHTML = '';

            $optionsVCJson = get_option('vc_json_posvirtual');
            $optionsVC = json_decode($optionsVCJson);

            if (!empty($optionsVC)) {
                foreach ($optionsVC as $vc) {
                    $optionsHTML .= "<option value='$vc->code'>$vc->name</option>";
                }
            }

            $vcq_field = '<p class="form-row form-row-wide"> <br>
                <label for="' . esc_attr($this->id) . '-card-type">' . __('Elige tus cuotas', 'woocommerce') . ' <span class="required">*</span></label>
                <select style="width: 100% !important;" onchange="gt_alarma()" id="' . esc_attr($this->id) . '-vcq" class="input-text wc-credit-card-form-card-type" ' . $this->field_name('vcq') . ' >
                ' . $optionsHTML . '
                </select>
                </p>';
            $cvc_field = '<p class="form-row form-row-last">
                <label for="' . esc_attr($this->id) . '-card-cvc">' . esc_html__('Código', 'woocommerce') . ' <span class="required">*</span></label>
                <input id="' . esc_attr($this->id) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc" inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="password" maxlength="4" placeholder="' . esc_attr__('CVC', 'woocommerce') . '" ' . $this->field_name('card-cvc') . ' style="width: 100% !important;" />
                </p>';

            $default_fields = array(
                'card-name-field' => '<p class="form-row form-row-wide"> <br>
                <label for="' . esc_attr($this->id) . '-card-name">' . esc_html__('Nombre en tarjeta', 'woocommerce') . ' <span class="required">*</span></label>
                <input id="' . esc_attr($this->id) . '-card-name" class="input-text wc-credit-card-form-card-name" inputmode="text" autocomplete="cc-name" autocorrect="no" autocapitalize="no" spellcheck="no" type="text" placeholder="Nombre en tarjeta" ' . $this->field_name('card-name') . ' />
                </p>',
                'card-number-field' => '<p class="form-row form-row-wide"> <br>
                <label for="' . esc_attr($this->id) . '-card-number">' . esc_html__('Número en tarjeta', 'woocommerce') . ' <span class="required">*</span></label>
                <input id="' . esc_attr($this->id) . '-card-number" class="input-text wc-credit-card-form-card-number" inputmode="numeric" autocomplete="cc-number" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" ' . $this->field_name('card-number') . ' />
                </p>',
                'card-expiry-field' => '<p class="form-row form-row-first">
                <label for="' . esc_attr($this->id) . '-card-expiry">' . esc_html__('Vence', 'woocommerce') . ' <span class="required">*</span></label>
                <input id="' . esc_attr($this->id) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" inputmode="numeric" autocomplete="cc-exp" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="' . esc_attr__('MM/YY', 'woocommerce') . '" ' . $this->field_name('card-expiry') . '  maxlength="7" />
                </p>',
            );

            if (!$this->supports('credit_card_form_cvc_on_saved_method')) {
                $default_fields['card-cvc-field'] = $cvc_field;
            }
            $fields = wp_parse_args($fields, apply_filters('woocommerce_credit_card_form_fields', $default_fields, $this->id));
            ?>

            <fieldset>
                <?php

                $mid = !empty(get_option('mid_posvirtual')) ? get_option('mid_posvirtual') : false;
                $scriptToUse = false;

                $autoIncrement = self::getAutoincrement();
                //                $inputAutoIncrement = '<input id="' . esc_attr($this->id) . '-autoincrement-to-use" type="hidden" value="' . $autoIncrement . '" ' . $this->field_name('autoincrement-to-use') . '/>';

                if ($mid):
                    $useInputIncremente = true;
                    define("POSTMERCHANTID", $mid);
                    if (get_option('environment_dev_cyb_posvirtual') == 'SI') {
                        define("POS_ORGID", "45ssiuz3");
                    } else {
                        define("POS_ORGID", "9ozphlqx");
                    }

                    define("POS_UID", $autoIncrement);

                    $urlJsCyberSourceEbipay = 'https://h.online-metrix.net/fp/tags.js?org_id=' . POS_ORGID . '&' . 'session_id=' . POSTMERCHANTID . POS_UID;

//
                    echo wp_get_inline_script_tag(
                        '',
                        array(
                            'id' => 'cybersourseEbipay-js-header',
                            'src' => $urlJsCyberSourceEbipay,
                            'type' => 'text/javascript'
                        )
                    );
                    ?>
                <?php
                endif;
                ?>
                <?php do_action('woocommerce_credit_card_form_start', $this->id); ?>
                <?php
                $scriptCleanFields = plugins_url('js/actions_pos_virtual_form.js', __FILE__);
                echo wp_get_inline_script_tag(
                    '',
                    array(
                        'id' => 'actions_pos_virtual_form',
                        'src' => $scriptCleanFields,
                        'type' => 'text/javascript'
                    )
                );
                echo '<input id="' . esc_attr($this->id) . '-autoincrement-to-use" type="hidden" value="' . esc_attr($autoIncrement) . '" ' . $this->field_name('autoincrement-to-use') . '/>';
                echo '<img src="' . plugins_url('/img/logo-posvirtual.png', __FILE__) . '" alt="POS" width="100%" style=" max-width: 325px; margin: auto; margin-top: 15px; display: block; max-height: 173px !important;">';

                //                foreach ($fields as $field) {
                //                    echo esc_html__($field, 'woocommerce');
                //                }
                echo '<p class="form-row form-row-wide"> <br>
                <label for="' . esc_attr($this->id) . '-card-name">' . esc_html__('Nombre en tarjeta', 'woocommerce') . ' <span class="required">*</span></label>
                <input id="' . esc_attr($this->id) . '-card-name" class="input-text wc-credit-card-form-card-name" inputmode="text" autocomplete="cc-name" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="Nombre en tarjeta" ' . $this->field_name('card-name') . ' />
                </p>';
                echo '<p class="form-row form-row-wide"> <br>
                <label for="' . esc_attr($this->id) . '-card-number">' . esc_html__('Número en tarjeta', 'woocommerce') . ' <span class="required">*</span></label>
                <input id="' . esc_attr($this->id) . '-card-number" class="input-text wc-credit-card-form-card-number" inputmode="numeric" autocomplete="cc-number" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;" ' . $this->field_name('card-number') . ' />
                </p>';
                echo '<p class="form-row form-row-first">
                <label for="' . esc_attr($this->id) . '-card-expiry">' . esc_html__('Vence', 'woocommerce') . ' <span class="required">*</span></label>
                <input id="' . esc_attr($this->id) . '-card-expiry" class="input-text wc-credit-card-form-card-expiry" inputmode="numeric" autocomplete="cc-exp" autocorrect="no" autocapitalize="no" spellcheck="no" type="tel" placeholder="' . esc_attr__('MM/YY', 'woocommerce') . '" ' . $this->field_name('card-expiry') . '  maxlength="7" />
                </p>';
                echo '<p class="form-row form-row-last">
                <label for="' . esc_attr($this->id) . '-card-cvc">' . esc_html__('Código', 'woocommerce') . ' <span class="required">*</span></label>
                <input id="' . esc_attr($this->id) . '-card-cvc" class="input-text wc-credit-card-form-card-cvc" inputmode="numeric" autocomplete="off" autocorrect="no" autocapitalize="no" spellcheck="no" type="password" maxlength="4" placeholder="' . esc_attr__('CVC', 'woocommerce') . '" ' . $this->field_name('card-cvc') . ' style="width: 100% !important;" />
                </p>';
                echo '<p class="form-row form-row-wide"> <br>
                <label for="' . esc_attr($this->id) . '-card-type">' . __('Elige tus cuotas', 'woocommerce') . ' <span class="required">*</span></label>
                <select style="width: 100% !important;" onchange="gt_alarma()" id="' . esc_attr($this->id) . '-vcq" class="input-text wc-credit-card-form-card-type" ' . $this->field_name('vcq') . ' >
                ' . $optionsHTML . '
                </select>
                </p>';

                ?>
                <?php do_action('woocommerce_credit_card_form_end', $this->id); ?>
                <div class="clear"></div>
                <?php
                echo '<img src="' . plugins_url('/img/logos-tarjetas-mayor.png', __FILE__) . '" style=" width: 45% !important; margin: auto; display: block; max-height: 173px !important;">';
                ?>
            </fieldset>
            <div class="row">
                <div class="columns large-8 large-centered">
                    <center><label class="derechos" style="font-size: 11px;padding: 1rem; color: #707070;  font-weight: bold;"> <?php echo date("Y"); ?>. <a class="links" href="https://shopshop.com.gt/" target="_blank" style="color: #0B1D6C;"><b><u>ShopShop</u></b> </a> </label></center>
                </div>
            </div>
            <?php

            if ($this->supports('credit_card_form_cvc_on_saved_method')) {
                echo '<fieldset>' . esc_html($cvc_field) . '</fieldset>';

            }

        }

        function gt_alarma()
        {
            wc_add_notice('Exitosa', 'success');
        }

        public function cleanStringBlackListPosVirtual($string)
        {
            $additional = '&#42;,&#40;,&#41;,&#63;,&#33;,&#36;,&#61;';
            $blacklist = html_entity_decode("$additional,&curren;,&brvbar;,&sect;,&not;,&macr;,&micro;,&para;,&middot;,&cedil;,&Aring;,&AElig;,&ETH;,&Oslash;,&Yacute;,&THORN;,&szlig;,&aring;,&aelig;,&eth;,&oslash;,&yacute;,&thorn;,&oline;,&weierp;,&image;,&real;,&alefsym;,&larr;,&uarr;,&rarr;,&darr;,&harr;,&crarr;,&lArr;,&uArr;,&rArr;,&dArr;,&hArr;,&forall;,&part;,&exist;,&empty;,&nabla;,&isin;,&notin;,&ni;,&prod;,&ang;,&and;,&or;,&cap;,&cup;,&there4;,&sim;,&cong;,&sub;,&sup;,&nsub;,&sube;,&supe;,&oplus;,&otimes;,&perp;,&lceil;,&rceil;,&lfloor;,&rfloor;,&lang;,&rang;,&loz;,&spades;,&clubs;,&hearts;,&diams;,&OElig;,&oelig;,&Scaron;,&scaron;,&dagger;,&Dagger;,&permil;,&lt;,&gt;,~,{,[,],},/,\\");
            // var_dump($blacklist);
            $blacklist = explode(",", $blacklist);
            // $this->printJson($blacklist);
            $stringFormat = html_entity_decode($string);
            foreach ($blacklist as $itemBL) {
                $responseBL = strpos($stringFormat, ($itemBL));
                if (!($responseBL === false))
                    $stringFormat = str_replace($itemBL, "-", $stringFormat);
            }

            return $stringFormat;
        }

        public function process_payment($order_id)
        {
//			printJsonDJPOS($_POST);
            global $woocommerce;
            // almacenamos los datos de la compra en $order
            $order = wc_get_order($order_id);

            $shoppingCart = [];
            $namesOfCouponsToWS = WC()->cart->applied_coupons;

            /** PRODUCTOS EN CARRITO **/
            $cartWc = WC()->cart->get_cart_contents();
            foreach ($cartWc as $wcItemCart) {

                $dataProduct = get_product_by_id_POSVIRTAULSHOPSHOP($wcItemCart['product_id']);
                $productName = "";
                $productSku = "SkuDefault";
                $productPrice = 0;

                foreach ($dataProduct['product'] as $item1) {
                    $productName = !empty($item1->post_title) ? self::cleanStringBlackListPosVirtual($item1->post_title) : 'Nombre de producto';
                }
                foreach ($dataProduct['productSku'] as $item2) {
                    $productSku = $item2->meta_value;
                }
                foreach ($dataProduct['productPrice'] as $item3) {
                    $productPrice = $item3->meta_value;
                }
                $totalDiscountOfEachProduct = $wcItemCart['line_subtotal'] - $wcItemCart['line_total'];

                $shoppingCart[] = [
                    'type' => 'product',
                    'name' => $productName,
                    'code' => $productSku,
                    'price' => "$productPrice",
                    'quantity' => "{$wcItemCart['quantity']}",
                    'total' => "{$wcItemCart['line_subtotal']}",
                    'coupon_amount' => "$totalDiscountOfEachProduct",
                ];
            }
//printJsonDJPOS($shoppingCart);
            /** CUPONES EN CARRITO **/
            if (!empty($namesOfCouponsToWS)) {
                $couponsToWS = WC()->cart->coupon_discount_totals;

                foreach ($couponsToWS as $couponWS) {
                    $nameOfCoupon = "";
                    foreach ($namesOfCouponsToWS as $nameCoupon) {
                        $nameOfCoupon = (WC()->cart->coupon_discount_totals[$nameCoupon] == $couponWS) ? $nameCoupon : null;
                        if (!empty($nameOfCoupon)) {
                            break;
                        }
                    }

                    $shoppingCart[] = [
                        'type' => 'coupon',
                        'name' => $nameOfCoupon,
                        'total' => "$couponWS"
                    ];
                }
            }


            /** RECARGOS DE ENVIO **/
            $totalShipping = $order->calculate_shipping();
            if ($totalShipping) {
                $shoppingCart[] = [
                    'type' => 'delivery',
                    'name' => 'Envío',
                    'total' => "$totalShipping"
                ];
            }
            $shoppingCartJson = json_encode($shoppingCart);

//            printJsonDJPOS($shoppingCart,0);

            /** FIN CARRITO **/

            /** DATOS DE CLIENTE **/
            $clientFullName = $order->get_billing_first_name() . " " . $order->get_billing_last_name();
            $clientName = $order->get_billing_first_name();
            $clientSurnames = $order->get_billing_last_name();
            $clientAddress = $order->get_billing_city();
            $clientEmail = $order->get_billing_email();
            $clientNit = !empty(sanitize_text_field($_POST['billing_nit'])) ? sanitize_text_field($_POST['billing_nit']) : 'C/F';
            if ($clientNit == 'CF' or $clientNit == 'cf') {
                $clientNit = 'C/F';
            }
            $clientPhone = $order->get_billing_phone();

            $client = [
                'full_name' => $clientFullName,
                'names' => $clientName,
                'surnames' => $clientSurnames,
                'address' => $clientAddress,
                'email' => $clientEmail,
                'nit' => $clientNit,
                'phone' => $clientPhone,
            ];

            $clientJson = json_encode($client);
//            printJsonDJPOS($client);
            /** FIN DATOS DE CLIENTE **/


            /** DATOS DE TARJETA **/
            $tmk_card = sanitize_text_field($_POST[$this->id . '-card-number']);
            $cardNumber = str_replace(' ', '', $tmk_card);
            $year = sanitize_text_field($_POST[$this->id . '-card-expiry']);
            $cardMonth = $year[0] . $year[1];
            $cardYear = $year[5] . $year[6];
            $cardCvv = sanitize_text_field($_POST[$this->id . '-card-cvc']);
            /** FIN DATOS DE TARJETA **/

            /** EMPIEZA CONSUMO API TRANSACCIÓN **/
            $autoIncrementeToUse = sanitize_text_field($_POST[$this->id . '-autoincrement-to-use']);
            $type = 'transaction';
            $auditNumber = $autoIncrementeToUse;
            $installmentCode = sanitize_text_field($_POST[$this->id . '-vcq']) ?? 'VC00';
            $jwtPosvirtual = get_option('jwt_posvirtual');
            $authorizationHeader = "Authorization: Bearer $jwtPosvirtual";
            $postFields = "-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"cart\"\r\n\r\n$shoppingCartJson\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"client\"\r\n\r\n$clientJson\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"type\"\r\n\r\n$type\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"audit_number\"\r\n\r\n$auditNumber\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"installment_code\"\r\n\r\n$installmentCode\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"card_number\"\r\n\r\n$cardNumber\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"card_month\"\r\n\r\n$cardMonth\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"card_year\"\r\n\r\n$cardYear\r\n-----011000010111000001101001\r\nContent-Disposition: form-data; name=\"card_cvv\"\r\n\r\n$cardCvv\r\n-----011000010111000001101001--\r\n";

            /** EMPIEZA CONSUMO HTTP API **/

            $responseCurl = wp_remote_post(PosVirtual_TRANSACTION_URL, array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(
                        'Authorization' => 'Bearer ' . $jwtPosvirtual,
                        'Content-Type' => 'multipart/form-data; boundary=---011000010111000001101001'
                    ),
                    'body' => $postFields,
                )
            );
            //printJsonDJPOS($responseCurl,0);

            //printJsonDJPOS(json_decode($responseCurl['body']), 0);


            if (is_wp_error($responseCurl)) {
                $error_message = $responseCurl->get_error_message();
                wc_add_notice(__('cURL Error #:', 'woothemes') . $error_message, 'error');

            } else {
                $body = $responseCurl['body'];
                $bodyDescompress = json_decode($responseCurl['body']);

                $decompress = $bodyDescompress;

                $result = $decompress->result;

                $message = !empty($decompress->message) ? $decompress->message : 'Operación Rechazada';

                if ($result == 'success') {

                    $data = $decompress->data;

                    wc_add_notice("$message", 'success');
                    // Mark as on-hold (we're awaiting the payment) :: Marcar como en espera (estamos esperando el pago)
                    $order->update_status('processing');

                    // Reduce stock levels
                    $order->reduce_order_stock();
                    // Remove cart
                    WC()->cart->empty_cart();

                    /** TODO:                    Return thank you redirect        */
                    return array(
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order)
                    );

                } else {

                    $note = __("$message");
                    // Add the note
                    $order->add_order_note($note);
                    wc_add_notice(__('Resultado: ', 'woothemes') . $message, $result);
                }
            }
        }
    }
}