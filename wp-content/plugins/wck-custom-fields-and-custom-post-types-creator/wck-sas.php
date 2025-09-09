<?php 
/* Start and settings page */

/* Add Scripts */
add_action('admin_enqueue_scripts', 'wck_sas_print_scripts' );
function wck_sas_print_scripts($hook){
	if( 'wck_page_sas-page' == $hook ){		
		wp_register_style('wck-sas-css', plugins_url('/css/wck-sas.css', __FILE__));
		wp_enqueue_style('wck-sas-css');
	}
}

/* Create the WCK "Start & Settings" Page only for admins ( 'capability' => 'edit_theme_options' ) */
add_action( 'init', function(){
    $args = array(
                'page_title' => __( 'Start Here & General Settings', 'wck' ),
                'menu_title' => __( 'Start and Settings', 'wck' ),
                'capability' => 'edit_theme_options',
                'menu_slug' => 'sas-page',
                'page_type' => 'submenu_page',
                'parent_slug' => 'wck-page',
                'priority' => 7,
                'page_icon' => plugins_url('/images/wck-32x32.png', __FILE__)
            );
    new WCK_Page_Creator( $args );
});

/* create the meta box only for admins ( 'capability' => 'edit_theme_options' ) */
add_action( 'init', 'wck_sas_create_box', 11 );
function wck_sas_create_box(){

	if( is_admin() && current_user_can( 'edit_theme_options' ) ){
		
		/* set up the fields array */
		$sas_serial_fields = array(
            array( 'type' => 'html', 'html-content' => wck_register_version_form() ),
		);
		
		/* set up the box arguments */
		$args = array(
			'metabox_id' => 'option_page',
			'metabox_title' => __( 'Register Your Version', 'wck' ),
			'post_type' => 'sas-page',
			'meta_name' => 'wck_serial',
			'meta_array' => $sas_serial_fields,
			'context' 	=> 'option',
			'single' => true,
			'sortable' => false
		);

		/* create the box */
		$wck_premium_update = WCK_PLUGIN_DIR.'/update/';
		if (file_exists ($wck_premium_update . 'update-checker.php'))
			new Wordpress_Creation_Kit( $args );
				
		/* set up the tools array */			
		$sas_tools_activate = array(
			array( 'type' => 'radio', 'title' => __( 'Custom Fields Creator', 'wck' ), 'slug' => 'custom-fields-creator', 'options' => array( 'enabled', 'disabled' ), 'default' => 'enabled' ),
			array( 'type' => 'radio', 'title' => __( 'Custom Post Type Creator', 'wck' ), 'slug' => 'custom-post-type-creator', 'options' => array( 'enabled', 'disabled' ), 'default' => 'enabled' ),
			array( 'type' => 'radio', 'title' => __( 'Custom Taxonomy Creator', 'wck' ), 'slug' => 'custom-taxonomy-creator', 'options' => array( 'enabled', 'disabled' ), 'default' => 'enabled' ),
		);
		if( file_exists( dirname(__FILE__).'/wck-fep.php' ) )
			$sas_tools_activate[] = array( 'type' => 'radio', 'title' => __( 'Frontend Posting', 'wck' ), 'slug' => 'frontend-posting', 'options' => array( 'enabled', 'disabled' ), 'default' => 'enabled' );
		if( file_exists( dirname(__FILE__).'/wck-opc.php' ) )
			$sas_tools_activate[] = array( 'type' => 'radio', 'title' => __( 'Option Pages Creator', 'wck' ), 'slug' => 'option-pages-creator', 'options' => array( 'enabled', 'disabled' ), 'default' => 'enabled' );
		if( file_exists( dirname(__FILE__).'/wck-stp.php' ) )
			$sas_tools_activate[] = array( 'type' => 'radio', 'title' => __( 'Swift Templates', 'wck' ), 'slug' => 'swift-templates', 'options' => array( 'enabled', 'disabled' ), 'default' => 'enabled' );
		if( !file_exists( dirname(__FILE__).'/wck-stp.php' ) && !file_exists( dirname(__FILE__).'/wck-fep.php' )  )
			$sas_tools_activate[] = array( 'type' => 'radio', 'title' => __( 'Swift Templates and Front End Posting', 'wck' ), 'slug' => 'swift-templates-and-front-end-posting', 'options' => array( 'enabled', 'disabled' ), 'default' => 'enabled' );
			
		/* set up the box arguments */
		$args = array(
			'metabox_id' => 'wck_tools_activate',
			'metabox_title' => __( 'WordPress Creation Kit Tools: enable or disable the tools you want', 'wck' ),
			'post_type' => 'sas-page',
			'meta_name' => 'wck_tools',
			'meta_array' => $sas_tools_activate,	
			'context' 	=> 'option',
			'single' => true
		);

		/* create the box */
		new Wordpress_Creation_Kit( $args );


        /* set up the extra settings array */
        $sas_extra_options = array();

        if( file_exists( dirname( __FILE__ ) . '/wordpress-creation-kit-api/fields/map.php' ) )
            $sas_extra_options[] = array( 'type' => 'text', 'title' => __( 'Google Maps API', 'wck' ), 'description' => __( 'Enter your Google Maps API key ( <a href="https://console.developers.google.com/flows/enableapi?apiid=maps_backend" target="_blank">Get your API key</a> )', 'wck' ), 'required' => false );

        /* if there are extra options add the box */
        if( !empty( $sas_extra_options ) ) {

            /* set up the box arguments */
            $args = array(
                'metabox_id' => 'wck_extra_options',
                'metabox_title' => __( 'Extra Settings', 'wck' ),
                'post_type' => 'sas-page',
                'meta_name' => 'wck_extra_options',
                'meta_array' => $sas_extra_options,
                'context' 	=> 'option',
                'single' => true,
                'sortable' => false
            );

            /* create the box */
            if (file_exists ($wck_premium_update . 'update-checker.php'))
                new Wordpress_Creation_Kit( $args );

        }

	}
}

