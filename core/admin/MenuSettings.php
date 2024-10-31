<?php

namespace RMTCMP\Admin;

class MenuSettings extends Menu {
	public function __construct() {
		parent::__construct();
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts_child' ), 1 );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );


	}

	public function admin_notices() {

		if ( isset( $_REQUEST['page'], $_REQUEST['settings-updated'] ) && 'remind_me_password' === $_REQUEST['page'] && true === $_REQUEST['settings-updated'] ) {
			echo '<div class="notice notice-success is-dismissible"><p><strong>' . esc_html__( 'Settings saved.', 'remind-me-to-change-my-password' ) . '</strong></p></div>';
		}
	}

	public function admin_enqueue_scripts_child( $hook ) {
		wp_register_style( 'rmtcmp_styles', RMTCMP_PLUGIN_URL_ASSETS . '/css/styles.css', false, RMTCMP_VERSION );
		wp_enqueue_style( 'rmtcmp_styles' );

		if ( 'users_page_blocked-users' !== $hook && 'users_page_remind_me_password' !== $hook ) {
			return;
		}

		wp_enqueue_script( 'rmtcmp_scripts', RMTCMP_PLUGIN_URL_ASSETS . 'js/main.js', array(), RMTCMP_VERSION );

		wp_localize_script(
			'rmtcmp_scripts',
			'rmtcmp',
			array(
				'_nonce'    => wp_create_nonce( 'erase-users' ),
				'admin_url' => get_admin_url(),
			)
		);
	}


