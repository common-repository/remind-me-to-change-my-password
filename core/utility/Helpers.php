<?php

namespace RMTCMP\Utility;

class Helpers {

	public static function get_field_column( $user_id, $result, $class ) {
		$options         = get_option( 'rmtcmp_form', array() );
		$colors_exceeted = isset( $options['colors_exceeted'] ) ? $options['colors_exceeted'] : '#e66e49';
		$days            = isset( $options['max_days'] ) ? (int) $options['max_days'] : 365;
		$lock_days       = isset( $options['lock_days'] ) ? (int) $options['lock_days'] : 30;

		$color_text = '#fff';
		$data       = '-';
		if ( $result >= $days ) {
			$date_possible = $result - $days;
			$color_bg      = $colors_exceeted;
			$number        = $lock_days - $date_possible;
			if ( $number > 0 ) {
				$data = '<span class="rmtcmp__item-color" style="background: ' . $color_bg . ';padding: 10px;color: ' . $color_text . ';display: inline-block;text-align: center;">' . $number . '</span>';
			} else {
				$class->lock_user( $user_id, false );
				$data = '<span>' . esc_html__( 'The account will be suspended', 'remind-me-to-change-my-password' ) . '</span>';
			}
		}


		return $data;
	}


	public static function get_count_users_locked() {
		global $wpdb;

		$result = $wpdb->get_row(
			"SELECT count({$wpdb->usermeta}.user_id) AS 'number_users_locked' FROM $wpdb->usermeta 
					WHERE {$wpdb->usermeta}.meta_key = '{$wpdb->prefix}capabilities'
					AND {$wpdb->usermeta}.meta_value LIKE '%locked_rmtcmp%'",
			ARRAY_A
		);


		if ( isset( $result['number_users_locked'] ) && $result['number_users_locked'] > 0 ) {
			return (int) $result['number_users_locked'];
		}

		return 0;
	}


	public static function get_admin_full_access() {
		global $wpdb;

		$result = $wpdb->get_row(
			"SELECT {$wpdb->usermeta}.user_id FROM $wpdb->usermeta 
					WHERE {$wpdb->usermeta}.meta_key = '{$wpdb->prefix}capabilities'
					AND {$wpdb->usermeta}.meta_value LIKE '%administrator%' ORDER BY {$wpdb->usermeta}.user_id ASC",
			ARRAY_A
		);

		if ( isset( $result['user_id'] ) && $result['user_id'] > 0 ) {
			return (int) $result['user_id'];
		}

		return 0;
	}
}
