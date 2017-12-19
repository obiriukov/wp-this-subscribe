<?php
/**
 * Created by PhpStorm.
 * User: CaguCT
 * Date: 11/29/17
 * Time: 12:27
 */

namespace ThisSubscribe;

/**
 * Plugin Controller
 *
 * Class Controller
 * @package ThisSubscribe
 */
class PluginController {

	static $inst = null;

	public $api;

	public function __construct() {
		$this->api = new PluginApi();
	}

	/**
	 * Get instance
	 *
	 * @return null|PluginController
	 */
	public static function getInstance() {

		if ( self::$inst == null ) {
			self::$inst = new self();
		}

		return self::$inst;
	}

	/**
	 * Register all wp actions
	 */
	public function registers() {
		// Register short code
		add_shortcode( 'thisSubscribe', array( $this, 'shortCode' ) );
		add_shortcode( 'thisUnSubscribe', array( $this, 'thisUnSubscribeShortCode' ) );

		// Register send mail when post insert
		add_action( 'wp_insert_post', array( $this, 'insertPost' ), 10, 3 );

		// Register plugin scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'addScripts' ) );

		// Register ajax event for subscribe
		add_action( 'wp_ajax_add_subscriber_mail', array( $this, 'addMail' ) );
		add_action( 'wp_ajax_nopriv_add_subscriber_mail', array( $this, 'addMail' ) );

		// Register ajax event for change mail
		add_action( 'wp_ajax_change_subscriber_mail', array( $this, 'changeMail' ) );
		add_action( 'wp_ajax_nopriv_change_subscriber_mail', array( $this, 'changeMail' ) );

		// Register ajax event for abort subscriber
		add_action( 'wp_ajax_abort_subscriber', array( $this, 'abortSubscriber' ) );
		add_action( 'wp_ajax_nopriv_abort_subscriber', array( $this, 'abortSubscriber' ) );

		// Add admin menu
		add_action( 'admin_menu', array( $this, 'pluginMenu' ) );

		// Add meta box on admin post
		add_action( 'add_meta_boxes', array( $this, 'addPostBox' ) );

//		add_filter('manage_posts_columns' , array( $this, 'addPostColumn'));
//		add_filter('manage_posts_custom_column' , array( $this, 'displayPostColumn'));

		// Add admin init
		add_action( 'admin_init', array( $this, 'pluginAdminInit' ) );

		// Add init
		add_action( 'init', array( $this, 'pluginInit' ) );

		// Add "ts_mails" table to WP when plugin activating
		register_activation_hook( FILE_OF_PL, array( $this, 'install' ) );

		// Update DB
		add_action( 'plugins_loaded', array( $this, 'update' ) );
	}

	/**
	 * Creating Tables with Plugins (https://codex.wordpress.org/Creating_Tables_with_Plugins)
	 */
	public static function install() {

		// Add page for unsubscribe
		self::getInstance()->api->addUnSubscriberPage();

		// Install function of API
		$subscribersApi = new SubscriberApi();
		$subscribersApi->install();

		// Install def options
		$settingsPage = new SettingsPage();
		$settingsPage->install();
	}

	/**
	 * Creating Tables with Plugins (https://codex.wordpress.org/Creating_Tables_with_Plugins)
	 */
	public static function update() {
		// For future update
		$subscribersApi = new SubscriberApi();
		$subscribersApi->update();
	}

	/**
	 * @param $attributes
	 *
	 * @return bool|string
	 */
	public static function shortCode( $attributes ) {

		return self::getInstance()->api->shortCode( $attributes );
	}

	/**
	 * Add plugin scripts
	 */
	public static function addScripts() {

		self::getInstance()->api->addScripts();
	}

	/**
	 * Add subscriber action
	 */
	public static function addMail() {

		self::getInstance()->api->addMail();
	}

	/**
	 * Change subscriber mail action (or drop cookie)
	 */
	public static function changeMail() {

		self::getInstance()->api->changeMail();
	}

	/**
	 * Abort subscriber mail action
	 */
	public static function abortSubscriber() {

		self::getInstance()->api->abortSubscriber();
	}

	/**
	 * Action wp_insert_post
	 *
	 * @param $post_id
	 * @param \WP_Post $post
	 */
	public static function insertPost( $post_id, \WP_Post $post ) {

		self::getInstance()->api->sendInsertPost( $post_id, $post );
	}

	/**
	 * Action admin_menu
	 */
	public static function pluginMenu() {

		self::getInstance()->api->pluginMenu();
	}

	/**
	 * Action admin_init
	 */
	public static function pluginAdminInit() {
		self::getInstance()->api->pluginAdminInit();
	}

	/**
	 * Action init
	 */
	public static function pluginInit() {
		self::getInstance()->api->pluginInit();
	}

	/**
	 * Admin page - list of subscribers
	 */
	public static function subscribersAdmin() {
		$adminFrontEnd = new AdminFrontEnd();
		$adminFrontEnd->subscribersPage();
	}

	/**
	 * Admin page add subscriber
	 */
	public static function addSubscriberAdmin() {
		$adminFrontEnd = new AdminFrontEnd();
		$adminFrontEnd->addSubscriberPage();
	}

	/**
	 * @param $attributes
	 *
	 * @return bool|string
	 */
	public static function thisUnSubscribeShortCode( $attributes ) {

		return self::getInstance()->api->thisUnSubscribeShortCode( $attributes );
	}

	public static function addPostBox() {
		self::getInstance()->api->addPostBox();
	}

	public static function postBoxCallback( $post, $meta) {
		self::getInstance()->api->postBoxCallback($post, $meta);
	}
}