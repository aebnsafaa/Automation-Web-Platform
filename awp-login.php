<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Login extends WWO {

    private $is_login = false;

    private static $instance;

    /**
     * Returns an instance of this class. 
     */
    public static function get_instance() {

        return self::$instance;

    }

    public function __construct() {

        add_action ('woocommerce_login_form', [$this, 'login_form'] );
        add_action ('wp_enqueue_scripts', [$this, 'enqueue']);

        add_action('wp_ajax_awp_send_login_otp', [$this, 'login_otp']);
        add_action('wp_ajax_nopriv_awp_send_login_otp', [$this, 'login_otp']);

        add_action('wp_ajax_awp_login', [$this, 'login']);
        add_action('wp_ajax_nopriv_awp_login', [$this, 'login']);

    }

    public function redirect_myaccount() {
        if($this->is_login === false){
            wp_safe_redirect( site_url('register') );
        }
    }

        public function login_form() {
        $settings = get_option('wwo_settings');
        ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide awp" style="display:none;">
            <label for="password"><?php esc_html_e( 'Your WhatsApp Number', 'awp' ); ?>&nbsp;<span class="required">*</span></label>
            <?php if(isset($settings['general']) && $settings['general'] == 'on') { ?>

            <?php } else { ?>
            
            <?php } ?>
            <!-- Enqueue intl-tel-input CSS directly from CDN -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
            <input class="woocommerce-Input woocommerce-Input--text input-text" type="tel" name="login_your_whatsapp" id="login_your_whatsapp" />
            <button type="button" class="send_login_otp sendotpcss woocommerce-button button woocommerce-form-login__awp <?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="Send OTP">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-phone-vibrate" viewBox="0 0 16 16">
  <path d="M10 3a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zM6 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/>
  <path d="M8 12a1 1 0 1 0 0-2 1 1 0 0 0 0 2M1.599 4.058a.5.5 0 0 1 .208.676A7 7 0 0 0 1 8c0 1.18.292 2.292.807 3.266a.5.5 0 0 1-.884.468A8 8 0 0 1 0 8c0-1.347.334-2.619.923-3.734a.5.5 0 0 1 .676-.208m12.802 0a.5.5 0 0 1 .676.208A8 8 0 0 1 16 8a8 8 0 0 1-.923 3.734.5.5 0 0 1-.884-.468A7 7 0 0 0 15 8c0-1.18-.292-2.292-.807-3.266a.5.5 0 0 1 .208-.676M3.057 5.534a.5.5 0 0 1 .284.648A5 5 0 0 0 3 8c0 .642.12 1.255.34 1.818a.5.5 0 1 1-.93.364A6 6 0 0 1 2 8c0-.769.145-1.505.41-2.182a.5.5 0 0 1 .647-.284m9.886 0a.5.5 0 0 1 .648.284C13.855 6.495 14 7.231 14 8s-.145 1.505-.41 2.182a.5.5 0 0 1-.93-.364C12.88 9.255 13 8.642 13 8s-.12-1.255-.34-1.818a.5.5 0 0 1 .283-.648"/>
</svg>
            
 &nbsp;
                
                <?php esc_html_e( 'send otp', 'awp' ); ?></button>
        </p>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide awp-input" style="display:none;">
            <label for="tel"><?php esc_html_e( 'OTP', 'awp' ); ?>&nbsp;<span class="required">*</span></label>
            <input class="woocommerce-Input woocommerce-Input--text input-text" type="tel" name="login_otp" id="login_otp" />
        </p>
        <p class="form-row awp-login-otp-submit">
            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="awp_rememberme" value="forever" /> <span><?php esc_html_e( 'Remember me', 'awp' ); ?></span>
            </label>
            <?php wp_nonce_field( 'awp-login', 'awp-login-nonce' ); ?>
            
            <button type="submit" class="woocommerce-button button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-box-arrow-in-right" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M6 3.5a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-2a.5.5 0 0 0-1 0v2A1.5 1.5 0 0 0 6.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-9A1.5 1.5 0 0 0 14.5 2h-8A1.5 1.5 0 0 0 5 3.5v2a.5.5 0 0 0 1 0z"/>
  <path fill-rule="evenodd" d="M11.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 1 0-.708.708L10.293 7.5H1.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708z"/>
</svg>
  &nbsp; <?php esc_html_e( 'Log in', 'awp' ); ?>
</button>

            
            <button data-button="login_w_wa" type="button" class="awp_login_btn woocommerce-button button whatsappcss woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Login via WhatsApp', 'woocommerce' ); ?>">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
    <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
  </svg>
  &nbsp; <?php esc_html_e( 'Login via WhatsApp', 'awp' ); ?>
</button>


            
            
            
            <button data-button="login_w_email" type="button" class="awp_login_btn woocommerce-button emailcss button woocommerce-form-login__submit<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="<?php esc_attr_e( 'Login via Email & Password', 'woocommerce' ); ?>" style="display:none;">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-check" viewBox="0 0 16 16">
  <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/>
  <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-1.993-1.679a.5.5 0 0 0-.686.172l-1.17 1.95-.547-.547a.5.5 0 0 0-.708.708l.774.773a.75.75 0 0 0 1.174-.144l1.335-2.226a.5.5 0 0 0-.172-.686"/>
</svg>
  &nbsp; <?php esc_html_e( 'Login via Email & Password', 'awp' ); ?>
</button>

 <!-- Enqueue intl-tel-input JS directly from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <!-- Enqueue utils.js for formatting/validation functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>
    <script>
    // Initialize intl-tel-input for the login form
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.querySelector("#login_your_whatsapp");
        var iti = window.intlTelInput(input, {
            initialCountry: "auto",
            geoIpLookup: function(callback) {
                jQuery.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
                    var countryCode = (resp && resp.country) ? resp.country : "";
                    callback(countryCode);
                });
            },
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js" // Use utils script for formatting/validation
        });

        // Remove country code or 00 prefix when the input changes
        input.addEventListener('change', function() {
            var phoneNumber = iti.getNumber().replace(/^(\+00|\+)/, '');
            // Update the input value with the formatted number
            input.value = phoneNumber;
        });
    });
    </script>
        </p>
        
        <?php
    }

    public function login_otp() {

        $settings = get_option('wwo_settings');

        $country   = $_REQUEST['code'];
        $phone  = $_REQUEST['phone'];
        $otp    = rand(123456, 999999);
        
        if(!empty($phone)) {
            $args = array(
                'meta_query' => array(
                    'relation' => 'OR',
                        array(
                            'key'     => 'billing_phone',
                            'value'   => awp_break_number($country, $phone),
                            'compare' => 'LIKE'
                        ),
                    ),
                'order' => 'DESC',
            );
            $user_query = new WP_User_Query( $args );

            $user_exist = $user_query->get_results();

            if ( count($user_exist) > 0 && strlen($phone) > 10) {
                foreach ( $user_exist as $user ) {
                    $user_id = $user->ID;
                    $login = $user->user_login;
                    $your_name = $user->display_name;
                }
        
                setcookie('wc_log_awp', base64_encode(base64_encode(base64_encode($otp))), time()+300);

                $send_args = [
                    'country'   => $country,
                    'phone'     => $phone,
                    'user_id'   => $user_id,
                    'username'  => $login,
                    'name'      => $your_name,
                    'otp'       => $otp,
                    'activity'  => 'login'
                ];
        
                $send = do_action('awp/otp/login', $send_args );

                // if (true === WP_DEBUG) {
                //     error_log(json_encode($send));
                // }
        
                if(is_wp_error( $send )){
                    $failed_request = $settings['login']['error_3'] ?? 'Failed to send passkey. Please try again or contact administrator.';
                    wp_send_json_error( [
                        'message'   => '<li class="awp-notice danger"><i class="bi bi-exclamation-triangle-fill"></i>'.$failed_request.'</li>',
                    ] );
                }else{
                    $request_sent = $settings['login']['error_4'] ?? 'Request sent! Check your WhatsApp.';
                    wp_send_json_success( [
                        'message'   => '<li class="awp-notice success"><i class="bi bi-check-circle-fill"></i>'.$request_sent.'</li>', 
                        'user_id'  => $user_id,
                    ] );                
                }
        
            }else{
                $number_not_registered = $settings['login']['error_5'] ?? 'This number is not registered on this site. Please try again with a valid number or register.';
                wp_send_json_error( [
                    'message'   => '<li class="awp-notice danger"><i class="bi bi-exclamation-triangle-fill"></i>'.$number_not_registered.'</li>', 
                ] );
            }
        }else{
            wp_send_json_error( [
                'message'   => '<li class="awp-notice danger"><i class="bi bi-exclamation-triangle-fill"></i>'.$settings['login']['error_6'] ?? 'WhatsApp number is not provided.' .'</li>', 
            ] );
        }
        exit();
    }

    public function login() {

        $settings = get_option('wwo_settings');

        $code = $_REQUEST['code'];
        $confirm_code = base64_decode(base64_decode(base64_decode($_COOKIE['wc_log_awp'])));
        $user_id = $_REQUEST['user'];
        $nonce = $_REQUEST['nonce'];

        if(wp_verify_nonce( $nonce, 'awp-login' ) && $code == $confirm_code){

            wp_clear_auth_cookie();
            wp_set_current_user ( $user_id );

            $log_this_user = wp_set_auth_cookie  ( $user_id, true );

            // Redirect URL //
            if ( !is_wp_error( $log_this_user ) ){

                if(!empty($settings['login']['url_redirection']) && $_REQUEST['referer'] !== '/checkout/'){
                    $redirect = $settings['login']['url_redirection'];
                }else{
                    $redirect = 'reload';
                }
                wp_send_json_success( [
                    'message'   => '<li class="awp-notice success"><i class="bi bi-check-circle-fill"></i>Success!</li>',
                    'action'    => $redirect  
                ] );

            }else{
                wp_send_json_error( [
                    'message'   => '<li class="awp-notice danger"><i class="bi bi-exclamation-triangle-fill"></i> '.$settings['login']['error_1'] ?? 'Something wrong with your login'.'</li>',
                ] );
            }
        }else{
            wp_send_json_error( [
                'message'   => '<li class="awp-notice danger"><i class="bi bi-exclamation-triangle-fill"></i> '.$settings['login']['error_2'] ?? 'Missmatch passkey. Try again.'.'</li>',
            ] );
        }

        exit();
    }
    
    public function enqueue() {
        if(!is_user_logged_in()){
            wp_enqueue_style( 'custom-my-account',  WWO_URL . 'assets/css/my-account.css' );
            wp_enqueue_script( 'custom-my-account',  WWO_URL . 'assets/js/my-account.js', array('jquery'), false, true );
            wp_enqueue_script( 'awp-login',  WWO_URL . 'assets/js/my-account-login.js', array('jquery'), false, true );
            // Localize the script with new data
            $script_data_array = array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'admin_nonce' => wp_create_nonce('wwo_nonce'),
            );
            wp_localize_script( 'custom-my-account', 'wwo', $script_data_array );
            // Enqueued script with localized data.
            wp_enqueue_script( 'custom-my-account' );
        }
    }

}