/* Add the welcoming text on WCK Start and Settings Page */
add_action( 'wck_before_meta_boxes', 'wck_sas_welcome');
function wck_sas_welcome($hook){
	if('wck_page_sas-page' == $hook ){
		$plugin_path = dirname( __FILE__ ) . '/wck.php';
		$default_plugin_headers = get_plugin_data($plugin_path);
		$plugin_name = $default_plugin_headers['Name'];
		$plugin_version = $default_plugin_headers['Version'];
		$plugin_name_class = ( strpos( strtolower($plugin_name), 'pro' ) !== false ? 'Pro' : ( strpos( strtolower($plugin_name), 'hobbyist' ) !== false ? 'Hobbyist' : 'Free' ) );

        if( version_compare(PHP_VERSION, '5.3.0') < 0 ) { ?>
            <div class="notice-error notice">
                <p>
                    <?php esc_html_e('<strong>You are using a very old version of PHP</strong> (5.2.x or older) which has serious security and performance issues. Please ask your hoster to provide you with an upgrade path to 5.6 or 7.0','wck'); ?>
                </p>
            </div>
        <?php }
?>
		<div class="wrap about-wrap">
			<div class="wck-badge <?php echo esc_attr($plugin_name_class); ?>"><span><?php echo esc_html( sprintf( __( 'Version %s', "wck" ), esc_html( $plugin_version ) ) ); ?></span></div>
			<h1><?php echo esc_html( sprintf( __( 'Welcome to %s', 'wck' ), $plugin_name ) ); ?></h1>
			<div class="about-text"><?php echo wp_kses_post( 'WCK helps you create <strong>repeater custom fields, custom post types</strong> and <strong>taxonomies</strong> in just a couple of clicks, directly from the WordPress admin interface. WCK content types will improve the usability of the sites you build, making them easy to manage by your clients. ', 'wck' ); ?></div>
		</div>

<?php
	}
}

