<?php

namespace RMTCMP\User;


class Views extends User {
	public function __construct() {
		/**
		 * Suppression du rôle locked_rmtcmp dans le listing des utilisateurs (admin)
		 * Modification du nombre d'utilisateur
		 * Un utilisateur avec le rôle locked_rmtcmp n'est plus considéré comme utilisateur WordPress
		 */
		add_filter( 'views_users', array( $this, 'views_users' ), 15, 1 );

		/**
		 * Ajout des colonnes
		 */
		add_filter( 'manage_users_custom_column', array( $this, 'manage_users_custom_column' ), 10, 3 );

		add_filter( 'manage_users_columns', array( $this, 'manage_users_columns' ) );

		add_action( 'load-user-edit.php', array( $this, 'load_user_edit' ) );
	}


	public function load_user_edit() {
		$user_id = (int) $_REQUEST['user_id'];
		if ( $this->has_role_lock( get_userdata( $user_id ) ) ) {
			set_transient( 'rmtcmp_notice_not_allowed', 1 );
			wp_redirect( admin_url() . 'users.php?page=blocked-users' );
			exit;
		}

	}

	public function views_users( $views ) {

		unset( $views['locked_rmtcmp'] );
		global $wpdb;

		$sql = "SELECT count({$wpdb->prefix}usermeta.meta_value) AS 'count_locked'
FROM {$wpdb->prefix}users INNER JOIN {$wpdb->prefix}usermeta
ON {$wpdb->prefix}users.ID = {$wpdb->prefix}usermeta.user_id 
WHERE {$wpdb->prefix}usermeta.meta_key = '{$wpdb->prefix}capabilities'
AND {$wpdb->prefix}usermeta.meta_value LIKE '%locked_rmtcmp%'";

		$result = $wpdb->get_row( $sql, ARRAY_A );

		$result = ( isset( $result ) && is_array( $result ) && isset( $result['count_locked'] ) ) ? $result['count_locked'] : 0;

		if ( isset( $views['all'] ) ) {
			preg_match( '/\((\d*)\)/', $views['all'], $output_array );

			if ( isset( $output_array[1] ) && $output_array[1] != '' ) {
				$new_val = $output_array[1] - $result;

				$tmp = explode( '<span class="count">', $views['all'] );

				$views['all'] = $tmp[0] . ' <span class="count">(' . $new_val . ')</span></a>';
			}
		}

		return $views;
	}

	public function manage_users_custom_column( $val, $column_name, $user_id ) {
		switch ( $column_name ) {
			case 'password_reset':
				$result = $this->get_days_whitout_password_reset( $user_id );
				$html   = '-';
				if ( - 1 === $result ) {
					$url  = wp_nonce_url( "users.php?action=init_counter_reset_pwd&amp;user=$user_id", 'init_counter_reset_pwd-user_' . get_current_user_id() );
					$html = '<a href="' . $url . '" class="button button-small button-secondary">' . esc_html__( 'Initialize counter', 'remind-me-to-change-my-password' ) . '</a>';
				} else {
					$options    = get_option( 'rmtcmp_form', array() );
					$user_meta  = get_userdata( $user_id );
					$roles_test = explode( '|', $options['lock_roles'] );

					if ( (int) $options['full_access'] !== $user_id ) {

						$test = false;

						foreach ( $user_meta->roles as $r ) {
							if ( in_array( $r, $roles_test, true ) ) {
								$test = true;
								break;
							}
						}

						if ( false === $test ) {
							$key = array_search( 'without-role', $roles_test, true );
							if ( false !== $key && count( $user_meta->roles ) === 0 ) {
								$test = true;
							}
						}


						if ( $test ) {
							$html = \RMTCMP\Utility\Helpers::get_field_column( $user_id, $result, $this );
						}
					}
				}

				return $html;
			default:
		}

		return $val;
	}

	public function manage_users_columns( $columns ) {

		$offset   = 5;
		$inserted = array(
			'password_reset' => esc_html__( 'Days before suspension', 'remind-me-to-change-my-password' ),
		);

		$columns = array_slice( $columns, 0, $offset, true ) + $inserted + array_slice( $columns, $offset, null, true );

		return $columns;
	}

}


