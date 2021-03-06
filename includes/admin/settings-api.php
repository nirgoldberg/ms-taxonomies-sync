<?php
/**
 * Settings API functions
 *
 * @author		Nir Goldberg
 * @package		includes/admin
 * @version		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * mstaxsync_admin_display_form_element
 *
 * This function will display an admin form element
 *
 * @since		1.0.0
 * @param		$args (array)
 * @return		N/A
 */
function mstaxsync_admin_display_form_element( $args ) {

	switch ( $args[ 'type' ] ) {

		case 'text':
		case 'password':
		case 'number':
			mstaxsync_admin_display_text_form_element( $args );
			break;

		case 'textarea':
			mstaxsync_admin_display_textarea_form_element( $args );
			break;

		case 'select':
		case 'multiselect':
			mstaxsync_admin_display_select_form_element( $args );
			break;

		case 'radio':
		case 'checkbox':
			mstaxsync_admin_display_radio_form_element( $args );
			break;

	}

	// If there is supplimental text
	if ( isset( $args[ 'supplimental' ] ) ) {
		printf( '<p class="description">%s</p>', $args[ 'supplimental' ] );
	}

	// If there is helper text
	if ( isset( $args[ 'helper' ] ) ) {
		printf( '<span class="helper"> %s</span>', $args[ 'helper' ] );
	}

}

/**
 * mstaxsync_admin_display_text_form_element
 *
 * This function will display an admin text/password/number form element
 *
 * @since		1.0.0
 * @param		$args (array)
 * @return		N/A
 */
function mstaxsync_admin_display_text_form_element( $args ) {

	// vars
	$value = get_option( $args[ 'uid' ] );

	printf( '<input name="%1$s" id="%1$s" type="%2$s" placeholder="%3$s" value="%4$s" />',
		$args[ 'uid' ],
		$args[ 'type' ],
		$args[ 'placeholder' ],
		( $value !== false ) ? $value : ''
	);

}

/**
 * mstaxsync_admin_display_textarea_form_element
 *
 * This function will display an admin textarea form element
 *
 * @since		1.0.0
 * @param		$args (array)
 * @return		N/A
 */
function mstaxsync_admin_display_textarea_form_element( $args ) {

	// vars
	$value = get_option( $args[ 'uid' ] );

	printf( '<textarea name="%1$s" id="%1$s" placeholder="%2$s" rows="5" cols="50">%3$s</textarea>',
		$args[ 'uid' ],
		$args[ 'placeholder' ],
		( $value !== false ) ? $value : ''
	);

}

/**
 * mstaxsync_admin_display_select_form_element
 *
 * This function will display an admin select/multiselect form element
 *
 * @since		1.0.0
 * @param		$args (array)
 * @return		N/A
 */
function mstaxsync_admin_display_select_form_element( $args ) {

	// vars
	$value = get_option( $args[ 'uid' ] );

	if ( ! empty ( $args[ 'options' ] ) && is_array( $args[ 'options' ] ) ) {

		// vars
		$attributes		= '';
		$options_markup	= '';

		foreach ( $args[ 'options' ] as $key => $label ) {
			$options_markup .=	sprintf( '<option value="%s" %s>%s</option>',
									$key,
									( $value !== false && is_array( $value ) ) ? selected( $value[ array_search( $key, $value, true ) ], $key, false ) : '',
									$label
								);
		}

		if ( $args[ 'type' ] === 'multiselect' ) {
			$attributes = ' multiple="multiple" ';
		}

		printf( '<select name="%1$s[]" id="%1$s" %2$s>%3$s</select>',
			$args[ 'uid' ],
			$attributes,
			$options_markup
		);

	}

}

/**
 * mstaxsync_admin_display_radio_form_element
 *
 * This function will display an admin radio/checkbox form element
 *
 * @since		1.0.0
 * @param		$args (array)
 * @return		N/A
 */
function mstaxsync_admin_display_radio_form_element( $args ) {

	// vars
	$default	= isset( $args[ 'default' ] ) ? $args[ 'default' ] : false;
	$value		= get_option( $args[ 'uid' ], $default );

	if ( ! empty ( $args[ 'options' ] ) && is_array( $args[ 'options' ] ) ) {

		// vars
		$options_markup	= '';
		$iterator		= 0;

		foreach ( $args[ 'options' ] as $key => $label ) {

			$iterator++;
			$options_markup .=	sprintf( '<label for="%1$s_%6$s"><input id="%1$s_%6$s" name="%1$s[]" type="%2$s" value="%3$s" %4$s /> %5$s</label><br/>',
				$args[ 'uid' ],
				$args[ 'type' ],
				$key,
				( $value !== false && is_array( $value ) ) ? checked( $value[ array_search( $key, $value, true ) ], $key, false ) : '',
				$label,
				$iterator
			);

		}

		printf( '<fieldset>%s</fieldset>', $options_markup );

	}

}