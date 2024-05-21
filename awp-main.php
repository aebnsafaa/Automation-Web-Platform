<?php
define( 'awp_FUNCTION', 'awp_connection' );

class awp_Main {

    protected static $instance = NULL;
    
    public static function get_instance()
    {
        if ( NULL === self::$instance )
            self::$instance = new self;

        return self::$instance;
    }
    
	public $ui;
	
	public function __construct() {
		$this->ui = new awp_UI;
		$this->log = new awp_logger();
		add_action( 'init', array( $this, 'awp_textdomain' ) );
		add_action( 'admin_init', array( $this, 'awp_register_settings' ) );
		add_action( 'admin_init', array( $this, 'awp_custom_order_status' ) );
		add_filter( 'manage_edit-shop_order_columns', array($this,'awp_wa_manual_new_columns') );
		add_action( 'manage_shop_order_posts_custom_column', array($this, 'awp_wa_manual_manage_columns' ), 10, 2);
		add_action( 'admin_menu', array( $this, 'awp_admin_menu' ) );
		add_action( 'admin_notices', array( $this, 'awp_admin_notices' ) );
		add_action( 'woocommerce_thankyou', array( $this, 'awp_wa_order_receive' ), 10);
		add_action( 'woocommerce_order_status_pending', array( $this, 'awp_wa_process_states_pending' ), 10 );
		add_action( 'awp_wa_process_states_receive', array( $this, 'awp_wa_process_states' ), 10 );
		add_action( 'woocommerce_order_status_failed', array( $this, 'awp_wa_process_states_failed' ), 10 );
		add_action( 'woocommerce_order_status_on-hold', array( $this, 'awp_wa_process_states_onhold' ), 10 );
		add_action( 'woocommerce_order_status_completed', array( $this, 'awp_wa_process_states_completed' ), 10 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'awp_wa_process_states_processing' ), 10 );
		add_action( 'woocommerce_order_status_refunded', array( $this, 'awp_wa_process_states_refunded' ), 10 );
		add_action( 'woocommerce_order_status_cancelled', array( $this, 'awp_wa_process_states_cancelled' ), 10 );
		add_action( 'woocommerce_new_customer_note', array( $this, 'awp_wa_process_note' ), 10 );
        add_action( 'woocommerce_before_checkout_form', array($this, 'woo_phone_intltel_input' ) );
        add_action( 'woocommerce_save_account_details', array($this, 'save_billing_phone_on_edit_account' ) );
        add_action( 'woocommerce_edit_account_form', array($this, 'add_billing_phone_to_edit_account_form' ) );
        add_action( 'init', array($this, 'send_one_time_welcome_email' ) );
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_select2' ) );
		add_action( 'followup_cron_hook', array( $this, 'followup_order') );
		add_action( 'followup_cron_hook_2', array( $this, 'followup_order_2') );
		add_action( 'followup_cron_hook_3', array( $this, 'followup_order_3') );
		add_action( 'aftersales_cron_hook', array( $this, 'aftersales_order') );
		add_action( 'abandoned_cron_hook', array( $this, 'abandoned_order') );
		add_filter( 'cron_schedules', array( $this, 'followup_cron_schedule' ) );
		
		
		add_filter( 'manage_users_columns', array( $this, 'add_billing_phone_column' ) );
		add_filter( 'manage_users_custom_column', array( $this, 'display_billing_phone_content' ), 10, 3 );

		add_action( 'admin_init', array($this, 'hide_notification_handler' ) );
			add_action( 'admin_notices', array($this, 'display_evaluation_notification' ) );

		if ( ! wp_next_scheduled( 'followup_cron_hook' ) ) {
			wp_schedule_event( time(), 'every_half_hours', 'followup_cron_hook' );
		}
		if ( ! wp_next_scheduled( 'followup_cron_hook_2' ) ) {
			wp_schedule_event( time(), 'every_half_hours', 'followup_cron_hook_2' );
		}
		if ( ! wp_next_scheduled( 'followup_cron_hook_3' ) ) {
			wp_schedule_event( time(), 'every_half_hours', 'followup_cron_hook_3' );
		}
		if ( ! wp_next_scheduled( 'aftersales_cron_hook' ) ) {
			wp_schedule_event( time(), 'every_half_hours', 'aftersales_cron_hook' );
		}
		if ( ! wp_next_scheduled( 'abandoned_cron_hook' ) ) {
			wp_schedule_event( time(), 'every_half_hours', 'abandoned_cron_hook' );
		}
		
		add_action( 'admin_bar_menu', array( $this, 'status_on_admin_bar' ), 100 );

