<?php
/**
 * Plugin Name: Frontend Registration - Contact Form 7
 * Plugin URL: http://www.wpbuilderweb.com/frontend-registration-contact-form-7/
 * Description:  This plugin will convert your Contact form 7 in to registration form for WordPress. PRO Plugin available now with New Features. <strong>PRO Version is also available with New Features.</strong>.
 * Version: 4.3
 * Author: David Pokorny
 * Author URI: http://www.wpbuilderweb.com
 * Developer: Pokorny David
 * Developer E-Mail: pokornydavid4@gmail.com
 * Text Domain: contact-form-7-freg
 * Domain Path: /languages
 *
 * Copyright: © 2009-2015 izept.com.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package FRCF7
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

define( 'FRCF7_VERSION', '4.3' );
define( 'FRCF7_REQUIRED_WP_VERSION', '4.0' );
define( 'FRCF7_PLUGIN', __FILE__ );
define( 'FRCF7_PLUGIN_BASENAME', plugin_basename( FRCF7_PLUGIN ) );
define( 'FRCF7_PLUGIN_NAME', trim( dirname( FRCF7_PLUGIN_BASENAME ), '/' ) );
define( 'FRCF7_PLUGIN_DIR', untrailingslashit( dirname( FRCF7_PLUGIN ) ) );
define( 'FRCF7_PLUGIN_CSS_DIR', FRCF7_PLUGIN_DIR . '/css' );

// Load functions file.
require_once dirname( __FILE__ ) . '/frontend-registration-opt-cf7.php';

/**
 * Register extra settings panel.
 *
 * @param array $panels Settings panel array.
 * @return array
 */
function cf7fr_editor_panels_reg( $panels ) {
	$new_page = array(
		'Error' => array(
			'title'    => __( 'Registration Settings', 'contact-form-7' ),
			'callback' => 'cf7fr_admin_reg_additional_settings',
		),
	);

	$panels = array_merge( $panels, $new_page );
	return $panels;
}
add_filter( 'wpcf7_editor_panels', 'cf7fr_editor_panels_reg' );

/**
 * Render callback function for extra settings.
 *
 * @param Object $cf7 Contact Form 7 Object.
 */