	public function admin_init() {

		register_setting( 'rmtcmp_options', 'rmtcmp_form' );

		add_settings_section(
			'rmtcmp_options_section',
			null,
			null,
			'rmtcmp_options'
		);

		add_settings_field(
			'max_days',
			esc_html__( 'User(s) password(s) validity', 'remind-me-to-change-my-password' ),
			function () {
				$options = get_option( 'rmtcmp_form', array() );
				$days    = isset( $options['max_days'] ) ? $options['max_days'] : 365; ?>
				<label class="description">
					<input style="width: 10%;text-align: right;" type="number" min="1" max="365" name="rmtcmp_form[max_days]" placeholder="365" value="<?php echo esc_attr( $days ); ?>"/>
					<span>
						<?php esc_html_e( 'Days', 'remind-me-to-change-my-password' ); ?>
					</span>
				</label>
				<?php
			},
			'rmtcmp_options',
			'rmtcmp_options_section'
		);


		add_settings_field(
			'lock_days',
			esc_html__( 'Day(s) before account suspension', 'remind-me-to-change-my-password' ),
			function () {
				$options = get_option( 'rmtcmp_form', array() );
				$days    = isset( $options['lock_days'] ) ? $options['lock_days'] : 42;
				?>
				<label class="description">
					<input style="width: 10%;text-align: right;" type="number" min="1" max="365" name="rmtcmp_form[lock_days]" placeholder="10" value="<?php echo esc_attr( $days ); ?>"/>
					<span>
						<?php echo esc_html__( 'Days', 'remind-me-to-change-my-password' ); ?>
					</span>
				</label>
				<p><?php esc_html_e( 'In this field, define number of days before the account is suspended, once the password is expired.', 'remind-me-to-change-my-password' ); ?></p>
				<?php
			},
			'rmtcmp_options',
			'rmtcmp_options_section'
		);

		add_settings_field(
			'colors_exceeted',
			esc_html__( 'Highlight color on exceeded due date', 'remind-me-to-change-my-password' ),
			function () {
				$options = get_option( 'rmtcmp_form', array() );
				$colors  = isset( $options['colors_exceeted'] ) ? $options['colors_exceeted'] : '#f0a15b';
				?>
				<label class="description">
					<input class="rmtcmp__color-field" type="text" name="rmtcmp_form[colors_exceeted]" placeholder="#f0a15b" value="<?php echo esc_attr( $colors ); ?>"/>
				</label>
				<?php
			},
			'rmtcmp_options',
			'rmtcmp_options_section'
		);

		add_settings_field(
			'lock_roles',
			esc_html__( 'Select the roles that will be submitted to this account suspension rule', 'remind-me-to-change-my-password' ),
			function () {
				$options        = get_option( 'rmtcmp_form', array() );
				$roles_selected = isset( $options['lock_roles'] ) ? explode( '|', $options['lock_roles'] ) : array();
				$roles          = get_editable_roles();

				$selected_values = array();
				$selected        = '';
				if ( in_array( 'without-role', $roles_selected, true ) ) {
					$selected                        = 'disabled';
					$selected_values['without-role'] = esc_html__( 'Without role', 'remind-me-to-change-my-password' );
				}

				$tmp = array(
					'<li class="' . esc_attr( $selected ) . '" data-value="without-role">' . esc_html__( 'Without role', 'remind-me-to-change-my-password' ) . '</li>',
				);
				foreach ( $roles as $role => $role_data ) {
					if ( 'locked_rmtcmp' === $role ) {
						continue;
					}
					$selected = '';
					if ( in_array( $role, $roles_selected, true ) ) {
						$selected                 = 'disabled';
						$selected_values[ $role ] = translate_user_role( $role_data['name'] );
					}

					$tmp[] = '<li class="' . esc_attr( $selected ) . '" data-value="' . esc_attr( $role ) . '">' . esc_html( translate_user_role( $role_data['name'] ) ) . '</li>';
				}
				?>
				<div class="rmtcmp__selection">
					<div class="header">
						<div class="search">
							<input type="search" placeholder="<?php esc_html_e( 'Search', 'remind-me-to-change-my-password' ); ?>">
							<input id="rmtcmp_form-roles" type="hidden" name="rmtcmp_form[lock_roles]" value="<?php echo esc_attr( implode( '|', array_keys( $selected_values ) ) ); ?>">
						</div>
						<div class="title">
							<h2><?php esc_html_e( 'Selected items', 'remind-me-to-change-my-password' ); ?></h2>
						</div>
					</div>
					<div class="choices">
						<ul class="rmtcmp__list-choices">
							<?php
							echo wp_kses(
								implode( ' ', $tmp ),
								array(
									'li' => array(
										'class'      => array(),
										'data-value' => array(),
									),
								)
							);
							?>
						</ul>
					</div>
					<div class="values">
						<ul class="values-list">
							<?php
							if ( count( $selected_values ) > 0 ) {
								echo implode(
									' ',
									array_map(
										function ( $value, $key ) {
											return '<li data-value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</li>';
										},
										$selected_values,
										array_keys( $selected_values )
									)
								);
							}
							?>
						</ul>
					</div>
				</div>
				<?php
			},
			'rmtcmp_options',
			'rmtcmp_options_section'
		);

		$rmtcmp_activate_full_access = apply_filters( 'rmtcmp_activate_full_access', true );
		if ( true === $rmtcmp_activate_full_access ) {
			add_settings_field(
				'full_access',
				esc_html__( 'Email', 'remind-me-to-change-my-password' ),
				function () {
					$options     = get_option( 'rmtcmp_form', array() );
					$full_access = isset( $options['full_access'] ) ? (int) $options['full_access'] : \RMTCMP\Utility\Helpers::get_admin_full_access();
					$users_admin = get_users(
						array(
							'role__in' => array( 'administrator' ),
							'number'   => - 1,
						)
					);
					?>
					<select name="rmtcmp_form[full_access]">
						<?php
						foreach ( $users_admin as $user ) :
							$selected = '';
							if ( $full_access === $user->ID ) {
								$selected = 'selected';
							}
							?>
							<option <?php echo esc_attr( $selected ); ?> value="<?php echo esc_attr( $user->ID ); ?>"><?php echo esc_html( $user->user_email ); ?></option>
						<?php endforeach; ?>
					</select>

					<p><?php esc_html_e( 'Specify the email that will not be submitted to this account suspension rule, so that your website remains reachable at all time.', 'remind-me-to-change-my-password' ); ?></p>


					<?php
				},
				'rmtcmp_options',
				'rmtcmp_options_section'
			);
		}


	}
}
