<?php
defined( 'ABSPATH' ) or die( 'Cheatin&#8217; uh?' );

$args = array(
	'role__in' => array( 'locked_rmtcmp' ),
	'number'   => - 1,
);

if ( isset( $_GET['search'] ) && ! empty( $_GET['search'] ) ) {
	$args['search'] = sanitize_text_field( $_GET['search'] );
}

$locked_rmtcmp = get_users( $args );

?>
<div class="wrap rmtcmp">

	<h2><?php esc_html_e( 'Suspended account(s)', 'remind-me-to-change-my-password' ); ?></h2>

	<div class="tablenav top">

		<div class="alignleft actions bulkactions">
			<label for="bulk-action-selector-top" class="screen-reader-text"><?php esc_html_e( 'Select bulk action', 'remind-me-to-change-my-password' ); ?></label><select name="action" id="bulk-action-selector-top">
				<option value="-1"><?php esc_html_e( 'Bulk actions', 'remind-me-to-change-my-password' ); ?></option>
				<option value="delete"><?php esc_html_e( 'Delete', 'remind-me-to-change-my-password' ); ?></option>
			</select>
			<input type="submit" id="erase-users" class="button action" value="<?php esc_html_e( 'Apply', 'remind-me-to-change-my-password' ); ?>">
		</div>

		<div class="search-box suspended-users">
			<label class="screen-reader-text" for="user-search-input">
				<?php esc_html_e( 'Search Users:', 'remind-me-to-change-my-password' ); ?>
			</label>
			<form action="<?php echo admin_url( 'users.php?page=blocked-users' ); ?>">
				<input type="hidden" name="page" value="blocked-users">
				<input type="search" id="user-search-input" name="search" value="">
				<input type="submit" id="search-submit" class="button" value="<?php esc_html_e( 'Search Users', 'remind-me-to-change-my-password' ); ?>">
			</form>
		</div>
	</div>


	<table class="wp-list-table widefat fixed striped">
		<thead>
		<tr>
			<td id="cb" class="manage-column column-cb check-column">
				<label class="screen-reader-text" for="cb-select-all-1"><?php esc_html_e( 'Select All', 'remind-me-to-change-my-password' ); ?></label>
				<input id="cb-select-all-1" type="checkbox">
			</td>
			<th scope="col" id="title" class="manage-column column-title column-primary sortable desc">
				<span><?php esc_html_e( 'Login', 'remind-me-to-change-my-password' ); ?></span>
			</th>

			<th scope="col" id="date" class="manage-column column-date">
				<span><?php esc_html_e( 'Blocking date', 'remind-me-to-change-my-password' ); ?></span>
			</th>

			<th scope="col" id="unlock-user" class="manage-column column-unlock-user column-primary">
				<span><?php esc_html_e( 'Reactivate account', 'remind-me-to-change-my-password' ); ?></span>
			</th>
			<th scope="col" id="remove-user" class="manage-column column-remove-user column-primary">
				<span><?php esc_html_e( 'Delete account', 'remind-me-to-change-my-password' ); ?></span>
			</th>
		</tr>
		</thead>

		<tbody id="the-list" class="rmtcmp-list">
		<?php
		if ( count( $locked_rmtcmp ) > 0 ) :
			foreach ( $locked_rmtcmp as $user_item ) :
				$lockdate = get_user_meta( $user_item->ID, 'rmtcmp_lock_date', true );

				?>
				<tr id="lock_user-<?php echo esc_attr( $user_item->ID ); ?>">
					<th scope="row" class="check-column">
						<label class="screen-reader-text" for="cb-select-<?php echo esc_attr( $user_item->ID ); ?>"><?php esc_html_e( 'Select User', 'remind-me-to-change-my-password' ); ?> #<?php echo $user_item->ID; ?></label>
						<input id="cb-select-<?php echo esc_attr( $user_item->ID ); ?>" type="checkbox" name="lock_user[]" value="1">
					</th>
					<td class="title column-title has-row-actions column-primary page-title" data-colname="<?php esc_html_e( 'Login', 'remind-me-to-change-my-password' ); ?>">
						<strong>
							<?php echo esc_html( $user_item->display_name ); ?> - <?php echo esc_html( $user_item->user_email ); ?>
						</strong>

					</td>

					<td class="date column-date" data-colname="<?php esc_html_e( 'Blocking date', 'remind-me-to-change-my-password' ); ?>">
						<p>
							<?php
							$format_date = esc_html__( 'Y-m-d H:i', 'remind-me-to-change-my-password' );
							echo date_i18n( $format_date, $lockdate );
							?>
						</p>
					</td>

					<td class="manage-column column-unlock-user column-primary sortable desc" data-colname="<?php esc_html_e( 'Reactivate account', 'remind-me-to-change-my-password' ); ?>">
						<a href="<?php echo wp_nonce_url( "users.php?page=blocked-users&action=unlock_user&amp;user=$user_item->ID", 'unlock_user_' . get_current_user_id() ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 30" x="0px" y="0px"><title>_</title>
								<g>
									<path d="M17.99994,13l-.36856.73709a2,2,0,0,1-.89435.89436L16,15l.7369.36842a2,2,0,0,1,.89451.89449L17.99994,17l.36852-.73707a2,2,0,0,1,.89454-.89451L19.99994,15l-.73707-.36855a2,2,0,0,1-.89438-.89438Z"/>
									<path d="M17.99994,8l.36852-.73707a2,2,0,0,1,.89454-.89451L19.99994,6l-.73707-.36855a2,2,0,0,1-.89438-.89438L17.99994,4l-.36856.73709a2,2,0,0,1-.89435.89436L16,6l.7369.36842a2,2,0,0,1,.89451.89449Z"/>
									<path d="M7,9l.70179-1.40367a2,2,0,0,1,.89447-.89448L10,6,8.59626,5.29815a2,2,0,0,1-.89447-.89448L7,3,6.29808,4.40376a2,2,0,0,1-.89439.89437L4,6l1.40369.70187a2,2,0,0,1,.89439.89437Z"/>
									<path d="M15.12128,8.87866a2,2,0,0,0-2.82837,0L4.636,16.53552A2,2,0,0,0,7.46442,19.364l7.65692-7.65686a2,2,0,0,0,0-2.82837Zm-8.364,9.7782a1,1,0,1,1-1.41418-1.41418L11.58582,11,13,12.41418ZM14.41425,11l-.70716.70709-1.41418-1.41418L13,9.58575A1,1,0,0,1,14.41425,11Z"/>
								</g>
							</svg>
						</a>
					</td>

					<td class="manage-column column-remove-user column-primary sortable desc" data-colname="<?php esc_html_e( 'Delete account', 'remind-me-to-change-my-password' ); ?>">
						<a href="<?php echo wp_nonce_url( "users.php?action=delete&amp;user=$user_item->ID", 'bulk-users' ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" data-name="Layer 1" viewBox="0 0 100 125" x="0px" y="0px"><title>06</title>
								<g data-name="Group">
									<path data-name="Compound Path" d="M81.5,24.6H62.7V20.8a7,7,0,0,0-7-7H44.3a7,7,0,0,0-7,7v3.8H18.5a3,3,0,0,0,0,6h6.1V75.2a11,11,0,0,0,11,11H64.4a11,11,0,0,0,11-11V30.6h6.1a3,3,0,0,0,0-6ZM43.3,20.8a1,1,0,0,1,1-1H55.7a1,1,0,0,1,1,1v3.8H43.3Zm26,54.5a5,5,0,0,1-5,5H35.6a5,5,0,0,1-5-5V30.6H69.4Z"/>
									<path data-name="Path" d="M57.5,73a3,3,0,0,0,3-3V40.1a3,3,0,1,0-6,0V70A3,3,0,0,0,57.5,73Z"/>
									<path data-name="Path" d="M42.5,73a3,3,0,0,0,3-3V40.1a3,3,0,1,0-6,0V70A3,3,0,0,0,42.5,73Z"/>
								</g>
							</svg>
						</a>
					</td>
				</tr>
			<?php
			endforeach;
		else :
			?>
			<tr class="no-items">
				<td class="colspanchange" colspan="5">
					<?php esc_html_e( 'No account suspended', 'remind-me-to-change-my-password' ); ?></td>
			</tr>
		<?php
		endif;
		?>
		</tbody>
		<tfoot>
		<tr>
			<td class="manage-column column-cb check-column">
				<label class="screen-reader-text" for="cb-select-all-2"><?php esc_html_e( 'Select All', 'remind-me-to-change-my-password' ); ?></label>
				<input id="cb-select-all-2" type="checkbox">
			</td>
			<th scope="col" class="manage-column column-title column-primary">
				<span><?php esc_html_e( 'Login', 'remind-me-to-change-my-password' ); ?></span>
			</th>

			<th scope="col" class="manage-column column-date sortable asc">
				<span><?php esc_html_e( 'Blocking date', 'remind-me-to-change-my-password' ); ?></span>
			</th>


			<th scope="col" class="manage-column column-unlock-user column-primary">
				<span><?php esc_html_e( 'Reactivate account', 'remind-me-to-change-my-password' ); ?></span>
			</th>

			<th scope="col" class="manage-column column-remove-user column-primary">
				<span><?php esc_html_e( 'Delete account', 'remind-me-to-change-my-password' ); ?></span>
			</th>

		</tr>
		</tfoot>
	</table>

</div>