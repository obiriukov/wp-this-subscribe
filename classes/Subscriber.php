<?php
/**
 * Created by PhpStorm.
 * User: CaguCT
 * Date: 11/30/17
 * Time: 20:35
 */

namespace ThisSubscribe;

/**
 * Model for subscriber
 *
 * Class Subscriber
 * @package ThisSubscribe
 */
class Subscriber extends AbstractModel {

	const TABLE = 'ts_mails';
	const COOKIE = 'this_subscriber_id';
	const HASH = 'hash';

	// Api
	public $api;

	// fields
	public $mail;
	public $hash;
	public $signed;

	/**
	 * Subscriber constructor.
	 *
	 * @param null $id_or_mail_or_hash
	 */
	public function __construct( $id_or_mail_or_hash = null ) {

		$this->api = new SubscriberApi();

		if ( $id_or_mail_or_hash !== null ) {

			$subscriber = $this->api->getSubscriber( array(
				'id'   => $id_or_mail_or_hash,
				'mail' => $id_or_mail_or_hash,
				'hash' => $id_or_mail_or_hash,
			), 'OR' );

			if ( $subscriber !== null ) {
				$this->setter( $subscriber );
			}
		}
	}

	/**
	 * Remove subscriber
	 *
	 * @return bool
	 */
	public function remove() {
		global $wpdb;

		if ( $this->id ) {
			$wpdb->delete( $wpdb->prefix . self::TABLE, array( 'id' => $this->id ) );

			return true;
		}

		return false;
	}

	/**
	 * Insert or update if not exist
	 *
	 * @return bool
	 */
	public function save() {
		// Anyway we need mail, for update or add
		if ( $this->mail === null ) {
			return false;
		}

		if ( $this->id === null ) {
			// Get subscriber
			$subscriber = new self( $this->mail );
			if ( $subscriber->id === null ) {
				// Add subscriber
				return $this->add();
			} else {
				$update_flag = true;
			}
		} else {
			$update_flag = true;
		}

		if ( $update_flag === true ) {
			return $this->update();
		}

		return false;
	}

	/**
	 * Add in subscriber table
	 *
	 * @return bool
	 */
	private function add() {
		global $wpdb;

		// Current time
		$this->time = current_time( 'mysql' );

		// Hash
		if ( $this->hash === null ) {
			$this->hash = wp_hash_password( $this->mail . SECURE_AUTH_SALT );
		}

		// Signed
		if ( $this->signed === null ) {
			$this->signed = 0;
		}

		// Add new subscriber
		$insert = $wpdb->insert( $wpdb->prefix . self::TABLE, array(
			'time'   => $this->time,
			'mail'   => sanitize_text_field( $this->mail ),
			'hash'   => sanitize_text_field( $this->hash ),
			'signed' => sanitize_text_field( $this->signed )
		) );

		if ( $insert !== false ) {
			$this->id = $wpdb->insert_id;
		}

		if ( $this->id !== null ) {
			return true;
		}

		return false;
	}

	/**
	 * Update the subscriber table
	 *
	 * @return bool
	 */
	private function update() {
		global $wpdb;

		if ( $this->id !== null ) {
			$update = $wpdb->update( $wpdb->prefix . self::TABLE, array(
				'time'   => $this->time,
				'mail'   => sanitize_text_field( $this->mail ),
				'hash'   => sanitize_text_field( $this->hash ),
				'signed' => sanitize_text_field( $this->signed )
			), array( 'id' => $this->id ) );

			if ( $update !== false ) {
				return true;
			}

		}

		return false;
	}

	public function generateHash() {
		return wp_hash_password( wp_rand( 0, 999999999 ) . SECURE_AUTH_SALT );
	}

	/**
	 * @param object|array $object_or_array
	 */
	private function setter( $object_or_array ) {
		if ( is_object( $object_or_array ) ) {
			$this->id     = $object_or_array->id;
			$this->time   = $object_or_array->time;
			$this->mail   = $object_or_array->mail;
			$this->hash   = $object_or_array->hash;
			$this->signed = (int) $object_or_array->signed;
		}
		if ( is_array( $object_or_array ) ) {
			$this->id     = $object_or_array['id'];
			$this->time   = $object_or_array['time'];
			$this->mail   = $object_or_array['mail'];
			$this->hash   = $object_or_array['hash'];
			$this->signed = (int) $object_or_array['signed'];
		}
	}
}