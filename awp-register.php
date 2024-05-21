
<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Register extends WWO {

    private $is_login = false;

    private static $instance;

    /**
     * Returns an instance of this class. 
     */
    public static function get_instance() {

        return self::$instance;

    }

    public function __construct() {

        add_action( 'woocommerce_register_form', [$this, 'register_form'] );
        add_action( 'woocommerce_created_customer', [$this, 'register_action'] );

        add_action('wp_enqueue_scripts', [$this, 'enqueue'] );

        add_action('wp_ajax_awp_send_register_otp', [$this, 'register_otp']);
        add_action('wp_ajax_nopriv_awp_send_register_otp', [$this, 'register_otp']);

        add_action('wp_ajax_awp_register', [$this, 'register']);
        add_action('wp_ajax_nopriv_awp_register', [$this, 'register']);

    }


    
    public function register_form() {
    $settings = get_option('wwo_settings');
    ?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide awp">
        <label for="password"><?php esc_html_e( 'Your WhatsApp Number', 'awp' ); ?>&nbsp;<span class="required">*</span></label>
        <?php if(isset($settings['general']) && $settings['general'] == 'on') { ?>
       
        <?php } else { ?>
        
        <?php } ?>
        <!-- Enqueue intl-tel-input CSS directly from CDN -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
        <!-- WhatsApp number input field -->
        
        
           <!-- Enqueue intl-tel-input JS directly from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <!-- Enqueue utils.js for formatting/validation functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"></script>
    <script>
    // Initialize intl-tel-input
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.querySelector("#register_your_whatsapp");
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
        
        <input class="woocommerce-Input woocommerce-Input--text input-text" type="tel" name="register_your_whatsapp" id="register_your_whatsapp" />
        <button type="button" class="send_register_otp sendotpcss woocommerce-button button woocommerce-form-register__awp <?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="login" value="Send OTP">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-phone-vibrate" viewBox="0 0 16 16">
  <path d="M10 3a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1zM6 2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2z"/>
  <path d="M8 12a1 1 0 1 0 0-2 1 1 0 0 0 0 2M1.599 4.058a.5.5 0 0 1 .208.676A7 7 0 0 0 1 8c0 1.18.292 2.292.807 3.266a.5.5 0 0 1-.884.468A8 8 0 0 1 0 8c0-1.347.334-2.619.923-3.734a.5.5 0 0 1 .676-.208m12.802 0a.5.5 0 0 1 .676.208A8 8 0 0 1 16 8a8 8 0 0 1-.923 3.734.5.5 0 0 1-.884-.468A7 7 0 0 0 15 8c0-1.18-.292-2.292-.807-3.266a.5.5 0 0 1 .208-.676M3.057 5.534a.5.5 0 0 1 .284.648A5 5 0 0 0 3 8c0 .642.12 1.255.34 1.818a.5.5 0 1 1-.93.364A6 6 0 0 1 2 8c0-.769.145-1.505.41-2.182a.5.5 0 0 1 .647-.284m9.886 0a.5.5 0 0 1 .648.284C13.855 6.495 14 7.231 14 8s-.145 1.505-.41 2.182a.5.5 0 0 1-.93-.364C12.88 9.255 13 8.642 13 8s-.12-1.255-.34-1.818a.5.5 0 0 1 .283-.648"/>
</svg>
            
 &nbsp; <?php esc_html_e( 'Send OTP', 'awp' ); ?>
</button>
    </p>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide awp-input" style="display:none;">
        <label for="tel"><?php esc_html_e( 'OTP', 'woocommerce' ); ?>&nbsp;<span class="required">*</span></label>
        <input class="woocommerce-Input woocommerce-Input--text input-text" type="tel" name="register_otp" id="register_otp" />
    </p>
 
    <?php
}


    public function register_action( $customer_id ) {
    if ( isset( $_POST[ 'register_your_whatsapp' ] ) ) {
        update_user_meta( $customer_id, 'billing_phone', wc_clean( $_POST[ 'register_your_whatsapp' ] ) );
    }
}





    public function register_otp() {

        $settings = get_option('wwo_settings');
        
        $phone      = $_REQUEST['phone'];
        $otp        = rand(123456, 999999);

        if ( !empty($phone) && strlen($phone) >= 9) {
    
            setcookie('wc_reg_awp', base64_encode(base64_encode(base64_encode($otp))), time()+300);

            $send_args = [
                'country'   => $country,
                'phone'     => $phone,
                'name'      => '',
                'otp'       => $otp,
                'activity'  => 'register'
            ];
    
            $send = do_action('awp/otp/register', $send_args );
    
            if(is_wp_error( $send )){
                $failed_request = $settings['register']['error_3'] ?? 'Failed to send passkey. Please try again or contact administrator.';
                wp_send_json_error( [
                    'message'   => '<li class="awp-notice danger"><i class="bi bi-exclamation-triangle-fill"></i>'.$failed_request.'</li>',
                ] );
            }else{
                $request_sent = $settings['register']['error_4'] ?? 'Request sent! Check your WhatsApp.';
                wp_send_json_success( [
                    'message'   => '<li class="awp-notice success"><i class="bi bi-check-circle-fill"></i>'.$request_sent.'</li>', 
                ] );                
            }
    
        }else{
            if(empty($phone)){
                $failed_request = $settings['register']['error_5'] ?? 'WhatsApp number is not provided.';
                wp_send_json_error( [
                    'message'   => '<li class="awp-notice danger"><i class="bi bi-exclamation-triangle-fill"></i>'.$failed_request.'</li>',
                ] );
            }
            if(strlen($phone < 0)){
                $failed_request = 'Your WhatsApp number may not valid. Please recheck';
                wp_send_json_error( [
                    'message'   => '<li class="awp-notice danger"><i class="bi bi-exclamation-triangle-fill"></i>'.$failed_request.'</li>',
                ] );
            }
        }
        exit();
    }

    public function register() {

        $settings = get_option('wwo_settings');

        $error_message = '';

        if(
            isset($_REQUEST['email']) && !empty($_REQUEST['email']) &&
            isset($_REQUEST['phone']) && !empty($_REQUEST['phone']) &&
            isset($_REQUEST['code']) && !empty($_REQUEST['code'])
        ) {

            $email      = $_REQUEST['email'];
            
            if(isset($_REQUEST['username'])){
                $username   = $_REQUEST['username'];
            }else{
                $username   = $_REQUEST['email'];
            }

            if(isset($_REQUEST['pass'])){
                $password   = $_REQUEST['pass'];
            }else{
                $characters       = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!!!';
                $charactersLength = strlen($characters);
                $password         = '';

                for ($i = 0; $i < 8; $i++) :
                    $password .= $characters[rand(0, $charactersLength - 1)];
                endfor;
            }

            $country    = $_REQUEST['country'];
            $phone      = $_REQUEST['phone'];
            $code       = $_REQUEST['code'];
            $confirm_code = base64_decode(base64_decode(base64_decode($_COOKIE['wc_reg_awp'])));
            $nonce      = $_REQUEST['nonce'];

            $check_whatsapp = new WP_User_Query( array(
                'meta_query' => array(
                    'relation' => 'OR',
                        array(
                            'key'     => 'billing_phone',
                            'value'   => awp_break_number($country, $phone),
                            'compare' => 'LIKE'
                        ),
                )
            ) );

            $check_username = new WP_User_Query( array(
                'search' => $username,
                'search_columns' => array( 'user_login' )
            ) );
        
            $check_email = new WP_User_Query( array(
                'search' => $email,
                'search_columns' => array( 'user_login', 'user_email' )
            ) );

            $error_message = '';

            if(!empty( $check_email->get_results() )) {
                $error_message .= '<li class="awp-notice danger"><i class="bi bi bi-exclamation-triangle-fill"></i>'.$settings['register']['error_9'].'</li>';
            }

            if(!empty( $check_whatsapp->get_results() )) {
                $error_message .= '<li class="awp-notice danger"><i class="bi bi bi-exclamation-triangle-fill"></i>'.$settings['register']['error_8'].'</li>';
            }

            if(!empty( $check_username->get_results() )) {
                $error_message .= '<li class="awp-notice danger"><i class="bi bi bi-exclamation-triangle-fill"></i>'.$settings['register']['error_10'].'</li>';
            }

            if(strlen($phone) < 10) {
                $error_message .= '<li class="awp-notice danger"><i class="bi bi bi-exclamation-triangle-fill"></i>Your WhatsApp number may not valid. Please recheck.</li>';
            }

            if( !wp_verify_nonce( $_REQUEST['nonce'], 'woocommerce-register' ) ){
                $error_message .= '<li class="awp-notice danger"><i class="bi bi bi-exclamation-triangle-fill"></i>Your session has been expired. Please reload the page and try again.</li>';
            }

            if($error_message == '' && wp_verify_nonce( $_REQUEST['nonce'], 'woocommerce-register' ) && empty( $check_whatsapp->get_results() ) && empty( $check_email->get_results() ) && $code == $confirm_code){

                $userdata = array(
                    'user_login'      => $username,
                    'user_pass'       => $password,
                    'user_nicename'   => sanitize_text_field($username),
                    'user_email'      => $email,
                    'user_name'       => $username,
                    'display_name'    => $username,
                    'meta_input'      => array(
                        'nickname'        => sanitize_text_field($username),
                        'first_name'      => $username,
                    ),
                );

                $user_id = wp_insert_user( $userdata ) ;

                update_user_meta($user_id, 'billing_phone', $phone);

                $someone = new WP_User( $user_id );

                $new_customer_data = apply_filters(
                    'woocommerce_new_customer_data',
                    array(
                        'user_login' => $phone,
                        'user_pass'  => $password,
                        'user_email' => $email,
                        'role'       => 'customer',
                    )
                );
            
                do_action( 'woocommerce_created_customer', $user_id, $new_customer_data, false );

                // Redirect URL //
                if ( !is_wp_error( $user_id ) ){

                    wp_clear_auth_cookie();
                    wp_set_current_user($user_id); // set the current wp user
                    wp_set_auth_cookie($user_id, true);

                    if(!empty($settings['register']['url_redirection']) && $_REQUEST['referer'] !== '/checkout/'){
                        $redirect = $settings['register']['url_redirection'];
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
                    'message'   => $error_message,
                ] );    
            }

        }else{

            if(!isset($_REQUEST['email']) || empty($_REQUEST['email'])) {
                $error_message .= '<li class="awp-notice danger"><i class="bi bi bi-exclamation-triangle-fill"></i>'.$settings['register']['error_7'].'</li>';
            }
            if(!isset($_REQUEST['phone']) || empty($_REQUEST['phone'])){
                $error_message .= '<li class="awp-notice danger"><i class="bi bi bi-exclamation-triangle-fill"></i>'.$settings['register']['error_5'].'</li>';
            }
            if(!isset($_REQUEST['code']) || empty($_REQUEST['code'])){
                $error_message .= '<li class="awp-notice danger"><i class="bi bi bi-exclamation-triangle-fill"></i>'.$settings['register']['error_2'].'</li>';
            }
            wp_send_json_error( [
                'message'   => $error_message,
            ] );
            
        }

        exit();
    }

    public function enqueue() {
        if(!is_user_logged_in()){
            wp_enqueue_script( 'awp-register',  WWO_URL . 'assets/js/my-account-register.js', array('jquery'), false, true );
        }
    }

}