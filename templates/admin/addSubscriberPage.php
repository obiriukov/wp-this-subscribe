<div class="wrap">
    <h1 class="wp-heading-inline">Add subscriber</h1>

    <form method="get" action="">
        <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
		<?php wp_nonce_field(); ?>

        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
					<?php if ( ! empty( $_GET['error_msg'] ) ) : ?>
                        <div class="error">
                            <p>
								<?php if ( ! empty( $_GET['error_msg'] === '1' ) ) : ?>
									<?php echo __( 'This subscriber is already exist' ); ?>
								<?php endif; ?>
                            </p>
                        </div>
					<?php endif; ?>
                    <div id="titlediv">
                        <div id="titlewrap">
                            <input type="text" name="mail" size="30" id="title" spellcheck="true" autocomplete="off"
                                   placeholder="<?php echo __( 'Enter mail here' ); ?>"
								<?php if ( ! empty( $_GET['mail'] ) ) : ?>
                                    value="<?php echo $_GET['mail']; ?>"
								<?php endif; ?>>
                        </div>
                    </div>
                </div>
                <div id="postbox-container-1" class="postbox-container">
                    <div id="submitdiv" class="postbox">
                        <h3>Save</h3>
                        <div class="inside">
                            <div class="submitbox" id="submitpost">
                                <div id="major-publishing-actions">
                                    <div id="publishing-action">
                                        <input type="submit" class="button-primary" name="save" value="Save">
                                    </div>
                                    <div class="clear"></div>
                                </div><!-- #major-publishing-actions -->
                            </div><!-- #submitpost -->
                        </div>
                    </div><!-- #submitdiv -->

                </div>
            </div>
            <br class="clear">
        </div>
    </form>
</div>