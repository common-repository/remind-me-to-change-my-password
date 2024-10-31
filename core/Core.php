<?php

namespace RMTCMP\Init;

class Core {
	private static $_instance;

	final private function __construct() {

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'plugin_loaded' ) );
		/**
		 * on modifie la query user pour supprimer les utilisateurs bloqués de la query
		 *
		 */
		add_action( 'pre_user_query', array( $this, 'pre_user_query' ) );

		add_filter( 'editable_roles', array( $this, 'editable_roles' ) );

		/**
		 * Init class
		 */


		$this->init_class( 'Login', 'User' );
		$this->init_class( 'Password', 'User' );
		$this->init_class( 'Views', 'User' );
		$this->init_class( 'Actions', 'User' );
		$this->init_class( 'MenuSettings', 'Admin' );

		add_filter( 'plugin_action_links_' . RMTCMP_PLUGIN_FILE_BASENAME, array( $this, 'plugin_action_links' ), 10, 1 );
	}

	public function editable_roles( $all_roles ) {
		unset( $all_roles['locked_rmtcmp'] );
		return $all_roles;
	}

	public function plugin_action_links( $actions ) {

		array_unshift( $actions, '<a href="' . esc_url( get_admin_url( null, '/users.php?page=remind_me_password' ) ) . '">' . __( 'Settings', 'remind-me-to-change-my-password' ) . '</a>' );

		return $actions;
	}

	public function init() {
		add_role( 'locked_rmtcmp', esc_html__( 'Locked', 'remind-me-to-change-my-password' ), array() );
	}

	public function plugin_loaded() {
		load_plugin_textdomain( 'remind-me-to-change-my-password', false, dirname( plugin_basename( RMTCMP_FILE ) ) . '/languages/' );
	}

	public function init_class( $name, $namespace ) {
		$class_name = '\\RMTCMP\\' . $namespace . '\\' . ( $name );

		return new $class_name();
	}

	public function pre_user_query( $query ) {
		global $wpdb;

		if ( isset( $_GET['page'] ) && 'blocked-users' === $_GET['page'] ) {
			return;
		}

		/*
		 * On enlève les users qui ont le rôle locked_rmtcmp
		 */
		$query->query_where = str_replace(
			'WHERE 1=1',
			"WHERE 1=1 AND {$wpdb->users}.ID IN (
				SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta 
					WHERE {$wpdb->usermeta}.meta_key = '{$wpdb->prefix}capabilities'
					AND {$wpdb->usermeta}.meta_value NOT LIKE '%locked_rmtcmp%')",
			$query->query_where
		);

	}

	public static function get_instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new Core();
		}

		return self::$_instance;
	}
}