/* Add the Quick Start-Up Guide text on WCK Start and Settings Page */
add_action( 'wck_after_meta_boxes', 'wck_sas_quickintro', 12);
function wck_sas_quickintro($hook){
	if('wck_page_sas-page' == $hook ){
?>



        <div class="wrap about-wrap" style="clear:both;">

            <div>
                <div style="float:right">
                    <a href="https://wordpress.org/plugins/translatepress-multilingual/" target="_blank"><img src="<?php echo esc_url( plugins_url( './images/pb-trp-cross-promotion.png', __FILE__ ) ); ?>" alt="TranslatePress Logo"/></a>
                </div>
                <div>
                    <h3>Easily translate your entire WordPress website</h3>
                    <p>Translate your Custom Post Types and Custom Fields with a WordPress translation plugin that anyone can use.</p>
                    <p>It offers a simpler way to translate WordPress sites, with full support for WooCommerce and site builders.</p>
                    <p><a href="https://wordpress.org/plugins/translatepress-multilingual/" class="button" target="_blank">Find out how</a></p>

                </div>
            </div>


			<div class="changelog">
				<h2><?php esc_html_e( 'Quick Start-Up Guide', 'wck' ); ?></h2>

				<div class="feature-section">

					<h4><?php esc_html_e( 'Custom Fields Creator', 'wck' ); ?></h4>
					<p><?php esc_html_e( 'WordPress Creation Kit Pro has support for a wide list of custom fields: WYSIWYG Editor, Upload Field, Date, User, Country, Text Input, Textarea, Drop-Down, Select, Checkboxes, Radio Buttons', 'wck' ); ?></p>
					<p><?php echo wp_kses_post( 'Access documentation <a href="http://www.cozmoslabs.com/docs/wordpress-creation-kit-documentation/#Custom_Fields_Creator" target="_blank">here</a> about how to display them in your templates.', 'wck' ); ?></p>

					<h4><?php esc_html_e( 'Post Type Creator', 'wck' ); ?></h4>
					<p><?php esc_html_e( 'Create & manage all your custom content types', 'wck' ); ?></p>
					<p><?php echo wp_kses_post( 'Access documentation <a href="http://www.cozmoslabs.com/docs/wordpress-creation-kit-documentation/#Custom_Post_Type_Creator" target="_blank">here</a> about how to display them in your templates.', 'wck' ); ?></p>
					
					<h4><?php esc_html_e( 'Taxonomy Creator', 'wck' ); ?></h4>
					<p><?php esc_html_e( 'Create new taxonomies for filtering your content', 'wck' ); ?></p>
					<p><?php echo wp_kses_post( 'Access documentation <a href="http://www.cozmoslabs.com/docs/wordpress-creation-kit-documentation/#Custom_Taxonomy_Creator" target="_blank">here</a> about how to display them in your templates.', 'wck' ); ?></p>
					
					<h4><?php echo wp_kses_post( 'Swift Templates (available in the <a href="http://www.cozmoslabs.com/wck-custom-fields-custom-post-types-plugin/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=WCKFree-SAS" target="_blank">PRO</a> version)', 'wck' ); ?></h4>
					<p><?php esc_html_e( 'Build your front-end templates directly from the WordPress admin UI, without writing any PHP code.', 'wck' ); ?></p>
					<p><?php echo wp_kses_post( 'Access documentation <a href="http://www.cozmoslabs.com/docs/wordpress-creation-kit-documentation/#Swift_Templates" target="_blank">here</a> on how to easily display registered custom post types, custom fields and taxonomies in your theme.', 'wck' ); ?></p>
					
					<h4><?php echo wp_kses_post( 'Front-End Posting (available in the <a href="http://www.cozmoslabs.com/wck-custom-fields-custom-post-types-plugin/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=WCKFree-SAS" target="_blank">PRO</a> version)', 'wck' ); ?></h4>
					<p><?php esc_html_e( 'Create and edit posts/pages or custom posts directly from the front-end.', 'wck' ); ?></p>
					<p><?php esc_html_e( 'Available shortcodes:', 'wck' ); ?></p>
					<ul>
						<li><?php esc_html_e( '[fep form_name="front-end-post-name"] - displays your form in the front-end', 'wck' ); ?></li>
						<li><?php esc_html_e( '[fep-dashboard] - the quick-dashboard allows: simple profile updates, editing/deletion of posts, pages and custom post types.', 'wck' ); ?></li>
						<li><?php esc_html_e( '[fep-lilo] - login/logout/register widget with the simple usage of a shortcode. Can be added in a page or text widget.', 'wck' ); ?></li>
					</ul>
					<p><?php echo wp_kses_post( 'Access documentation <a href="http://www.cozmoslabs.com/docs/wordpress-creation-kit-documentation/frontend-posting/" target="_blank">here</a> about how to display them in your templates.', 'wck' ); ?></p>
					
					<h4><?php echo wp_kses_post( 'Option Pages (available in the <a href="http://www.cozmoslabs.com/wck-custom-fields-custom-post-types-plugin/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=WCKFree-SAS" target="_blank">PRO</a> version)', 'wck' ); ?></h4>
					<p><?php esc_html_e( 'The Options Page Creator Allows you to create a new menu item called "Options"(for example) which can hold advanced custom field groups. Perfect for theme options or a simple UI for your custom plugin (like a simple testimonials section in the front-end).', 'wck' ); ?></p>

				</div>
			</div>
		</div>

<?php
	}
}


