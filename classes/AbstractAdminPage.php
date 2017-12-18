<?php
/**
 * Created by PhpStorm.
 * User: CaguCT
 * Date: 12/2/17
 * Time: 16:56
 */

namespace ThisSubscribe;


/**
 * Class AbstractAdminPage
 * @package ThisSubscribe
 */
class AbstractAdminPage {
	const MENU_SLUG = 'wpts-page';


	public function __construct() {
	}

	public function pageInit() {
	}

	/**
	 * So here we create new page
	 */
	public static function createAdminPage() {
		?>
        <div class="wrap">
            <h1>Page title</h1>
            <p>Hello world!</p>
        </div>
		<?php
	}
}