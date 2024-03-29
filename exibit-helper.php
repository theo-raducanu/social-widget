<?php
if ( ! function_exists( 'exibit_pointer_load' ) ):
	function exibit_pointer_load( $hook_suffix ) {

		// Don't run on WP < 3.3
		if ( get_bloginfo( 'version' ) < '3.3' ) {
			return;
		}

		$screen    = get_current_screen();
		$screen_id = $screen->id;

		// Get pointers for this screen
		$pointers = apply_filters( 'exibit_admin_pointers_' . $screen_id, array() );

		if ( ! $pointers || ! is_array( $pointers ) ) {
			return;
		}

		// Get dismissed pointers
		$dismissed      = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );
		$valid_pointers = array();

		// Check pointers and remove dismissed ones.
		foreach ( $pointers as $pointer_id => $pointer ) {

			// Sanity check
			if ( in_array( $pointer_id, $dismissed ) || empty( $pointer ) || empty( $pointer_id ) || empty( $pointer['target'] ) || empty( $pointer['options'] ) ) {
				continue;
			}

			$pointer['pointer_id'] = $pointer_id;

			// Add the pointer to $valid_pointers array
			$valid_pointers['pointers'][] = $pointer;
		}

		// No valid pointers? Stop here.
		if ( empty( $valid_pointers ) ) {
			return;
		}

		wp_localize_script( 'wp-pointer', 'wpPointerL10n', array(
			'dismiss' => __( 'I already did this' ),
		) );
		// Add pointers style to queue.
		wp_enqueue_style( 'wp-pointer' );

		// Add pointers script to queue. Add custom script.
		wp_enqueue_script( 'exibit-social-pointer', plugins_url( 'assets/js/exibit-social-pointer.js', dirname( __FILE__ ) . '/social-icons-widget-exibit.php' ), array( 'wp-pointer' ) );

		// Add pointer options to script.
		wp_localize_script( 'exibit-social-pointer', 'exibit_social_pointer', $valid_pointers );
	}
endif;

if ( ! function_exists( 'exibit_register_pointer_callback' ) ):
	function exibit_register_pointer_callback( $p ) {

		$pointer = exibit_get_pointer_data();

		if ( ! empty( $pointer['transient_name'] ) &&
		     ! empty( $pointer['lifetime'] ) &&
		     ! get_site_transient( $pointer['transient_name'] )
		) {
			exibit_set_pointer_transient( $pointer['transient_name'], $pointer['lifetime'] );
		}

		if ( ! empty( $pointer['transient_name'] ) && get_option( '_site_transient_timeout_' . $pointer['transient_name'] ) ) {

			$data_timeout = get_option( '_site_transient_timeout_' . $pointer['transient_name'] );
			$lifetime     = ! empty( $pointer['lifetime'] ) ? $pointer['lifetime'] : MONTH_IN_SECONDS * 6;
			$delay_time   = ! empty( $pointer['delay_time'] ) ? $pointer['delay_time'] : DAY_IN_SECONDS * 3;

			if ( time() > $data_timeout - ( $lifetime - $delay_time ) ) {
				$p[ $pointer['transient_name'] ] = $pointer;
			}
		}

		return $p;
	}
endif;

if ( ! function_exists( 'exibit_set_pointer_transient' ) ):
	function exibit_set_pointer_transient( $key, $time ) {
		set_site_transient( $key, true, $time );
	}
endif;

if ( ! function_exists( 'exibit_ajax_set_pointer_transient' ) ):
	function exibit_ajax_set_pointer_transient() {

		if ( empty( $_POST['lifetime'] ) &&
		     empty( $_POST['transient_name'] ) &&
		     ! is_int( $_POST['lifetime'] ) &&
		     ! is_string( $_POST['transient_name'] )
		) {
			return wp_send_json_error( array( 'response' => 'Failed, $lifetime is not int or empty, $transient name is not string or empty ' ) );
		}

		exibit_set_pointer_transient( $_POST['transient_name'], $_POST['lifetime'] );
		wp_send_json_success( array( 'response' => 'Done, transient is set' ) );
	}
endif;

if ( ! function_exists( 'exibit_get_pointer_data' ) ):
	function exibit_get_pointer_data() {

		$plugin_data = get_plugin_data( dirname( __FILE__ ) . '/social-icons-widget-exibit.php' );
		$plugin_name = $plugin_data['Name'];

		return array(
			'target'         => '#wp-admin-bar-my-account',
			'options'        => array(
				'content'      => sprintf( '<h3> %s </h3> <p> %s </p>',
					__( 'Rate ' . $plugin_name, 'plugindomain' ),
					__( 'Thank you for using <b>' . $plugin_name )
				),
				'position'     => array( 'edge' => 'top', 'align' => 'left' ),
				'pointerClass' => 'wp-pointer exibit-pointer-class',
				'pointerWidth' => 400
			),
			'lifetime'       => MONTH_IN_SECONDS * 6,
			'delay_time'     => DAY_IN_SECONDS * 2,
			'transient_name' => 'exibit-social-pointer'
		);
	}
endif;

add_action( 'admin_enqueue_scripts', 'exibit_pointer_load', 1000 );
add_action( 'wp_ajax_exibit_ajax_set_pointer_transient', 'exibit_ajax_set_pointer_transient' );
add_filter( 'exibit_admin_pointers_widgets', 'exibit_register_pointer_callback' );


