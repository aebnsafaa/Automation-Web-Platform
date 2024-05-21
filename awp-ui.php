<?php


class awp_UI {

	public function __construct() {
		$this->notif  = get_option( 'awp_notifications' );
		$this->instances  = get_option( 'awp_instances' );
	}

    public function is_plugin_active( $plugin ) {
        return in_array( $plugin, (array) get_option( 'active_plugins', array() ) );
    }

	public function admin_page() {
		?>
        <div class="wrap" id="awp-wrap">
            <h1>
				<?php echo get_admin_page_title(); ?>
            </h1>
            
            <div class="form-wrapper">
				<div class="awp-tab-wrapper">
					<ul class="nav-tab-wrapper woo-nav-tab-wrapper">
                        <li name="awp_notifications[notification-message]" class="nav-tab nav-tab-active"><a href="#notification"><?php _e('Client Notifications', 'awp'); ?></a></li>
					
                        <li name="awp_admin_notifications[admin_notification-message]" class="nav-tab"><a href="#admin-notification"><?php _e('Admin Notifications', 'awp'); ?></a></li>
                        										
                        
                        <li class="nav-tab"><a href="#followup"><?php _e('Follow-Up', 'awp'); ?></a></li>
                        
                        <li class="nav-tab"><a href="#abandoned-cart"><?php _e('Abandoned Cart', 'awp'); ?></a></li>
                        
                        <li class="nav-tab"><a href="#other"><?php _e('Other Integration', 'awp'); ?></a></li>
                        <li class="nav-tab"><a href="#help">
                        	
                        <?php _e('Help', 'awp'); ?></a></li>
                        
					</ul>
                    <form method="post" action="options.php">
    					<div class="wp-tab-panels" id="notification">
    							<?php
    								$this->notification_settings();	
    							?>
    					</div>
    					<div class="wp-tab-panels" id="admin-notification" style="display: none;">
    							<?php
    								$this->admin_notification_settings();	
    							?>
    					</div>
    					<div class="wp-tab-panels" id="followup" style="display: none;">
    							<?php
    								$this->followup_settings();	
    							?>
    					</div>
    					<div class="wp-tab-panels" id="abandoned-cart" style="display: none;">
    							<?php
    								$this->abandoned_cart_settings();	
    							?>
    					</div>
        				<div class="wp-tab-panels" id="other" style="display: none;">
    							<?php
    								$this->other_settings();	
    							?>
    					</div>
    	            </form>
					<div class="wp-tab-panels" id="help" style="display: none;">
							<?php
    								$this->help_info();	
							?>
					</div>
				</div>				
				<div class="info">
							<?php
								$this->setup_info();	
							?>					
				</div>
			</div>
        </div>
		<?php
	}	

