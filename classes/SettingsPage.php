<?php
/**
 * Created by PhpStorm.
 * User: CaguCT
 * Date: 12/2/17
 * Time: 15:48
 */

namespace ThisSubscribe;

/**
 * Class SettingsPage
 * @package ThisSubscribe
 */
class SettingsPage extends AbstractAdminPage {

	const OPTION_NAME = 'this-subscribe-option';
	const OPTION_GROUP = 'this-subscribe-group';
	const MENU_SLUG = 'wpts-setting';

	public $options;
	private $settingsFields;

	/**
	 * SettingsPage constructor.
	 */
	public function __construct() {

		$this->options = get_option( self::OPTION_NAME );

		$this->settingsFields = array(
			array(
				'id'       => 'message_section',
				'title'    => __( 'Messages' ),
				'callback' => 'printSectionInfo',
				'type'     => 'section',
			),
			array(
				'id'       => 'subscribed_message',
				'title'    => __( 'Subscribed message' ),
				'callback' => 'textareaCallback',
				'type'     => 'field',
				'section'  => 'message_section',
			),
			array(
				'id'       => 'subscribe_abort_message',
				'title'    => __( 'Subscribe abort message' ),
				'callback' => 'textareaCallback',
				'type'     => 'field',
				'section'  => 'message_section',
			),
			array(
				'id'       => 'unsubscribe_message',
				'title'    => __( 'Unsubscribe message' ),
				'callback' => 'textareaCallback',
				'type'     => 'field',
				'section'  => 'message_section',
			),
			array(
				'id'       => 'unsubscribe_fail_message',
				'title'    => __( 'Unsubscribe fail message' ),
				'callback' => 'textareaCallback',
				'type'     => 'field',
				'section'  => 'message_section',
			),
			array(
				'id'       => 'mail_section',
				'title'    => __( 'Mail body' ),
				'callback' => 'printSectionInfo',
				'type'     => 'section',
			),
			array(
				'id'       => 'post_subject',
				'title'    => __( 'Mail subject to subscriber' ),
				'callback' => 'textCallback',
				'type'     => 'field',
				'section'  => 'mail_section',
			),
			array(
				'id'       => 'post_message',
				'title'    => __( 'Message to subscriber' ),
				'callback' => 'editorCallback',
				'type'     => 'field',
				'section'  => 'mail_section',
			),
			array(
				'id'       => 'abort_section',
				'title'    => __( 'Abort mail body' ),
				'callback' => 'printSectionInfo',
				'type'     => 'section',
			),
			array(
				'id'       => 'abort_subject',
				'title'    => __( 'Abort mail subject to subscriber' ),
				'callback' => 'textCallback',
				'type'     => 'field',
				'section'  => 'abort_section',
			),
			array(
				'id'       => 'abort_message',
				'title'    => __( 'Abort message to subscriber' ),
				'callback' => 'editorCallback',
				'type'     => 'field',
				'section'  => 'abort_section',
			),
		);
	}

	/**
	 * Add default options
	 */
	public function install() {
		$this->options = get_option( self::OPTION_NAME );
		if ( $this->options === false ) {
			$this->options = array(
				'subscribed_message'       => __( 'You already subscribed with email: [mail]
<br>
You can <a href="#abort" class="this-subscribe-abort">abort</a> subscribe or <a href="#change" class="this-subscribe-change">add other</a> email.' ),
				'subscribe_abort_message'  => __( 'We sent you a mail with instruction, how you can abort subscribe.' ),
				'unsubscribe_message'      => __( 'You successfully unsubscribed from the subscription.' ),
				'unsubscribe_fail_message' => __( 'Wrong request.' ),
				'post_subject'             => __( 'Updates on site [blogname]' ),
				'post_message'             => __( 'A post has been updated on site [blogname]:
[post-title]: <a href="[post-url]" target="_blank">[post-url]</a>

For abort subscribe click here - <a href="[abort-link]" target="_blank">[abort-link]</a>' ),
				'abort_subject'            => __( 'Abort your subscriber on [blogname]' ),
				'abort_message'            => __( 'If you want abort subscriber on site [blogname]:

Just click on this link - <a href="[abort-link]" target="_blank">[abort-link]</a>' ),
			);

			add_option( self::OPTION_NAME, $this->options );
		}
	}

	/**
	 * Register settings
	 */
	public function pageInit() {
		register_setting(
			self::OPTION_GROUP, // Option group
			self::OPTION_NAME, // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		$this->setSettingsFields();
	}

	/**
	 * Add settings on page
	 */
	public function setSettingsFields() {
		if ( $this->settingsFields !== null ) {
			foreach ( $this->settingsFields as $setting ) {
				if ( ! empty( $setting['type'] ) ) {
					if ( $setting['type'] === 'section' ) {
						add_settings_section(
							$setting['id'], // ID
							$setting['title'], // Title
							array( $this, $setting['callback'] ),
							self::MENU_SLUG
						);
					} elseif ( $setting['type'] === 'field' ) {
						add_settings_field(
							$setting['id'], // ID
							$setting['title'], // Title
							array( $this, $setting['callback'] ), // Callback
							self::MENU_SLUG,
							$setting['section'], // Section
							$setting['id']
						);
					}
				}
			}
		}
	}

	/**
	 * Now we do nothing here
	 *
	 * @param $section
	 */
	public function printSectionInfo( $section ) {
	}

	/**
	 * Print <textarea>
	 *
	 * @param $id
	 */
	public function textareaCallback( $id ) {
		printf(
			'<textarea id="%s" name="' . self::OPTION_NAME . '[%s]" rows="5" class="large-text code">%s</textarea>',
			$id, $id, isset( $this->options[ $id ] ) ? esc_attr( $this->options[ $id ] ) : ''
		);
	}

	/**
	 * Print wp_editor
     *
	 * @param $id
	 */
	public function editorCallback( $id ) {
		$content   = isset( $this->options[ $id ] ) ? $this->options[ $id ] : null;
		$editor_id = 'field_' . $id;
		$settings  = array(
			'media_buttons' => false,
			'textarea_name' => self::OPTION_NAME . '[' . $id . ']',
		);
		wp_editor( $content, $editor_id, $settings );
	}

	/**
	 * Print <input>
	 *
	 * @param $id
	 */
	public function textCallback( $id ) {
		printf(
			'<input type="text" id="%s" name="' . self::OPTION_NAME . '[%s]" value="%s" class="regular-text">',
			$id, $id, isset( $this->options[ $id ] ) ? $this->options[ $id ] : ''
		);
	}

	/**
	 * So here we create new page
	 */
	public static function createAdminPage() {

		?>
        <div class="wrap">
            <h1>This subscribe Settings</h1>
            <form method="post" action="options.php">
				<?php
				// This prints out all hidden setting fields
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::MENU_SLUG );
				submit_button();
				?>
            </form>
        </div>
		<?php
	}

	/**
     * Get setting option from option name
     *
	 * @param $option
	 *
	 * @return null
	 */
	public static function getOption( $option ) {
		$options = get_option( self::OPTION_NAME );

		if ( ! empty( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return null;
	}


	/**
	 * Sanitize each setting field as needed
	 *
	 * @param $input
	 *
	 * @return array
	 */
	public function sanitize( $input ) {
		$new_input = array();

		if ( $this->settingsFields !== null ) {
			foreach ( $this->settingsFields as $settings ) {
				if ( isset( $input[ $settings['id'] ] ) ) {
					$new_input[ $settings['id'] ] = $input[ $settings['id'] ];
				}
			}
		}

		return $new_input;
	}
}