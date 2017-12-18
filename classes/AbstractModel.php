<?php
/**
 * Created by PhpStorm.
 * User: CaguCT
 * Date: 12/1/17
 * Time: 09:49
 */

namespace ThisSubscribe;

/**
 * Class AbstractModel
 * @package ThisSubscribe
 */
abstract class AbstractModel implements \ArrayAccess {

	// fields
	public $id;
	public $time;

	/**
	 * Save model
	 */
	public function save() {
	}

	/**
	 * Remove model
	 */
	public function remove() {
	}

	/**
	 * @param object|array $object_or_array
	 */
	private function setter( $object_or_array ) {
		if ( is_object( $object_or_array ) ) {
			$this->id   = $object_or_array->id;
			$this->time = $object_or_array->time;
		}
		if ( is_array( $object_or_array ) ) {
			$this->id   = $object_or_array['id'];
			$this->time = $object_or_array['time'];
		}
	}

	/**
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	public function offsetExists( $offset ) {
		return isset( $this->$offset );
	}

	/**
	 * @param mixed $offset
	 *
	 * @return mixed
	 */
	public function offsetGet( $offset ) {
		return $this->$offset;
	}

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet( $offset, $value ) {
		$this->$offset = $value;
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset( $offset ) {
		unset( $this->$offset );
	}
}