	public function notification_settings() {
	    if( $this->is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
    		$status_list = wc_get_order_statuses();
    		$status_list_temp = array();
    		$original_status = array( 
    			'pending',
    			'failed',
    			'on-hold',
    			'processing',
    			'completed',
    			'refunded',
    			'cancelled',
    		);
    		foreach( $status_list as $key => $status ) {
    			$status_name = str_replace( "wc-", "", $key );
    			if ( !in_array( $status_name, $original_status ) ) {
    				$status_list_temp[$status] = $status_name;
    			}
    		}
    		$status_list = $status_list_temp;	    
	    }
		?>
		
			<?php settings_fields( 'awp_storage_notifications' ); ?>
  		    
  		   <a href="https://wawp.net/docs/third-party/wordpress-woocommerce/customize-woocommerce-messages/" target="_blank"> <div class="info-banner">
  		        <label for="awp_banner_info" class="banner-title"><?php _e('Client Notifications', 'awp'); ?></label>
  		        <p class="banner-text"><?php _e('Send notifications to your customers based on order status. It will sent instantly when the order is created or its status is changed.', 'awp'); ?></p>
  		    </div></a>
  		    
  		    
  		    <div class="notification-form">
            	<div class="heading-bar">
            	<label for="awp_notifications[default_country]" class="notification-title"><?php _e('Default Country Code:', 'awp'); ?></label>
            	</div>
            	<p class="deactive-hint"><em><?php echo esc_html__('Add your country code without any 00 or + ex: 2 for EG or 966 for SA  ', 'awp'); ?></em></p>
            	<br>
            	<div class="notification">
            			<div class="phone-field">
		    			   <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="phone-icon"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm7.931 9h-2.764a14.67 14.67 0 0 0-1.792-6.243A8.013 8.013 0 0 1 19.931 11zM12.53 4.027c1.035 1.364 2.427 3.78 2.627 6.973H9.03c.139-2.596.994-5.028 2.451-6.974.172-.01.344-.026.519-.026.179 0 .354.016.53.027zm-3.842.7C7.704 6.618 7.136 8.762 7.03 11H4.069a8.013 8.013 0 0 1 4.619-6.273zM4.069 13h2.974c.136 2.379.665 4.478 1.556 6.23A8.01 8.01 0 0 1 4.069 13zm7.381 6.973C10.049 18.275 9.222 15.896 9.041 13h6.113c-.208 2.773-1.117 5.196-2.603 6.972-.182.012-.364.028-.551.028-.186 0-.367-.016-.55-.027zm4.011-.772c.955-1.794 1.538-3.901 1.691-6.201h2.778a8.005 8.005 0 0 1-4.469 6.201z"></path></svg>
            				<input type="text" name="awp_notifications[default_country]" placeholder="<?php echo esc_attr__('Your country code', 'awp'); ?>" class="admin_number regular-text admin_number upload-text" value="<?php echo esc_attr(isset($this->notif['default_country']) ? $this->notif['default_country'] : ''); ?>">
            			</div>
            	</div>
                <p class="deactive-hint"><em><?php echo esc_html__('Insert country code only if your customer is from a single country. This will remove the country detection library on the old checkout page. Leave blank if your customer is from many countries.', 'awp'); ?></em></p>
            </div>

			
		
		<?php
		
		    $message_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="message-icon"><path d="M19.045 7.401c.378-.378.586-.88.586-1.414s-.208-1.036-.586-1.414l-1.586-1.586c-.378-.378-.88-.586-1.414-.586s-1.036.208-1.413.585L4 13.585V18h4.413L19.045 7.401zm-3-3 1.587 1.585-1.59 1.584-1.586-1.585 1.589-1.584zM6 16v-1.585l7.04-7.018 1.586 1.586L7.587 16H6zm-2 4h16v2H4z"></path></svg>';

		    $link_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="link-icon"><path d="M17.004 5H9c-1.838 0-3.586.737-4.924 2.076C2.737 8.415 2 10.163 2 12c0 1.838.737 3.586 2.076 4.924C5.414 18.263 7.162 19 9 19h8v-2H9c-1.303 0-2.55-.529-3.51-1.49C4.529 14.55 4 13.303 4 12c0-1.302.529-2.549 1.49-3.51C6.45 7.529 7.697 7 9 7h8V6l.001 1h.003c.79 0 1.539.314 2.109.886.571.571.886 1.322.887 2.116a2.966 2.966 0 0 1-.884 2.11A2.988 2.988 0 0 1 17 13H9a.99.99 0 0 1-.698-.3A.991.991 0 0 1 8 12c0-.252.11-.507.301-.698A.987.987 0 0 1 9 11h8V9H9c-.79 0-1.541.315-2.114.889C6.314 10.461 6 11.211 6 12s.314 1.54.888 2.114A2.974 2.974 0 0 0 9 15h8.001a4.97 4.97 0 0 0 3.528-1.473 4.967 4.967 0 0 0-.001-7.055A4.95 4.95 0 0 0 17.004 5z"></path></svg>';
  		?>
  		
  		

<!-- Add tabs for Arabic and English editors -->
<div class="editor-tabs">
    <div class="editor-tab" data-lang="english">setup Default language</div>
    <div class="editor-tab" data-lang="arabic">إعداد رسائل اللغة العربية</div>
</div>

<div class="editor-content" data-lang="english">
    

     <div class="notification-form english">
        <div class="heading-bar">
            <label for="awp_notifications[customer_neworder]" class="notification-title"><?php _e('Default language', 'awp'); ?></label>
        </div>
        <p class="deactive-hint"><em><?php _e('It works based on the primary language of the user’s WordPress account, including English, French, Italian, German, Spanish, or any other language. Just put your notification messages and let them work automatically.', 'awp'); ?></em></p>
    
    </div>
    
    
    
     <div class="notification-form english">
        <div class="heading-bar">
            <label for="awp_notifications[customer_neworder]" class="notification-title"><?php _e('Order - New (Thankyou Page):', 'awp'); ?></label>
        </div>

        <div class="notification">
            <div class="form">
                <?php echo $message_icon; ?>

                <!-- Add textareas for  English messages -->
                <textarea id="awp_notifications[customer_neworder]" name="awp_notifications[customer_neworder]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message in English here..', 'awp'); ?>"><?php echo isset($this->notif['customer_neworder']) ? esc_textarea($this->notif['customer_neworder']) : ''; ?></textarea>
                <div class="upload-field">
                    <?php echo $link_icon; ?>
                    <input type="text" name="awp_notifications[customer_neworder_img]" placeholder="<?php _e('Image URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text customer_neworder_img upload-text" value="<?php echo esc_attr(isset($this->notif['customer_neworder_img']) ? $this->notif['customer_neworder_img'] : ''); ?>">
                    <input type="button" name="upload-btn" value="<?php _e('Upload Image', 'awp'); ?>" class="upload-btn" data-id="customer_neworder_img">
                </div>
            </div>
        </div>
        <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
    </div>
     <div class="notification-form english">
	<div class="heading-bar">
	<label for="awp_notifications[order_onhold]" class="notification-title"><?php _e('Order - On Hold:', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[order_onhold]" name="awp_notifications[order_onhold]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['order_onhold']) ? $this->notif['order_onhold'] : ''); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[order_onhold_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text order_onhold_img upload-text" value="<?php echo esc_attr(isset($this->notif['order_onhold_img']) ? $this->notif['order_onhold_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="order_onhold_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>
     <div class="notification-form english">
	<div class="heading-bar">
	<label for="awp_notifications[order_processing]" class="notification-title"><?php _e('Order - Processing:', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[order_processing]" name="awp_notifications[order_processing]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_html(isset($this->notif['order_processing']) ? $this->notif['order_processing'] : ''); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[order_processing_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text order_processing_img upload-text" value="<?php echo esc_attr(isset($this->notif['order_processing_img']) ? $this->notif['order_processing_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="order_processing_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>
     <div class="notification-form english">
	<div class="heading-bar">
	<label for="awp_notifications[order_completed]" class="notification-title"><?php _e('Order - Completed:', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[order_completed]" name="awp_notifications[order_completed]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo isset($this->notif['order_completed']) ? esc_textarea($this->notif['order_completed']) : ''; ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[order_completed_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text order_completed_img upload-text" value="<?php echo esc_attr(isset($this->notif['order_completed_img']) ? $this->notif['order_completed_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="order_completed_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>
     <div class="notification-form english">
	<div class="heading-bar">
	<label for="awp_notifications[order_pending]" class="notification-title"><?php _e('Order - Pending Payment:', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[order_pending]" name="awp_notifications[order_pending]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo isset($this->notif['order_pending']) ? esc_textarea($this->notif['order_pending']) : ''; ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[order_pending_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text order_pending_img upload-text" value="<?php echo esc_attr(isset($this->notif['order_pending_img']) ? $this->notif['order_pending_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="order_pending_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>
     <div class="notification-form english">
	<div class="heading-bar">
	<label for="awp_notifications[order_failed]" class="notification-title"><?php _e('Order - Failed:', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[order_failed]" name="awp_notifications[order_failed]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo isset($this->notif['order_failed']) ? esc_textarea($this->notif['order_failed']) : ''; ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[order_failed_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text order_failed_img upload-text" value="<?php echo esc_attr(isset($this->notif['order_failed_img']) ? $this->notif['order_failed_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="order_failed_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>
     <div class="notification-form english">
	<div class="heading-bar">
	<label for="awp_notifications[order_refunded]" class="notification-title"><?php _e('Order - Refunded:', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[order_refunded]" name="awp_notifications[order_refunded]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo isset($this->notif['order_refunded']) ? esc_textarea($this->notif['order_refunded']) : ''; ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[order_refunded_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text order_refunded_img upload-text" value="<?php echo esc_attr(isset($this->notif['order_refunded_img']) ? $this->notif['order_refunded_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="order_refunded_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>
     <div class="notification-form english">
	<div class="heading-bar">
	<label for="awp_notifications[order_cancelled]" class="notification-title"><?php _e('Order - Cancelled:', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[order_cancelled]" name="awp_notifications[order_cancelled]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['order_cancelled']) ? $this->notif['order_cancelled'] : ''); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[order_cancelled_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text order_cancelled_img upload-text" value="<?php echo isset($this->notif['order_cancelled_img']) ? esc_attr($this->notif['order_cancelled_img']) : ''; ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="order_cancelled_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>
     <div class="notification-form english">
	<div class="heading-bar">
	<label for="awp_notifications[order_note]" class="notification-title"><?php _e('Order - Notes:', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[order_note]" name="awp_notifications[order_note]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo isset($this->notif['order_note']) ? esc_textarea($this->notif['order_note']) : ''; ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[order_note_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text order_note_img upload-text" value="<?php echo esc_attr( isset( $this->notif['order_note_img'] ) ? $this->notif['order_note_img'] : '' ); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="order_note_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>

     <?php if ( !empty( $status_list ) ) : ?>
     <?php foreach ( $status_list as $status_name => $custom_status ) : ?>
     <div class="notification-form english">
	<div class="heading-bar">
	<label for="awp_notifications[order_<?php echo esc_attr($custom_status); ?>]" class="notification-title"><?php echo sprintf( __( 'Order - %s:', 'awp' ), esc_html($status_name) ); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[order_<?php echo esc_html($custom_status); ?>]" name="awp_notifications[order_<?php echo esc_html($custom_status); ?>]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea( isset( $this->notif['order_'.esc_html($custom_status)] ) ? $this->notif['order_'.esc_html($custom_status)] : '' ); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[order_<?php echo esc_attr( $custom_status ); ?>_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text order_<?php echo esc_attr( $custom_status ); ?>_img upload-text" value="<?php echo esc_attr( isset( $this->notif['order_' . $custom_status . '_img'] ) ? $this->notif['order_' . $custom_status . '_img'] : '' ); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="order_<?php echo $custom_status; ?>_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>
     <?php endforeach; ?>
     <?php endif; ?>	
</div>

<div class="editor-content" data-lang="arabic">
  
    <div class="notification-form arabic">
        <div class="heading-bar">
            <label for="awp_notifications[customer_neworder]" class="notification-title"><?php _e('اللغة العربية', 'awp'); ?></label>
        </div>
        <p class="deactive-hint"><em><?php _e('اذا كان موقعك باللغة العربية يمكنك استخدام هذا القسم لاضافة رسائل اللغة، العربية، تعمل في حالة اذا كانت لغة الموقع الرئيسية او لغة اضافية بموقع متعدد اللغات.', 'awp'); ?></em></p>

    </div>
    
    
        <div class="notification-form arabic">
        <div class="heading-bar">
            <label for="awp_notifications[customer_neworder]" class="notification-title"><?php _e('اشعار بعد اتمام الطلب مباشرة بواسطة العميل', 'awp'); ?></label>
        </div>
        <div class="notification">
            <div class="form">
                <?php echo $message_icon; ?>

                <!-- Add textareas for Arabic and English messages -->
                <textarea id="awp_notifications[customer_neworder_arabic]" name="awp_notifications[customer_neworder_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('اكتب رسالتك باللغة العربية هنا..', 'awp'); ?>"><?php echo isset($this->notif['customer_neworder_arabic']) ? esc_textarea($this->notif['customer_neworder_arabic']) : ''; ?></textarea>
                <div class="upload-field">
                    <?php echo $link_icon; ?>
                    <input type="text" name="awp_notifications[customer_neworder_img_arabic]" placeholder="<?php _e('رابط الصورة (الحجم الاقصي 1 MB)...', 'awp'); ?>" class="image_url regular-text customer_neworder_img_arabic upload-text" value="<?php echo esc_attr(isset($this->notif['customer_neworder_img_arabic']) ? $this->notif['customer_neworder_img_arabic'] : ''); ?>">
                    <input type="button" name="upload-btn" value="<?php _e('رفع الصورة', 'awp'); ?>" class="upload-btn" data-id="customer_neworder_img_arabic">
                </div>
            </div>
        </div>
        <p class="deactive-hint"><em><?php _e('اتركها فارغة لتعطيلها.', 'awp'); ?></em></p>
    </div>
        <div class="notification-form arabic">
        <div class="heading-bar">
            <label for="awp_notifications[order_onhold_arabic]" class="notification-title"><?php _e('اشعار الطلب قيد الانتظار', 'awp'); ?></label>
        </div>
        <div class="notification">
            <div class="form">
                <?php echo $message_icon; ?>

                <!-- Add textareas for Arabic and English messages -->
                <textarea id="awp_notifications[order_onhold_arabic]" name="awp_notifications[order_onhold_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('اكتب رسالتك باللغة العربية هنا..', 'awp'); ?>"><?php echo isset($this->notif['order_onhold_arabic']) ? esc_textarea($this->notif['order_onhold_arabic']) : ''; ?></textarea>
                <div class="upload-field">
                    <?php echo $link_icon; ?>
                    <input type="text" name="awp_notifications[order_onhold_img_arabic]" placeholder="<?php _e('رابط الصورة (الحجم الاقصي 1 MB)...', 'awp'); ?>" class="image_url regular-text order_onhold_img_arabic upload-text" value="<?php echo esc_attr(isset($this->notif['order_onhold_img_arabic']) ? $this->notif['order_onhold_img_arabic'] : ''); ?>">
                    <input type="button" name="upload-btn" value="<?php _e('رفع الصورة', 'awp'); ?>" class="upload-btn" data-id="order_onhold_img_arabic">
                </div>
            </div>
        </div>
        <p class="deactive-hint"><em><?php _e('اتركها فارغة لتعطيلها.', 'awp'); ?></em></p>
    </div>
        <div class="notification-form arabic">
        <div class="heading-bar">
            <label for="awp_notifications[order_processing_arabic]" class="notification-title"><?php _e('اشعار الطلب قيد التنفيذ', 'awp'); ?></label>
        </div>
        <div class="notification">
            <div class="form">
                <?php echo $message_icon; ?>

                <!-- Add textareas for Arabic and English messages -->
                <textarea id="awp_notifications[order_processing_arabic]" name="awp_notifications[order_processing_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('اكتب رسالتك باللغة العربية هنا..', 'awp'); ?>"><?php echo isset($this->notif['order_processing_arabic']) ? esc_textarea($this->notif['order_processing_arabic']) : ''; ?></textarea>
                <div class="upload-field">
                    <?php echo $link_icon; ?>
                    <input type="text" name="awp_notifications[order_processing_img_arabic]" placeholder="<?php _e('رابط الصورة (الحجم الاقصي 1 MB)...', 'awp'); ?>" class="image_url regular-text order_processing_img_arabic upload-text" value="<?php echo esc_attr(isset($this->notif['order_processing_img_arabic']) ? $this->notif['order_processing_img_arabic'] : ''); ?>">
                    <input type="button" name="upload-btn" value="<?php _e('رفع الصورة', 'awp'); ?>" class="upload-btn" data-id="order_processing_img_arabic">
                </div>
            </div>
        </div>
        <p class="deactive-hint"><em><?php _e('اتركها فارغة لتعطيلها.', 'awp'); ?></em></p>
    </div>
        <div class="notification-form arabic">
        <div class="heading-bar">
            <label for="awp_notifications[order_completed_arabic]" class="notification-title"><?php _e('اشعار الطلب المكتمل', 'awp'); ?></label>
        </div>
        <div class="notification">
            <div class="form">
                <?php echo $message_icon; ?>

                <!-- Add textareas for Arabic and English messages -->
                <textarea id="awp_notifications[order_completed_arabic]" name="awp_notifications[order_completed_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('اكتب رسالتك باللغة العربية هنا..', 'awp'); ?>"><?php echo isset($this->notif['order_completed_arabic']) ? esc_textarea($this->notif['order_completed_arabic']) : ''; ?></textarea>
                <div class="upload-field">
                    <?php echo $link_icon; ?>
                    <input type="text" name="awp_notifications[order_completed_img_arabic]" placeholder="<?php _e('رابط الصورة (الحجم الاقصي 1 MB)...', 'awp'); ?>" class="image_url regular-text order_completed_img_arabic upload-text" value="<?php echo esc_attr(isset($this->notif['order_completed_img_arabic']) ? $this->notif['order_completed_img_arabic'] : ''); ?>">
                    <input type="button" name="upload-btn" value="<?php _e('رفع الصورة', 'awp'); ?>" class="upload-btn" data-id="order_completed_img_arabic">
                </div>
            </div>
        </div>
        <p class="deactive-hint"><em><?php _e('اتركها فارغة لتعطيلها.', 'awp'); ?></em></p>
    </div>
        <div class="notification-form arabic">
        <div class="heading-bar">
            <label for="awp_notifications[order_pending_arabic]" class="notification-title"><?php _e('اشعار الطلب بإنتظار الدفع', 'awp'); ?></label>
        </div>
        <div class="notification">
            <div class="form">
                <?php echo $message_icon; ?>

                <!-- Add textareas for Arabic and English messages -->
                <textarea id="awp_notifications[order_pending_arabic]" name="awp_notifications[order_pending_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('اكتب رسالتك باللغة العربية هنا..', 'awp'); ?>"><?php echo isset($this->notif['order_pending_arabic']) ? esc_textarea($this->notif['order_pending_arabic']) : ''; ?></textarea>
                <div class="upload-field">
                    <?php echo $link_icon; ?>
                    <input type="text" name="awp_notifications[order_pending_img_arabic]" placeholder="<?php _e('رابط الصورة (الحجم الاقصي 1 MB)...', 'awp'); ?>" class="image_url regular-text order_pending_img_arabic upload-text" value="<?php echo esc_attr(isset($this->notif['order_pending_img_arabic']) ? $this->notif['order_pending_img_arabic'] : ''); ?>">
                    <input type="button" name="upload-btn" value="<?php _e('رفع الصورة', 'awp'); ?>" class="upload-btn" data-id="order_pending_img_arabic">
                </div>
            </div>
        </div>
        <p class="deactive-hint"><em><?php _e('اتركها فارغة لتعطيلها.', 'awp'); ?></em></p>
    </div>
        <div class="notification-form arabic">
        <div class="heading-bar">
            <label for="awp_notifications[order_failed_arabic]" class="notification-title"><?php _e('اشعار الطلب فشل', 'awp'); ?></label>
        </div>
        <div class="notification">
            <div class="form">
                <?php echo $message_icon; ?>

                <!-- Add textareas for Arabic and English messages -->
                <textarea id="awp_notifications[order_failed_arabic]" name="awp_notifications[order_failed_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('اكتب رسالتك باللغة العربية هنا..', 'awp'); ?>"><?php echo isset($this->notif['order_failed_arabic']) ? esc_textarea($this->notif['order_failed_arabic']) : ''; ?></textarea>
                <div class="upload-field">
                    <?php echo $link_icon; ?>
                    <input type="text" name="awp_notifications[order_failed_img_arabic]" placeholder="<?php _e('رابط الصورة (الحجم الاقصي 1 MB)...', 'awp'); ?>" class="image_url regular-text order_failed_img_arabic upload-text" value="<?php echo esc_attr(isset($this->notif['order_failed_img_arabic']) ? $this->notif['order_failed_img_arabic'] : ''); ?>">
                    <input type="button" name="upload-btn" value="<?php _e('رفع الصورة', 'awp'); ?>" class="upload-btn" data-id="order_failed_img_arabic">
                </div>
            </div>
        </div>
        <p class="deactive-hint"><em><?php _e('اتركها فارغة لتعطيلها.', 'awp'); ?></em></p>
    </div>
        <div class="notification-form arabic">
        <div class="heading-bar">
            <label for="awp_notifications[order_refunded_arabic]" class="notification-title"><?php _e('اشعار الطلب مُسترد', 'awp'); ?></label>
        </div>
        <div class="notification">
            <div class="form">
                <?php echo $message_icon; ?>

                <!-- Add textareas for Arabic and English messages -->
                <textarea id="awp_notifications[order_refunded_arabic]" name="awp_notifications[order_refunded_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('اكتب رسالتك باللغة العربية هنا..', 'awp'); ?>"><?php echo isset($this->notif['order_refunded_arabic']) ? esc_textarea($this->notif['order_refunded_arabic']) : ''; ?></textarea>
                <div class="upload-field">
                    <?php echo $link_icon; ?>
                    <input type="text" name="awp_notifications[order_refunded_img_arabic]" placeholder="<?php _e('رابط الصورة (الحجم الاقصي 1 MB)...', 'awp'); ?>" class="image_url regular-text order_refunded_img_arabic upload-text" value="<?php echo esc_attr(isset($this->notif['order_refunded_img_arabic']) ? $this->notif['order_refunded_img_arabic'] : ''); ?>">
                    <input type="button" name="upload-btn" value="<?php _e('رفع الصورة', 'awp'); ?>" class="upload-btn" data-id="order_refunded_img_arabic">
                </div>
            </div>
        </div>
        <p class="deactive-hint"><em><?php _e('اتركها فارغة لتعطيلها.', 'awp'); ?></em></p>
    </div>
        <div class="notification-form arabic">
        <div class="heading-bar">
            <label for="awp_notifications[order_cancelled_arabic]" class="notification-title"><?php _e('اشعار الطلب ملغي', 'awp'); ?></label>
        </div>
        <div class="notification">
            <div class="form">
                <?php echo $message_icon; ?>

                <!-- Add textareas for Arabic and English messages -->
                <textarea id="awp_notifications[order_cancelled_arabic]" name="awp_notifications[order_cancelled_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('اكتب رسالتك باللغة العربية هنا..', 'awp'); ?>"><?php echo isset($this->notif['order_cancelled_arabic']) ? esc_textarea($this->notif['order_cancelled_arabic']) : ''; ?></textarea>
                <div class="upload-field">
                    <?php echo $link_icon; ?>
                    <input type="text" name="awp_notifications[order_cancelled_img_arabic]" placeholder="<?php _e('رابط الصورة (الحجم الاقصي 1 MB)...', 'awp'); ?>" class="image_url regular-text order_cancelled_img_arabic upload-text" value="<?php echo esc_attr(isset($this->notif['order_cancelled_img_arabic']) ? $this->notif['order_cancelled_img_arabic'] : ''); ?>">
                    <input type="button" name="upload-btn" value="<?php _e('رفع الصورة', 'awp'); ?>" class="upload-btn" data-id="order_cancelled_img_arabic">
                </div>
            </div>
        </div>
        <p class="deactive-hint"><em><?php _e('اتركها فارغة لتعطيلها.', 'awp'); ?></em></p>
    </div>
        <?php if (!empty($status_list)) : ?>
    <?php foreach ($status_list as $status_name => $custom_status) : ?>
        <div class="notification-form arabic">
            <div class="heading-bar">
                <label for="awp_notifications[order_<?php echo esc_attr($custom_status); ?>_arabic]" class="notification-title"><?php echo sprintf(__('Order - %s (العربية):', 'awp'), esc_html($status_name)); ?></label>
            </div>
            <div class="notification">
                <div class="form">
                    <?php echo $message_icon; ?>
                    <textarea id="awp_notifications[order_<?php echo esc_html($custom_status); ?>_arabic]" name="awp_notifications[order_<?php echo esc_html($custom_status); ?>_arabic]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('اكتب رسالتك باللغة العربية هنا..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['order_' . esc_html($custom_status) . '_arabic']) ? $this->notif['order_' . esc_html($custom_status) . '_arabic'] : ''); ?></textarea>
                    <div class="upload-field">
                        <?php echo $link_icon; ?>
                        <input type="text" name="awp_notifications[order_<?php echo esc_attr($custom_status); ?>_img_arabic]" placeholder="<?php _e('رابط الصورة (الحجم الاقصي 1 MB)...', 'awp'); ?>" class="image_url regular-text order_<?php echo esc_attr($custom_status); ?>_img_arabic upload-text" value="<?php echo esc_attr(isset($this->notif['order_' . $custom_status . '_img_arabic']) ? $this->notif['order_' . $custom_status . '_img_arabic'] : ''); ?>">
                        <input type="button" name="upload-btn" value="<?php _e('رفع الصورة', 'awp'); ?>" class="upload-btn" data-id="order_<?php echo $custom_status; ?>_img_arabic">
                    </div>
                </div>
            </div>
            <p class="deactive-hint"><em><?php _e('اتركها فارغة لتعطيلها.', 'awp'); ?></em></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

</div>

	<footer class="awp-panel-footer">
        <input type="submit" class="button-primarywa"
               value="<?php _e( 'Save Changes', 'awp' ); ?>">
    </footer>

    
<?php

	}

	public function admin_notification_settings() {
	    if( $this->is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
    		$status_list = wc_get_order_statuses();
    		$status_list_temp = array();
    		$original_status = array( 
    			'pending',
    			'failed',
    			'on-hold',
    			'processing',
    			'completed',
    			'refunded',
    			'cancelled',
    		);
    		foreach( $status_list as $key => $status ) {
    			$status_name = str_replace( "wc-", "", $key );
    			if ( !in_array( $status_name, $original_status ) ) {
    				$status_list_temp[$status] = $status_name;
    			}
    		}
    		$status_list = $status_list_temp;	    
	    }
		?>
			<?php settings_fields( 'awp_storage_notifications' ); ?>
			
			
			
			<a href="https://wawp.net/docs/third-party/wordpress-woocommerce/setup-admin-notifications-message/" target="_blank"> 
			<div class="info-banner">
  		        <label for="awp_banner_info" class="banner-title"><?php _e('Admin Notification', 'awp'); ?></label>
  		        <p class="banner-text"><?php _e('Receive notifications on your WhatsApp number about orders to inform you of the changes that have been made to them.', 'awp'); ?></p>
  		    </div></a>

            <div class="notification-form">
            	<div class="heading-bar">
            	<label for="awp_notifications[admin_number]" class="notification-title"><?php _e('Default Admin Number:', 'awp'); ?></label>
            	</div>
	<div class="notification">
			<div class="phone-field">
			    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="phone-icon"><path fill-rule="evenodd" clip-rule="evenodd" d="M18.403 5.633A8.919 8.919 0 0 0 12.053 3c-4.948 0-8.976 4.027-8.978 8.977 0 1.582.413 3.126 1.198 4.488L3 21.116l4.759-1.249a8.981 8.981 0 0 0 4.29 1.093h.004c4.947 0 8.975-4.027 8.977-8.977a8.926 8.926 0 0 0-2.627-6.35m-6.35 13.812h-.003a7.446 7.446 0 0 1-3.798-1.041l-.272-.162-2.824.741.753-2.753-.177-.282a7.448 7.448 0 0 1-1.141-3.971c.002-4.114 3.349-7.461 7.465-7.461a7.413 7.413 0 0 1 5.275 2.188 7.42 7.42 0 0 1 2.183 5.279c-.002 4.114-3.349 7.462-7.461 7.462m4.093-5.589c-.225-.113-1.327-.655-1.533-.73-.205-.075-.354-.112-.504.112s-.58.729-.711.879-.262.168-.486.056-.947-.349-1.804-1.113c-.667-.595-1.117-1.329-1.248-1.554s-.014-.346.099-.458c.101-.1.224-.262.336-.393.112-.131.149-.224.224-.374s.038-.281-.019-.393c-.056-.113-.505-1.217-.692-1.666-.181-.435-.366-.377-.504-.383a9.65 9.65 0 0 0-.429-.008.826.826 0 0 0-.599.28c-.206.225-.785.767-.785 1.871s.804 2.171.916 2.321c.112.15 1.582 2.415 3.832 3.387.536.231.954.369 1.279.473.537.171 1.026.146 1.413.089.431-.064 1.327-.542 1.514-1.066.187-.524.187-.973.131-1.067-.056-.094-.207-.151-.43-.263"></path></svg>
				<input type="text" name="awp_notifications[admin_number]" placeholder="<?php echo esc_attr__('Admin Number with country code', 'awp'); ?>" class="admin_number regular-text admin_number upload-text" value="<?php echo esc_attr(isset($this->notif['admin_number']) ? $this->notif['admin_number'] : ''); ?>">
			</div>
	</div>
    <p class="deactive-hint"><em><?php echo esc_html__('Add your number without any 00 or + ex: 201xxxxxxx or 966xxxxxxxx', 'awp'); ?></em></p>
</div>
	
<?php

    $message_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="message-icon"><path d="M19.045 7.401c.378-.378.586-.88.586-1.414s-.208-1.036-.586-1.414l-1.586-1.586c-.378-.378-.88-.586-1.414-.586s-1.036.208-1.413.585L4 13.585V18h4.413L19.045 7.401zm-3-3 1.587 1.585-1.59 1.584-1.586-1.585 1.589-1.584zM6 16v-1.585l7.04-7.018 1.586 1.586L7.587 16H6zm-2 4h16v2H4z"></path></svg>';

    $link_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="link-icon"><path d="M17.004 5H9c-1.838 0-3.586.737-4.924 2.076C2.737 8.415 2 10.163 2 12c0 1.838.737 3.586 2.076 4.924C5.414 18.263 7.162 19 9 19h8v-2H9c-1.303 0-2.55-.529-3.51-1.49C4.529 14.55 4 13.303 4 12c0-1.302.529-2.549 1.49-3.51C6.45 7.529 7.697 7 9 7h8V6l.001 1h.003c.79 0 1.539.314 2.109.886.571.571.886 1.322.887 2.116a2.966 2.966 0 0 1-.884 2.11A2.988 2.988 0 0 1 17 13H9a.99.99 0 0 1-.698-.3A.991.991 0 0 1 8 12c0-.252.11-.507.301-.698A.987.987 0 0 1 9 11h8V9H9c-.79 0-1.541.315-2.114.889C6.314 10.461 6 11.211 6 12s.314 1.54.888 2.114A2.974 2.974 0 0 0 9 15h8.001a4.97 4.97 0 0 0 3.528-1.473 4.967 4.967 0 0 0-.001-7.055A4.95 4.95 0 0 0 17.004 5z"></path></svg>';
  ?>

<div class="notification-form">
	<div class="heading-bar">
	<label for="awp_notifications[admin_pending]" class="notification-title"><?php _e('Admin Notification (Pending Payment):', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[admin_pending]" name="awp_notifications[admin_pending]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['admin_pending']) ? $this->notif['admin_pending'] : ''); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[admin_pending_img]" placeholder="<?php _e('mage URL (Max 1 MB).', 'awp'); ?>" class="image_url regular-text admin_pending_img upload-text" value="<?php echo esc_attr(isset($this->notif['admin_pending_img']) ? $this->notif['admin_pending_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="admin_pending_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>



<div class="notification-form">
	<div class="heading-bar">
	<label for="awp_notifications[admin_failed]" class="notification-title"><?php _e('Admin Notification (failed):', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[admin_failed]" name="awp_notifications[admin_failed]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['admin_failed']) ? $this->notif['admin_failed'] : ''); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[admin_failed_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text admin_failed_img upload-text" value="<?php echo esc_attr(isset($this->notif['admin_failed_img']) ? $this->notif['admin_failed_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="admin_failed_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>


<div class="notification-form">
	<div class="heading-bar">
	<label for="awp_notifications[admin_onhold]" class="notification-title"><?php _e('Admin Notification (On-Hold):', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[admin_onhold]" name="awp_notifications[admin_onhold]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['admin_onhold']) ? $this->notif['admin_onhold'] : ''); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[admin_onhold_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text admin_onhold_img upload-text" value="<?php echo esc_attr(isset($this->notif['admin_onhold_img']) ? $this->notif['admin_onhold_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="admin_onhold_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>


<div class="notification-form">
	<div class="heading-bar">
	<label for="awp_notifications[admin_processing]" class="notification-title"><?php _e('Admin Notification (processing):', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[admin_processing]" name="awp_notifications[admin_processing]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['admin_processing']) ? $this->notif['admin_processing'] : ''); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[admin_processing_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text admin_processing_img upload-text" value="<?php echo esc_attr(isset($this->notif['admin_processing_img']) ? $this->notif['admin_processing_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="admin_processing_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>


<div class="notification-form">
	<div class="heading-bar">
	<label for="awp_notifications[admin_completed]" class="notification-title"><?php _e('Admin Notification (completed):', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[admin_completed]" name="awp_notifications[admin_completed]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['admin_completed']) ? $this->notif['admin_completed'] : ''); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[admin_completed_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text admin_completed_img upload-text" value="<?php echo esc_attr(isset($this->notif['admin_completed_img']) ? $this->notif['admin_completed_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="admin_completed_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>



<div class="notification-form">
	<div class="heading-bar">
	<label for="awp_notifications[admin_refunded]" class="notification-title"><?php _e('Admin Notification (refunded):', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[admin_refunded]" name="awp_notifications[admin_refunded]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['admin_refunded']) ? $this->notif['admin_refunded'] : ''); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[admin_refunded_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text admin_refunded_img upload-text" value="<?php echo esc_attr(isset($this->notif['admin_refunded_img']) ? $this->notif['admin_refunded_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="admin_refunded_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>


<div class="notification-form">
	<div class="heading-bar">
	<label for="awp_notifications[admin_cancelled]" class="notification-title"><?php _e('Admin Notification (cancelled):', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[admin_cancelled]" name="awp_notifications[admin_cancelled]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['admin_cancelled']) ? $this->notif['admin_cancelled'] : ''); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[admin_cancelled_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text admin_cancelled_img upload-text" value="<?php echo esc_attr(isset($this->notif['admin_cancelled_img']) ? $this->notif['admin_cancelled_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="admin_cancelled_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>

			<footer class="awp-panel-footer">
                <input type="submit" class="button-primarywa"
                       value="<?php _e( 'Save Changes', 'awp' ); ?>">
            </footer>
		<?php
	}

	public function followup_settings() {
		?>
			<?php settings_fields( 'awp_storage_notifications' ); ?>

	  		<a href="https://wawp.net/docs/third-party/wordpress-woocommerce/setup-woocommerce-follow-up-message/" target="_blank"> 
			<div class="info-banner">
  		        <label for="awp_banner_info" class="banner-title"><?php _e('Follow Up', 'awp'); ?></label>
  		        <p class="banner-text"><?php _e('Retarget customers with messages about orders based on their current status and after a period of time specified by you.', 'awp'); ?></p>
  		    </div></a>
  		<?php
		
		    $message_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="message-icon"><path d="M19.045 7.401c.378-.378.586-.88.586-1.414s-.208-1.036-.586-1.414l-1.586-1.586c-.378-.378-.88-.586-1.414-.586s-1.036.208-1.413.585L4 13.585V18h4.413L19.045 7.401zm-3-3 1.587 1.585-1.59 1.584-1.586-1.585 1.589-1.584zM6 16v-1.585l7.04-7.018 1.586 1.586L7.587 16H6zm-2 4h16v2H4z"></path></svg>';

		    $link_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="link-icon"><path d="M17.004 5H9c-1.838 0-3.586.737-4.924 2.076C2.737 8.415 2 10.163 2 12c0 1.838.737 3.586 2.076 4.924C5.414 18.263 7.162 19 9 19h8v-2H9c-1.303 0-2.55-.529-3.51-1.49C4.529 14.55 4 13.303 4 12c0-1.302.529-2.549 1.49-3.51C6.45 7.529 7.697 7 9 7h8V6l.001 1h.003c.79 0 1.539.314 2.109.886.571.571.886 1.322.887 2.116a2.966 2.966 0 0 1-.884 2.11A2.988 2.988 0 0 1 17 13H9a.99.99 0 0 1-.698-.3A.991.991 0 0 1 8 12c0-.252.11-.507.301-.698A.987.987 0 0 1 9 11h8V9H9c-.79 0-1.541.315-2.114.889C6.314 10.461 6 11.211 6 12s.314 1.54.888 2.114A2.974 2.974 0 0 0 9 15h8.001a4.97 4.97 0 0 0 3.528-1.473 4.967 4.967 0 0 0-.001-7.055A4.95 4.95 0 0 0 17.004 5z"></path></svg>';
		    
		    $timer_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="message-icon"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path><path d="M13 7h-2v5.414l3.293 3.293 1.414-1.414L13 11.586z"></path></svg>';
  		?>


            <div class="tabs">
                
              <input type="radio" name="tabs" id="tabone" checked="checked">
              <label for="tabone"><?php _e('On-Hold', 'awp'); ?> </label>
              <div class="tab">
                  
                  
    <div class="notification-form">
        <div class="heading-bar">
    	    <label for="awp_notifications[followup_onhold]" class="notification-title"><?php _e('Follow Up On-Hold Order #1:', 'awp'); ?></label>
    	</div>
    	<div class="notification">
                <div class="form">
        			<?php echo $message_icon; ?>
                    <textarea class="awp-emoji" id="awp_notifications[followup_onhold]" name="awp_notifications[followup_onhold]" cols="50" rows="5" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['followup_onhold']) ? $this->notif['followup_onhold'] : ''); ?></textarea>
			        <div class="upload-field">
		                <?php echo $link_icon; ?>
                        <input type="text" name="awp_notifications[followup_onhold_img]" placeholder="<?php _e('Insert image url to attach into message (Max 1 MB)', 'awp'); ?>" class="image_url regular-text followup_onhold_img upload-text" value="<?php echo esc_attr(isset($this->notif['followup_onhold_img']) ? $this->notif['followup_onhold_img'] : ''); ?>">
                        <input type="button" name="upload-btn" class="upload-btn" data-id="followup_onhold_img" value="<?php _e( 'Upload Image', 'awp' ); ?>">
                    </div>
                </div>
        </div>
	    <p><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
    	<div class="timer">
            <label for="awp_notifications[followup_onhold_day]"><?php _e('Send Message #1 After:', 'awp'); ?></label>
            <div>
                <?php echo $timer_icon; ?>
                <input id="awp_notifications[followup_onhold_day]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_onhold_day]" type="number" placeholder="<?php _e('24 Hours', 'awp'); ?>" value="<?php echo esc_attr(isset($this->notif['followup_onhold_day']) ? $this->notif['followup_onhold_day'] : ''); ?>">
            </div>
        </div>
    </div>
                  

    <div class="notification-form">
        <div class="heading-bar">
    	    <label for="awp_notifications[followup_onhold_2]" class="notification-title"><?php _e('Follow Up On-Hold Order #2:', 'awp'); ?></label>
    	</div>
    	<div class="notification">
                <div class="form">
        			<?php echo $message_icon; ?>
                    <textarea class="awp-emoji" id="awp_notifications[followup_onhold_2]" name="awp_notifications[followup_onhold_2]" cols="50" rows="5" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['followup_onhold_2']) ? $this->notif['followup_onhold_2'] : ''); ?></textarea>
			        <div class="upload-field">
		                <?php echo $link_icon; ?>
                        <input type="text" name="awp_notifications[followup_onhold_img_2]" placeholder="<?php _e('Insert image url to attach into message (Max 1 MB)', 'awp'); ?>" class="image_url regular-text followup_onhold_img_2 upload-text" value="<?php echo esc_attr(isset($this->notif['followup_onhold_img_2']) ? $this->notif['followup_onhold_img_2'] : ''); ?>">
                        <input type="button" name="upload-btn" class="upload-btn" data-id="followup_onhold_img_2" value="<?php _e( 'Upload Image', 'awp' ); ?>">
                    </div>
                </div>
        </div>
	    <p><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
    	<div class="timer">
            <label for="awp_notifications[followup_onhold_day_2]"><?php _e('Send Message #2 After:', 'awp'); ?></label>
            <div>
                <?php echo $timer_icon; ?>
                <input id="awp_notifications[followup_onhold_day_2]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_onhold_day_2]" type="number" placeholder="<?php _e('48 Hours', 'awp'); ?>" value="<?php echo esc_attr(isset($this->notif['followup_onhold_day_2']) ? $this->notif['followup_onhold_day_2'] : ''); ?>">
            </div>
        </div>
    </div>



    <div class="notification-form">
        <div class="heading-bar">
    	    <label for="awp_notifications[followup_onhold_3]" class="notification-title"><?php _e('Follow Up On-Hold Order #3:', 'awp'); ?></label>
    	</div>
    	<div class="notification">
                <div class="form">
        			<?php echo $message_icon; ?>
                    <textarea class="awp-emoji" id="awp_notifications[followup_onhold_3]" name="awp_notifications[followup_onhold_3]" cols="50" rows="5" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['followup_onhold_3']) ? $this->notif['followup_onhold_3'] : ''); ?></textarea>
			        <div class="upload-field">
		                <?php echo $link_icon; ?>
                        <input type="text" name="awp_notifications[followup_onhold_img_3]" placeholder="<?php _e('Insert image url to attach into message (Max 1 MB)', 'awp'); ?>" class="image_url regular-text followup_onhold_img_3 upload-text" value="<?php echo esc_url(isset($this->notif['followup_onhold_img_3']) ? $this->notif['followup_onhold_img_3'] : ''); ?>">
                        <input type="button" name="upload-btn" class="upload-btn" data-id="followup_onhold_img_3" value="<?php _e( 'Upload Image', 'awp' ); ?>">
                    </div>
                </div>
        </div>
	    <p><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
    	<div class="timer">
            <label for="awp_notifications[followup_onhold_day_3]"><?php _e('Send Message #3 After:', 'awp'); ?></label>
            <div>
                <?php echo $timer_icon; ?>
                <input id="awp_notifications[followup_onhold_day_3]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_onhold_day_3]" type="number" placeholder="<?php _e('72 Hours', 'awp'); ?>" value="<?php echo esc_attr(isset($this->notif['followup_onhold_day_3']) ? $this->notif['followup_onhold_day_3'] : ''); ?>">
            </div>
        </div>
    </div>

 			    
</div>			  
              
              <input type="radio" name="tabs" id="tabtwo">
              
                <label for="tabtwo"><?php _e('Completed', 'awp'); ?></label>
                          <div class="tab">
                     
                    <div class="notification-form">
                    <div class="heading-bar">
                	    <label for="awp_notifications[followup_aftersales]" class="notification-title"><?php _e('Follow Up Completed Order #1:', 'awp'); ?></label>
                	</div>
                	<div class="notification">
                            <div class="form">
                    			<?php echo $message_icon; ?>
                                <textarea class="awp-emoji" id="awp_notifications[followup_aftersales]" name="awp_notifications[followup_aftersales]" cols="50" rows="5" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['followup_aftersales']) ? $this->notif['followup_aftersales'] : ''); ?></textarea>
            			        <div class="upload-field">
            		                <?php echo $link_icon; ?>
                                    <input type="text" name="awp_notifications[followup_aftersales_img]" placeholder="<?php _e('Insert image url to attach into message (Max 1 MB)', 'awp'); ?>" class="image_url regular-text followup_aftersales_img upload-text" value="<?php echo esc_url(isset($this->notif['followup_aftersales_img']) ? $this->notif['followup_aftersales_img'] : ''); ?>">
                                    <input type="button" name="upload-btn" class="upload-btn" data-id="followup_aftersales_img" value="<?php _e( 'Upload Image', 'awp' ); ?>">
                                </div>
                            </div>
                    </div>
            	    <p><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
                	<div class="timer">
                        <label for="awp_notifications[followup_aftersales_day]"><?php _e('Send Message #1 After:', 'awp'); ?></label>
                        <div>
                            <?php echo $timer_icon; ?>
                            <input id="awp_notifications[followup_aftersales_day]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_aftersales_day]" type="number" placeholder="<?php _e('48 Hours', 'awp'); ?>" value="<?php echo esc_attr(isset($this->notif['followup_aftersales_day']) ? $this->notif['followup_aftersales_day'] : ''); ?>">
                        </div>
                    </div>
                </div>
                          
                     <div class="notification-form">
                    <div class="heading-bar">
                	    <label for="awp_notifications[followup_aftersales_2]" class="notification-title"><?php _e('Follow Up Completed Order #2:', 'awp'); ?></label>
                	</div>
                	<div class="notification">
                            <div class="form">
                    			<?php echo $message_icon; ?>
                                <textarea class="awp-emoji" id="awp_notifications[followup_aftersales_2]" name="awp_notifications[followup_aftersales_2]" cols="50" rows="5" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['followup_aftersales_2']) ? $this->notif['followup_aftersales_2'] : ''); ?></textarea>
            			        <div class="upload-field">
            		                <?php echo $link_icon; ?>
                                    <input type="text" name="awp_notifications[followup_aftersales_img_2]" placeholder="<?php _e('Insert image url to attach into message (Max 1 MB)', 'awp'); ?>" class="image_url regular-text followup_aftersales_img_2 upload-text" value="<?php echo esc_url(isset($this->notif['followup_aftersales_img_2']) ? $this->notif['followup_aftersales_img_2'] : ''); ?>">
                                    <input type="button" name="upload-btn" class="upload-btn" data-id="followup_aftersales_img_2" value="<?php _e( 'Upload Image', 'awp' ); ?>">
                                </div>
                            </div>
                    </div>
            	    <p><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
                	<div class="timer">
                        <label for="awp_notifications[followup_aftersales_day_2]"><?php _e('Send Message #2 After:', 'awp'); ?></label>
                        <div>
                            <?php echo $timer_icon; ?>
                            <input id="awp_notifications[followup_aftersales_day_2]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_aftersales_day_2]" type="number" placeholder="<?php _e('72 Hours', 'awp'); ?>" value="<?php echo esc_attr(isset($this->notif['followup_aftersales_day_2']) ? $this->notif['followup_aftersales_day_2'] : ''); ?>">
                        </div>
                    </div>
                </div>              
                 <div class="notification-form">
                    <div class="heading-bar">
                	    <label for="awp_notifications[followup_aftersales_3]" class="notification-title"><?php _e('Follow Up Completed Order #3:', 'awp'); ?></label>
                	</div>
                	<div class="notification">
                            <div class="form">
                    			<?php echo $message_icon; ?>
                                <textarea class="awp-emoji" id="awp_notifications[followup_aftersales_3]" name="awp_notifications[followup_aftersales_3]" cols="50" rows="5" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['followup_aftersales_3']) ? $this->notif['followup_aftersales_3'] : ''); ?></textarea>
            			        <div class="upload-field">
            		                <?php echo $link_icon; ?>
                                    <input type="text" name="awp_notifications[followup_aftersales_img_3]" placeholder="<?php _e('Insert image url to attach into message (Max 1 MB)', 'awp'); ?>" class="image_url regular-text followup_aftersales_img_3 upload-text" value="<?php echo esc_url(isset($this->notif['followup_aftersales_img_3']) ? $this->notif['followup_aftersales_img_3'] : ''); ?>">
                                    <input type="button" name="upload-btn" class="upload-btn" data-id="followup_aftersales_img_3" value="<?php _e( 'Upload Image', 'awp' ); ?>">
                                </div>
                            </div>
                    </div>
            	    <p><em><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
                	<div class="timer">
                        <label for="awp_notifications[followup_aftersales_day_3]"><?php _e('Send Message #3 After:', 'awp'); ?></label>
                        <div>
                            <?php echo $timer_icon; ?>
                            <input id="awp_notifications[followup_aftersales_day_3]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_aftersales_day_3]" type="number" placeholder="96 Hours" value="<?php echo esc_attr(isset($this->notif['followup_aftersales_day_3']) ? $this->notif['followup_aftersales_day_3'] : ''); ?>">
                        </div>
                    </div>
                </div>              
            
                          </div>			  
		  
              
            </div>  
            
			<footer class="awp-panel-footer">
                <input type="submit" class="button-primarywa"
                       value="<?php _e( 'Save Changes', 'awp' ); ?>">
            </footer>
		<?php
	}
	
	public function abandoned_cart_settings() {
		    ?>
			<?php settings_fields( 'awp_storage_notifications' ); ?>

			  		<a href="https://wawp.net/docs/third-party/wordpress-woocommerce/setup-abandoned-cart-notification-message/" target="_blank"> 
			<div class="info-banner">
  		        <label for="awp_banner_info" class="banner-title"><?php _e('Abandoned Cart', 'awp'); ?></label>
  		        <p class="banner-text"><?php _e('Target visitors who reached the shopping cart and entered the data but did not complete the purchase.', 'awp'); ?></p>
  		    </div></a>

              <div class="tab">
                  
  		<table class="form-table awp-table">
			 
			  <tr valign="top" style="border-top: 1px solid #ccc;">
				  <th colspan="2">
					  <?php echo sprintf( __('Enable abandoned cart notification by installing "<a href="%s">Cartbounty Abandoned Carts</a>" plugin', 'awp'), admin_url('plugin-install.php?s=Cartbounty%20Abandoned%20Cart&tab=search&type=term') ); ?>
				  </th>
			  </tr>					
			  <?php 
	            if( is_plugin_active( 'woo-save-abandoned-carts/cartbounty-abandoned-carts.php' ) ) {
			  ?>
			  
			  
	<?php
		
		    $message_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="message-icon"><path d="M19.045 7.401c.378-.378.586-.88.586-1.414s-.208-1.036-.586-1.414l-1.586-1.586c-.378-.378-.88-.586-1.414-.586s-1.036.208-1.413.585L4 13.585V18h4.413L19.045 7.401zm-3-3 1.587 1.585-1.59 1.584-1.586-1.585 1.589-1.584zM6 16v-1.585l7.04-7.018 1.586 1.586L7.587 16H6zm-2 4h16v2H4z"></path></svg>';

		    $link_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="link-icon"><path d="M17.004 5H9c-1.838 0-3.586.737-4.924 2.076C2.737 8.415 2 10.163 2 12c0 1.838.737 3.586 2.076 4.924C5.414 18.263 7.162 19 9 19h8v-2H9c-1.303 0-2.55-.529-3.51-1.49C4.529 14.55 4 13.303 4 12c0-1.302.529-2.549 1.49-3.51C6.45 7.529 7.697 7 9 7h8V6l.001 1h.003c.79 0 1.539.314 2.109.886.571.571.886 1.322.887 2.116a2.966 2.966 0 0 1-.884 2.11A2.988 2.988 0 0 1 17 13H9a.99.99 0 0 1-.698-.3A.991.991 0 0 1 8 12c0-.252.11-.507.301-.698A.987.987 0 0 1 9 11h8V9H9c-.79 0-1.541.315-2.114.889C6.314 10.461 6 11.211 6 12s.314 1.54.888 2.114A2.974 2.974 0 0 0 9 15h8.001a4.97 4.97 0 0 0 3.528-1.473 4.967 4.967 0 0 0-.001-7.055A4.95 4.95 0 0 0 17.004 5z"></path></svg>';
		    
		    $timer_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="message-icon"><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path><path d="M13 7h-2v5.414l3.293 3.293 1.414-1.414L13 11.586z"></path></svg>';
  	?>

			  
			  
    <div class="notification-form">
        <div class="heading-bar">
    	    <label for="awp_notifications[followup_abandoned]" class="notification-title"><?php _e('Follow Up Abandoned Cart:', 'awp'); ?></label>
    	</div>
    	<div class="notification">
                <div class="form">
        			<?php echo $message_icon; ?>
                    <textarea class="awp-emoji" id="awp_notifications[followup_abandoned]" name="awp_notifications[followup_abandoned]" cols="50" rows="5" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo isset($this->notif['followup_abandoned']) ? esc_textarea($this->notif['followup_abandoned']) : ''; ?></textarea>
			        <div class="upload-field">
		                <?php echo $link_icon; ?>
                        <input type="text" name="awp_notifications[followup_abandoned_img]" placeholder="<?php _e('Insert image url to attach into message (Max 1 MB)', 'awp'); ?>" class="image_url regular-text followup_abandoned_img upload-text" value="<?php echo esc_attr(isset($this->notif['followup_abandoned_img']) ? $this->notif['followup_abandoned_img'] : ''); ?>">
                        <input type="button" name="upload-btn" class="upload-btn" data-id="followup_abandoned_img" value="<?php _e( 'Upload Image', 'awp' ); ?>">
                    </div>
                </div>
        </div>
	    <p><em><?php _e('Available tags:', 'awp'); ?></b> {{billing_first_name}} -  {{billing_last_name}} - {{billing_email}} -  {{billing_phone}} -  {{product}} -  {{order_total}} -  {{currency}} <br><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
    	<div class="timer">
            <label for="awp_notifications[followup_abandoned_day]"><?php _e('Send Message After:', 'awp'); ?></label>
            <div>
                <?php echo $timer_icon; ?>
                <input id="awp_notifications[followup_abandoned_day]" class="admin_number regular-text admin_number upload-text" name="awp_notifications[followup_abandoned_day]" type="number" placeholder="<?php _e('24 Hours', 'awp'); ?>" value="<?php echo esc_attr(isset($this->notif['followup_abandoned_day']) ? $this->notif['followup_abandoned_day'] : ''); ?>">
            </div>
        </div>
    </div>


			<?php 
		        } 
		    ?>  
			</table>    	

              </div>
	
		      
        <footer class="awp-panel-footer">
                <input type="submit" class="button-primarywa"
                       value="<?php _e( 'Save Changes', 'awp' ); ?>">
            </footer>
            <?php

	}

	public function other_settings() {
		?>			
    		<?php settings_fields( 'awp_storage_notifications' ); ?>
    		
    		
    		<div class="info-banner">
  		        <label for="awp_banner_info" class="banner-title"><?php _e('Other Integration', 'awp'); ?></label>
  		        <p class="banner-text"><?php _e('You can find other supported Plugins in this section.', 'awp'); ?></p>
  		    </div>
    		
    		             <div class="tab">
                  
  		<table class="form-table awp-table">
			 
			  <tr valign="top" style="border-top: 1px solid #ccc;">
				  <th colspan="2">
					  <?php echo sprintf( __('Enable Easy Digital Downloads notification by installing "<a href="%s">Easy Digital Downloads</a>" plugin', 'awp'), admin_url('plugin-install.php?s=Easy%2520Digital%2520Downloads&tab=search&type=term') ); ?>
				  </th>
			  </tr>					
			  <?php 
	            if( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {
			  ?>

<?php

    $message_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="message-icon"><path d="M19.045 7.401c.378-.378.586-.88.586-1.414s-.208-1.036-.586-1.414l-1.586-1.586c-.378-.378-.88-.586-1.414-.586s-1.036.208-1.413.585L4 13.585V18h4.413L19.045 7.401zm-3-3 1.587 1.585-1.59 1.584-1.586-1.585 1.589-1.584zM6 16v-1.585l7.04-7.018 1.586 1.586L7.587 16H6zm-2 4h16v2H4z"></path></svg>';

    $link_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="link-icon"><path d="M17.004 5H9c-1.838 0-3.586.737-4.924 2.076C2.737 8.415 2 10.163 2 12c0 1.838.737 3.586 2.076 4.924C5.414 18.263 7.162 19 9 19h8v-2H9c-1.303 0-2.55-.529-3.51-1.49C4.529 14.55 4 13.303 4 12c0-1.302.529-2.549 1.49-3.51C6.45 7.529 7.697 7 9 7h8V6l.001 1h.003c.79 0 1.539.314 2.109.886.571.571.886 1.322.887 2.116a2.966 2.966 0 0 1-.884 2.11A2.988 2.988 0 0 1 17 13H9a.99.99 0 0 1-.698-.3A.991.991 0 0 1 8 12c0-.252.11-.507.301-.698A.987.987 0 0 1 9 11h8V9H9c-.79 0-1.541.315-2.114.889C6.314 10.461 6 11.211 6 12s.314 1.54.888 2.114A2.974 2.974 0 0 0 9 15h8.001a4.97 4.97 0 0 0 3.528-1.473 4.967 4.967 0 0 0-.001-7.055A4.95 4.95 0 0 0 17.004 5z"></path></svg>';
  ?>    		
    		
    		
<div class="notification-form">
	<div class="heading-bar">
	<label for="awp_notifications[edd_notification]" class="notification-title"><?php _e('Easy Digital Downloads - New Order Notification:', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[edd_notification]" name="awp_notifications[edd_notification]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['edd_notification']) ? $this->notif['edd_notification'] : ''); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[edd_notification_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text edd_notification_img upload-text" value="<?php echo esc_attr(isset($this->notif['edd_notification_img']) ? $this->notif['edd_notification_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="edd_notification_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Available tags:', 'awp'); ?></b> {{site_name}} -  {{product}} -  {{currency}} -  {{subtotal_price}} -  {{total_price}} - {{payment_id}} - {{payment_status}} - {{payment_method}} - {{date}} - {{first_name}} - {{last_name}} - {{email}}<br><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>



<div class="notification-form">
	<div class="heading-bar">
	<label for="awp_notifications[edd_notification_complete]" class="notification-title"><?php _e('Easy Digital Downloads - Complete Order Notification:', 'awp'); ?></label>
	</div>
	<div class="notification">
		<div class="form">
			<?php echo $message_icon; ?>
			<textarea id="awp_notifications[edd_notification_complete]" name="awp_notifications[edd_notification_complete]" cols="50" rows="5" class="awp-emoji" placeholder="<?php _e('Write your message here..', 'awp'); ?>"><?php echo esc_textarea(isset($this->notif['edd_notification_complete']) ? $this->notif['edd_notification_complete'] : ''); ?></textarea>
			<div class="upload-field">
		        <?php echo $link_icon; ?>
				<input type="text" name="awp_notifications[edd_notification_complete_img]" placeholder="<?php _e('mage URL (Max 1 MB)...', 'awp'); ?>" class="image_url regular-text edd_notification_complete_img upload-text" value="<?php echo esc_attr(isset($this->notif['edd_notification_complete_img']) ? $this->notif['edd_notification_complete_img'] : ''); ?>">
				<input type="button" name="upload-btn" Value="<?php _e( 'Upload Image', 'awp' ); ?>" class="upload-btn" data-id="edd_notification_complete_img">
			</div>
		</div>
	</div>
    <p class="deactive-hint"><em><?php _e('Available tags:', 'awp'); ?></b> {{site_name}} -  {{product}} -  {{currency}} -  {{subtotal_price}} -  {{total_price}} - {{payment_id}} - {{payment_status}} - {{payment_method}} - {{date}} - {{first_name}} - {{last_name}} - {{email}}<br><?php _e('Leave blank to deactivate.', 'awp'); ?></em></p>
</div>
<?php 
		        } 
		    ?>  
			</table>    	

              </div>
			<footer class="awp-panel-footer">
                <input type="submit" class="button-primarywa"
                       value="<?php _e( 'Save Changes', 'awp' ); ?>">
            </footer>    		  
		<?php
	}	

	
	public function help_info() {
		?>
		<?php settings_fields( 'awp_storage_notifications' ); ?>

		<div class="info-banner">
  		        <label for="awp_banner_info" class="banner-title"><?php _e('Help info', 'awp'); ?></label>
  		        <p class="banner-text"><?php _e('You can find the identifiers for order and user data to make your message more personalized. You can also send a message to any number through this section.', 'awp'); ?></p></a>
  		    </div>
		
		
		<?php
		
		    $message_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="message-icon"><path d="M19.045 7.401c.378-.378.586-.88.586-1.414s-.208-1.036-.586-1.414l-1.586-1.586c-.378-.378-.88-.586-1.414-.586s-1.036.208-1.413.585L4 13.585V18h4.413L19.045 7.401zm-3-3 1.587 1.585-1.59 1.584-1.586-1.585 1.589-1.584zM6 16v-1.585l7.04-7.018 1.586 1.586L7.587 16H6zm-2 4h16v2H4z"></path></svg>';

		    $link_icon = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="link-icon"><path d="M17.004 5H9c-1.838 0-3.586.737-4.924 2.076C2.737 8.415 2 10.163 2 12c0 1.838.737 3.586 2.076 4.924C5.414 18.263 7.162 19 9 19h8v-2H9c-1.303 0-2.55-.529-3.51-1.49C4.529 14.55 4 13.303 4 12c0-1.302.529-2.549 1.49-3.51C6.45 7.529 7.697 7 9 7h8V6l.001 1h.003c.79 0 1.539.314 2.109.886.571.571.886 1.322.887 2.116a2.966 2.966 0 0 1-.884 2.11A2.988 2.988 0 0 1 17 13H9a.99.99 0 0 1-.698-.3A.991.991 0 0 1 8 12c0-.252.11-.507.301-.698A.987.987 0 0 1 9 11h8V9H9c-.79 0-1.541.315-2.114.889C6.314 10.461 6 11.211 6 12s.314 1.54.888 2.114A2.974 2.974 0 0 0 9 15h8.001a4.97 4.97 0 0 0 3.528-1.473 4.967 4.967 0 0 0-.001-7.055A4.95 4.95 0 0 0 17.004 5z"></path></svg>';
  		?>

		<div class="awp-panel">


<form method="post">    
    <div class="notification-form">
        <div class="heading-bar" style="display: grid; gap: 6px;">
    	    <label for="awp_test-message" class="notification-title"><?php _e('Send Test Message', 'awp'); ?></label>
		<p style="padding: 0;"><?php _e( 'You can test the service by sending WhatsApp message here:', 'awp' ); ?></p>
    	</div>
    	<div style="display: grid;">
        <label for="awp_test_number"><?php _e('To:', 'awp'); ?></label>
        <input id="awp_test_number" class="admin_number regular-text admin_number upload-text" name="awp_test_number" type="text">
        </div>
    	<div class="notification">
    	    <div style="width: 100%; margin-top: 16px;">
                <label for="awp_test_message">
                    <?php _e('Message:', 'awp'); ?>
                </label>
                <div class="form" style="margin-bottom: 32px;">
        			<?php echo $message_icon; ?>
                    <textarea class="awp-emoji" id="awp_test_message" name="awp_test_message" cols="50" rows="5" placeholder="<?php _e('Write your message here..', 'awp'); ?>"></textarea>
			        <div class="upload-field">
		                <?php echo $link_icon; ?>
                        <input type="text" name="awp_test_image" placeholder="<?php _e('Insert image url to attach into message (Max 1 MB)', 'awp'); ?>" class="image_url regular-text edd_notification_img upload-text">
                        <input type="button" name="upload-btn" class="upload-btn" data-id="awp-test-image" value="<?php _e( 'Upload Image', 'awp' ); ?>">
                    </div>
                </div>
                <input type="submit" name="awp_send_test" class="button-primarywa" value="<?php _e( 'Send Message', 'awp' ); ?>">
            </div>
        </div>
    </div>
</form>




			<div class="awp-panel-body">
				<p style="margin-bottom: 10px;"><strong><?php _e( 'Below is list of available fields you can use on notification &amp; follow-up message:', 'awp' ); ?></strong></p>
				<div class="awp-item-body">
					<div class="awp-body-left">					
						<strong><?php _e( 'ORDER', 'awp' ); ?></strong><br />
						<strong><?php _e( 'Order ID:', 'awp' ); ?></strong> {{id}}<br />
						<strong><?php _e( 'Order Key:', 'awp' ); ?></strong> {{order_key}}<br />
						<strong><?php _e( 'Order Date:', 'awp' ); ?></strong> {{order_date}}<br />
						<strong><?php _e( 'Order Summary Link:', 'awp' ); ?></strong> {{order_link}}<br />
						
						<strong><?php _e( 'Product List:', 'awp' ); ?></strong> {{product}}<br />
						<strong><?php _e( 'Product Name:', 'awp' ); ?></strong> {{product_name}}<br />
						<strong><?php _e( 'Order Discount:', 'awp' ); ?></strong> {{order_discount}}<br />
						<strong><?php _e( 'Cart Discount:', 'awp' ); ?></strong> {{cart_discount}}<br />
						<strong><?php _e( 'Tax:', 'awp' ); ?></strong> {{order_tax}}<br />
						<strong><?php _e( 'Currency Symbol:', 'awp' ); ?></strong> {{currency}}<br />
						<strong><?php _e( 'Subtotal Amount:', 'awp' ); ?></strong> {{order_subtotal}}<br />
						<strong><?php _e( 'Total Amount:', 'awp' ); ?></strong> {{order_total}}<br />
						<strong><?php _e( 'Unique Transfer Code:', 'awp' ); ?></strong> {{unique_transfer_code}}<br />
						<br />
						<strong><?php _e( 'BILLING DETAILS', 'awp' ); ?></strong><br />
						<strong><?php _e( 'First Name:', 'awp' ); ?></strong> {{billing_first_name}}<br />
						<strong><?php _e( 'Last Name:', 'awp' ); ?></strong> {{billing_last_name}}<br />
						<strong><?php _e( 'Company:', 'awp' ); ?></strong> {{billing_company}}<br />
						<strong><?php _e( 'Address 1:', 'awp' ); ?></strong> {{billing_address_1}}<br />
						<strong><?php _e( 'Address 2:', 'awp' ); ?></strong> {{billing_address_2}}<br />
						<strong><?php _e( 'City:', 'awp' ); ?></strong> {{billing_city}}<br />
						<strong><?php _e( 'Postcode:', 'awp' ); ?></strong> {{billing_postcode}}<br />
						<strong><?php _e( 'Country:', 'awp' ); ?></strong> {{billing_country}}<br />
						<strong><?php _e( 'Province:', 'awp' ); ?></strong> {{billing_state}}<br />
						<strong><?php _e( 'Email:', 'awp' ); ?></strong> {{billing_email}}<br />
						<strong><?php _e( 'Phone:', 'awp' ); ?></strong> {{billing_phone}}<br />
						<strong><?php _e( 'Customer Note:', 'awp' ); ?></strong> {{cust_note}}<br />
					</div>
					<div class="awp-body-right">					
						<strong><?php _e( 'SHIP TO DIFFERENT ADDRESS', 'awp' ); ?></strong><br />
						<strong><?php _e( 'First Name:', 'awp' ); ?></strong> {{shipping_first_name}}<br />
						<strong><?php _e( 'Last Name:', 'awp' ); ?></strong> {{shipping_last_name}}<br />
						<strong><?php _e( 'Company:', 'awp' ); ?></strong> {{shipping_company}}<br />
						<strong><?php _e( 'Address 1:', 'awp' ); ?></strong> {{shipping_address_1}}<br />
						<strong><?php _e( 'Address 2:', 'awp' ); ?></strong> {{shipping_address_2}}<br />
						<strong><?php _e( 'City:', 'awp' ); ?></strong> {{shipping_city}}<br />
						<strong><?php _e( 'Postcode:', 'awp' ); ?></strong> {{shipping_postcode}}<br />
						<strong><?php _e( 'Country:', 'awp' ); ?></strong> {{shipping_country}}<br />
						<strong><?php _e( 'Province:', 'awp' ); ?></strong> {{shipping_state}}<br />
						<br />
						<strong><?php _e( 'PAYMENT &amp; SHIPPING', 'awp' ); ?></strong><br />
						<strong><?php _e( 'Shipping Method:', 'awp' ); ?></strong> {{shipping_method}}<br />
						<strong><?php _e( 'Shipping Cost:', 'awp' ); ?></strong> {{order_shipping}}<br />
						<strong><?php _e( 'Shipping Tax:', 'awp' ); ?></strong> {{order_shipping_tax}}<br />
						<strong><?php _e( 'Payment Method:', 'awp' ); ?></strong> {{payment_method_title}}<br />
						<strong><?php _e( 'Bank Account Info:', 'awp' ); ?></strong> {{bacs_account}}<br />
						<br />
						<strong><?php _e( 'INFO', 'awp' ); ?></strong><br />
						<strong><?php _e( 'Shop Name:', 'awp' ); ?></strong> {{shop_name}}<br />
						<strong><?php _e( 'Order Note:', 'awp' ); ?></strong> {{note}}<br />
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	
    public function setup_info() {
		?>		
    <div class="info-body">
		<p class="head"><a href="https://wawp.net" title="Wawp" target="_blank"><img style="width:200px;" src="<?php echo plugins_url( '/assets/img/wawp-logo.png' , __FILE__ ); ?>"></a></p>
        <div>
            <hr class="line">
	        <form method="post" action="options.php" class="setting-form">
		    <?php settings_fields( 'awp_storage_instances' ); ?>
			<div class="heading-bar credential">
                <div class="access-title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="access-icon"><path d="M17 14H12.6586C11.8349 16.3304 9.61244 18 7 18C3.68629 18 1 15.3137 1 12C1 8.68629 3.68629 6 7 6C9.61244 6 11.8349 7.66962 12.6586 10H23V14H21V18H17V14ZM7 14C8.10457 14 9 13.1046 9 12C9 10.8954 8.10457 10 7 10C5.89543 10 5 10.8954 5 12C5 13.1046 5.89543 14 7 14Z"></path></svg>
                    <span><?php _e('Access Keys', 'awp'); ?></span>
                </div>
				<p><span><?php _e('Get your access token and instance ID on ', 'awp'); ?></span>
				<a href="https://app.wawp.net/whatsapp_profile" class="" target="_blank"><?php _e('https://app.wawp.net', 'awp'); ?></a>
                </p>
            </div>
		    <label for="awp_instances[instance_id]" class="keys-label">
			<?php _e('Instance ID:', 'awp'); ?></label>
		  <input type="text" id="instance_id" name="awp_instances[instance_id]" placeholder="Your Instance ID" class="regular-text data" value="<?php echo esc_attr(isset($this->instances['instance_id']) ? $this->instances['instance_id'] : ''); ?>">
<label for="awp_instances[access_token]" class="keys-label">
					<?php _e('Access Token:', 'awp'); ?>
				  </label>
			    <input type="text" id="access_token" name="awp_instances[access_token]" placeholder="Your Access Token" class="regular-text data" value="<?php echo esc_attr(isset($this->instances['access_token']) ? $this->instances['access_token'] : ''); ?>">
	            <input type="submit" class="setting-button"
					 value="<?php _e( 'Save ID', 'awp' ); ?>" style="margin-top:10px;">  
			  </form>

			  <?php if( isset($this->instances['access_token']) && isset($this->instances['instance_id']) ): ?>
			  <div class="instance-control">
				  <p><strong><?php _e('Instance Control', 'awp'); ?></strong></p>
				   
				  <a href="#" class="button button-secondarywa ins-action" data-action="status"><?php _e('Number Status', 'awp'); ?></a>
			 
             <a href="#" class="button button-secondarywa ins-action" data-action="connectionButtons"><?php _e('Send Message Test', 'awp'); ?></a>
             
				  <div class="instance-desc">
				      <br>
					 <strong> <span>▼</span>  <?php _e('Control Description', 'awp'); ?> </strong>
					  <div>
						  <strong><?php _e('Number Status', 'awp'); ?>:  </strong><?php _e('A connection test is performed between the WhatsApp number and the Wawp system to inform you of the result whether it is connected or not', 'awp'); ?>
						  <br>
						  <strong><?php _e('Connection test', 'awp'); ?>:  </strong><?php _e('A WhatsApp message is sent from your number registered with Wawp and added to the WordPress plugin settings (account ID and access Token) to the Wawp Bot number to verify that the plugin is active and notifications are sent normally.', 'awp'); ?>
				  </div>	  
			  </div>
			  
			  <div id="control-modal" class="modal"></div>
			  <?php endif; ?>
		  </div>

          <hr class="line">

      	<div class="quick-links">  
          <a href="https://wawp.net/docs/how-to-use/basic-account-settings/whatsapp-number-qr-connect/" target="_blank" class="quick-link"><?php _e('⚡How to Connect WhatsApp number to using Wawp QR code?', 'awp'); ?></a>
          <a href="https://wawp.net/docs/third-party/wordpress-woocommerce/link-woocommerce-plugin-using-wawp-api-keys/" target="_blank" class="quick-link"><?php _e('🔗How to Link WooCommerce plugin using Instance ID', 'awp'); ?></a>
          <a href="https://wawp.net/docs/third-party/wordpress-woocommerce/" target="_blank" class="quick-link"><?php _e('🗂️WAWP WooCommerce Plugin Docs', 'awp'); ?></a>
         </div>
         
          <hr class="line">
          
        <div class="help-items">
            <a href="https://wawp.net/docs/third-party/wordpress-woocommerce/" target="_blank">
                <div class="help-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="help-icon"><path d="M12 6a3.939 3.939 0 0 0-3.934 3.934h2C10.066 8.867 10.934 8 12 8s1.934.867 1.934 1.934c0 .598-.481 1.032-1.216 1.626a9.208 9.208 0 0 0-.691.599c-.998.997-1.027 2.056-1.027 2.174V15h2l-.001-.633c.001-.016.033-.386.441-.793.15-.15.339-.3.535-.458.779-.631 1.958-1.584 1.958-3.182A3.937 3.937 0 0 0 12 6zm-1 10h2v2h-2z"></path><path d="M12 2C6.486 2 2 6.486 2 12s4.486 10 10 10 10-4.486 10-10S17.514 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8z"></path></svg>
                    <span><?php _e('Help center', 'awp'); ?></span>
                </div> 
            </a>

            <a href="https://www.youtube.com/@wawpapp" target="_blank">
                <div class="help-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="help-icon"><path d="M18 7c0-1.103-.897-2-2-2H4c-1.103 0-2 .897-2 2v10c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-3.333L22 17V7l-4 3.333V7zm-1.998 10H4V7h12l.001 4.999L16 12l.001.001.001 4.999z"></path></svg>
                    <span><?php _e('Watch tutorials', 'awp'); ?></span>
                </div> 
            </a>
            
            <a href="https://www.facebook.com/groups/wawpcommunity" target="_blank">
                <div class="help-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="help-icon"><path d="M16.604 11.048a5.67 5.67 0 0 0 .751-3.44c-.179-1.784-1.175-3.361-2.803-4.44l-1.105 1.666c1.119.742 1.8 1.799 1.918 2.974a3.693 3.693 0 0 1-1.072 2.986l-1.192 1.192 1.618.475C18.951 13.701 19 17.957 19 18h2c0-1.789-.956-5.285-4.396-6.952z"></path><path d="M9.5 12c2.206 0 4-1.794 4-4s-1.794-4-4-4-4 1.794-4 4 1.794 4 4 4zm0-6c1.103 0 2 .897 2 2s-.897 2-2 2-2-.897-2-2 .897-2 2-2zm1.5 7H8c-3.309 0-6 2.691-6 6v1h2v-1c0-2.206 1.794-4 4-4h3c2.206 0 4 1.794 4 4v1h2v-1c0-3.309-2.691-6-6-6z"></path></svg>
                    <span><?php _e('Join our community', 'awp'); ?></span>
                                     
                </div> 
            </a>


            <a href="https://api.whatsapp.com/send?phone=447441429009&text=Plugin-help" target="_blank">
                <div class="help-item">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" class="help-icon"><path d="M5 18v3.766l1.515-.909L11.277 18H16c1.103 0 2-.897 2-2V8c0-1.103-.897-2-2-2H4c-1.103 0-2 .897-2 2v8c0 1.103.897 2 2 2h1zM4 8h12v8h-5.277L7 18.234V16H4V8z"></path><path d="M20 2H8c-1.103 0-2 .897-2 2h12c1.103 0 2 .897 2 2v8c1.103 0 2-.897 2-2V4c0-1.103-.897-2-2-2z"></path></svg>
                    <span><?php _e('Contact us', 'awp'); ?></span>   
                </div> 
            </a>


          </div>

		</div>
		
        <div class="setting-banner"></div>


		<?php
	}
	
    public function logs_page() {
    $logger = new awp_logger();
    $customer_logs = $logger->get_log_file("awpsend");
    
    // Check if the "Clear Logs" button is clicked
    if (isset($_GET['clear_logs']) && $_GET['clear_logs'] == 1) {
        $handle_to_clear = "awpsend"; // Specify the handle you want to clear
        $logger->clear($handle_to_clear);
        
        // Display a success message
        echo '<div class="notice notice-success is-dismissible"><p>';
        echo __('Logs cleared successfully.', 'awp');
        echo '</p></div>';
    }
    
    ?>
    
    <div class="wrap" id="awp-wrap">
        <h1>
            <?php echo get_admin_page_title(); ?>
            <a href="<?php echo admin_url('admin.php?page=awp-message-log&clear_logs=1'); ?>" class="button log-clear">Clear Logs</a>
            
        </h1>

        <div class="form-wrapper">
            <div class="awp-tab-wrapper">
                <div class="search-container">
                    <label for="log-search"><?php _e('Search in log:', 'awp'); ?></label>
                    <input type="text" id="log-search" placeholder="<?php _e('Type to search...Date/WhatsApp Number/Message/Image Attachment/Status', 'awp'); ?>">
                 </div>

                <table class="wp-list-table widefat fixed striped table-view-list posts table-message-logs" style="margin:10px 0;">
                    <thead>
                        <tr class="header-row">
                            <th><?php _e('Date', 'awp'); ?></th>
                            <th><?php _e('WhatsApp Number', 'awp'); ?></th>
                            <th><?php _e('Message', 'awp'); ?></th>
                            <th><?php _e('Image Attachment', 'awp'); ?></th>
                            <th><?php _e('Plugin status', 'awp'); ?></th>
                             <th><?php _e('wawp.net status', 'awp'); ?></th>
                            <th><?php _e('Resend', 'awp'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $customer_logs; ?>
                    </tbody>   
                </table>
            </div>
            <div class="info">
                <?php
                    $this->setup_info();	
                ?>	
            </div>
        </div>
    </div>
    <?php
}

}