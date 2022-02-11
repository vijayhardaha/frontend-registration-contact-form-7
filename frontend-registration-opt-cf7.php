<?php
/**
 * Frontend functions
 *
 * @package FRCF7
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Skip email.
 *
 * @param bool $skip_mail Skip mail status.
 * @return bool
 */
function cf7fr_skip_registration_mail( $skip_mail ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$post_id    = isset( $_POST['_wpcf7'] ) ? absint( wp_unslash( $_POST['_wpcf7'] ) ) : 0;
	$enablemail = get_post_meta( $post_id, '_cf7fr_enablemail_registration', true );
	if ( ! empty( $enablemail ) ) {
		$skip_mail = true;
	}
	return $skip_mail;
}
add_filter( 'wpcf7_skip_mail', 'cf7fr_skip_registration_mail', 10 );

/**
 * Create user on registration.
 *
 * @param Object $cfdata Contact form 7 data object.
 * @return void
 */
function cf7fr_create_user_from_registration( $cfdata ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$post_id         = isset( $_POST['_wpcf7'] ) ? absint( wp_unslash( $_POST['_wpcf7'] ) ) : 0;
	$cf7fru          = get_post_meta( $post_id, '_cf7fru_', true );
	$cf7fre          = get_post_meta( $post_id, '_cf7fre_', true );
	$cf7frr          = get_post_meta( $post_id, '_cf7frr_', true );
	$autologinfield  = get_post_meta( $post_id, '_cf7fr_autologinfield_reg', true );
	$enable          = get_post_meta( $post_id, '_cf7fr_enable_registration', true );
	$loginurlmail    = get_post_meta( $post_id, '_cf7fr_loginurlmail_reg', true );
	$loginurlformail = get_post_meta( $post_id, '_cf7fr_loginurlformail_reg', true );
	if ( ! empty( $enablemail ) ) {
		if ( ! isset( $cfdata->posted_data ) && class_exists( 'WPCF7_Submission' ) ) {
			$submission = WPCF7_Submission::get_instance();
			if ( $submission ) {
				$formdata = $submission->get_posted_data();
			}
		} elseif ( isset( $cfdata->posted_data ) ) {
			$formdata = $cfdata->posted_data;
		}

		$password = wp_generate_password( 12, false );
		$email    = $formdata[ '' . $cf7fre . '' ];
		$name     = $formdata[ '' . $cf7fru . '' ];

		// Construct a username from the user's name.
		$username   = strtolower( str_replace( ' ', '', $name ) );
		$name_parts = explode( ' ', $name );
		if ( ! email_exists( $email ) ) {
			// Find an unused username.
			$username_tocheck = $username;
			$i                = 1;
			while ( username_exists( $username_tocheck ) ) {
				$i++;
				$username_tocheck = $username . $i;
			}
			$username = $username_tocheck;

			// Create the user.
			$userdata = array(
				'user_login'   => $username,
				'user_pass'    => $password,
				'user_email'   => $email,
				'nickname'     => reset( $name_parts ),
				'display_name' => $name,
				'first_name'   => reset( $name_parts ),
				'last_name'    => end( $name_parts ),
				'role'         => $cf7frr,
			);
			$user_id  = wp_insert_user( $userdata );
			if ( ! is_wp_error( $user_id ) ) {
				// Email login details to user.
				$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
				$message  = 'Welcome! Your login details are as follows:' . "\r\n";
				$message .= sprintf( /* translators: %s Username */ __( 'Username: %s' ), $username ) . "\r\n";
				$message .= sprintf( /* translators: %s Password */ __( 'Password: %s' ), $password ) . "\r\n";
				if ( $loginurlmail ) {
					if ( ! empty( $loginurlformail ) ) {
						$message .= $loginurlformail . "\r\n";
					} else {
						$message .= wp_login_url() . "\r\n";
					}
				}
				wp_mail( $email, sprintf( /* translators: %s Site name */ __( '[%s] Your username and password' ), $blogname ), $message );
			}

			if ( ! empty( $autologinfield ) && ! is_wp_error( $user_id ) ) {
				$user = get_user_by( 'id', $user_id );
				if ( $user ) {
					wp_set_current_user( $user_id, $user->user_login );
					wp_set_auth_cookie( $user_id );
					do_action( 'wp_login', $user->user_login, $user );
				}
			}
		}
	}
}
add_action( 'wpcf7_before_send_mail', 'cf7fr_create_user_from_registration', 1, 2 );