/**
 * Class that adds a notice when either the serial number wasn't found, or it has expired
 *
 * @since v.2.1.1
 *
 * @return void
 */
class wck_add_serial_notices{
    public $pluginPrefix = '';
    public $notificaitonMessage = '';
    public $pluginSerialStatus = '';

    function __construct( $pluginPrefix, $notificaitonMessage, $pluginSerialStatus ){
        $this->pluginPrefix = $pluginPrefix;
        $this->notificaitonMessage = $notificaitonMessage;
        $this->pluginSerialStatus = $pluginSerialStatus;

        add_action( 'admin_notices', array( $this, 'add_admin_notice' ) );
        add_action( 'admin_init', array( $this, 'dismiss_notification' ) );
    }


    // Display a notice that can be dismissed in case the serial number is inactive
    function add_admin_notice() {
        global $current_user ;
        global $pagenow;

        $user_id = $current_user->ID;

        do_action( $this->pluginPrefix.'_before_notification_displayed', $current_user, $pagenow );

        if ( current_user_can( 'manage_options' ) ){

            $plugin_serial_status = get_option( $this->pluginSerialStatus );
            if ( $plugin_serial_status != 'found' ){

                //we want to show the expiration notice on our plugin pages even if the user dismissed it on the rest of the site
                $force_show = false;
                if ( $plugin_serial_status == 'expired' ) {
                    $notification_instance = WCK_Plugin_Notifications::get_instance();
                    if ($notification_instance->is_plugin_page()) {
                        $force_show = true;
                    }
                }

                // Check that the user hasn't already clicked to ignore the message
                if ( ! get_user_meta($user_id, $this->pluginPrefix.'_dismiss_notification' ) || $force_show ) {
                    echo wp_kses_post( apply_filters($this->pluginPrefix.'_notification_message','<div class="error wck-serial-notification" >'.$this->notificaitonMessage.'</div>', $this->notificaitonMessage) );
                }
            }

            do_action( $this->pluginPrefix.'_notification_displayed', $current_user, $pagenow, $plugin_serial_status );

        }

        do_action( $this->pluginPrefix.'_after_notification_displayed', $current_user, $pagenow );

    }

    function dismiss_notification() {
        global $current_user;

        $user_id = $current_user->ID;

        do_action( $this->pluginPrefix.'_before_notification_dismissed', $current_user );

        // If user clicks to ignore the notice, add that to their user meta
        if ( isset( $_GET[$this->pluginPrefix.'_dismiss_notification']) && '0' === $_GET[$this->pluginPrefix.'_dismiss_notification'] )
            add_user_meta( $user_id, $this->pluginPrefix.'_dismiss_notification', 'true', true );

        do_action( $this->pluginPrefix.'_after_notification_dismissed', $current_user );
    }
}

