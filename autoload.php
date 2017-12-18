<?php
/**
 * Created by PhpStorm.
 * User: CaguCT
 * Date: 11/29/17
 * Time: 12:24
 */

spl_autoload_register( 'thisSubscribe_autoloader' );
function thisSubscribe_autoloader( $className ) {

	if ( false !== strpos( $className, 'ThisSubscribe' ) ) {
		$classesDir = PL_ROOT . DS . 'classes' . DS;

		$className = explode( '\\', $className );

		$fileOfClass = $classesDir . $className[1] . '.php';

		if ( is_file( $fileOfClass ) ) {
			require_once $fileOfClass;
		} else {
			echo 'Class ' . $className . ' not found';
			die();
		}
	}
}