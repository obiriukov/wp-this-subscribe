<?php
/**
 * Created by PhpStorm.
 * User: CaguCT
 * Date: 11/29/17
 * Time: 13:30
 */

namespace ThisSubscribe;

/**
 * Plugin Api
 *
 * Class Api
 * @package ThisSubscribe
 */
class PluginApi {

	const TEMPLATE_EXT = 'php';
	const UNSUBSCRIBER_PAGE_SLUG = 'this-unsubscribe';

	public $subscribersApi;

	public function __construct() {
		$this->subscribersApi = new SubscriberApi();
	}

	/**
	 * Add plugin scripts
	 */
	public static function addScripts() {

		wp_enqueue_script( 'this-subscribe', PL_URL . 'assets/js/this-subscribe.js', array( 'jquery' ), 1.0, true );
		wp_localize_script( 'this-subscribe', 'ThisSubscribeAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	 * Add plugin menu pages
	 */
	public function pluginMenu() {
		global $_wp_last_object_menu;

		// Position
		$_wp_last_object_menu ++;

		add_menu_page( __( 'This subscribe' ), __( 'This subscribe' ), 'customize',
			'wpts', null, 'dashicons-email-alt', $_wp_last_object_menu );

		add_submenu_page( 'wpts', __( 'Subscribers' ), __( 'Subscribers' ), 'customize',
			'wpts', array( 'ThisSubscribe\PluginController', 'subscribersAdmin' ) );

		add_submenu_page( 'wpts', __( 'Add This subscribe' ),
			__( 'Add New' ), 'customize', 'wpts-add', array( 'ThisSubscribe\PluginController', 'addSubscriberAdmin' ) );

		add_submenu_page( 'wpts', __( 'Settings' ),
			__( 'Settings' ), 'manage_options', SettingsPage::MENU_SLUG, array(
				'ThisSubscribe\SettingsPage',
				'createAdminPage'
			) );
	}

	/**
	 * Install staff
	 */
	public function pluginAdminInit() {

		// SettingsPage
		$settingsPage = new SettingsPage();
		$settingsPage->pageInit();

		// Subscriber page backend
		$adminFrontEnd = new AdminFrontEnd();
		$adminFrontEnd->subscribersPageAction();
		$adminFrontEnd->addSubscriberPageAction();
	}

	/**
	 * Action init staff
	 */
	public function pluginInit() {

		if ( ! session_id() ) {
			session_start();
		}

		// Remove Subscriber COOKIE if isset session
		if ( ! empty( $_SESSION[ Subscriber::COOKIE ] ) && ! empty( $_COOKIE[ Subscriber::COOKIE ] ) ) {
			setcookie( Subscriber::COOKIE, '', time() - 3600, '/' );
			unset( $_SESSION[ Subscriber::COOKIE ] );
		}
	}

	/**
	 * AJAX: Add subscriber from mail
	 */
	public function addMail() {

		$mail   = ! empty( $_REQUEST['mail'] ) ? $_REQUEST['mail'] : null;
		$result = array();

		if ( $mail !== null ) {

			$subscriber = new Subscriber( $mail );

			// If subscriber not found
			if ( $subscriber->id === null ) {
				// Add new Subscriber
				$subscriber->hash = wp_hash_password( $mail . SECURE_AUTH_SALT );
				$subscriber->mail = $mail;
				$subscriber->save();
			}

			if ( $subscriber->id !== null ) {

				// Subscribe
				$subscriber->api->subscribe( $subscriber );

				// Send template
				$result['html'] = $this->getTemplate( 'subscribed', $subscriber );

				// Save subscriber to cookies one year
				$oneYearTimestamp = time() + 3600 * 24 * 365;
				setcookie( Subscriber::COOKIE, $subscriber->hash, $oneYearTimestamp, '/' );
			}
		}

		echo json_encode( $result );
		wp_die();
	}

	/**
	 * AJAX: Change subscriber mail
	 */
	public function changeMail() {
		$result = array();

		// Remove cookie
		setcookie( Subscriber::COOKIE, '', time() - 3600, '/' );

		// Return def template
		$result['html'] = $this->getTemplate( 'subs-form' );

		echo json_encode( $result );
		wp_die();
	}

	/**
	 * AJAX: Send mail to subscriber for abort
	 */
	public function abortSubscriber() {
		$result = array();

		$subscriberId = ! empty( $_COOKIE[ Subscriber::COOKIE ] ) ? $_COOKIE[ Subscriber::COOKIE ] : null;

		if ( $subscriberId !== null ) {

			// Get subscriber
			$subscriber = new Subscriber( $subscriberId );
			if ( $subscriber->id !== null ) {

				$unLink = get_bloginfo( 'wpurl' ) . '/' . self::UNSUBSCRIBER_PAGE_SLUG . '?' . Subscriber::HASH . '=';

				$replace = array(
					'[blogname]'   => get_option( 'blogname' ),
					'[abort-link]' => $unLink . urlencode( $subscriber->hash ),
				);

				// Send mail with instructions
				$subject = strtr( SettingsPage::getOption( 'abort_subject' ), $replace );
				$message = strtr( SettingsPage::getOption( 'abort_message' ), $replace );
				$headers = array( 'Content-Type: text/html; charset=UTF-8' );

				// Send email to subscriber
				wp_mail( $subscriber->mail, $subject, nl2br( $message ), $headers );

				// Return abort info
				$result['html'] = $this->getTemplate( 'abort-info' );
			}
		}
		echo json_encode( $result );
		wp_die();
	}

	/**
	 * [thisSubscribe] short code
	 *
	 * @return bool|string
	 */
	public function shortCode() {

		$subscriberHash = ! empty( $_COOKIE[ Subscriber::COOKIE ] ) ? $_COOKIE[ Subscriber::COOKIE ] : null;

		// If user already subscribed
		if ( $subscriberHash !== null ) {

			// Get subscribe
			$subscriber = new Subscriber( $subscriberHash );

			if ( $subscriber->id !== null ) {
				return $this->getTemplate( 'subscribed', $subscriber );
			}
		}

		// View subscribe template
		return $this->getTemplate( 'subs-form' );
	}

	/**
	 * [thisUnSubscribe] short code
	 *
	 * @return bool|string
	 */
	public function thisUnSubscribeShortCode() {

		$subscriberHash = ! empty( $_GET[ Subscriber::HASH ] ) ? $_GET[ Subscriber::HASH ] : null;

		// If user already subscribed
		if ( $subscriberHash !== null ) {

			// Sanitize and encode
			$subscriberHash = sanitize_text_field( urldecode( $subscriberHash ) );

			// Get subscribe
			$subscriber = new Subscriber( $subscriberHash );

			if ( $subscriber->id !== null ) {

				// Unsubscribe
				if ( $subscriber->api->unSubscribe( $subscriber ) === true ) {

					// Remove cookie after update page
					$_SESSION[ Subscriber::COOKIE ] = 'remove';

					//Return template
					return $this->getTemplate( 'unsubscribed', $subscriber );
				}
			}
		}

		// View subscribe template
		return $this->getTemplate( 'unsubscribed-fail' );
	}

	/**
	 * Send mail wen we insert post
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 */
	public function sendInsertPost( $post_id, \WP_Post $post ) {

		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$replace = array(
			'[blogname]'   => get_option( 'blogname' ),
			'[post-url]'   => get_the_permalink( $post->ID ),
			'[post-title]' => get_the_title( $post->ID ),
			'[abort-link]' => '',
		);

		$unLink = get_bloginfo( 'wpurl' ) . '/' . self::UNSUBSCRIBER_PAGE_SLUG . '?' . Subscriber::HASH . '=';

		$subscribers = $this->subscribersApi->getSubscribers();
		if ( $subscribers !== null ) {
			foreach ( $subscribers as $subscriber ) {
				if ( $subscriber->signed > 0 ) {
					$replace['[abort-link]'] = $unLink . urlencode( $subscriber->hash );

					$subject = strtr( SettingsPage::getOption( 'post_subject' ), $replace );
					$message = strtr( SettingsPage::getOption( 'post_message' ), $replace );
					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					wp_mail( $subscriber->mail, $subject, nl2br( $message ), $headers );
				}
			}
		}
	}

	/**
	 * Return html template
	 *
	 * @param $template - string file name without extension
	 * @param array|object $vars
	 *
	 * @return null|string
	 */
	public function getTemplate( $template, $vars = array() ) {
		$pathToTemplate = PL_TEMPLATES . DS . $template . '.' . self::TEMPLATE_EXT;

		if ( is_file( $pathToTemplate ) ) {

			// extract vars from object
			if ( is_object( $vars ) ) {
				$vars = get_object_vars( $vars );
			}

			if ( $vars ) {
				extract( $vars );
			}

			ob_start();

			include $pathToTemplate;

			$template = ob_get_clean();

			return $template;
		}

		return null;
	}

	/**
	 * Get class name without namespace
	 *
	 * @param $object
	 *
	 * @return mixed|null
	 */
	public function getClassNameFromObject( $object ) {
		if ( is_object( $object ) ) {

			$classNameWithNamespace = get_class( $object );
			$classNameWithNamespace = explode( "\\", $classNameWithNamespace );
			$className              = array_pop( $classNameWithNamespace );

			return $className;
		}

		return null;
	}

	/**
	 * Add un subscriber page to wordpress if not exist
	 */
	public function addUnSubscriberPage() {
		$unSubscriberPage = get_page_by_path( self::UNSUBSCRIBER_PAGE_SLUG );
		if ( $unSubscriberPage === null ) {
			// add page
			$page = array(
				'post_author'  => 1,
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => __( 'Unsubscribe' ),
				'post_name'    => self::UNSUBSCRIBER_PAGE_SLUG,
				'post_content' => '[thisUnSubscribe]',
			);
			wp_insert_post( $page );
		}
	}
}