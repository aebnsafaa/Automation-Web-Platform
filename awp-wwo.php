<?php 
ob_start();
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WWO {

    private static $instance;

    /**
     * Returns an instance of this class. 
     */
    public static function get_instance() {

        return self::$instance;

    }

    public function __construct() {

        $settings = get_option('wwo_settings');
        
        add_filter( 'wwo/frontend/fields', [$this, 'fields'] );
		

        new awpotp();

        require WWO_PATH . 'admin/functions.php';

        if(isset($settings['general']['active_login']) && $settings['general']['active_login'] == 'on'){
            require 'awp-login.php';
            new Login();
        }

        if(isset($settings['general']['active_register']) && $settings['general']['active_register'] == 'on'){
            require 'awp-register.php';
            new Register();
        }

        add_action( 'wp_ajax_wwo_register', [$this, 'register'] );
        add_action( 'wp_ajax_nopriv_wwo_register', [$this, 'register'] );

        add_action( 'admin_menu', [$this, 'settings'] );
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue'] );
        add_action( 'wp_enqueue_scripts', [$this, 'public_enqueue'] );

        add_action( 'wwo/register', [$this, 'notification'], 100, 2 );
            }

    public function fields($fields) {

        $fields = [
            'name'  => [
                'label' => 'Nama Lengkap',
                'type'  => 'text',
            ],
            'phone' => [
                'label' => 'No. WhatsApp',
                'type' => 'tel',
            ],
            'kode_agen' => [
                'label' => 'Kode Agen',
                'type'  => 'text',
            ],
            'password' => [
                'label' => 'Password',
                'type' => 'password',
            ],
            'pin_transaksi' => [
                'label' => 'Pin Transaksi',
                'type'  => 'password',
            ],
            'email' => [
                'label' => 'Email',
                'type'  => 'email',
            ],
        ];

        return $fields;

    }

    public function register() {

        $data = $_REQUEST['data'];

        if( wp_verify_nonce( $data['wwo_register_nonce_field'], 'wwo_register_nonce' )){
            $userdata = array(
                'user_pass'				=> sanitize_text_field($data['password']), 	//(string) The plain-text user password.
                'user_login' 			=> sanitize_text_field($data['phone']), 	//(string) The user's login username.
                'user_nicename' 		=> strtolower(str_replace(' ', '-', sanitize_text_field($data['name']))), 	//(string) The URL-friendly user name.
                'user_email' 			=> sanitize_email($data['email']), 	//(string) The user email address.
                'display_name' 			=> sanitize_text_field($data['name']), 	//(string) The user's display name. Default is the user's username.
                'first_name' 			=> sanitize_text_field($data['name']), 	//(string) The user's first name. For new users, will be used to build the first part of the user's display name if $display_name is not specified. 
            );

            $user_id = wp_insert_user( $userdata );
            if(!is_wp_error( $user_id )){
                update_user_meta($user_id, 'kode_agen', sanitize_text_field($data['kode_agen']));
                update_user_meta($user_id, 'pin_transaksi', sanitize_text_field($data['pin_transaksi']));
                update_user_meta($user_id, 'password_transaksi', sanitize_text_field($data['password']));
                update_user_meta($user_id, 'billing_first_name', sanitize_text_field($data['name']));
                update_user_meta($user_id, 'billing_phone', sanitize_text_field($data['phone']));

                wp_clear_auth_cookie();
                wp_set_current_user ( $user_id );
                wp_set_auth_cookie  ( $user_id, true );

                do_action( 'wwo/register', $user_id, $data );

                wp_send_json_success();
            }else{
                wp_send_json_error([
                    'message'   => $user_id
                ]);
            }
        }

        wp_send_json_error([
            'message'   => 'Not verified'
        ]);

    }

    public function notification($user_id, $data) {
        $wawp_api = get_option('wawp_whatsapp_notification_key');
        $admin_number = get_option( 'wawp_whatsapp_notification_admin_number' );

        $message = str_replace(
            [
                '{nama}', 
                '{kode_agen}', 
                '{pass_trx}', 
                '{pin_trx}', 
                '{email}', 
                '{no_whatsapp}'
            ],
            [
                $data['name'],
                $data['kode_agen'],
                $data['password'],
                $data['pin_transaksi'],
                $data['email'],
                $data['phone']
            ],
            get_option('wwo_register_message')
        );

        $send = wp_remote_post( 
            'https://app.wawp.net/api/send?message='.rawurlencode($message).'&tujuan='.rawurlencode($admin_number.'@s.whatsapp.net'), 
            [
                'method'      => 'POST',
                'headers'     => [
                    'apikey' => $wawp_api
                ],
            ] );
        
        return json_decode($send, true);
    }

    public function settings(){
        $hook = add_menu_page(
            'Wawp OTP',
            'Wawp OTP',
            'administrator',
            'awp-otp',
            [$this, 'setting_page'], 
            WWO_URL . 'assets/img/menu.png',
            101 );
            remove_menu_page( 'awp-otp');
    }

    public function setting_page() {
        include 'admin/wc-setting-page.php';
    }

    public function enqueue() {

        global $pagenow;

        if($pagenow == 'admin.php' && $_GET['page'] == 'awp-otp') :

            wp_enqueue_style( 'bootstrap-css', plugins_url( '/assets/css/resources/bootstrap.min.css', __FILE__ ), array(), '5.2.3' );
            
            wp_enqueue_style( 'bootstrap-icons-css', plugins_url( '/assets/css/resources/bootstrap-icons.css', __FILE__ ), array(), '1.8.1' );

            wp_enqueue_style( 'bootstrap-table-css', plugins_url( '/assets/css/resources/bootstrap-table.min.css', __FILE__ ), array(), '1.21.1' );          

            wp_enqueue_style( 'sweetalert2-css', plugins_url( '/assets/css/resources/sweetalert2.min.css', __FILE__ ), array(), '11.4.35' );  
            
            wp_enqueue_style( 'jquery-ui-css', plugins_url( '/assets/css/resources/jquery-ui.css', __FILE__ ), array(), '1.13.2' );
            
            wp_enqueue_style( 'lineicons-css', plugins_url( '/assets/css/resources/lineicons.css', __FILE__ ), array(), '3.0' );
   
            wp_enqueue_style( 'select2', plugins_url( '/assets/css/resources/select2.min.css', __FILE__ ), array(), '4.1.0' );

            wp_enqueue_style( 'admin',  WWO_URL . '/assets/css/admin.css' );

            wp_enqueue_script( 'jquery-js', plugins_url( 'assets/js/resources/jquery.min.js', __FILE__ ), array(), '3.6.0' );

            wp_enqueue_script( 'jquery-ui-js', plugins_url( 'assets/js/resources/jquery-ui.js', __FILE__ ), array(), '1.13.2' );

            wp_enqueue_script( 'bootstrap-js', plugins_url( 'assets/js/resources/bootstrap.bundle.min.js', __FILE__ ), array(), '5.2.3' );

             wp_enqueue_script( 'bootstrap-table-js', plugins_url( 'assets/js/resources/bootstrap-table.min.js', __FILE__ ), array(), '1.21.1' );

            wp_enqueue_script( 'sweetalert2-js', plugins_url( 'assets/js/resources/sweetalert2.min.js', __FILE__ ), array(), '11.4.35' );

            wp_enqueue_script( 'select2', plugins_url( 'assets/js/resources/select2.js', __FILE__ ), array(), '4.1.0' );

            wp_register_script( 'ajax-script', WWO_URL . '/assets/js/admin-ajax.js', array('jquery'), false, true );
            wp_enqueue_script( 'admin-script', WWO_URL . '/assets/js/admin.js', array('jquery'), false, true );
            
                    if (is_rtl()) {
            // Enqueue RTL versions of your CSS files here
            wp_enqueue_style('otp-rtl-css', plugins_url('/assets/css/otp-rtl.css', __FILE__), array(), '5.2.3');
            // You can enqueue more RTL CSS files as needed
        }
            
            // Localize the script with new data
            $script_data_array = array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'admin_nonce' => wp_create_nonce('wwo_nonce'),
            );
            wp_localize_script( 'ajax-script', 'wwo', $script_data_array );
            // Enqueued script with localized data.
            wp_enqueue_script( 'ajax-script' );

        endif;
        
        

    }

    public function public_enqueue() {
        wp_enqueue_style( 'bs-icons', plugins_url( '/assets/css/resources/font/bootstrap-icons.css', __FILE__ ), array(), '1.8.1' );
        
    }

}