// Verify if it's a premium version and display serial notifications
$wck_premium_update = WCK_PLUGIN_DIR.'/update/';
if (file_exists ($wck_premium_update . 'update-checker.php')) {

    add_action('admin_init', function() {
        $wck_serial_status = get_option('wck_serial_status');
        $license_details = get_option( 'wck_license_details', false );

        if (file_exists(WCK_PLUGIN_DIR . '/wordpress-creation-kit-api/wck-fep/wck-fep.php'))
            $wck_version = 'pro';
        else
            $wck_version = 'hobbyist';

        if ($wck_serial_status === 'notFound' || $wck_serial_status === 'noserial' || $wck_serial_status === 'missing' || empty( $wck_serial_status ) ) {
            new wck_add_serial_notices('wck', sprintf(__('<p>Your <strong>WordPress Creation Kit</strong> serial number is invalid or missing. <br/>Please %1$sregister your copy%2$s of WCK to receive access to automatic updates and support. Need a license key? %3$sPurchase one now%4$s</p>', 'wck'), "<a href='admin.php?page=sas-page'>", "</a>", "<a href='https://www.cozmoslabs.com/wck-custom-fields-custom-post-types-plugin/?utm_source=WCK&utm_medium=dashboard&utm_campaign=WCK-SN-Purchase' target='_blank' class='button-primary'>", "</a>"), 'wck_serial_status'); //phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
        } elseif ($wck_serial_status == 'expired') {
            /* on our plugin pages do not add the dismiss button for the expired notification*/
            $wck_notifications = WCK_Plugin_Notifications::get_instance();
            if ($wck_notifications->is_plugin_page())
                $message = __('<p style="position:relative;">Your <strong>WordPress Creation Kit</strong> licence has expired. <br/>Please %1$sRenew Your Licence%2$s to continue receiving access to product downloads, automatic updates and support. %3$sRenew now %4$s</p>', 'wck'); //phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
            else
                $message = __('<p style="position:relative;">Your <strong>WordPress Creation Kit</strong> licence has expired. <br/>Please %1$sRenew Your Licence%2$s to continue receiving access to product downloads, automatic updates and support. %3$sRenew now %4$s %5$sDismiss%6$s</p>', 'wck'); //phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
            new wck_add_serial_notices('wck_expired', sprintf($message, "<a href='https://www.cozmoslabs.com/account/?utm_source=WCK&utm_medium=dashboard&utm_campaign=WCK-Renewal' target='_blank'>", "</a>", "<a href='" . esc_url("https://www.cozmoslabs.com/account/?utm_source=WCK&utm_medium=dashboard&utm_campaign=WCK-Renewal") . "' target='_blank' class='button-primary'>", "</a>", "<a href='" . esc_url(add_query_arg('wck_expired_dismiss_notification', '0')) . "' class='wck-dismiss-notification' style='position:absolute; right:0px; top:50%; margin-top:-7px;'>", "</a>"), 'wck_serial_status');
        } elseif ( $wck_serial_status != 'expired' && ( !empty( $license_details ) && !empty( $license_details->expires ) && $license_details->expires !== 'lifetime' ) && ( ( !isset( $license_details->subscription_status ) || $license_details->subscription_status != 'active' ) && strtotime( $license_details->expires ) < strtotime( '+14 days' ) ) ) {
            $date = date_i18n( get_option( 'date_format' ), strtotime( $license_details->expires ) );
            new wck_add_serial_notices('wck_about_to_expire', sprintf(__('<p style="position:relative;">Your <strong>WordPress Creation Kit</strong> serial number is about to expire on %5$s. <br/>Please %1$sRenew Your Licence%2$s to continue receiving access to product downloads, automatic updates and support. %3$sRenew now %4$s %6$sDismiss%7$s</p>', 'wck'), "<a href='https://www.cozmoslabs.com/account/?utm_source=WCK&utm_medium=dashboard&utm_campaign=WCK-Renewal'>", "</a>", "<a href='" . esc_url("https://www.cozmoslabs.com/account/?utm_source=WCK&utm_medium=dashboard&utm_campaign=WCK-Renewal") . "' target='_blank' class='button-primary'>", "</a>", $date, "<a href='" . esc_url(add_query_arg('wck_about_to_expire_dismiss_notification', '0')) . "' class='wck-dismiss-notification' style='position:absolute; right:0px; top:50%; margin-top:-7px;'>", "</a>"), 'wck_serial_status'); //phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
        }
    });

}