function cf7fr_admin_reg_additional_settings( $cf7 ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	$post_id             = isset( $_GET['post'] ) ? absint( wp_unslash( $_GET['post'] ) ) : 0;
	$tags                = $cf7->scan_form_tags();
	$reg_enabled         = get_post_meta( $post_id, '_cf7fr_enable_registration', true );
	$skip_email          = get_post_meta( $post_id, '_cf7fr_enablemail_registration', true );
	$autologin_after_reg = get_post_meta( $post_id, '_cf7fr_autologinfield_reg', true );
	$send_login_url      = get_post_meta( $post_id, '_cf7fr_loginurlmail_reg', true );
	$custom_login_url    = get_post_meta( $post_id, '_cf7fr_loginurlformail_reg', true );
	$cf7fru              = get_post_meta( $post_id, '_cf7fru_', true );
	$cf7fre              = get_post_meta( $post_id, '_cf7fre_', true );
	$cf7frr              = get_post_meta( $post_id, '_cf7frr_', true );
	$selected_role       = $cf7frr;
	if ( empty( $selected_role ) ) {
		$selected_role = 'subscriber';
	}

	$selected        = '';
	$admin_cm_output = '';

	$admin_cm_output .= '<div id="cf7frr-additional-settings">';
	$admin_cm_output .= '<h2>Frontend Registration Settings</h2>';

	$admin_cm_output .= '<p class="form-field"><label for="cf7frr-cf7frenable-field">';
	$admin_cm_output .= '<input type="checkbox" name="cf7frenable" id="cf7frr-cf7frenable-field" value="1" ' . checked( $reg_enabled, 1, false ) . '>';
	$admin_cm_output .= 'Enable Registration on this form';
	$admin_cm_output .= '</label></p>';

	$admin_cm_output .= '<p class="form-field"><label for="cf7frr-enablemail-field">';
	$admin_cm_output .= '<input type="checkbox" name="enablemail" id="cf7frr-enablemail-field" value="1" ' . checked( $skip_email, 1, false ) . '>';
	$admin_cm_output .= 'Skip Contact Form 7 Mails?</lab';
	$admin_cm_output .= '</label></p>';

	$admin_cm_output .= '<p class="form-field"><label for="cf7frr-autologinfield-field">';
	$admin_cm_output .= '<input type="checkbox" name="autologinfield" id="cf7frr-autologinfield-field" value="1" ' . checked( $autologin_after_reg, 1, false ) . '>';
	$admin_cm_output .= 'Enable auto login after registration?';
	$admin_cm_output .= '</label></p>';

	$admin_cm_output .= '<p class="form-field"><label for="cf7frr-loginurlmail-field">';
	$admin_cm_output .= '<input type="checkbox" name="loginurlmail" id="cf7frr-loginurlmail-field" value="1" ' . checked( $send_login_url, 1, false ) . '>';
	$admin_cm_output .= 'Enable sent Login URL in Mail</lab';
	$admin_cm_output .= '</label></p>';

	$admin_cm_output .= '<p class="form-field"><label for="cf7frr-loginurlformail-field">Set Custom Login URL for email:</label>';
	$admin_cm_output .= '<input type="text" name="loginurlformail" id="cf7frr-loginurlformail-field" class="regular-text" value="' . esc_attr( $custom_login_url ) . '"/>';
	$admin_cm_output .= '</p>';

	$admin_cm_output .= '<h2>Map Fields</h2>';
	$admin_cm_output .= '<table class="form-table">';

	$admin_cm_output .= '<tr><th scope="row">User Name:</th>';
	$admin_cm_output .= '<td><select name="_cf7fru_" class="regular-text">';
	$admin_cm_output .= '<option value="">Select Field</option>';
	foreach ( $tags as $key => $value ) {
		$admin_cm_output .= '<option value="' . esc_attr( $value['name'] ) . '" ' . selected( $cf7fru, $value['name'], false ) . '>' . esc_html( $value['name'] ) . '</option>';
	}
	$admin_cm_output .= '</select>';
	$admin_cm_output .= '</td></tr>';

	$admin_cm_output .= '<tr><th scope="row">User Email:</th>';
	$admin_cm_output .= '<td><select name="_cf7fre_" class="regular-text">';
	$admin_cm_output .= '<option value="">Select Field</option>';
	foreach ( $tags as $key => $value ) {
		$admin_cm_output .= '<option value="' . esc_attr( $value['name'] ) . '" ' . selected( $cf7fre, $value['name'], false ) . '>' . esc_html( $value['name'] ) . '</option>';
	}
	$admin_cm_output .= '</select>';
	$admin_cm_output .= '</td></tr>';

	$admin_cm_output .= '<tr><th scope="row">User Role:</th>';
	$admin_cm_output .= '<td><select name="_cf7frr_" class="regular-text">';
	$editable_roles   = get_editable_roles();
	foreach ( $editable_roles as $role => $details ) {
		$name             = translate_user_role( $details['name'] );
		$admin_cm_output .= '<option value="' . esc_attr( $role ) . '" ' . selected( $selected_role, $role, false ) . '>' . esc_html( $name ) . '</option>';
	}
	$admin_cm_output .= '</select>';
	$admin_cm_output .= '</td></tr>';
	$admin_cm_output .= '</table>';
	$admin_cm_output .= '</div>';

	echo $admin_cm_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Save extra settings.
 *
 * @param Object $cf7 Contact Form 7 Object.
 */
function cf7_save_reg_contact_form( $cf7 ) {
	$tags = $cf7->scan_form_tags();

	// phpcs:disabled WordPress.Security.NonceVerification.Missing
	$post_id = isset( $_POST['post_ID'] ) ? absint( wp_unslash( $_POST['post_ID'] ) ) : 0;
	if ( empty( $post_id ) ) {
		return;
	}

	if ( isset( $_POST['cf7frenable'] ) ) {
		update_post_meta( $post_id, '_cf7fr_enable_registration', 1 );
	} else {
		delete_post_meta( $post_id, '_cf7fr_enable_registration' );
	}

	if ( isset( $_POST['enablemail'] ) ) {
		update_post_meta( $post_id, '_cf7fr_enablemail_registration', 1 );
	} else {
		delete_post_meta( $post_id, '_cf7fr_enablemail_registration' );
	}

	if ( isset( $_POST['autologinfield'] ) ) {
		update_post_meta( $post_id, '_cf7fr_autologinfield_reg', 1 );
	} else {
		delete_post_meta( $post_id, '_cf7fr_autologinfield_reg' );
	}

	if ( isset( $_POST['loginurlmail'] ) ) {
		update_post_meta( $post_id, '_cf7fr_loginurlmail_reg', 1 );
	} else {
		delete_post_meta( $post_id, '_cf7fr_loginurlmail_reg' );
	}

	if ( isset( $_POST['loginurlformail'] ) ) {
		update_post_meta( $post_id, '_cf7fr_loginurlformail_reg', esc_url_raw( wp_unslash( $_POST['loginurlformail'] ) ) );
	} else {
		delete_post_meta( $post_id, '_cf7fr_loginurlformail_reg' );
	}

	$fields = array( '_cf7fru_', '_cf7fre_', '_cf7frr_' );
	foreach ( $fields as $field_key ) {
		if ( isset( $_POST[ $field_key ] ) ) {
			update_post_meta( $post_id, $field_key, sanitize_text_field( wp_unslash( $_POST[ $field_key ] ) ) );
		} else {
			delete_post_meta( $post_id, $field_key );
		}
	}
	// phpcs:enabled WordPress.Security.NonceVerification.Missing
}
add_action( 'wpcf7_save_contact_form', 'cf7_save_reg_contact_form' );