        add_action( 'edd_purchase_form_user_info_fields', array($this, 'edd_buyer_phone_field' ) );
        add_action( 'edd_checkout_error_checks', array($this, 'edd_validate_checkout_field'), 10, 2 );
        add_filter( 'edd_payment_meta', array($this, 'edd_save_phone_field' ) );
        add_action( 'edd_payment_personal_details_list', array($this, 'edd_show_phone_on_personal_details'), 10, 2 );
        add_action( 'edd_payment_receipt_before', array($this, 'edd_send_wa_after_purchase' ) );
        add_action( 'edd_complete_purchase', array($this, 'edd_send_wa_on_complete' ) );
        add_action( 'edd_before_checkout_cart', array($this, 'edd_phone_intltel_input' ) );
	}

    public function is_plugin_active( $plugin ) {
        return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
    }
	
	public function awp_textdomain() {
		load_plugin_textdomain( 'awp-send', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function awp_register_settings() {
		register_setting( 'awp_storage_notifications', 'awp_notifications' );
		register_setting( 'awp_storage_instances', 'awp_instances' );
	}

	public function awp_admin_menu() {
		$config = get_option('awp_notifications');
		$my_page_1 = add_menu_page(
			__( 'Wawp Notification Settings', 'awp' ),
			__( 'Wawp', 'awp' ),
			'manage_options',
			'awp',
			array(
				$this->ui,
				'admin_page'
			),
			plugin_dir_url( __FILE__ ) . 'assets/img/menu.png'
		);
        add_action( 'load-' . $my_page_1, array( $this, 'awp_load_admin_js' ) );
        
        
        
	     add_submenu_page(
    'awp',
    __('Wawp Notification Settings', 'awp'),
    __('Notification Settings', 'awp'),
    'manage_options', 
    'awp',
    array($this->ui, 'admin_page')
);
        

	    $my_page_2 = add_submenu_page(
    'awp',
    __('Wawp Notification Message Logs', 'awp'),
    __('Notification Logs', 'awp'),
    'manage_options', 
    'awp-message-log',
    array($this->ui, 'logs_page')
);

        add_action( 'load-' . $my_page_2, array( $this, 'awp_load_admin_js' ) );   add_submenu_page(
    'awp',
    __('Wawp OTP Settings', 'awp'),
    __('Wawp OTP Settings', 'awp'),
    'administrator',
    'awp-otp'
);

        if (isset($_GET['post_type']) && $_GET['post_type'] == 'shop_order') {
            if( isset( $_GET['id'] ) ) {
                // $post_id = sanitize_text_field($_GET['id']);
				$post_id = isset( $_GET['id'] ) ? absint( sanitize_text_field( $_GET['id'] ) ) : 0;
				$result = $this->awp_wa_process_states( $post_id );
    		    ?>
    			    <div class="notice notice-success is-dismissible">
    <p><?php echo sprintf( __( 'Resend Message %s', 'awp-send' ), esc_html( $result ) ); ?></p>

</div>

	    	    <?php
            }
        }		
	}

    public function awp_load_admin_js(){
        add_action( 'admin_enqueue_scripts', array( $this, 'awp_admin_assets' ) );
    }
    
    public function awp_admin_assets(){
		wp_enqueue_style( 'awp-admin-style', plugins_url( 'assets/css/awp-admin-style.css', __FILE__ ), array(), '1.1.4' );
		wp_enqueue_style( 'awp-admin-emojicss', plugins_url( 'assets/css/emojionearea.min.css', __FILE__ ) );
			wp_enqueue_style( 'awp-admin-telcss', plugins_url( 'assets/css/intlTelInput.css', __FILE__ ) );

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-sortable' );		
		wp_enqueue_script( 'awp-admin-teljs', plugins_url( 'assets/js/intlTelInput.js', __FILE__ ), array( 'jquery' ), '17.0.8', true );
		wp_enqueue_script( 'awp-jquery-modal', plugins_url( 'assets/js/jquery.modal.min.js', __FILE__ ) );
		wp_enqueue_script( 'awp-admin-utils', plugins_url( 'assets/js/utils.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
     wp_enqueue_script( 'awp-plugin-textcomplete', plugins_url( 'assets/js/jquery.textcomplete.js', __FILE__ ), array( 'jquery' ), '1.0', true );

		wp_enqueue_script( 'awp-admin-js', plugins_url( 'assets/js/awp-admin-js.js', __FILE__ ), array(), '1.1.4' );
		wp_enqueue_script( 'awp-admin-emojijs', plugins_url( 'assets/js/emojionearea.min.js', __FILE__ ));
wp_enqueue_script( 'awp-admin-emojijs', plugins_url( 'assets/js/emojionearea.min.js', __FILE__ ), array('jquery'), '1.0.0', true );


 // Check if WordPress is in RTL mode and load RTL CSS if necessary
 
    if (is_rtl()) {
        wp_enqueue_style( 'awp-admin-rtl-style', plugins_url( 'assets/css/awp-admin-rtl-style.css', __FILE__ ), array(), '1.1.4' );
    }


        wp_enqueue_media();
        remove_action( 'admin_print_styles', 'print_emoji_styles' );
        remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
        remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
        remove_action( 'wp_print_styles', 'print_emoji_styles' );
    }    

	public function awp_admin_notices() {
		$screen = get_current_screen();
		if ( isset( $_GET['settings-updated'] ) && $screen->id == 'toplevel_page_awp' ) {
			?>
    			<div class="notice notice-success is-dismissible">
    				<p><?php _e( 'All changes has been saved!', 'awp-send' ); ?></p>
    			</div>
			<?php
		}
		if ( $screen->id == 'awp-send-new_page_awp-message-log' ) {
		    if( isset( $_GET['clear'] ) ) {
            $this->log->clear( 'awp-send','awp_logger');
		    ?>
	    		<div class="notice notice-success is-dismissible">
		    		<p><?php _e( 'Message logs has been cleared!', 'awp-send' ); ?></p>
			    </div>
    	    <?php
		    }
		    if( isset( $_POST['awp_resend_wa'] ) ) {
				$resend_phone = isset( $_POST['awp_resend_phone'] ) ? sanitize_text_field( $_POST['awp_resend_phone'] ) : '';
				$resend_message = isset( $_POST['awp_resend_message'] ) ? sanitize_textarea_field( $_POST['awp_resend_message'] ) : '';
				$resend_image = isset( $_POST['awp_resend_image'] ) ? esc_url_raw( $_POST['awp_resend_image'] ) : '';
				if ( ! $resend_phone || ! $resend_message ) {
					// handle the error here
				}else{
					$result = $this->awp_wa_send_msg( '', $resend_phone, $resend_message, $resend_image, '');
				}
		    ?>
			    <div class="notice notice-success is-dismissible">
    <p><?php echo esc_html( sprintf( __( 'Resend Message %s', 'awp-send' ), $result ) ); ?></p>
</div>

    	    <?php
            }
        }        
		if ( isset( $_POST['awp_send_test'] ) ) {
		    if(!empty($_POST['awp_test_number']))  {

				$test_number = isset( $_POST['awp_test_number'] ) ? sanitize_text_field( $_POST['awp_test_number'] ) : '';
				$test_message = isset( $_POST['awp_test_message'] ) ? sanitize_textarea_field( $_POST['awp_test_message'] ) : '';
				$test_image = isset( $_POST['awp_test_image'] ) ? esc_url_raw( $_POST['awp_test_image'] ) : '';
				if ( ! $test_number || ! $test_message ) {
					// handle the error here
				}else{
					$result = $this->awp_wa_send_msg( '', $test_number, $test_message, $test_image, '');
				}
    		    ?>
    		    <div class="notice notice-success is-dismissible">
    <p><?php echo esc_html( sprintf( __( 'Send Message %s', 'awp-send' ), $result ) ); ?></p>
</div>

	    	    <?php
		    }
		}
	}
		
	public function awp_wa_manual_new_columns($columns){
		$columns['notification']= __('Notification');
		return $columns;
	}

	public function awp_wa_manual_manage_columns($column_name, $id) {
		global $wpdb,$post;
		if ("notification" == $column_name){
			echo '<a href="'.admin_url('edit.php?post_type=shop_order&id='.$post->ID).'" class="button wc-action-buttonv">Resend WhatsApp</a>';
		}
	}  

    public function awp_custom_order_status() {
		if( $this->is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			global $custom_status_list_temp;
			$custom_status_list = wc_get_order_statuses();
			$custom_status_list_temp = array();
			$original_status = array( 
				'pending',
				'failed',
				'on-hold',
				'processing',
				'completed',
				'refunded',
				'cancelled',
			);
			foreach( $custom_status_list as $key => $status ) {
				$status_name = str_replace( "wc-", "", $key );
				if ( !in_array( $status_name, $original_status ) ) {
					$custom_status_list_temp[$status] = $status_name;
					add_action( 'woocommerce_order_status_'.$status_name, array( $this, 'awp_wa_process_states' ), 10 );
				}
			}
		}
	}

	public function awp_wa_order_receive( $order_id ) {
    if ( ! $order_id ) {
        return;
    }

    global $woocommerce;
    $order = new WC_Order( $order_id );
    $config = get_option('awp_notifications');
    $phone = $order->get_billing_phone();

    // Get the user's locale
    $user_locale = get_user_locale(get_current_user_id());

    // Use different messages based on the user's locale
      if ($user_locale == 'ar') {
    
   
        $msg = $this->awp_wa_process_variables($config['customer_neworder_arabic'], $order, '');
        $img = $config['customer_neworder_img_arabic'];
    } else {
       
        $msg = $this->awp_wa_process_variables($config['customer_neworder'], $order, '');
        $img = $config['customer_neworder_img'];
    }

    if (!empty($msg)) {
        $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
    }
}

    public function awp_wa_process_states_onhold($order) {
        global $woocommerce, $custom_status_list_temp;
        $order = new WC_Order($order);
        $status = $order->get_status();
        $status_list = array(
            'on-hold' => __('Receive', 'awp-send'),
        );
    
        foreach ($status_list as $status_lists => $translations) {
            if ($status == $status_lists) {
                $status = $translations;
            }
        }
    
        $config = get_option('awp_notifications');
        $phone = $order->get_billing_phone();
        
        // Get the user's locale
        $user_locale = get_user_locale(get_current_user_id());
    
        // Use different messages based on the user's locale
        if ($status == __('Receive', 'awp-send')) {
            if ($user_locale == 'ar') {
                $msg = $this->awp_wa_process_variables($config['order_onhold_arabic'], $order, '');
                $img = $config['order_onhold_img_arabic'];
            } else {
                $msg = $this->awp_wa_process_variables($config['order_onhold'], $order, '');
                $img = $config['order_onhold_img'];
            }
        }
        
        /* Admin Receive Notification */
    		if ($status == 'Receive') {
    		    $msg_admin = $this->awp_wa_process_variables($config['admin_onhold'], $order, '');
    		    $img_admin = $config['admin_onhold_img'];
    			$phone_admin = preg_replace('/[^0-9]/', '', $config['admin_number']);
    			if(!empty($msg_admin)) $this->awp_wa_send_msg($config, $phone_admin, $msg_admin, $img_admin, '');
    		}
    		
    
        if (!empty($msg)) {
            $result = $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
            return $result;
        }
    }

    public function awp_wa_process_states_pending($order) {
    global $woocommerce, $custom_status_list_temp;
    $order = new WC_Order($order);
    $status = $order->get_status();
    $status_list = array(
        'pending' => __('Pending', 'awp-send'),
    );

    foreach ($status_list as $status_lists => $translations) {
        if ($status == $status_lists) {
            $status = $translations;
        }
    }
     
    $config = get_option('awp_notifications');
    $phone = $order->get_billing_phone();
    
    // Get the user's locale
    $user_locale = get_user_locale(get_current_user_id());

    // Use different messages based on the user's locale
    if ($status == __('Pending', 'awp-send')) {
        if ($user_locale == 'ar') {
            $msg = $this->awp_wa_process_variables($config['order_pending_arabic'], $order, '');
            $img = $config['order_pending_img_arabic'];
        } else {
            $msg = $this->awp_wa_process_variables($config['order_pending'], $order, '');
            $img = $config['order_pending_img'];
        }
    }
    
    		/* Admin Pending Notification */
		if ($status == 'Pending') {
		    $msg_admin = $this->awp_wa_process_variables($config['admin_pending'], $order, '');
		    $img_admin = $config['admin_pending_img'];
			$phone_admin = preg_replace('/[^0-9]/', '', $config['admin_number']);
			if(!empty($msg_admin)) $this->awp_wa_send_msg($config, $phone_admin, $msg_admin, $img_admin, '');
		}


    if (!empty($msg)) {
        $result = $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
        return $result;
    }
}

    public function awp_wa_process_states_processing($order) {
    global $woocommerce, $custom_status_list_temp;
    $order = new WC_Order($order);
    $status = $order->get_status();
    $status_list = array(
        'processing' => __('Processing', 'awp-send'),
    );

    foreach ($status_list as $status_lists => $translations) {
        if ($status == $status_lists) {
            $status = $translations;
        }
    }
     
    $config = get_option('awp_notifications');
    $phone = $order->get_billing_phone();
    
    // Get the user's locale
    $user_locale = get_user_locale(get_current_user_id());

    // Use different messages based on the user's locale
    if ($status == __('Processing', 'awp-send')) {
        if ($user_locale == 'ar') {
         
            $msg = $this->awp_wa_process_variables($config['order_processing_arabic'], $order, '');
            $img = $config['order_processing_img_arabic'];
        } else {
              
            $msg = $this->awp_wa_process_variables($config['order_processing'], $order, '');
            $img = $config['order_processing_img'];
        }
    }
    
    /* Admin Processing Notification */
		if ($status == 'Processing') {
		    $msg_admin = $this->awp_wa_process_variables($config['admin_processing'], $order, '');
		    $img_admin = $config['admin_processing_img'];
			$phone_admin = preg_replace('/[^0-9]/', '', $config['admin_number']);
			if(!empty($msg_admin)) $this->awp_wa_send_msg($config, $phone_admin, $msg_admin, $img_admin, '');
		}

    if (!empty($msg)) {
        $result = $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
        return $result;
    }
}

    public function awp_wa_process_states_completed($order) {
    global $woocommerce, $custom_status_list_temp;
    $order = new WC_Order($order);
    $status = $order->get_status();
    $status_list = array(
        'completed' => __('Completed', 'awp-send'),
    );

    foreach ($status_list as $status_lists => $translations) {
        if ($status == $status_lists) {
            $status = $translations;
        }
    }
     
    $config = get_option('awp_notifications');
    $phone = $order->get_billing_phone();
    
    // Get the user's locale
    $user_locale = get_user_locale(get_current_user_id());

    // Use different messages based on the user's locale
    if ($status == __('Completed', 'awp-send')) {
        if ($user_locale == 'ar') {
            $msg = $this->awp_wa_process_variables($config['order_completed_arabic'], $order, '');
            $img = $config['order_completed_img_arabic'];
        } else {
            $msg = $this->awp_wa_process_variables($config['order_completed'], $order, '');
            $img = $config['order_completed_img'];
        }
    }
    
    /* Admin Completed Notification */
		if ($status == 'Completed') {
		    $msg_admin = $this->awp_wa_process_variables($config['admin_completed'], $order, '');
		    $img_admin = $config['admin_completed_img'];
			$phone_admin = preg_replace('/[^0-9]/', '', $config['admin_number']);
			if(!empty($msg_admin)) $this->awp_wa_send_msg($config, $phone_admin, $msg_admin, $img_admin, '');
		}

    if (!empty($msg)) {
        $result = $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
        return $result;
    }
}

    public function awp_wa_process_states_failed($order) {
    global $woocommerce, $custom_status_list_temp;
    $order = new WC_Order($order);
    $status = $order->get_status();
    $status_list = array(
        'failed' => __('Failed', 'awp-send'),
    );

    foreach ($status_list as $status_lists => $translations) {
        if ($status == $status_lists) {
            $status = $translations;
        }
    }
     
    $config = get_option('awp_notifications');
    $phone = $order->get_billing_phone();
    
    // Get the user's locale
    $user_locale = get_user_locale(get_current_user_id());

    // Use different messages based on the user's locale
    if ($status == __('Failed', 'awp-send')) {
        if ($user_locale == 'ar') {
            $msg = $this->awp_wa_process_variables($config['order_failed_arabic'], $order, '');
            $img = $config['order_failed_img_arabic'];
        } else {
            $msg = $this->awp_wa_process_variables($config['order_failed'], $order, '');
            $img = $config['order_failed_img'];
        }
    }
    
    /* Admin Failed Notification */
		if ($status == 'Failed') {
		    $msg_admin = $this->awp_wa_process_variables($config['admin_failed'], $order, '');
		    $img_admin = $config['admin_failed_img'];
			$phone_admin = preg_replace('/[^0-9]/', '', $config['admin_number']);
			if(!empty($msg_admin)) $this->awp_wa_send_msg($config, $phone_admin, $msg_admin, $img_admin, '');
		}

    if (!empty($msg)) {
        $result = $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
        return $result;
    }
}

    public function awp_wa_process_states_refunded($order) {
    global $woocommerce, $custom_status_list_temp;
    $order = new WC_Order($order);
    $status = $order->get_status();
    $status_list = array(
        'refunded' => __('Refunded', 'awp-send'),
    );

    foreach ($status_list as $status_lists => $translations) {
        if ($status == $status_lists) {
            $status = $translations;
        }
    }
     
    $config = get_option('awp_notifications');
    $phone = $order->get_billing_phone();
    
    // Get the user's locale
    $user_locale = get_user_locale(get_current_user_id());

    // Use different messages based on the user's locale
    if ($status == __('Refunded', 'awp-send')) {
        if ($user_locale == 'ar') {
            $msg = $this->awp_wa_process_variables($config['order_refunded_arabic'], $order, '');
            $img = $config['order_refunded_img_arabic'];
        } else {
            $msg = $this->awp_wa_process_variables($config['order_refunded'], $order, '');
            $img = $config['order_refunded_img'];
        }
    }
    
    /* Admin Refunded Notification */
		if ($status == 'Refunded') {
		    $msg_admin = $this->awp_wa_process_variables($config['admin_refunded'], $order, '');
		    $img_admin = $config['admin_refunded_img'];
			$phone_admin = preg_replace('/[^0-9]/', '', $config['admin_number']);
			if(!empty($msg_admin)) $this->awp_wa_send_msg($config, $phone_admin, $msg_admin, $img_admin, '');
		}

    if (!empty($msg)) {
        $result = $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
        return $result;
    }
}

    public function awp_wa_process_states_cancelled($order) {
    global $woocommerce, $custom_status_list_temp;
    $order = new WC_Order($order);
    $status = $order->get_status();
    $status_list = array(
        'cancelled' => __('Cancelled', 'awp-send'),
    );

    foreach ($status_list as $status_lists => $translations) {
        if ($status == $status_lists) {
            $status = $translations;
        }
    }
     
    $config = get_option('awp_notifications');
    $phone = $order->get_billing_phone();
    
    // Get the user's locale
    $user_locale = get_user_locale(get_current_user_id());

    // Use different messages based on the user's locale
    if ($status == __('Cancelled', 'awp-send')) {
        if ($user_locale == 'ar') {
            $msg = $this->awp_wa_process_variables($config['order_cancelled_arabic'], $order, '');
            $img = $config['order_cancelled_img_arabic'];
        } else {
            $msg = $this->awp_wa_process_variables($config['order_cancelled'], $order, '');
            $img = $config['order_cancelled_img'];
        }
    }
    
    /* Admin Cancelled Notification */
		if ($status == 'Cancelled') {
		    $msg_admin = $this->awp_wa_process_variables($config['admin_cancelled'], $order, '');
		    $img_admin = $config['admin_cancelled_img'];
			$phone_admin = preg_replace('/[^0-9]/', '', $config['admin_number']);
			if(!empty($msg_admin)) $this->awp_wa_send_msg($config, $phone_admin, $msg_admin, $img_admin, '');
		}

    if (!empty($msg)) {
        $result = $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
        return $result;
    }
}

    public function awp_wa_process_states($order) {
    global $woocommerce, $custom_status_list_temp;
    $order = new WC_Order($order);
    $status = $order->get_status();
    $status_list = array();
    foreach ($status_list as $status_lists => $translations) {
        if ($status == $status_lists) {
            $status = $translations;
        }
    }
     
    $config = get_option('awp_notifications');
    $phone = $order->get_billing_phone();
    $custom_status_list = $custom_status_list_temp;
    $msg = '';
    $img = '';

    if (!empty($custom_status_list)) {
        foreach ($custom_status_list as $status_name => $custom_status) {
            if (strtolower($status) == $custom_status) {
                // Use different messages based on the user's locale
                $user_locale = get_user_locale(get_current_user_id());

                if ($user_locale == 'ar') {
                    $msg = $this->awp_wa_process_variables($config['order_'.$custom_status.'_arabic'], $order, '');
                    $img = $config['order_'.$custom_status.'_img_arabic'];
                } else {
                    $msg = $this->awp_wa_process_variables($config['order_'.$custom_status], $order, '');
                    $img = $config['order_'.$custom_status.'_img'];
                }
            }
        }
    }

    if (!empty($msg)) {
        $result = $this->awp_wa_send_msg($config, $phone, $msg, $img, '');
        return $result;
    }
}

	public function awp_wa_default_country_code( $phone ) {
		$config = get_option('awp_notifications');
		$country_code = preg_replace('/[^0-9]/', '', $config['default_country']);
		if( ! $country_code ) {
			return $phone;
		}
		if (strpos($phone, $country_code) === 0) {
			return $phone;
		} else {
			if (strpos($phone, '0') === 0) {
				return preg_replace('/^0/',$country_code,$phone);
			} else {
				return $country_code . $phone;
			}
		}
	}

	public function awp_wa_process_note($data) {
		global $woocommerce;
		$order = new WC_Order($data['order_id']);
		$config = get_option('awp_notifications');
		$phone = $order->get_billing_phone();
		$this->awp_wa_send_msg($config, $phone, $this->awp_wa_process_variables($config['order_note'], $order, '', wptexturize($data['customer_note'])), $config['order_note_img'], '');
	}

    public function remove_emoji($text){
          return preg_replace('/[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0077}\x{E006C}\x{E0073}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0073}\x{E0063}\x{E0074}\x{E007F})|[\x{1F3F4}](?:\x{E0067}\x{E0062}\x{E0065}\x{E006E}\x{E0067}\x{E007F})|[\x{1F3F4}](?:\x{200D}\x{2620}\x{FE0F})|[\x{1F3F3}](?:\x{FE0F}\x{200D}\x{1F308})|[\x{0023}\x{002A}\x{0030}\x{0031}\x{0032}\x{0033}\x{0034}\x{0035}\x{0036}\x{0037}\x{0038}\x{0039}](?:\x{FE0F}\x{20E3})|[\x{1F441}](?:\x{FE0F}\x{200D}\x{1F5E8}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F467})|[\x{1F468}](?:\x{200D}\x{1F468}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467}\x{200D}\x{1F466})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F467})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F469}\x{200D}\x{1F466})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F468})|[\x{1F469}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F469})|[\x{1F469}\x{1F468}](?:\x{200D}\x{2764}\x{FE0F}\x{200D}\x{1F48B}\x{200D}\x{1F468})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B3})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B2})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B1})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F9B0})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F9B0})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2640}\x{FE0F})|[\x{1F575}\x{1F3CC}\x{26F9}\x{1F3CB}](?:\x{FE0F}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FF}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FE}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FD}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FC}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FB}\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F9B8}\x{1F9B9}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{200D}\x{2640}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FF}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FE}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FD}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FC}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{1F3FB}\x{200D}\x{2642}\x{FE0F})|[\x{1F46E}\x{1F9B8}\x{1F9B9}\x{1F482}\x{1F477}\x{1F473}\x{1F471}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F9DE}\x{1F9DF}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F46F}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93C}\x{1F93D}\x{1F93E}\x{1F939}](?:\x{200D}\x{2642}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F692})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F680})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2708}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A8})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3A4})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BB})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F52C})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F4BC})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3ED})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F527})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F373})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F33E})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2696}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F3EB})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{200D}\x{1F393})|[\x{1F468}\x{1F469}](?:\x{1F3FF}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FE}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FD}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FC}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{1F3FB}\x{200D}\x{2695}\x{FE0F})|[\x{1F468}\x{1F469}](?:\x{200D}\x{2695}\x{FE0F})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FF})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FE})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FD})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FC})|[\x{1F476}\x{1F9D2}\x{1F466}\x{1F467}\x{1F9D1}\x{1F468}\x{1F469}\x{1F9D3}\x{1F474}\x{1F475}\x{1F46E}\x{1F575}\x{1F482}\x{1F477}\x{1F934}\x{1F478}\x{1F473}\x{1F472}\x{1F9D5}\x{1F9D4}\x{1F471}\x{1F935}\x{1F470}\x{1F930}\x{1F931}\x{1F47C}\x{1F385}\x{1F936}\x{1F9D9}\x{1F9DA}\x{1F9DB}\x{1F9DC}\x{1F9DD}\x{1F64D}\x{1F64E}\x{1F645}\x{1F646}\x{1F481}\x{1F64B}\x{1F647}\x{1F926}\x{1F937}\x{1F486}\x{1F487}\x{1F6B6}\x{1F3C3}\x{1F483}\x{1F57A}\x{1F9D6}\x{1F9D7}\x{1F9D8}\x{1F6C0}\x{1F6CC}\x{1F574}\x{1F3C7}\x{1F3C2}\x{1F3CC}\x{1F3C4}\x{1F6A3}\x{1F3CA}\x{26F9}\x{1F3CB}\x{1F6B4}\x{1F6B5}\x{1F938}\x{1F93D}\x{1F93E}\x{1F939}\x{1F933}\x{1F4AA}\x{1F9B5}\x{1F9B6}\x{1F448}\x{1F449}\x{261D}\x{1F446}\x{1F595}\x{1F447}\x{270C}\x{1F91E}\x{1F596}\x{1F918}\x{1F919}\x{1F590}\x{270B}\x{1F44C}\x{1F44D}\x{1F44E}\x{270A}\x{1F44A}\x{1F91B}\x{1F91C}\x{1F91A}\x{1F44B}\x{1F91F}\x{270D}\x{1F44F}\x{1F450}\x{1F64C}\x{1F932}\x{1F64F}\x{1F485}\x{1F442}\x{1F443}](?:\x{1F3FB})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FA}](?:\x{1F1FF})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1FA}](?:\x{1F1FE})|[\x{1F1E6}\x{1F1E8}\x{1F1F2}\x{1F1F8}](?:\x{1F1FD})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F9}\x{1F1FF}](?:\x{1F1FC})|[\x{1F1E7}\x{1F1E8}\x{1F1F1}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1FB})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1FB}](?:\x{1F1FA})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FE}](?:\x{1F1F9})|[\x{1F1E6}\x{1F1E7}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FA}\x{1F1FC}](?:\x{1F1F8})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F7})|[\x{1F1E6}\x{1F1E7}\x{1F1EC}\x{1F1EE}\x{1F1F2}](?:\x{1F1F6})|[\x{1F1E8}\x{1F1EC}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}](?:\x{1F1F5})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EE}\x{1F1EF}\x{1F1F2}\x{1F1F3}\x{1F1F7}\x{1F1F8}\x{1F1F9}](?:\x{1F1F4})|[\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1F3})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1EC}\x{1F1ED}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F4}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FF}](?:\x{1F1F2})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1F1})|[\x{1F1E8}\x{1F1E9}\x{1F1EB}\x{1F1ED}\x{1F1F1}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FD}](?:\x{1F1F0})|[\x{1F1E7}\x{1F1E9}\x{1F1EB}\x{1F1F8}\x{1F1F9}](?:\x{1F1EF})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EB}\x{1F1EC}\x{1F1F0}\x{1F1F1}\x{1F1F3}\x{1F1F8}\x{1F1FB}](?:\x{1F1EE})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F5}\x{1F1F8}\x{1F1F9}](?:\x{1F1ED})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}](?:\x{1F1EC})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F9}\x{1F1FC}](?:\x{1F1EB})|[\x{1F1E6}\x{1F1E7}\x{1F1E9}\x{1F1EA}\x{1F1EC}\x{1F1EE}\x{1F1EF}\x{1F1F0}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F7}\x{1F1F8}\x{1F1FB}\x{1F1FE}](?:\x{1F1EA})|[\x{1F1E6}\x{1F1E7}\x{1F1E8}\x{1F1EC}\x{1F1EE}\x{1F1F2}\x{1F1F8}\x{1F1F9}](?:\x{1F1E9})|[\x{1F1E6}\x{1F1E8}\x{1F1EA}\x{1F1EE}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F8}\x{1F1F9}\x{1F1FB}](?:\x{1F1E8})|[\x{1F1E7}\x{1F1EC}\x{1F1F1}\x{1F1F8}](?:\x{1F1E7})|[\x{1F1E7}\x{1F1E8}\x{1F1EA}\x{1F1EC}\x{1F1F1}\x{1F1F2}\x{1F1F3}\x{1F1F5}\x{1F1F6}\x{1F1F8}\x{1F1F9}\x{1F1FA}\x{1F1FB}\x{1F1FF}](?:\x{1F1E6})|[\x{00A9}\x{00AE}\x{203C}\x{2049}\x{2122}\x{2139}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{2328}\x{23CF}\x{23E9}-\x{23F3}\x{23F8}-\x{23FA}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2600}-\x{2604}\x{260E}\x{2611}\x{2614}-\x{2615}\x{2618}\x{261D}\x{2620}\x{2622}-\x{2623}\x{2626}\x{262A}\x{262E}-\x{262F}\x{2638}-\x{263A}\x{2640}\x{2642}\x{2648}-\x{2653}\x{2660}\x{2663}\x{2665}-\x{2666}\x{2668}\x{267B}\x{267E}-\x{267F}\x{2692}-\x{2697}\x{2699}\x{269B}-\x{269C}\x{26A0}-\x{26A1}\x{26AA}-\x{26AB}\x{26B0}-\x{26B1}\x{26BD}-\x{26BE}\x{26C4}-\x{26C5}\x{26C8}\x{26CE}-\x{26CF}\x{26D1}\x{26D3}-\x{26D4}\x{26E9}-\x{26EA}\x{26F0}-\x{26F5}\x{26F7}-\x{26FA}\x{26FD}\x{2702}\x{2705}\x{2708}-\x{270D}\x{270F}\x{2712}\x{2714}\x{2716}\x{271D}\x{2721}\x{2728}\x{2733}-\x{2734}\x{2744}\x{2747}\x{274C}\x{274E}\x{2753}-\x{2755}\x{2757}\x{2763}-\x{2764}\x{2795}-\x{2797}\x{27A1}\x{27B0}\x{27BF}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}\x{1F004}\x{1F0CF}\x{1F170}-\x{1F171}\x{1F17E}-\x{1F17F}\x{1F18E}\x{1F191}-\x{1F19A}\x{1F201}-\x{1F202}\x{1F21A}\x{1F22F}\x{1F232}-\x{1F23A}\x{1F250}-\x{1F251}\x{1F300}-\x{1F321}\x{1F324}-\x{1F393}\x{1F396}-\x{1F397}\x{1F399}-\x{1F39B}\x{1F39E}-\x{1F3F0}\x{1F3F3}-\x{1F3F5}\x{1F3F7}-\x{1F3FA}\x{1F400}-\x{1F4FD}\x{1F4FF}-\x{1F53D}\x{1F549}-\x{1F54E}\x{1F550}-\x{1F567}\x{1F56F}-\x{1F570}\x{1F573}-\x{1F57A}\x{1F587}\x{1F58A}-\x{1F58D}\x{1F590}\x{1F595}-\x{1F596}\x{1F5A4}-\x{1F5A5}\x{1F5A8}\x{1F5B1}-\x{1F5B2}\x{1F5BC}\x{1F5C2}-\x{1F5C4}\x{1F5D1}-\x{1F5D3}\x{1F5DC}-\x{1F5DE}\x{1F5E1}\x{1F5E3}\x{1F5E8}\x{1F5EF}\x{1F5F3}\x{1F5FA}-\x{1F64F}\x{1F680}-\x{1F6C5}\x{1F6CB}-\x{1F6D2}\x{1F6E0}-\x{1F6E5}\x{1F6E9}\x{1F6EB}-\x{1F6EC}\x{1F6F0}\x{1F6F3}-\x{1F6F9}\x{1F910}-\x{1F93A}\x{1F93C}-\x{1F93E}\x{1F940}-\x{1F945}\x{1F947}-\x{1F970}\x{1F973}-\x{1F976}\x{1F97A}\x{1F97C}-\x{1F9A2}\x{1F9B0}-\x{1F9B9}\x{1F9C0}-\x{1F9C2}\x{1F9D0}-\x{1F9FF}]/u', '', $text);
    }	
    
	public function awp_wa_send_msg($config, $phone, $msg, $img, $resend) {
        global $result;	    
		$config = get_option('awp_notifications');
        $phone = preg_replace('/[^0-9]/', '', $phone);
		$phone = $this->awp_wa_default_country_code($phone);
		if(substr( $phone, 0, 2 ) === "52") {
			if(substr( $phone, 0, 3 ) !== "521") {
				$phone = '521' . substr($phone, 2);
			}
		}
		
		$instances = get_option('awp_instances');
        if(isset($instances['dashboard_prefix']) && isset($instances['access_token']) && isset($instances['instance_id'])) {
            $dashboard_prefix = $instances['dashboard_prefix'];
            $access_token = $instances['access_token'];
            $instance_id = $instances['instance_id'];
        
            $msg = $this->spintax($msg);
        
        } else {
           // handle error or provide default values
        }

		$msg = $this->spintax($msg);
		$instances = get_option('awp_instances');
		$dashboard_prefix = isset($instances['dashboard_prefix']) ? $instances['dashboard_prefix'] : '';

		$access_token = $instances['access_token'];
		$instance_id = $instances['instance_id'];
        if ( empty( $img ) ) {
			$url = 'https://app.wawp.net/api/send?number=' . $phone . '&type=text&message=' . urlencode($msg) . '&instance_id=' . $instance_id . '&access_token=' . $access_token;
			$rest_response = wp_remote_retrieve_body( wp_remote_get( $url, array( 'sslverify' => true, 'timeout' => 60 ) ) );
        } else {
			$url = 'https://app.wawp.net/api/send?number=' . $phone . '&type=media&message=' . urlencode($msg) . '&media_url=' . $img . '&instance_id=' . $instance_id . '&access_token=' . $access_token;			
			$rest_response = wp_remote_retrieve_body( wp_remote_get( $url, array( 'sslverify' => true, 'timeout' => 60 ) ) );
        }
		$current_datetime = date( get_option('date_format') . ' ' . get_option('time_format') );
		$result = json_decode($rest_response, true);
$this->log->add(
    'awpsend', 
    '<tr><td>' . $current_datetime . 
    '</td><td class="log-phone">' . $phone . 
    '</td><td class="log-msg"><div>' . $msg . '</div></td><td class="log-img">' . $img . '</td>
    
<td>' . $result["status"] . '</td> 
<td style="max-height: 50px; overflow-y: auto;">' . (is_array($result['message']) ? json_encode($result['message']) : $result['message']) .
    '</td>
    

    <td><button type="button" class="button log-resend" data-instance-id="' . $instance_id . '" data-access-token="' . $access_token . '" data-phone="' . $phone . '" data-message="' . $msg . '" data-img="' . $img . '">Resend WhatsApp</button></td>
    
    </tr>'
);


if ( empty( $result["status"] ) ) {
			$url = 'https://app.wawp.net/api/reconnect?instance_id=' . $instance_id . '&access_token=' . $access_token;
			$rest_response = wp_remote_retrieve_body( wp_remote_get( $url, array( 'sslverify' => true, 'timeout' => 60 ) ) );
        }
        return $result["status"];
	}
    
	public function awp_wa_encoding($msg) {
		return htmlentities($msg, ENT_QUOTES, "UTF-8");
	}

	public function awp_wa_process_variables($msg, $order, $variables, $note = '') {
		global $wpdb, $woocommerce;
		$awp_wa = array( "id", "order_key", "billing_first_name", "billing_last_name", "billing_company", "billing_address_1", "billing_address_2", "billing_city", "billing_postcode", "billing_country", "billing_state", "billing_email", "billing_phone", "shipping_first_name", "shipping_last_name", "shipping_company", "shipping_address_1", "shipping_address_2", "shipping_city", "shipping_postcode", "shipping_country", "shipping_state", "shipping_method", "shipping_method_title", "bacs_account", "payment_method", "payment_method_title", "order_subtotal", "order_discount", "cart_discount", "order_tax", "order_shipping", "order_shipping_tax", "order_total", "status", "shop_name", "currency", "cust_note", "note", "product", "product_name", "dpd", "unique_transfer_code", "order_date", "order_link" ); 
		$variables = str_replace(array("\r\n", "\r"), "\n", $variables);
		$variables = explode("\n", $variables);
    preg_match_all("/{{(.*?)}}/", $msg, $search);
    $currency = get_woocommerce_currency_symbol();
		foreach ($search[1] as $variable) { 
			$variable = strtolower($variable);
			// if (!in_array($variable, $awp_wa) && !in_array($variable, $variables)) continue;
			if ($variable != "id" && $variable != "shop_name" && $variable != "currency" && $variable != "shipping_method" && $variable != "cust_note" && $variable != "note" && $variable != "bacs_account" && $variable != "order_subtotal" && $variable != "order_shipping" && $variable != "product" && $variable != "product_name" && $variable != "dpd" && $variable != "unique_transfer_code" && $variable != "order_date" && $variable != "order_link") {
					if (in_array($variable, $awp_wa)) {
						$msg = str_replace("{{" . $variable . "}}", get_post_meta($order->get_id(), '_'.$variable, true), $msg);	
					} else {
						if(strlen($order->order_custom_fields[$variable][0]) == 0) {
							$msg = str_replace("{{" . $variable . "}}", get_post_meta($order->get_id(), $variable, true), $msg);	
						} else {
							$msg = str_replace("{{" . $variable . "}}", $order->order_custom_fields[$variable][0], $msg);
						}
					}
				}
			else if ($variable == "id") $msg = str_replace("{{" . $variable . "}}", $order->get_id(), $msg);
			else if ($variable == "shop_name") $msg = str_replace("{{" . $variable . "}}", get_bloginfo('name'), $msg);
			else if ($variable == "currency") $msg = str_replace("{{" . $variable . "}}", html_entity_decode($currency), $msg);
			else if ($variable == "cust_note") $msg = str_replace("{{" . $variable . "}}", $order->get_customer_note(), $msg);
			else if ($variable == "shipping_method") $msg = str_replace("{{" . $variable . "}}", $order->get_shipping_method(), $msg);
			else if ($variable == "note") $msg = str_replace("{{" . $variable . "}}", $note, $msg);
			else if ($variable == "order_subtotal") $msg = str_replace("{{" . $variable . "}}", number_format($order->get_subtotal(), wc_get_price_decimals()), $msg);
			else if ($variable == "order_shipping") $msg = str_replace("{{" . $variable . "}}", number_format(get_post_meta($order->get_id(), '_order_shipping', true), wc_get_price_decimals()), $msg);
			else if ($variable == "dpd") {
				$order_id = $order->get_id();
				$table_name = $wpdb->prefix.'dpd_orders';
				$parcels = $wpdb->get_results("SELECT id, parcel_number, date FROM $table_name WHERE order_id = $order_id AND (order_type != 'amazon_prime' OR order_type IS NULL ) AND status !='trash'");
				if( count ( $parcels ) > 0 ) {
					foreach ( $parcels as $parcel ) {
						$dpd = $parcel->parcel_number;	
					}
				}
				$msg = str_replace("{{" . $variable . "}}", $dpd, $msg);				    
			}
			else if ($variable == "product") {
			    $product_items = '';
                $order = wc_get_order($order->get_id());
                $i = 0;
                foreach ($order->get_items() as $item_id => $item_data) {
                    $i++;
                    $new_line = ($i > 1) ? '
' : '';
                    $product = $item_data->get_product();
                    $product_name = $product->get_name();
                    $item_quantity = $item_data->get_quantity();
                    $item_total = $item_data->get_total();
                    $product_items .= $new_line . $i . '. '.$product_name.' x '.$item_quantity.' = '.$currency.' '.number_format($item_total, wc_get_price_decimals());
                }	
                $msg = str_replace("{{" . $variable . "}}", html_entity_decode($product_items), $msg);
			}
			else if ($variable == "product_name") {
			    $product_items = '';
                $order = wc_get_order($order->get_id());
                $i = 0;
                foreach ($order->get_items() as $item_id => $item_data) {
                    $i++;
                    $new_line = ($i > 1) ? '
' : '';
                    $product = $item_data->get_product();
                    $product_name = $product->get_name();
                    $product_items .= $new_line . $i . '. '.$product_name;
                }	
                $msg = str_replace("{{" . $variable . "}}", html_entity_decode($product_items), $msg);
			}
			else if ($variable == "unique_transfer_code") {
				$mtotal = get_post_meta($order->get_id(), '_order_total', true);
				$mongkir = get_post_meta($order->get_id(), '_order_shipping', true);
				$kode_unik = $mtotal - $mongkir;
				$msg = str_replace("{{" . $variable . "}}", $kode_unik, $msg);						
			}
			else if ($variable == "order_date") {
				$order = wc_get_order($order->get_id());
				$date = $order->get_date_created();
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );
				$msg = str_replace("{{" . $variable . "}}", date($date_format . ' ' . $time_format, strtotime($date)), $msg);						
			}
			else if ($variable == "order_link") {
				$order_received_url = wc_get_endpoint_url( 'order-received', $order->get_id(), wc_get_checkout_url() );
				$order_received_url = add_query_arg( 'key', $order->get_order_key(), $order_received_url );
				$msg = str_replace("{{" . $variable . "}}", $order_received_url, $msg);						
			}
			else if ($variable == "bacs_account") {
				$gateway    = new WC_Gateway_BACS();
				$country    = WC()->countries->get_base_country();
				$locale     = $gateway->get_country_locale();
				$bacs_info  = get_option( 'woocommerce_bacs_accounts');
				$sort_code_label = isset( $locale[ $country ]['sortcode']['label'] ) ? $locale[ $country ]['sortcode']['label'] : __( 'Sort code', 'woocommerce' );
				$i = -1;
				$bacs_items = '';
				if ( $bacs_info ) {
					foreach ( $bacs_info as $account ) {
						$i++;
						$new_line = ($i > 0) ? '

' : '';
						$account_name   = esc_attr( wp_unslash( $account['account_name'] ) );
						$bank_name      = esc_attr( wp_unslash( $account['bank_name'] ) );
						$account_number = esc_attr( $account['account_number'] );
						$sort_code      = esc_attr( $account['sort_code'] );
						$iban_code      = esc_attr( $account['iban'] );
						$bic_code       = esc_attr( $account['bic'] );
						$bacs_items .=  $new_line . '馃彟 ' . $bank_name .'
' . '馃さ ' . $account_name . '
' . '鉁?? ' . $account_number;
					}
				}
				$msg = str_replace("{{" . $variable . "}}", $bacs_items, $msg);
			}
		}
		return $msg;
	}	
	
	public function spintax($str) {
		return preg_replace_callback("/{(.*?)}/", function ($match) {
			$words = explode("|", $match[1]);
			return $words[array_rand($words)];
		}, $str);
	}

	public function followup_order() {	
		global $woocommerce;
		$config = get_option('awp_notifications');
		$customer_orders = wc_get_orders( array(
			'limit'    => -1,
			'date_after' => date( 'Y-m-d', strtotime( '-14 days' ) ),
			'status'   => 'on-hold'
			) );
		if( isset( $customer_orders ) ) {	
			$followup_send = [];
			foreach ( $customer_orders as $order => $single_order ) {
				$today = date_create(date('Y-m-d H:i:s'));
				$purchase_date = date_create( $single_order->date_created->date('Y-m-d H:i:s') );
				$ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($purchase_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;
				$followup_day = $config['followup_onhold_day'];
				
				if( empty( $followup_day ) )
					$followup_day = 24;
				if( $day_range >= $followup_day ) {
					$sent = get_post_meta( $single_order->ID, 'followup', true );
					if(empty($sent) || $sent == null){
						update_post_meta($single_order->ID, 'followup', '0');
					}
					if($sent == '0'){
						echo esc_attr( $single_order->ID ) . ' = ' . esc_attr( $sent ) . '<br>';
						// echo esc_html($single_order->ID.' = '.$sent).'<br>';
						$followup_send[] = $single_order->ID;
					}
				}
			}
			if( count( $followup_send ) != 0 ) {
				foreach ( $followup_send as $flw => $foll_id ) {
					$order = new WC_Order( $foll_id );
					$msg = $this->awp_wa_process_variables($config['followup_onhold'], $order, '');
				    $img = $config['followup_onhold_img'];
					$phone = $order->get_billing_phone();
					if(!empty($msg)) 
						$this->awp_wa_send_msg($config, $phone, $msg, $img, '');
					update_post_meta($foll_id, 'followup', '1');
				}
			}
		}
	}

	public function followup_order_2() {	
		global $woocommerce;
		$config = get_option('awp_notifications');
		$customer_orders = wc_get_orders( array(
			'limit'    => -1,
			'date_after' => date( 'Y-m-d', strtotime( '-14 days' ) ),
			'status'   => 'on-hold'
			) );
		if( isset( $customer_orders ) ) {	
			$followup_send_2 = [];
			foreach ( $customer_orders as $order => $single_order ) {
				$today = date_create(date('Y-m-d H:i:s'));
				$purchase_date = date_create( $single_order->date_created->date('Y-m-d H:i:s') );
				$ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($purchase_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;
				$followup_day = $config['followup_onhold_day_2'];
				
				if( empty( $followup_day ) )
					$followup_day = 48;
				if( $day_range >= $followup_day ) {
					$sent = get_post_meta( $single_order->ID, 'followup_2', true );
					if(empty($sent) || $sent == null){
						update_post_meta($single_order->ID, 'followup_2', '0');
					}
					if($sent == '0'){
						echo esc_attr( $single_order->ID ) . ' = ' . esc_attr( $sent ) . '<br>';
						// echo esc_html($single_order->ID.' = '.$sent).'<br>';
						$followup_send_2[] = $single_order->ID;
					}
				}
			}
			if( count( $followup_send_2 ) != 0 ) {
				foreach ( $followup_send_2 as $flw => $foll_id ) {
					$order = new WC_Order( $foll_id );
					$msg = $this->awp_wa_process_variables($config['followup_onhold_2'], $order, '');
				    $img = $config['followup_onhold_img_2'];
					$phone = $order->get_billing_phone();
					if(!empty($msg)) 
						$this->awp_wa_send_msg($config, $phone, $msg, $img, '');
					update_post_meta($foll_id, 'followup_2', '1');
				}
			}
		}
	}
	
	public function followup_order_3() {	
		global $woocommerce;
		$config = get_option('awp_notifications');
		$customer_orders = wc_get_orders( array(
			'limit'    => -1,
			'date_after' => date( 'Y-m-d', strtotime( '-14 days' ) ),
			'status'   => 'on-hold'
			) );
		if( isset( $customer_orders ) ) {	
			$followup_send_3 = [];
			foreach ( $customer_orders as $order => $single_order ) {
				$today = date_create(date('Y-m-d H:i:s'));
				$purchase_date = date_create( $single_order->date_created->date('Y-m-d H:i:s') );
				$ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($purchase_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;
				$followup_day = $config['followup_onhold_day_3'];
				
				if( empty( $followup_day ) )
					$followup_day = 72;
				if( $day_range >= $followup_day ) {
					$sent = get_post_meta( $single_order->ID, 'followup_3', true );
					if(empty($sent) || $sent == null){
						update_post_meta($single_order->ID, 'followup_3', '0');
					}
					if($sent == '0'){
						echo esc_attr( $single_order->ID ) . ' = ' . esc_attr( $sent ) . '<br>';
						// echo esc_html($single_order->ID.' = '.$sent).'<br>';
						$followup_send_3[] = $single_order->ID;
					}
				}
			}
			if( count( $followup_send_3 ) != 0 ) {
				foreach ( $followup_send_3 as $flw => $foll_id ) {
					$order = new WC_Order( $foll_id );
					$msg = $this->awp_wa_process_variables($config['followup_onhold_3'], $order, '');
				    $img = $config['followup_onhold_img_3'];
					$phone = $order->get_billing_phone();
					if(!empty($msg)) 
						$this->awp_wa_send_msg($config, $phone, $msg, $img, '');
					update_post_meta($foll_id, 'followup_3', '1');
				}
			}
		}
	}
	
	public function aftersales_order() {	
		global $woocommerce;
		$config = get_option('awp_notifications');
		$customer_orders = wc_get_orders( array(
			'limit'    => -1,
			'date_after' => date( 'Y-m-d', strtotime( '-14 days' ) ),
			'status'   => 'completed'
			) );
		if( isset( $customer_orders ) ) {	
			$aftersales_send = [];
			foreach ( $customer_orders as $order => $single_order ) {
				$today = date_create(date('Y-m-d H:i:s'));
				$purchase_date = date_create( $single_order->date_created->date('Y-m-d H:i:s') );
				$paid_date_raw = date_format( date_create( get_post_meta($single_order->ID,'_completed_date',true) ), "Y-m-d H:i:s" );
				$paid_date_obj = new DateTime();
				$paid_date = $paid_date_obj->createFromFormat('Y-m-d H:i:s', $paid_date_raw);
				$ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($paid_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;
				
				$aftersales_day = $config['followup_aftersales_day'];
				if( empty( $aftersales_day ) )
					$aftersales_day = 48;
				if( $day_range >= $aftersales_day ) {
					$sent = get_post_meta( $single_order->ID, 'aftersales', true );
					if(empty($sent) || $sent == null){
						update_post_meta($single_order->ID, 'aftersales', '0');
					}
					if($sent == '0'){
						$aftersales_send[] = $single_order->ID;
					}
				}
			}
			if( count( $aftersales_send ) != 0 ) {
				foreach ( $aftersales_send as $flw => $foll_id ) {
					$order = new WC_Order( $foll_id );
					$msg = $this->awp_wa_process_variables($config['followup_aftersales'], $order, '');
				    $img = $config['followup_aftersales_img'];
					$phone = $order->get_billing_phone();
					if(!empty($msg)) 
						$this->awp_wa_send_msg($config, $phone, $msg, $img, '');
					update_post_meta($foll_id, 'aftersales', '1');
				}
			}
		}
	}	
	
    public function aftersales_order_2() {	
		global $woocommerce;
		$config = get_option('awp_notifications');
		$customer_orders = wc_get_orders( array(
			'limit'    => -1,
			'date_after' => date( 'Y-m-d', strtotime( '-14 days' ) ),
			'status'   => 'completed'
			) );
		if( isset( $customer_orders ) ) {	
			$aftersales_send_2 = [];
			foreach ( $customer_orders as $order => $single_order ) {
				$today = date_create(date('Y-m-d H:i:s'));
				$purchase_date = date_create( $single_order->date_created->date('Y-m-d H:i:s') );
				$paid_date_raw = date_format( date_create( get_post_meta($single_order->ID,'_completed_date',true) ), "Y-m-d H:i:s" );
				$paid_date_obj = new DateTime();
				$paid_date = $paid_date_obj->createFromFormat('Y-m-d H:i:s', $paid_date_raw);
				$ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($paid_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;
				
				$aftersales_day_2 = $config['followup_aftersales_day_2'];
				if( empty( $aftersales_day_2 ) )
					$aftersales_day_2 = 72;
				if( $day_range >= $aftersales_day_2 ) {
					$sent = get_post_meta( $single_order->ID, 'aftersales_2', true );
					if(empty($sent) || $sent == null){
						update_post_meta($single_order->ID, 'aftersales_2', '0');
					}
					if($sent == '0'){
						$aftersales_send_2[] = $single_order->ID;
					}
				}
			}
			if( count( $aftersales_send_2 ) != 0 ) {
				foreach ( $aftersales_send_2 as $flw => $foll_id ) {
					$order = new WC_Order( $foll_id );
					$msg = $this->awp_wa_process_variables($config['followup_aftersales_2'], $order, '');
				    $img = $config['followup_aftersales_img_2'];
					$phone = $order->get_billing_phone();
					if(!empty($msg)) 
						$this->awp_wa_send_msg($config, $phone, $msg, $img, '');
					update_post_meta($foll_id, 'aftersales_2', '1');
				}
			}
		}
	}	
	
	public function aftersales_order_3() {	
		global $woocommerce;
		$config = get_option('awp_notifications');
		$customer_orders = wc_get_orders( array(
			'limit'    => -1,
			'date_after' => date( 'Y-m-d', strtotime( '-14 days' ) ),
			'status'   => 'completed'
			) );
		if( isset( $customer_orders ) ) {	
			$aftersales_send_3 = [];
			foreach ( $customer_orders as $order => $single_order ) {
				$today = date_create(date('Y-m-d H:i:s'));
				$purchase_date = date_create( $single_order->date_created->date('Y-m-d H:i:s') );
				$paid_date_raw = date_format( date_create( get_post_meta($single_order->ID,'_completed_date',true) ), "Y-m-d H:i:s" );
				$paid_date_obj = new DateTime();
				$paid_date = $paid_date_obj->createFromFormat('Y-m-d H:i:s', $paid_date_raw);
				$ts1 = strtotime($today->format('Y-m-d H:i:s'));
                $ts2 = strtotime($paid_date->format('Y-m-d H:i:s'));
                $day_range = abs($ts1 - $ts2) / 3600;
				
				$aftersales_day_3 = $config['followup_aftersales_day_3'];
				if( empty( $aftersales_day_3 ) )
					$aftersales_day_3 = 96;
				if( $day_range >= $aftersales_day_3 ) {
					$sent = get_post_meta( $single_order->ID, 'aftersales_3', true );
					if(empty($sent) || $sent == null){
						update_post_meta($single_order->ID, 'aftersales_3', '0');
					}
					if($sent == '0'){
						$aftersales_send_3[] = $single_order->ID;
					}
				}
			}
			if( count( $aftersales_send_3 ) != 0 ) {
				foreach ( $aftersales_send_3 as $flw => $foll_id ) {
					$order = new WC_Order( $foll_id );
					$msg = $this->awp_wa_process_variables($config['followup_aftersales_3'], $order, '');
				    $img = $config['followup_aftersales_img_3'];
					$phone = $order->get_billing_phone();
					if(!empty($msg)) 
						$this->awp_wa_send_msg($config, $phone, $msg, $img, '');
					update_post_meta($foll_id, 'aftersales_3', '1');
				}
			}
		}
	}
	
	public function abandoned_order() {	
	    if( $this->is_plugin_active( 'woo-save-abandoned-carts/cartbounty-abandoned-carts.php' ) ) {
            global $wpdb;
            $config = get_option('awp_notifications');
            $table_name = $wpdb->prefix.'cartbounty';
            $ab_carts = $wpdb->get_results( "SELECT * FROM $table_name WHERE other_fields != '1'" );
            if( isset( $ab_carts ) ) {	
                foreach ( $ab_carts as $ab_cart => $cart ) {
                    $id = $cart->id;
                    $name = $cart->name;
                    $surname = $cart->surname;
                    $email = $cart->email;
                     // Check for an existing WooCommerce order by email
                    $orders = wc_get_orders( array('billing_email' => $email) );
                    if( count($orders) > 0 ) {
                        // An order exists for this email, skip sending message
                        continue;
                }
                    $phone = $cart->phone;
                    $total = $cart->cart_total;
                    $currency = $cart->currency;
            		$today = date_create(date('Y-m-d H:i:s'));
            		$abandoned_date_raw = date_format( date_create( $cart->time ), "Y-m-d H:i:s" );
            		$abandoned_date_obj = new DateTime();
            		$abandoned_date = $abandoned_date_obj->createFromFormat('Y-m-d H:i:s', $abandoned_date_raw);
            		$ts1 = strtotime($today->format('Y-m-d H:i:s'));
                    $ts2 = strtotime($abandoned_date->format('Y-m-d H:i:s'));
                    $day_range = round(abs($ts1 - $ts2) / 3600);
            		$abandoned_day = $config['followup_abandoned_day'];
                    $product_array = @unserialize($cart->cart_contents);
                    if ($product_array){
            		    $product_items = '';
                        $i = 0;
                        foreach($product_array as $product){
                            $i++;
                            $new_line = ($i > 1) ? '\n' : '';
                            $product_name = $product['product_title'];
                            $item_quantity =  $product['quantity'];
                            $item_total = $product['product_variation_price'];
                            $product_items .= $new_line . $i . '. '.$product_name.' x '.$item_quantity.' = '.$currency.' '.$item_total;
                        }
                    }        
            		if( empty( $abandoned_day ) )
            			$abandoned_day = 24;
            		if( $day_range >= $abandoned_day ) {
                        $replace_in_message = ["%billing_first_name%", "%billing_last_name%", "%billing_email%", "%billing_phone%", "%product%", "%order_total%", "%currency%"];
                        $replace_with_message   = [$name, $surname, $email, $phone, $product_items, $total, $currency];
                        $msg = str_replace($replace_in_message, $replace_with_message, $config['followup_abandoned']);
            		    $img = $config['followup_abandoned_img'];
						// Follow Up Abandoned Cart when status not shopping
						$type = $cart->type;
						$time = $cart->time;
						$status = $cart->status;					
						$cart_time = strtotime($time);
						$date = date_create(current_time( 'mysql', false ));
						$current_time = strtotime(date_format($date, 'Y-m-d H:i:s'));
						if($cart_time > $current_time - 60 * 60 && $item['type'] != 1){
							// Status is shopping
							// Do nothing
							// Source: woo-save-abandoned-carts/admin/class-cartbounty-admin-table.php:320
						} else {
							if(!empty($phone)) 
								$this->awp_wa_send_msg($config, $phone, $msg, $img, '');
							$wpdb->update( $table_name, array('other_fields'=>'1'), array('id'=>$id) );							
						}
            		}
                }
            }
	    }
	}	

	public function followup_cron_schedule( $schedules ) {
		$schedules['every_six_hours'] = array(
			'interval' => 21600,
			'display'  => __( 'Every 6 hours' ),
		);
		$schedules['every_half_hours'] = array(
			'interval' => 1800,
			'display'  => __( 'Every 30 minutes' ),
		);
		return $schedules;
	}

	public function status_on_admin_bar( $wp_admin_bar ) {
		$args = array(
			'id' => 'awp-admin-link',
			'title' => 'Wawp',
			'href' => admin_url().'admin.php?page=awp',
			'meta' => array(
				'class' => 'awp-admin-link'
			)
		);
		$wp_admin_bar -> add_node($args);

		
	$args = array(
			'id' => 'awp-sub-link-2',
			'title' => 'Wawp Notification',
			'href' => admin_url().'admin.php?page=awp',
			'parent' => 'awp-admin-link',
			'meta' => array(
				'class' => 'awp-admin-link'
			)
		);
		$wp_admin_bar -> add_node($args);
	
	
	
		
	$args = array(
			'id' => 'awp-sub-link-3',
			'title' => 'Wawp Otp',
			'href' => admin_url().'admin.php?page=awp-otp',
			'parent' => 'awp-admin-link',
			'meta' => array(
				'class' => 'awp-admin-link'
			)
		);
		$wp_admin_bar -> add_node($args);	
		
		
			$args = array(
			'id' => 'awp-sub-link-4',
			'title' => 'Visit Wawp Dashboard',
			'href' => 'https://app.wawp.net/',
			'parent' => 'awp-admin-link',
			'meta' => array(
				'class' => 'awp-sub-link',
				'title' => 'Go to Wawp.net',
				'target' => '_blank'
			)
		);
		$wp_admin_bar -> add_node($args);
		
	}
	
    public function hide_billing_phone_div() {
        // Enqueue the JavaScript file with the necessary script
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Add an action hook to print the script in the footer
        add_action('wp_footer', array($this, 'print_hide_billing_phone_script'));
    }

    public function print_hide_billing_phone_script() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Custom function to hide the phone input div
                function hideBillingPhoneDiv() {
                    $(".wc-block-components-address-form__phone").css("display", "none");
                }

                // Call the function to hide the phone input div
                hideBillingPhoneDiv();
            });
        </script>
        <?php
    }

    public function woo_phone_intltel_input(){
    	$config = get_option('awp_notifications');
    	
    	if( !$config['default_country'] ) {
    		wp_enqueue_style( 'awp-admin-telcss', plugins_url( 'assets/css/intlTelInput.css', __FILE__ ) );
    		wp_enqueue_script( 'awp-admin-teljs', plugins_url( 'assets/js/intlTelInput.js', __FILE__ ), array( 'jquery' ), '17.0.8', true );
    
    		// Enqueue the utils.js script
    		wp_enqueue_script( 'awp-admin-utilsjs', plugins_url( 'assets/js/utils.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
    
    		wp_enqueue_script( 'awp-admin-wootelinput', plugins_url( 'assets/js/woo-telinput.js', __FILE__ ) );
    	}
    }

    public function edd_phone_intltel_input(){
        if( $this->is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
			wp_enqueue_style( 'awp-admin-telcss', plugins_url( 'assets/css/intlTelInput.css', __FILE__ ) );
		wp_enqueue_script( 'awp-admin-teljs', plugins_url( 'assets/js/intlTelInput.js', __FILE__ ), array( 'jquery' ), '17.0.8', true );
		wp_enqueue_script( 'awp-admin-eddtelinput', plugins_url( 'assets/js/edd-telinput.js', __FILE__ ) );
    }
    }
    
    public function edd_buyer_phone_field() {
          if( $this->is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
        $config = get_option('awp_notifications');
        if (!empty($config['edd_notification'])) {
            $fields = array(
                array( 'phone', 'Phone Number', 'Insert your phone number to get order notification via WhatsApp.' )
            );
            foreach( $fields as $field ) {
                $field_id = $field[0];
                $field_label = $field[1];
                $field_desc = $field[2];
                ?>
				<p id="edd-<?php echo esc_attr($field_id);?>-wrap">
				  <label class="edd-label" for="edd-<?php echo esc_attr($field_id); ?>"><?php echo esc_html($field_label);?></label>  
				  <span class="edd-description"><?php echo esc_html($field_desc); ?></span>
				  <input class="edd-input" type="text" name="<?php echo esc_attr( 'edd_' . $field_id ); ?>" id="<?php echo esc_attr( 'edd_' . $field_id ); ?>" style="padding-right:6px;padding-left:52px;width:100%;" value="<?php echo esc_attr( $user_input ); ?>" />
				  
                </p>
                <?php
            }
        }
        }
    }
    
    public function edd_validate_checkout_field($valid_data, $data) {
         if( $this->is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
    $additional_required_fields = array(
        array('phone', 'Please enter a valid phone number.'),
    );
    foreach ($additional_required_fields as $field) {
        $field_id = $field[0];
        $field_error = $field[1];
        if (empty($data['edd_' . $field_id])) {
            edd_set_error('invalid_' . $field_id, $field_error);
            }
        }
    } 
    }
    
    public function edd_save_phone_field($payment_meta) {
         if( $this->is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
        if (did_action('edd_purchase')) {
            $additional_required_fields = array('phone');
            foreach ($additional_required_fields as $field) {
                $payment_meta[$field] = isset($_POST['edd_' . $field]) ? sanitize_text_field($_POST['edd_' . $field]) : '';
            }
        }
        return $payment_meta;
    }
    }
    
    public function edd_show_phone_on_personal_details($payment_meta, $user_info)  { if( $this->is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
       
    $fields = array(
        array('phone', 'Phone'),
    );
    $content = '<div class="column-container">';
    foreach ($fields as $field) {
        $field_id = $field[0];
        $field_label = esc_html($field[1]);
        if (!empty($payment_meta[$field_id])) {
            $payment_data = esc_html($payment_meta[$field_id]);
            $content .= '<div class="column"><strong>' . esc_html($field_label) . ' </strong>: ' . esc_html($payment_data) . '</div>';
        }
    }
    $content .= '</div>';
    return $content;
}
}
    
    public function edd_send_wa_after_purchase($payment) {
        if( $this->is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
        $config = get_option('awp_notifications');
        if (!empty($config['edd_notification'])) {
            $payment_meta = edd_get_payment_meta( $payment->ID );
            $payment_ids = edd_get_payment_number( $payment->ID );  
            $payment_status = edd_get_payment_status( $payment, true );
            $payment_method = edd_get_gateway_checkout_label( edd_get_payment_gateway( $payment->ID ) );
            $date = date_i18n( get_option( 'date_format' ), strtotime( $payment_meta['date'] ) );
            $user = edd_get_payment_meta_user_info( $payment->ID );
            $email = edd_get_payment_user_email( $payment->ID );
            $subtotal = edd_payment_subtotal( $payment->ID );
            $total_price = edd_payment_amount( $payment->ID );
            $cart = edd_get_payment_meta_cart_details( $payment->ID, true );
            if( $cart ) {
                $product_items = '';
                $i = 0;
                foreach ($cart as $key => $item) {
                    $i++;
                    $new_line = ($i > 1) ? '\n' : '';
                    $product_items .= $new_line . $i . '. '.$item['name'];
                }
            }
            $phone = $payment_meta['phone'];
            $buyer_wa  = $config['edd_notification'];
            $replace_in_wa_buyer = ["%payment_id%", "%payment_status%", "%payment_method%", "%date%", "%currency%", "%product%", "%subtotal_price%", "%total_price%", "%site_name%", "%first_name%", "%last_name%", "%email%"];
            $replace_with_buyer   = [$payment_ids, $payment_status, $payment_method, $date, $payment_meta['currency'], $product_items, $subtotal, $total_price, get_bloginfo( 'name' ), $user['first_name'], $user['last_name'], $email];
            $buyer_wa = str_replace($replace_in_wa_buyer, $replace_with_buyer, $buyer_wa);
            $msg = $buyer_wa;
            $img = $config['edd_notification_img'];
            if(!empty($msg) && $payment_status == 'Pending')
        		$this->awp_wa_send_msg($config, $phone, $msg, $img, '');
        }
    }
    }
    
    public function edd_send_wa_on_complete($payment_id) {
         if( $this->is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
        $config = get_option('awp_notifications');
        if (!empty($config['edd_notification_complete'])) {
            $payment_meta = edd_get_payment_meta( $payment_id );
            $payment_ids = edd_get_payment_number( $payment_id );  
            $payment_status = edd_get_payment_status( $payment_id, true );
            $payment_method = edd_get_gateway_checkout_label( edd_get_payment_gateway( $payment_id ) );
            $date = date_i18n( get_option( 'date_format' ), strtotime( $payment_meta['date'] ) );
            $user = edd_get_payment_meta_user_info( $payment_id );
            $email = edd_get_payment_user_email( $payment_id );
            $subtotal = edd_payment_subtotal( $payment_id );
            $total_price = edd_payment_amount( $payment_id );
            $cart = edd_get_payment_meta_cart_details( $payment_id, true );
            if( $cart ) {
                $product_items = '';
                $i = 0;
                foreach ($cart as $key => $item) {
                    $i++;
                    $new_line = ($i > 1) ? '\n' : '';
                    $product_items .= $new_line . $i . '. '.$item['name'];
                }
            }
            $phone = $payment_meta['phone'];
            $buyer_wa  = $config['edd_notification_complete'];
            $replace_in_wa_buyer = ["%payment_id%", "%payment_status%", "%payment_method%", "%date%", "%currency%", "%product%", "%subtotal_price%", "%total_price%", "%site_name%", "%first_name%", "%last_name%", "%email%"];
            $replace_with_buyer   = [$payment_ids, $payment_status, $payment_method, $date, $payment_meta['currency'], $product_items, $subtotal, $total_price, get_bloginfo( 'name' ), $user['first_name'], $user['last_name'], $email];
            $buyer_wa = str_replace($replace_in_wa_buyer, $replace_with_buyer, $buyer_wa);
            $msg = $buyer_wa;
            $img = $config['edd_notification_complete_img'];
            if(!empty($msg))
        		$this->awp_wa_send_msg($config, $phone, $msg, $img, '');
            }
        }
        
    }
        
    public function add_billing_phone_to_edit_account_form() {
    $user_id = get_current_user_id();
    
    // Get user data
    $billing_phone = get_user_meta($user_id, 'billing_phone', true);
    ?>

    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="billing_phone"><?php esc_html_e( 'Phone', 'woocommerce' ); ?> <span class="required">*</span></label>
        <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text" name="billing_phone" id="billing_phone" value="<?php echo esc_attr( $billing_phone ); ?>" />
    </p>

    <?php
    } 
    
    public function save_billing_phone_on_edit_account( $user_id ) {
    if ( isset( $_POST['billing_phone'] ) ) {
        update_user_meta( $user_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
     }
    }
    
    public function enqueue_select2() {
    wp_enqueue_script( 'select2', plugins_url( 'assets/js/resources/select2.js', __FILE__ ), array(), '4.1.0' );
    }
    
    public function display_evaluation_notification() {
    if (current_user_can('activate_plugins') && is_plugin_active('automation-web-platform/awp.php')) {
        
        $user_id = get_current_user_id();

        // Check if the notification should be displayed
        if (!get_user_meta($user_id, 'addon_evaluation_completed', true)) {
            $hide_until = get_user_meta($user_id, 'hide_notification_until', true);

            // Check if the notification should be hidden
            if (!$hide_until || current_time('timestamp') > $hide_until) {
                // Display the notification
                ?>
                <div class="notice notice-info is-dismissible" id="wawp-notification">
                    <h1><?php _e('We hope you like Wawp services 🥰', 'awp'); ?></h1>
                    <p><?php _e('Our mission in the automation web platform for developing web software is to improve the user experience, especially for marketers and entrepreneurs in marketing. We also improve the experience of the end user (buyer), which increases sales. If you have any opinion or evaluation of the service or our Wawp plugin, we will be happy to hear that from you.', 'awp'); ?></p>
                    <a href="https://login.wordpress.org/?redirect_to=https%3A%2F%2Fwordpress.org%2Fsupport%2Fplugin%2Fautomation-web-platform%2Freviews%2F%23new-post" class="button button-primary no-bu"><?php _e('Add your opinion', 'awp'); ?></a>
                    <a href="https://api.whatsapp.com/send?phone=966598594467&text=hi-from-wawp-plugin" class="button button-primary no-bu"><?php _e('Contact us on WhatsApp', 'awp'); ?></a>
<hr>
                    <form method="post" action="">
                        <?php wp_nonce_field('hide_notification_nonce', 'hide_notification_nonce_field'); ?>
                        <input type="hidden" name="action" value="hide_notification">
                        <button type="submit" class="button-primary button no-bu"><?php _e('Hide Notification for a Month', 'awp'); ?></button>
                    </form>
                </div>
                <?php
            }
        }
    }
}

    public function hide_notification_handler() {
    if (isset($_POST['action']) && $_POST['action'] === 'hide_notification' && wp_verify_nonce($_POST['hide_notification_nonce_field'], 'hide_notification_nonce')) {
        // Hide the notification for a month
        update_user_meta(get_current_user_id(), 'hide_notification_until', strtotime('+1 month'));

        // Redirect to the current page to remove the form submission from the URL
        wp_safe_redirect(wp_get_referer());
        exit();
    }
    }

    /**
     * Add "Billing Phone" column to User table and retrieve data
     * 
     * @param array $columns Current user table columns
     * @return array Updated user table columns with "Billing Phone"
     */
    public function add_billing_phone_column( $columns ) {
        $columns['billing_phone'] = __( 'Whatsapp Number', 'awp' );
        return $columns;
    }
    
    /**
     * Display billing phone number in "Billing Phone" column
     * 
     * @param string $content Current cell content
     * @param string $column_name Current column name
     * @param int $user_id User ID
     * @return string Updated cell content with billing phone number
     */
    public function display_billing_phone_content( $content, $column_name, $user_id ) {
        if ( 'billing_phone' === $column_name ) {
            $customer = new WC_Customer( $user_id );
            $billing_phone = $customer->get_billing_phone();
    
            if ( $billing_phone ) {
                $content = esc_html( $billing_phone );
            } else {
                $content = '-'; // Optional display if phone is empty
            }
        }
        return $content;
    }
        function send_one_time_welcome_email() {
    // Check if the email was already sent by checking the stored option
    $is_email_sent = get_option('one_time_welcome_email_sent');

    // If the email was sent already, don't send it again
    if ($is_email_sent) {
        return;
    }

    // Get the administrator email address using the first user ID
    $admin_user = get_userdata(1);
    $admin_email = $admin_user->user_email;

    // Get the WordPress site name and URL
    $site_name = get_bloginfo('name');
    $site_url = home_url();

 // Retrieve access_token and url from plugin settings
    $instances = get_option('awp_instances');
    $access_token = isset($instances['access_token']) ? $instances['access_token'] : '';
    $dashboard_prefix = isset($instances['dashboard_prefix']) ? $instances['dashboard_prefix'] : '';


    // Compose the message
    $message = "Welcome,\n";
    $message .= "My email: " . $admin_email . "\n";
    $message .= "My site name: " . $site_name . "\n";
    $message .= "My website link: " . $site_url . "\n";
    $message .= "Access Token: " . $access_token . "\n";

    // Email details
    $to = 'info@wawp.net';
    $subject = 'Welcome Message'.' '.get_bloginfo('name');
    $headers = array('Content-Type: text/plain; charset=UTF-8');

    // Attempt to send the email
    $sent = wp_mail($to, $subject, $message, $headers);

    // If the email is sent successfully, set the flag to prevent future emails
    if ($sent) {
        update_option('one_time_welcome_email_sent', true);
    }
}
    }