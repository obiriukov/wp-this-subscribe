<?php
/**
 * Created by PhpStorm.
 * User: CaguCT
 * Date: 12/8/17
 * Time: 09:27
 */

namespace ThisSubscribe;

/**
 * Class AdminFrontEnd
 * @package ThisSubscribe
 */
class AdminFrontEnd {

	public $pluginApi;
	public $subscriberApi;

	/**
	 * AdminFrontEnd constructor.
	 */
	public function __construct() {
		$this->pluginApi     = new PluginApi();
		$this->subscriberApi = new SubscriberApi();
	}

	/**
	 * Save (processing) from subscribers admin page
	 */
	public function subscribersPageAction() {
		$subscriberIds = ! empty( $_GET['subscribers'] ) ? $_GET['subscribers'] : null;
		$subscriberId  = ! empty( $_GET['post'] ) ? $_GET['post'] : null;
		$action        = ! empty( $_GET['action'] ) && $_GET['action'] !== '-1' ? $_GET['action'] : null;
		$action2       = ! empty( $_GET['action2'] ) && $_GET['action2'] !== '-1' ? $_GET['action2'] : null;

		// Action 2
		if ( $action2 !== null && $action2 === null ) {
			$action = $action2;
		}

		// Make $subscriberIds array if is null
		if ( $subscriberIds === null && $subscriberId !== null ) {
			$subscriberIds = array( $subscriberId );
		}

		// Action things
		if ( $action !== null && $subscriberIds !== null ) {
			if ( $action === 'subscribe' ) {
				foreach ( $subscriberIds as $id ) {
					$this->subscriberApi->subscribe( $id );
				}
			} elseif ( $action === 'unsubscribe' ) {
				foreach ( $subscriberIds as $id ) {
					$this->subscriberApi->unSubscribe( $id );
				}
			} elseif ( $action === 'delete' ) {
				foreach ( $subscriberIds as $id ) {
					$subscriber = new Subscriber( $id );
					if ( $subscriber->id !== null ) {
						$subscriber->remove();
					}
				}
			}

			// Redirect
			if ( ! empty( $_GET['_wp_http_referer'] ) ) {
				wp_redirect( remove_query_arg( array(
					'_wp_http_referer',
					'_wpnonce',
					'action',
					'action2',
					'subscribers',
					's'
				), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
				exit;
			}
		}
	}

	/**
	 * View subscribers admin page
	 */
	public function subscribersPage() {
		echo $this->pluginApi->getTemplate( 'admin/subscribersPage' );
	}

	/**
	 * Save (processing) from subscriber add admin page
	 */
	public function addSubscriberPageAction() {
		$subscriberMail = ! empty( $_GET['mail'] ) ? $_GET['mail'] : null;

		if ( $subscriberMail !== null ) {
			$subscriber = new Subscriber( $subscriberMail );
			if ( $subscriber->id === null ) {
				$subscriber->mail   = $subscriberMail;
				$subscriber->signed = 1;
				$subscriber->save();

				// Redirect
				wp_redirect( admin_url( 'admin.php?page=wpts' ) );
				exit();
			} else {

				$_SERVER['REQUEST_URI'] .= '&error_msg=1';

				// Redirect
				if ( ! empty( $_GET['_wp_http_referer'] ) ) {
					wp_redirect( remove_query_arg( array(
						'_wp_http_referer',
						'_wpnonce',
						'save',
						'_wpnonce',
					), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
					exit;
				}
			}
		}
	}

	/**
	 * View subscriber add admin page
	 */
	public function addSubscriberPage() {
		echo $this->pluginApi->getTemplate( 'admin/addSubscriberPage' );
	}
}