function wck_register_version_form() {
    $status          = get_option( 'wck_serial_status' );
    $license         = wck_get_serial_number();
    $license_details = get_option( 'wck_license_details', false );
    $version_details = wck_get_version_details();

    if( !empty( $license ) ){
        // process license so it doesn't get displayed in back-end
        $license_length = strlen( $license );
        $license        = substr_replace( $license, '***************', 7, $license_length - 14 );
    }

    ob_start();
    ?>
    <form method="post" action="<?php echo !is_multisite() ? 'options.php' : 'edit.php'; ?>">
        <?php settings_fields( 'wck_serial' ); ?>


        <label class="field-label" for="wck_serial"><?php esc_html_e( 'License key', 'wck' ); ?></label>

        <div class="mb-right-column">
            <input id="wck_serial" name="wck_serial" type="text" class="regular-text" value="<?php echo esc_attr( $license ); ?>" />
            <?php wp_nonce_field( 'wck_license_nonce', 'wck_license_nonce' ); ?>

            <?php if( $status !== false && $status == 'valid' ) {
                $button_name =  'wck_edd_license_deactivate';
                $button_value = __('Deactivate License', 'wck' );
                echo '<span title="'. esc_html__( 'Active on this site', 'wck' ) .'" class="wck-active-license dashicons dashicons-yes"></span>';

            } else {
                $button_name =  'wck_edd_license_activate';
                $button_value = __('Activate License', 'wck');
            }
            ?>
            <input type="submit" class="button-secondary" name="<?php echo esc_attr( $button_name ); ?>" value="<?php echo esc_attr( $button_value ); ?>"/>

        </div>


    </form>

    <?php if( $status != 'expired' && ( !empty( $license_details ) && !empty( $license_details->expires ) && $license_details->expires !== 'lifetime' ) && ( ( !isset( $license_details->subscription_status ) || $license_details->subscription_status != 'active' ) && strtotime( $license_details->expires ) < strtotime( '+14 days' ) ) ) : ?>
        <div class="serial-notification yellow">
            <p class="description"><?php echo wp_kses_post( sprintf( __( 'Your %s license is about to expire on %s', 'wck' ), '<strong>' . $version_details['wck_version_name'] . '</strong>', '<strong>' . date_i18n( get_option( 'date_format' ), strtotime( $license_details->expires ) ) . '</strong>' ) ); ?>
            <p class="description"><?php echo wp_kses_post( sprintf( __( 'Please %sRenew Your Licence%s to continue receiving access to product downloads, automatic updates and support.', 'wck' ), "<a href='https://www.cozmoslabs.com/account/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=PBPro&utm_content=license-about-to-expire' target='_blank'>", "</a>" ) ); ?></p>
        </div>
    <?php elseif( $status == 'expired' ) : ?>
        <div class="serial-notification red">
            <p class="description"><?php echo wp_kses_post( sprintf( __( 'Your %s license has expired.', 'wck' ), '<strong>' . $version_details['wck_version_name'] . '</strong>' ) ); ?>
            <p class="description"><?php echo wp_kses_post( sprintf( __( 'Please %1$sRenew Your Licence%2$s to continue receiving access to product downloads, automatic updates and support.', 'wck' ), '<a href="https://www.cozmoslabs.com/account/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=PBPro&utm_content=license-expired" target="_blank">', '</a>' ) ); ?></p>
        </div>
    <?php elseif( $status == 'no_activations_left' ) : ?>
        <div class="serial-notification red">
            <p class="description"><?php echo wp_kses_post( sprintf( __( 'Your %s license has reached its activation limit.', 'wck' ), '<strong>' . $version_details['wck_version_name'] . '</strong>' ) ); ?>
            <p class="description"><?php echo wp_kses_post( sprintf( __( '%sUpgrade now%s for unlimited activations and extra features like multiple registration and edit profile forms, userlisting, custom redirects and more.', 'wck' ), '<a href="https://www.cozmoslabs.com/account/?utm_source=wpbackend&utm_medium=clientsite&utm_campaign=PBPro&utm_content=license-activation-limit" target="_blank">', '</a>' ) ); ?>
        </div>
    <?php elseif( empty( $license ) || $status != 'valid' ) : ?>
        <div class="serial-notification">
            <p class="description"><?php echo wp_kses_post( sprintf( __( 'Enter and activate your license key. Your license key can be found in your %sCozmoslabs account%s. ', 'wck' ), '<a href="https://www.cozmoslabs.com/account/?utm_source=wckbackend&utm_medium=clientsite&utm_campaign=WCKPro&utm_content=license-missing" target="_blank">', '</a>' ) ); ?></p>
        </div>
    <?php endif; ?>

    <?php
    return ob_get_clean();
}
