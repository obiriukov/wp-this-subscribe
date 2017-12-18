<?php
/**
 * Created by PhpStorm.
 * User: CaguCT
 * Date: 12/1/17
 * Time: 09:17
 */

namespace ThisSubscribe;

/**
 * Api for subscribers
 *
 * Class SubscriberApi
 * @package ThisSubscribe
 */
class SubscriberApi {

	const DB_VERSION_OPTION_NAME = 'ts_db_version';
	const DB_VERSION = 1.2;

	/**
	 * Creating Tables with Plugins (https://codex.wordpress.org/Creating_Tables_with_Plugins)
	 */
	public function install() {
		global $wpdb;

		$table_name = $wpdb->prefix . Subscriber::TABLE;

		if ( $wpdb->get_var( 'show tables like "' . $table_name . '"' ) != $table_name ) {
			$sql = 'CREATE TABLE ' . $table_name . ' (
					  id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
					  time DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
					  mail TINYTEXT NOT NULL,
					  hash VARCHAR(255) NOT NULL,
					  PRIMARY KEY (id)
					);';

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			dbDelta( $sql );

			add_option( self::DB_VERSION_OPTION_NAME, 1.0 );
		}
	}

	/**
	 * Update Tables with Plugins (https://codex.wordpress.org/Creating_Tables_with_Plugins)
	 */
	public function update() {
		global $wpdb;

		$sql           = null;
		$installed_ver = get_option( self::DB_VERSION_OPTION_NAME );
		$table_name    = $wpdb->prefix . Subscriber::TABLE;

		if ( (float) $installed_ver < self::DB_VERSION ) {

			if ( self::DB_VERSION < 1.1 ) {
				$sql = 'ALTER TABLE ' . $table_name . ' ADD `hash` VARCHAR(255) NOT NULL AFTER `mail`;';
			} elseif ( self::DB_VERSION < 1.2 ) {
				$sql = 'ALTER TABLE ' . $table_name . ' ADD `signed` INT(1) NOT NULL AFTER `hash`;';
			}

			if ( $sql !== null ) {

				$wpdb->query( $sql );

				update_option( self::DB_VERSION_OPTION_NAME, self::DB_VERSION );

			}
		}
	}

	/**
	 * Get subscriber
	 *
	 * @param array $where
	 * @param string $glue Optional. Any of AND | OR
	 * @param string $output Optional. Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 *
	 * @return array|null|object - null if not exist
	 */
	public function getSubscriber( $where = null, $glue = 'AND', $output = OBJECT ) {
		global $wpdb;

		$table_name = $wpdb->prefix . Subscriber::TABLE;

		// If we have conditional for sql request
		$where = $this->whereConditional( $where, $glue );

		$subscriber = $wpdb->get_row( 'SELECT * FROM ' . $table_name . $where, $output );

		return $subscriber;
	}


	/**
	 * Get subscribers
	 *
	 * @param array|string $where
	 * @param string $glue Optional. Any of AND | OR
	 *
	 * @return array|null - array with Subscriber object
	 */
	public function getSubscribers( $where = null, $glue = 'AND' ) {
		global $wpdb;

		$table_name = $wpdb->prefix . Subscriber::TABLE;

		// If we have conditional for sql request
		$where = $this->whereConditional( $where, $glue );

		$get_results = $wpdb->get_results( 'SELECT * FROM ' . $table_name . $where );

		if ( $get_results ) {

			$subscribers = array();

			foreach ( $get_results as $get_result ) {
				$subscriber    = new Subscriber( $get_result->id );
				$subscribers[] = $subscriber;
			}

			return $subscribers;

		} else {
			return null;
		}
	}

	/**
	 * Get subscribers mails
	 *
	 * @param bool $unsigned
	 *
	 * @return array|null
	 */
	public function getSubscribersMails( $unsigned = false ) {

		$subscribers = $this->getSubscribers();

		if ( $subscribers !== null ) {
			$result = array();

			foreach ( $subscribers as $subscriber ) {
				if ( $unsigned ) {
					$result[ $subscriber->id ] = $subscriber->mail;
				} else {
					if ( $subscriber->signed > 0 ) {
						$result[ $subscriber->id ] = $subscriber->mail;
					}
				}
			}

			return $result;
		}

		return null;
	}


	/**
	 * Subscribe user
	 *
	 * @param int|object Subscriber $id_or_object
	 *
	 * @return bool
	 */
	public function subscribe( $id_od_object ) {

		$subscriber = $this->getSubscribeObject( $id_od_object );

		if ( $subscriber->id !== null ) {

			// new hash
			$subscriber->hash   = $subscriber->generateHash();
			$subscriber->signed = 1;
			if ( $subscriber->save() === true ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Un subscribe user
	 *
	 * @param int|object Subscriber $id_or_object
	 *
	 * @return bool
	 */
	public function unSubscribe( $id_od_object ) {

		$subscriber = $this->getSubscribeObject( $id_od_object );

		if ( $subscriber->id !== null ) {

			$subscriber->signed = 0;
			if ( $subscriber->save() === true ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int|object Subscriber $id_or_object
	 *
	 * @return Subscriber
	 */
	public function getSubscribeObject( $id_od_object ) {
		if ( is_object( $id_od_object ) ) {
			// Get class name without namespace
			$pluginApi = new PluginApi();
			$className = $pluginApi->getClassNameFromObject( $id_od_object );

			if ( $id_od_object->id !== null && $className === 'Subscriber' ) {
				$subscriber = $id_od_object;

				return $subscriber;
			}
		} else {
			$id_od_object = (int) $id_od_object;
			if ( $id_od_object > 0 ) {
				$subscriber = new Subscriber( $id_od_object );

				return $subscriber;
			}
		}

		return new Subscriber();
	}

	/**
	 * Helper function
	 *
	 * @param null $where
	 * @param string $glue Optional. Any of AND | OR
	 *
	 * @return null|string
	 */
	private function whereConditional( $where = null, $glue = 'AND' ) {
		if ( $where !== null ) {
			if ( is_array( $where ) ) {
				$where_temp = array();
				foreach ( $where as $key => $value ) {
					$where_temp[] = '`' . $key . '` = "' . sanitize_text_field( $value ) . '"';
				}
				$where = ' WHERE ' . implode( ' ' . $glue . ' ', $where_temp );
				unset( $where_temp );
			} else {
				$where = ' WHERE ' . $where;
			}
		}

		return $where;
	}

}