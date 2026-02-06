<?php
/**
 * Newsletter Notice Handler
 * Zeigt eine dezente Admin-Benachrichtigung für Newsletter-Anmeldung
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Config laden
require_once ATKP_PLUGIN_DIR . '/includes/atkp_newsletter_config.php';

class atkp_newsletter_notice {

	public function __construct() {
		add_action( 'admin_notices', array( $this, 'show_newsletter_notice' ) );
		add_action( 'wp_ajax_atkp_subscribe_newsletter', array( $this, 'handle_newsletter_subscription' ) );
		add_action( 'wp_ajax_atkp_dismiss_newsletter', array( $this, 'dismiss_newsletter_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Prüft ob die Notice angezeigt werden soll
	 */
	private function should_show_notice() {
		// Prüfen ob bereits dismissed
		if ( get_option( 'atkp_newsletter_dismissed', false ) ) {
			return false;
		}

		// Prüfen ob bereits eingetragen
		if ( get_option( 'atkp_newsletter_subscribed', false ) ) {
			return false;
		}

		// Nur für Admins anzeigen
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Zeigt dezente Admin-Notice
	 */
	public function show_newsletter_notice() {
		if ( ! $this->should_show_notice() ) {
			return;
		}

		$current_user = wp_get_current_user();
		$admin_email  = get_option( 'admin_email' );
		$user_name    = $current_user->display_name ? $current_user->display_name : '';

		?>
        <div class="notice notice-info is-dismissible atkp-newsletter-notice" id="atkp-newsletter-notice">
            <p>
                <strong>affiliate-toolkit Newsletter</strong>
				<?php _e( 'Updates on new features, performance improvements and special offers – directly from the developer.', 'affiliate-toolkit-starter' ); ?>
            </p>
            <form id="atkp-newsletter-form" style="margin: 10px 0;">
                <input type="text"
                       name="firstname"
                       placeholder="<?php _e( 'First Name', 'affiliate-toolkit-starter' ); ?>"
                       value="<?php echo esc_attr( $user_name ); ?>"
                       style="margin-right: 5px; width: 200px;"
                       required>
                <input type="email"
                       name="email"
                       placeholder="<?php _e( 'Email', 'affiliate-toolkit-starter' ); ?>"
                       value="<?php echo esc_attr( $admin_email ); ?>"
                       style="margin-right: 5px; width: 250px;"
                       required>
                <button type="submit" class="button button-primary" style="vertical-align: top;">
					<?php _e( 'Get Updates', 'affiliate-toolkit-starter' ); ?>
                </button>
                <button type="button" class="button atkp-newsletter-dismiss">
					<?php _e( 'Don\'t show again', 'affiliate-toolkit-starter' ); ?>
                </button>
                <span class="atkp-newsletter-message" style="margin-left: 10px;"></span>
            </form>
        </div>
		<?php
	}

	/**
	 * Lädt JavaScript
	 */
	public function enqueue_scripts() {
		if ( ! $this->should_show_notice() ) {
			return;
		}

		wp_enqueue_script( 'atkp-newsletter-notice', plugins_url( '../js/atkp-newsletter-notice.js', __FILE__ ), array( 'jquery' ), ATKP_UPDATE_VERSION, true );

		wp_localize_script( 'atkp-newsletter-notice', 'atkpNewsletterAjax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'atkp_newsletter_nonce' ),
		) );
	}

	/**
	 * Behandelt die Newsletter-Anmeldung
	 */
	public function handle_newsletter_subscription() {
		check_ajax_referer( 'atkp_newsletter_nonce', 'nonce' );

		$firstname = sanitize_text_field( $_POST['firstname'] ?? '' );
		$email     = sanitize_email( $_POST['email'] ?? '' );

		if ( empty( $firstname ) || empty( $email ) || ! is_email( $email ) ) {
			wp_send_json_error( array(
				'message' => __( 'Please fill in all fields correctly.', 'affiliate-toolkit-starter' )
			) );
		}

		// An Brevo senden
		$result = $this->send_to_brevo( $firstname, $email );

		if ( $result['success'] ) {
			update_option( 'atkp_newsletter_subscribed', true );
			wp_send_json_success( array(
				'message' => __( 'Please check your inbox and confirm your subscription.', 'affiliate-toolkit-starter' )
			) );
		} else {
			wp_send_json_error( array(
				'message' => $result['message']
			) );
		}
	}

	/**
	 * Notice schließen
	 */
	public function dismiss_newsletter_notice() {
		check_ajax_referer( 'atkp_newsletter_nonce', 'nonce' );
		update_option( 'atkp_newsletter_dismissed', true );
		wp_send_json_success();
	}

	/**
	 * Sendet die Daten an den Newsletter-Server
	 */
	private function send_to_brevo( $firstname, $email ) {
		$endpoint = ATKP_NEWSLETTER_ENDPOINT;

		if ( empty( $endpoint ) ) {
			return array(
				'success' => false,
				'message' => __( 'Newsletter endpoint is not configured.', 'affiliate-toolkit-starter' )
			);
		}

		$body = array(
			'firstname' => $firstname,
			'email'     => $email,
			'site_url'  => get_site_url(),
		);

		$response = wp_remote_post( $endpoint, array(
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => json_encode( $body ),
			'timeout' => 15,
		) );

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => __( 'Connection error: ', 'affiliate-toolkit-starter' ) . $response->get_error_message()
			);
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $response_code === 200 && isset( $response_body['success'] ) && $response_body['success'] ) {
			return array(
				'success' => true,
				'message' => $response_body['message'] ?? __( 'Successfully subscribed!', 'affiliate-toolkit-starter' )
			);
		}

		$error_message = __( 'An error occurred.', 'affiliate-toolkit-starter' );
		if ( isset( $response_body['message'] ) ) {
			$error_message = $response_body['message'];
		}

		return array(
			'success' => false,
			'message' => $error_message
		);
	}
}

// Initialisierung
new atkp_newsletter_notice();

