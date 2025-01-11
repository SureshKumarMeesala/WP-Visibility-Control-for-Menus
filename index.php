<?php
/**
 * Plugin Name: Visibility Control for Menus
 * Description: Add visibility options (Desktop/Mobile/Both) to WordPress menu items.
 * Version: 1.0
 * Author: Suresh Kumar M
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Add custom visibility fields to the menu item editor
function adk_add_visibility_fields_to_menu_item( $item_id, $item, $depth, $args ) {
    $visibility = get_post_meta( $item_id, '_menu_item_visibility', true ) ?: 'both'; // Default to 'both'

    // Add nonce field for verification
    wp_nonce_field( 'adk_visibility_nonce_action', 'adk_visibility_nonce_name' );

    ?>
    <fieldset class="adk-field-visibility adk-settings">
        <legend>Visibility Settings</legend>
        <p>
            <label for="menu-item-visibility-both-<?php echo esc_attr( $item_id ); ?>">
                <input type="radio" id="menu-item-visibility-both-<?php echo esc_attr( $item_id ); ?>" name="menu-item-visibility[<?php echo esc_attr( $item_id ); ?>]" value="both" <?php checked( $visibility, 'both' ); ?> />
                Show on Both Desktop and Mobile (Default)
            </label>
        </p>
        <p>
            <label for="menu-item-visibility-desktop-<?php echo esc_attr( $item_id ); ?>">
                <input type="radio" id="menu-item-visibility-desktop-<?php echo esc_attr( $item_id ); ?>" name="menu-item-visibility[<?php echo esc_attr( $item_id ); ?>]" value="desktop" <?php checked( $visibility, 'desktop' ); ?> />
                Show on Desktop Only
            </label>
        </p>
        <p>
            <label for="menu-item-visibility-mobile-<?php echo esc_attr( $item_id ); ?>">
                <input type="radio" id="menu-item-visibility-mobile-<?php echo esc_attr( $item_id ); ?>" name="menu-item-visibility[<?php echo esc_attr( $item_id ); ?>]" value="mobile" <?php checked( $visibility, 'mobile' ); ?> />
                Show on Mobile Only
            </label>
        </p>
    </fieldset>
    <?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'adk_add_visibility_fields_to_menu_item', 10, 4 );

// Save visibility settings for menu items
function adk_save_visibility_settings( $menu_id, $menu_item_db_id ) {
    // Verify the nonce (unslash the nonce before verification and sanitize)
    if ( ! isset( $_POST['adk_visibility_nonce_name'] ) || ! wp_verify_nonce( wp_unslash( $_POST['adk_visibility_nonce_name'] ), 'adk_visibility_nonce_action' ) ) {
        return; // If nonce is invalid, don't save the data
    }

    // Sanitize and unslash the visibility input before saving
    if ( isset( $_POST['menu-item-visibility'][ $menu_item_db_id ] ) ) {
        $visibility = sanitize_text_field( wp_unslash( $_POST['menu-item-visibility'][ $menu_item_db_id ] ) );
        update_post_meta( $menu_item_db_id, '_menu_item_visibility', $visibility );
    } else {
        delete_post_meta( $menu_item_db_id, '_menu_item_visibility' );
    }
}
add_action( 'wp_update_nav_menu_item', 'adk_save_visibility_settings', 10, 2 );

// Add custom CSS to style the visibility of menu items based on screen size
function adk_add_visibility_css() {
    ?>
    <style>
        /* Default: Show all menu items */
        .adk-desktop-only, .adk-mobile-only, .adk-both-visible {
            display: block;
        }

        /* Hide desktop-only menu items on mobile */
        @media screen and (max-width: 768px) {
            .adk-desktop-only {
                display: none;
            }
        }

        /* Hide mobile-only menu items on desktop */
        @media screen and (min-width: 769px) {
            .adk-mobile-only {
                display: none;
            }
        }

        /* Show both-visible items on both desktop and mobile */
        .adk-both-visible {
            display: block;
        }
    </style>
    <?php
}
add_action( 'wp_head', 'adk_add_visibility_css' );

// Add visibility class to menu items based on the visibility option selected
function adk_add_visibility_class_to_menu_item( $classes, $item, $args ) {
    $visibility = get_post_meta( $item->ID, '_menu_item_visibility', true ) ?: 'both';

    // Add appropriate classes based on visibility option
    if ( $visibility === 'desktop' ) {
        $classes[] = 'adk-desktop-only';
    } elseif ( $visibility === 'mobile' ) {
        $classes[] = 'adk-mobile-only';
    } else {
        $classes[] = 'adk-both-visible';
    }

    return array_map( 'esc_attr', $classes ); // Escape each class
}
add_filter( 'nav_menu_css_class', 'adk_add_visibility_class_to_menu_item', 10, 3 );
