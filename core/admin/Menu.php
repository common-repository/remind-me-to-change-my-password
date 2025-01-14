<?php

namespace RMTCMP\Admin;

class Menu {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 500 );

	}


	public function admin_bar_menu( \WP_Admin_Bar $wp_admin_bar ) {

		if ( ! is_admin() ) {
			return;
		}
		$count        = \RMTCMP\Utility\Helpers::get_count_users_locked();
		$notification = '';
		$class        = '';
		if ( $count > 0 ) {
			$class             = 'class="warning"';
			$notification_text = sprintf( _n( '%s notification', '%s notifications', $count, 'remind-me-to-change-my-password' ), $count );
			$notification      = ' <span class="update-plugins count-' . $count . '"><span class="plugin-count" aria-hidden="true">' . $count . '</span><span class="screen-reader-text">' . $notification_text . '</span></span>';
		}


		$svg = '<svg ' . $class . ' viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg">
							<g>
								<path d="M21.06 19.64c2.48-2.15 5.61-3.34 8.93-3.34 3.32 0 6.45 1.19 8.93 3.34l7.32-7.33c-4.44-4.1-10.18-6.34-16.26-6.34 -6.09 0-11.82 2.23-16.26 6.33l7.31 7.31Z"/>
								<path d="M30 22.75c1.5 0 2.72 1.23 2.72 2.73 0 .55.44 1 1 1 .55 0 1-.45 1-1 0-2.61-2.12-4.74-4.73-4.74 -2.62 0-4.75 2.13-4.75 4.73v2.36c-.91.44-1.55 1.36-1.55 2.44v5.18c0 1.5 1.22 2.73 2.73 2.73h7.09c1.5 0 2.73-1.24 2.73-2.74v-5.19c0-1.39-1.06-2.53-2.41-2.7 -.05-.02-.1-.04-.16-.04h-6.48v-2.09c0-1.51 1.23-2.74 2.74-2.74Z"/>
								<path d="M46.25 47.65l-7.33-7.33c-1.12.97-2.37 1.75-3.7 2.31 -1.65.66-3.41 1.02-5.25 1.02 -3.32 0-6.45-1.19-8.94-3.34l-1.2 1.2 -6.13 6.11c4.43 4.09 10.16 6.33 16.25 6.33 6.08-.001 11.81-2.25 16.25-6.35Z"/>
								<path d="M47.65 13.76l-7.31 7.3c2.15 2.48 3.33 5.61 3.33 8.93 0 3.31-1.18 6.44-3.34 8.92l7.3 7.3c8.42-9.16 8.42-23.31-.01-32.47Z"/>
								<path d="M12.34 46.22l7.29-7.3c-2.15-2.49-3.34-5.62-3.34-8.93 0-3.33 1.19-6.46 3.33-8.94l-7.31-7.3c-8.43 9.15-8.43 23.3-.01 32.46Z"/>
							</g>
						</svg>';

		$wp_admin_bar->add_menu(
			array(
				'id'     => 'rmtcmp_bar_menu',
				'parent' => 'top-secondary',
				'group'  => null,
				'title'  => $svg,
				'href'   => esc_url( get_admin_url( null, '/users.php?page=blocked-users' ) ),
			)
		);
		$wp_admin_bar->add_menu(
			array(
				'id'     => 'rmtcmp_bar_menu_child',
				'parent' => 'rmtcmp_bar_menu',
				'group'  => null,
				'title'  => esc_html__( 'Suspended account(s)', 'remind-me-to-change-my-password' ) . $notification,
				'href'   => esc_url( get_admin_url( null, '/users.php?page=blocked-users' ) ),
			)
		);

	}


	public function admin_enqueue_scripts() {
		$screen = get_current_screen();
		if ( 'users_page_remind_me_password' !== $screen->id ) {
			return;
		}

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	public function admin_menu() {
		add_submenu_page(
			'users.php',
			esc_html__( 'Password Reset Manager', 'remind-me-to-change-my-password' ),
			esc_html__( 'Password Reset Manager', 'remind-me-to-change-my-password' ),
			'manage_options',
			'remind_me_password',
			function () {
				include( RMTCMP_DIR . '/views/admin/admin-page.php' );
			}
		);

		$count        = \RMTCMP\Utility\Helpers::get_count_users_locked();
		$notification = '';
		if ( $count > 0 ) {
			$notification_text = sprintf( _n( '%s notification', '%s notifications', $count, 'remind-me-to-change-my-password' ), $count );
			$notification      = ' <span class="update-plugins count-' . $count . '"><span class="plugin-count" aria-hidden="true">' . $count . '</span><span class="screen-reader-text">' . $notification_text . '</span></span>';
		}
		add_submenu_page(
			'users.php',
			esc_html__( 'Suspended account(s)', 'remind-me-to-change-my-password' ),
			esc_html__( 'Suspended account(s)', 'remind-me-to-change-my-password' ) . $notification,
			'manage_options',
			'blocked-users',
			function () {
				include( RMTCMP_DIR . '/views/admin/blocked-users-list.php' );
			}
		);
	}
}