class awpotp extends WWO {

    private $instance_id;

    private $access_token;

    private $phone;

    private $message;

    private static $instance;
    

    public function __construct() {

        $settings = get_option('wwo_settings');

        $this->set_access_token($settings['general']['access_token']);
        $this->set_instance_id($settings['general']['instance_id']);
        add_action( 'awp/otp/login', [$this, 'send_otp'] );
        add_action( 'awp/otp/register', [$this, 'send_otp'] );
    }

 

    public function set_instance_id($data) {
        $this->instance_id = $data;
    }

    public function set_access_token($data) {
        $this->access_token = $data;
    }

    public function set_phone($to) {
        $this->phone = $to;
    }

    public function set_message($data) {
        $settings = get_option('wwo_settings');
        $this->message = str_replace(
            ['{{name}}', '{{otp}}'],
            [$data['name'], $data['otp']],
            $settings[$data['activity']]['message']
        );
    }


    public function send_otp( $data ) {

        $this->set_message($data);

        $query = [
            'number'    => $data['phone'],
            'type'      => 'text',
            'message'   => $this->message,
            'instance_id'   => $this->instance_id,
            'access_token'  => $this->access_token,
        ];

        if (true === WP_DEBUG) {
            error_log(json_encode($query));
        }

        $send = wp_remote_post( 'https://app.wawp.net/api/send?'.http_build_query( $query ), array() );
        $send = json_decode( wp_remote_retrieve_body( $send ) );

        // if (true === WP_DEBUG) {
        //     error_log(json_encode($send));
        // }

        return $send;
    }

}