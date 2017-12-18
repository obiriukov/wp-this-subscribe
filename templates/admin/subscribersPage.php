<div class="wrap">
    <h1 class="wp-heading-inline">Subscribers</h1>
    <a href="/wp-admin/admin.php?page=<?php echo $_GET['page']; ?>-add" class="add-new-h2">Add New</a>

    <form method="get" action="">
        <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>">
		<?php wp_nonce_field(); ?>
        <p class="search-box">
            <label class="screen-reader-text" for="<?php echo $_GET['page']; ?>-contact-search-input">Search
                Subscribers:</label>
            <input type="search" id="<?php echo $_GET['page']; ?>-contact-search-input" name="s" value="">
            <input type="submit" id="search-submit" class="button" value="Search Subscribers"></p>
        <div class="tablenav top">

            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                <select name="action" id="bulk-action-selector-top">
                    <option value="-1">Bulk Actions</option>
                    <option value="subscribe">Subscribe</option>
                    <option value="unsubscribe">Unsubscribe</option>
                    <option value="delete">Delete</option>
                </select>
                <input type="submit" id="doaction" class="button action" value="Apply">
            </div>
            <br class="clear">
        </div>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="cb-select-all-1">Select All</label>
                    <input id="cb-select-all-1" type="checkbox">
                </td>
                <th scope="col" id="title" class="manage-column column-title column-primary sortable asc">
                    <a href="/wp-admin/admin.php?page=<?php echo $_GET['page']; ?>&amp;orderby=title&amp;order=desc">
                        <span>Subscriber</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th scope="col" id="shortcode" class="manage-column column-signed">
                    Signed
                </th>
                <th scope="col" id="date" class="manage-column column-date sortable desc">
                    <a href="/wp-admin/admin.php?page=<?php echo $_GET['page']; ?>&amp;orderby=date&amp;order=asc">
                        <span>Date</span>
                        <span class="sorting-indicator"></span></a>
                </th>
            </tr>
            </thead>
            <tbody id="the-list" data-wp-lists="list:post">

			<?php

			$subscriberApi = new \ThisSubscribe\SubscriberApi();
			foreach ( $subscriberApi->getSubscribers() as $subscriber ) {
				?>

                <tr>
                    <th scope="row" class="check-column">
                        <input type="checkbox" name="subscribers[]" value="<?php echo $subscriber['id']; ?>">
                    </th>
                    <td class="title column-title has-row-actions column-primary" data-colname="Title">
                        <strong>
                            <a class="row-title"
                               href="/wp-admin/admin.php?page=<?php echo $_GET['page']; ?>&amp;post=805&amp;action=edit"
                               title="Edit “<?php echo $subscriber['mail']; ?>”"><?php echo $subscriber['mail']; ?></a>
                        </strong>
                        <div class="row-actions">
							<?php if ( $subscriber['signed'] == 1 ) : ?>
                                <span class="edit">
                            <a href="/wp-admin/admin.php?page=<?php echo $_GET['page']; ?>&amp;post=<?php echo $subscriber['id']; ?>&amp;action=unsubscribe">Unsubscribe</a> |
                        </span>
							<?php else : ?>
                                <span class="edit">
                            <a href="/wp-admin/admin.php?page=<?php echo $_GET['page']; ?>&amp;post=<?php echo $subscriber['id']; ?>&amp;action=subscribe">Subscribe</a> |
                        </span>
							<?php endif; ?>
                            <span class="trash">
                            <a href="/wp-admin/admin.php?page=<?php echo $_GET['page']; ?>&amp;post=<?php echo $subscriber['id']; ?>&amp;action=delete"
                               class="submitdelete"
                               aria-label="Delete “<?php echo $subscriber['mail']; ?>”">Delete</a>
                        </span>
                        </div>
                        <button type="button" class="toggle-row">
                            <span class="screen-reader-text">Show more details</span>
                        </button>
                        <button type="button" class="toggle-row">
                            <span class="screen-reader-text">Show more details</span>
                        </button>
                    </td>
                    <td class="shortcode column-signed" data-colname="Signed">
						<?php echo $subscriber['signed']; ?>
                    </td>
                    <td class="date column-date" data-colname="Date">
                        <abbr title="<?php echo date( "Y/m/d h:i:s A", strtotime( $subscriber['time'] ) ); ?>">
							<?php echo date( "Y/m/d", strtotime( $subscriber['time'] ) ); ?>
                        </abbr>
                    </td>
                </tr>

				<?php
			}

			?>
            </tbody>
            <tfoot>
            <tr>
                <td class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="cb-select-all-2">Select All</label>
                    <input id="cb-select-all-2" type="checkbox">
                </td>
                <th scope="col" class="manage-column column-title column-primary sortable asc">
                    <a href="/wp-admin/admin.php?page=<?php echo $_GET['page']; ?>&amp;orderby=title&amp;order=desc">
                        <span>Title</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
                <th scope="col" class="manage-column column-shortcode">
                    Signed
                </th>
                <th scope="col" class="manage-column column-date sortable desc">
                    <a href="/wp-admin/admin.php?page=<?php echo $_GET['page']; ?>&amp;orderby=date&amp;order=asc">
                        <span>Date</span>
                        <span class="sorting-indicator"></span>
                    </a>
                </th>
            </tr>
            </tfoot>

        </table>
        <div class="tablenav bottom">

            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-bottom" class="screen-reader-text">Select bulk action</label>
                <select name="action2" id="bulk-action-selector-bottom">
                    <option value="-1">Bulk Actions</option>
                    <option value="subscribe">Subscribe</option>
                    <option value="unsubscribe">Unsubscribe</option>
                    <option value="delete">Delete</option>
                </select>
                <input type="submit" id="doaction2" class="button action" value="Apply">
            </div>
            <br class="clear">
        </div>
    </form>
</div>