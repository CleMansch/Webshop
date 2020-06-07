<?php
/***************************************************************************
 *
 * 	----------------------------------------------------------------------
 * 							DO NOT EDIT THIS FILE
 *	----------------------------------------------------------------------
 *
 * 						Copyright (C) Themify
 *
 *	----------------------------------------------------------------------
 *
 ***************************************************************************/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 

/**
 * Theme and Themify Framework Path and URI
 * @since 1.2.2 
 */
defined( 'THEME_DIR' ) || define( 'THEME_DIR', get_template_directory() );
defined( 'THEME_URI' ) || define( 'THEME_URI', get_template_directory_uri() );
defined( 'THEMIFY_DIR' ) || define( 'THEMIFY_DIR', THEME_DIR . '/themify' );
defined( 'THEMIFY_URI' ) || define( 'THEMIFY_URI', THEME_URI . '/themify' );
defined( 'THEMIFYMIN' ) || define( 'THEMIFYMIN', defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min' );

function themify_config_init() {

	/* 	Global Vars
 	***************************************************************************/
	global $pagenow, $content_width;

	if ( ! isset( $content_width ) ) {
		$content_width = 1165;
	}

	/*	Activate Theme
 	***************************************************************************/
	if ( isset( $_GET['activated'] ) && 'themes.php' === $pagenow ) {
		themify_maybe_clear_legacy();
		add_action( 'init', 'themify_theme_first_run', 20 );

		include_once( trailingslashit( THEMIFY_DIR ) . 'themify-builder/first-run.php' );

		/* on new installations, set a flag to prevent shortcodes from loading */
		if( false == get_option( 'themify_data' ) ) {
			themify_set_flag( 'deprecate_shortcodes' );
		}
	}


	/* 	Themify Framework Version
 	****************************************************************************/
	define( 'THEMIFY_VERSION', '4.8.4' );

	/* 	Run after update
 	***************************************************************************/
	if ( is_admin() && 'update_ok' === get_option( 'themify_update_ok_flag' ) ) {
		/**
		 * Fires after the updater finished the updating process.
		 *
		 * @since 1.8.3
		 */
		do_action( 'themify_updater_post_install' );
	}

	/* 	Woocommerce
	 ***************************************************************************/
	defined( 'WOOCOMMERCE_VERSION' ) || define( 'WOOCOMMERCE_VERSION', '' );
	
	if( themify_is_woocommerce_active() ) {
		add_theme_support('woocommerce');
		if(!themify_check( 'setting-disable_product_image_zoom' )){
			add_theme_support( 'wc-product-gallery-zoom' );
		}
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
	}

	/**
	 * Editor Style
	 * @since 2.0.2
	 */
	add_editor_style();
	add_theme_support( 'title-tag' );

}
add_action( 'after_setup_theme', 'themify_config_init' );

function themify_theme_first_run() {
	flush_rewrite_rules();
	wp_redirect( admin_url() . 'admin.php?page=themify&firsttime=true' );
	exit;
}

///////////////////////////////////////
// Load theme languages
///////////////////////////////////////

load_theme_textdomain( 'themify', THEME_DIR.'/languages' );


/**
 * Load Filesystem Class
 * @since 2.5.8
 */
require_once( THEME_DIR . '/themify/class-themify-filesystem.php' );


require_once( THEME_DIR . '/themify/themify-icon-picker/themify-icon-picker.php' );
Themify_Icon_Picker::get_instance( THEMIFY_URI . '/themify-icon-picker' );
Themify_Icon_Picker::get_instance()->register( 'Themify_Icon_Picker_Themify' );
Themify_Icon_Picker::get_instance()->register( 'Themify_Icon_Picker_FontAwesome' );
include( THEMIFY_DIR . '/themify-fontello.php' );

require_once THEMIFY_DIR . '/img.php';


/**
 * Load Cache
 */
require_once(THEME_DIR . '/themify/class-themify-cache.php');

/**
 * Load Page Builder
 * @since 1.1.3
 */
require_once( THEMIFY_DIR . '/themify-builder/themify-builder.php' );


/**
 * Load Enqueue Class
 * @since 2.5.8
 */
require_once( THEMIFY_DIR . '/class-themify-enqueue.php' );

/**
 * Load Customizer
 * @since 1.8.2
 */
require_once THEMIFY_DIR . '/customizer/class-themify-customizer.php';

/**
 * Load Schema.org Microdata
 * @since 2.6.5
 */
if ( 'on' !== themify_get( 'setting-disable_microdata' ) ) {
	require_once THEMIFY_DIR . '/themify-microdata.php';
}

require_once THEMIFY_DIR . '/themify-wp-filters.php';
require_once THEMIFY_DIR . '/themify-plugin-compatibility.php';
require_once THEMIFY_DIR . '/themify-template-tags.php';
require_once THEMIFY_DIR . '/class-themify-menu-icons.php';

if( is_admin() )
	require_once THEMIFY_DIR . '/themify-admin.php';



/**
 * Sets the WP Featured Image size selected for Query Category pages
 */
add_action( 'template_redirect', 'themify_feature_size_page' );

/**
 * Outputs html to display alert messages in post edit/new screens. Excludes pages.
 */
add_action( 'admin_notices', 'themify_prompt_message' );

/**
 * Load Google fonts library
 */
add_filter( 'themify_google_fonts', 'themify_enqueue_gfonts' );



/**
 * Display sticky posts in the loops
 */
add_filter( 'the_posts', 'themify_sticky_post_helper' );

/**
 * Add support for feeds on the site
 */
add_theme_support( 'automatic-feed-links' );

/**
 * Add custom query_posts
 */
add_action( 'themify_custom_query_posts', 'themify_custom_query_posts' );

/**
 * Important CSS that needs be loaded before everything else
 */
add_action( 'wp_head', 'themify_above_the_fold_css', 7 );

/**
 * Load Themify Hooks
 * @since 1.2.2
 */
require_once(THEMIFY_DIR . '/themify-hooks.php' );
require_once(THEMIFY_DIR . '/class-hook-contents.php' );

/**
 * Load Themify Role Access Control
 * @since 2.6.2
 */
require_once( THEMIFY_DIR . '/class-themify-access-role.php' );

/**
 * Load Themify Theme Metabox
 * @since 2.6.2
 */
function themify_use_theme_metabox( $url ) {
	remove_action( 'site_url', 'themify_builder_plugin_metabox', 20 );

	return $url;
}
add_action( 'site_url', 'themify_use_theme_metabox', 10 );

defined( 'THEMIFY_METABOX_URI' ) || define( 'THEMIFY_METABOX_URI', THEMIFY_URI . '/themify-metabox/' );
defined( 'THEMIFY_METABOX_DIR' ) || define( 'THEMIFY_METABOX_DIR', THEMIFY_DIR . '/themify-metabox/' );
require_once( THEMIFY_DIR . '/themify-metabox/themify-metabox.php' );

// register custom field types only available in the framework
add_action( 'themify_metabox/field/fontawesome', 'themify_meta_field_fontawesome', 10, 1 );
add_action( 'themify_metabox/field/sidebar_visibility', 'themify_meta_field_sidebar_visibility', 10, 1 );
add_action( 'themify_metabox/field/featimgdropdown', 'themify_meta_field_featimgdropdown', 10, 1 );
add_action( 'themify_metabox/field/page_builder', 'themify_meta_field_page_builder', 10, 1 );

require_once( THEMIFY_DIR . '/google-fonts/functions.php' );

/**
 * Show recommended or full Google fonts list
 *
 * @since 2.8.9
 */
function themify_google_fonts_show_full() {
	return 'full' === themify_get( 'setting-webfonts_list' );
}
add_filter( 'themify_google_fonts_full_list', 'themify_google_fonts_show_full' );

/**
 * Filter Google web fonts list based on subset selection from user
 *
 * @since 2.8.9
 */
function themify_filter_google_fonts_subsets( $subsets ) {
	$setting_webfonts_subsets = sanitize_text_field( themify_get( 'setting-webfonts_subsets' ) );
	if ( themify_check( 'setting-webfonts_subsets' ) && '' != $setting_webfonts_subsets ) {
		$user_subsets = explode( ',', str_replace( ' ', '', $setting_webfonts_subsets ) );
	} else {
		$user_subsets = array();
	}

	return array_merge( $subsets, $user_subsets );
}
add_filter( 'themify_google_fonts_subsets', 'themify_filter_google_fonts_subsets' );

/**
 * Set the base image size that img.php will resize thumbnails from
 *
 * @return string
 */
function themify_image_script_source_size( $size ) {
	return themify_get( 'setting-img_php_base_size', 'large' );
}
add_filter( 'themify_image_script_source_size', 'themify_image_script_source_size', 1 );

/**
 * Admin Only code follows
 ******************************************************/
if( is_admin() ){

	/**
	 * Initialize settings page and update permissions.
	 * @since 2.1.8
	 */
	add_action( 'init', 'themify_after_user_is_authenticated' );

	/**
 	* Enqueue jQuery and other scripts
 	*******************************************************/
	add_action( 'admin_enqueue_scripts', 'themify_enqueue_scripts', 12 );

	/**
 	* Ajaxify admin
 	*******************************************************/
	require_once(THEMIFY_DIR . '/themify-wpajax.php');
}

/**
 * In this hook current user is authenticated so we can check for capabilities.
 *
 * @since 2.1.8
 */
function themify_after_user_is_authenticated() {
	if ( current_user_can( 'manage_options' ) ) {

		/**
	 	 * Themify - Admin Menu
	 	 *******************************************************/
		add_action( 'admin_menu', 'themify_admin_nav',1 );
	}
}

/**
 * Clear legacy themify-ajax.php and strange files that might have been uploaded to or directories created in the uploads folder within the theme.
 * @since 1.6.3
 */
function themify_maybe_clear_legacy() {
	if ( ! function_exists( 'WP_Filesystem' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
	}

	WP_Filesystem();
	global $wp_filesystem;

	$flag = 'themify_clear_legacy';
	$clear = get_option( $flag );
	if ( ! isset( $clear ) || ! $clear ) {
		$legacy = THEMIFY_DIR . '/themify-ajax.php';
		if ( $exists = $wp_filesystem->exists( $legacy ) ) {
			$wp_filesystem->delete( $legacy );
		}
		$list = $wp_filesystem->dirlist( THEME_DIR . '/uploads/', true, true );
		if ( is_array( $list ) ) {
			foreach ( $list as $item ) {
				if ( 'd' === $item['type'] ) {
					foreach ( $item['files'] as $subitem ) {
						if ( 'd' === $subitem['type'] ) {
							// There shouldn't be a directory here, let's delete it
							$del_dir = THEME_DIR . '/uploads/' . $item['name'] . '/' . $subitem['name'];
							$wp_filesystem->delete( $del_dir, true );
						} else {
							$extension = pathinfo( $subitem['name'], PATHINFO_EXTENSION );
							if ( ! in_array( $extension, array( 'jpg', 'gif', 'png', 'jpeg', 'bmp' ),true ) ) {
								$del_file = THEME_DIR . '/uploads/' . $item['name'] . '/' . $subitem['name'];
								$wp_filesystem->delete( $del_file );
							}
						}
					}
				} else {
					$extension = pathinfo( $item['name'], PATHINFO_EXTENSION );
					if ( ! in_array( $extension, array( 'jpg', 'gif', 'png', 'jpeg', 'bmp' ),true ) ) {
						$del_file = THEME_DIR . '/uploads/' . $item['name'];
						$wp_filesystem->delete( $del_file );
					}
				}
			}
		}
		update_option( $flag, true );
	}
}
add_action( 'init', 'themify_maybe_clear_legacy', 9 );

/**
 * Change setting name where theme settings are stored.
 * Runs after updater succeeded.
 * @since 1.7.6
 */
function themify_migrate_settings_name() {
	$flag = 'themify_migrate_settings_name';
	$change = get_option( $flag );
	if ( empty( $change )) {
		if ( $themify_data = get_option( wp_get_theme()->display('Name') . '_themify_data' ) ) {
			themify_set_data( $themify_data );
		}
		update_option( $flag, true );
	}
}
add_action( 'after_setup_theme', 'themify_migrate_settings_name', 1 );

/**
 * Function called after a successful update through WP Admin.
 * Code to run ONLY ONCE after update must be added here.
 *
 * @since 1.8.3
 */
function themify_theme_updater_post_install() {
	// Delete option to reset styling behaviour
	delete_option( 'themify_has_styling_data' );

	// Once all tasks have been executed, delete the flag.
	delete_option( 'themify_update_ok_flag' );
}
add_action( 'themify_updater_post_install', 'themify_theme_updater_post_install' );

/**
 * Refresh permalinks to avoid 404 on custom post type fetching.
 * @since 1.9.3
 */
function themify_flush_rewrite_rules_after_manual_update() {
	$flag = 'themify_flush_rewrite_rules_after_manual_update';
	$change = get_option( $flag );
	if (  empty( $change ) ) {
		flush_rewrite_rules();
		update_option( $flag, true );
	}
}
add_action( 'init', 'themify_flush_rewrite_rules_after_manual_update', 99 );

/**
 * After a Builder layout is loaded, adjust some page settings for better page display.
 *
 * @since 2.8.9
 */
function themify_adjust_page_settings_for_layouts( $args ) {
	if( 'custom' === $args['layout_group'] )
		return;
	$post_id = $args['current_builder_id'];
	$post = get_post( $post_id );
	update_post_meta( $post_id, 'content_width', 'full_width' );
	if( $post->post_type === 'page' ) {
		update_post_meta( $post_id, 'page_layout', 'sidebar-none' );
		update_post_meta( $post_id, 'hide_page_title', 'yes' );
	} else {
		update_post_meta( $post_id, 'layout', 'sidebar-none' );
		update_post_meta( $post_id, 'hide_post_title', 'yes' );
	}
}
add_action( 'themify_builder_layout_loaded', 'themify_adjust_page_settings_for_layouts' );
add_action( 'themify_builder_layout_appended', 'themify_adjust_page_settings_for_layouts' );

/**
 * Load themeforest-functions.php file if available
 * Additional functions for the theme from ThemeForest store.
 */
if( file_exists( trailingslashit( get_template_directory() ) . 'themeforest-functions.php' ) ) {
	include( trailingslashit( get_template_directory() ) . 'themeforest-functions.php' );
}

/**
 * Themify Shortcodes
 *
 * @deprecated since 3.1.3
 *
 * These shortcodes are only loaded if the theme was installed before the 3.1.3 update,
 * to provide backward compatibility.
 */
function themify_deprecated_shortcodes_init() {
	if( themify_get_flag( 'deprecate_shortcodes' ) ) {
		return;
	}

	require_once THEMIFY_DIR . '/themify-shortcodes.php';
	require_once THEMIFY_DIR . '/tinymce/class-themify-tinymce.php';

	/**
	 * Flush twitter transient data
	 */
	add_action( 'save_post', 'themify_twitter_flush_transient' );


	if ( ! function_exists( 'themify_shortcode_list' ) ) :
	/**
	 * Return list of Themify shortcodes.
	 *
	 * @since 1.9.4
	 *
	 * @return array Collection of shortcodes as keys and callbacks as values.
	 */
	function themify_shortcode_list() {
		return array(
			'is_logged_in' => 'themify_shortcode',
			'is_guest'     => 'themify_shortcode',
			'button'       => 'themify_shortcode',
			'quote'        => 'themify_shortcode',
			'col'          => 'themify_shortcode',
			'sub_col'      => 'themify_shortcode',
			'img'          => 'themify_shortcode',
			'hr'           => 'themify_shortcode',
			'map'          => 'themify_shortcode',
			'list_posts'   => 'themify_shortcode_list_posts',
			'flickr'       => 'themify_shortcode_flickr',
			'twitter'      => 'themify_shortcode_twitter',
			'box'          => 'themify_shortcode_box',
			'post_slider'  => 'themify_shortcode_post_slider',
			'slider'       => 'themify_shortcode_slider',
			'slide'        => 'themify_shortcode_slide',
			'author_box'   => 'themify_shortcode_author_box',
			'icon'         => 'themify_shortcode_icon',
			'list'         => 'themify_shortcode_icon_list',
		);
	}
	endif;

	/**
	 * Add Themify Shortcodes, an unprefixed version and a prefixed version.
	 */
	foreach( themify_shortcode_list() as $themify_sc => $themify_sc_callback) {
		add_shortcode( $themify_sc, $themify_sc_callback );
		add_shortcode( 'themify_' . $themify_sc, $themify_sc_callback );
	}
	// Backwards compatibility
	add_shortcode( 'themify_video', 'wp_video_shortcode' );
}
add_action( 'after_setup_theme', 'themify_deprecated_shortcodes_init' );

/**
 * Setup procedure to load theme features packed in Themify framework
 *
 * @since 3.2.0
 */
function themify_load_theme_features() {
	/* load megamenu feature */
	if ( current_theme_supports( 'themify-mega-menu' ) ) {
		include( THEMIFY_DIR . '/megamenu/class-mega-menu.php' );
	}

	/* check if Google fonts are disabled */
	if ( ! defined( 'THEMIFY_GOOGLE_FONTS' ) && themify_get( 'setting-webfonts_list' ) === 'disabled' ) {
		define( 'THEMIFY_GOOGLE_FONTS', false );
	}

	if ( current_theme_supports( 'themify-exclude-theme-from-wp-update' ) ) {
		add_filter( 'http_request_args', 'themify_hide_themes', 10, 2 );
	}
}
add_action( 'after_setup_theme', 'themify_load_theme_features', 11 );

if ( is_admin() ) {
	require_once THEMIFY_DIR . '/class-tgm-plugin-activation.php';
}

/**
 * List of recommended and/or required plugins
 *
 * @since 4.6.0
 * @return array
 */
function themify_tgmpa_plugins() {
	static $plugins;
	if ( $plugins === null ) {
		$plugins = array(
			array(
				'name'               => __( ' Themify Updater', 'themify' ),
				'slug'               => 'themify-updater',
				'source'             => 'https://themify.me/files/themify-updater/themify-updater.zip',
				'required'           => false,
				'version'            => '1.1.0',
				'force_activation'   => false,
				'force_deactivation' => false,
			),
			array(
				'name'               => __( 'HubSpot All-In-One Marketing', 'themify' ),
				'slug'               => 'leadin',
				'required'           => false,
			),
			array(
				'name'               => __( 'Contact Form by WPForms', 'themify' ),
				'slug'               => 'wpforms-lite',
				'required'           => false,
			),
			array(
				'name'               => __( 'WordPress Share Buttons Plugin – AddThis', 'themify' ),
				'slug'               => 'addthis',
				'required'           => false,
			),
			array(
				'name'               => __( 'Widget Shortcode', 'themify' ),
				'slug'               => 'widget-shortcode',
				'required'           => false,
			),
		);
		$plugins = apply_filters( 'themify_theme_required_plugins', $plugins );
	}

	return $plugins;
}

function themify_register_required_plugins() {
	$plugins = themify_tgmpa_plugins();

	/**
	 * Array of configuration settings. Amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * leave the strings uncommented.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */
	$config = array(
		'default_path' => '',                      // Default absolute path to pre-packaged plugins.
		'menu'         => 'themify-install-plugins', // Menu slug.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
		'strings'      => array(
			'page_title'                      => __( 'Install Required Plugins', 'themify' ),
			'menu_title'                      => __( 'Install Plugins', 'themify' ),
			'installing'                      => __( 'Installing Plugin: %s', 'themify' ), // %s = plugin name.
			'oops'                            => __( 'Something went wrong with the plugin API.', 'themify' ),
			'notice_can_install_required'     => _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'themify' ), // %1$s = plugin name(s).
			'notice_can_install_recommended'  => _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'themify' ), // %1$s = plugin name(s).
			'notice_cannot_install'           => _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'themify' ), // %1$s = plugin name(s).
			'notice_can_activate_required'    => _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'themify' ), // %1$s = plugin name(s).
			'notice_can_activate_recommended' => _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'themify' ), // %1$s = plugin name(s).
			'notice_cannot_activate'          => _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'themify' ), // %1$s = plugin name(s).
			'notice_ask_to_update'            => _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'themify' ), // %1$s = plugin name(s).
			'notice_cannot_update'            => _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'themify' ), // %1$s = plugin name(s).
			'activate_link'                   => _n_noop( 'Begin activating plugin', 'Begin activating plugins', 'themify' ),
			'return'                          => __( 'Return to Required Plugins Installer', 'themify' ),
			'plugin_activated'                => __( 'Plugin activated successfully.', 'themify' ),
			'complete'                        => __( 'All plugins installed and activated successfully. %s', 'themify' ), // %s = dashboard link.
			'nag_type'                        => 'updated' // Determines admin notice type - can only be 'updated', 'update-nag' or 'error'.
		)
	);

	tgmpa( $plugins, $config );
	add_action( 'admin_menu', 'themify_required_plugins_admin_menu', 11 );

	/* prevent duplicate menu item showing from various themes */
	remove_action( 'admin_menu', 'themify_theme_required_plugins_admin_menu', 11 );
}
add_action( 'tgmpa_register', 'themify_register_required_plugins', 11 );

/**
 * Before TGMPA shows admin_notices, remove non-essential plugins registered by Themify.
 * This prevents various notice messages from showing.
 *
 * @since 4.6.0
 */
function themify_tgmpa_before_notices() {
	$GLOBALS['tf_tgmpa'] = $GLOBALS['tgmpa']->plugins; // backup copy of plugins list to be restored later
	$themify_plugins = wp_list_pluck( themify_tgmpa_plugins(), 'slug' );
	foreach ( $themify_plugins as $slug ) {
		if ( isset( $GLOBALS['tgmpa']->plugins[ $slug ] ) ) {
			if ( ! ( isset( $GLOBALS['tgmpa']->plugins[ $slug ]['required'] ) ) || ! $GLOBALS['tgmpa']->plugins[ $slug ]['required'] ) {
				unset( $GLOBALS['tgmpa']->plugins[ $slug ] );
			}
		}
	}
}
add_action( 'admin_notices', 'themify_tgmpa_before_notices', 9 );

/**
 * Restore changes made in themify_tgmpa_before_notices()
 *
 * @since 4.6.0
 */
function themify_tgmpa_after_notices() {
	$GLOBALS['tgmpa']->plugins = $GLOBALS['tf_tgmpa'];
	unset( $GLOBALS['tf_tgmpa'] );
}
add_action( 'admin_notices', 'themify_tgmpa_after_notices', 11 );


/**
 * Relocate the tgmpa admin menu under Themify
 *
 * @since 1.0.0
 */
function themify_required_plugins_admin_menu() {
	// Make sure privileges are correct to see the page
	if ( ! current_user_can( 'install_plugins' ) ) {
		return;
	}

	TGM_Plugin_Activation::get_instance()->populate_file_path();

	foreach ( TGM_Plugin_Activation::get_instance()->plugins as $plugin ) {
		if ( ! is_plugin_active( $plugin['file_path'] ) ) {
			add_submenu_page( 'themify', __( 'Install Plugins', 'themify' ), __( 'Install Plugins', 'themify' ), 'manage_options', 'themify-install-plugins', array( TGM_Plugin_Activation::get_instance(), 'install_plugins_page' ) );
			break;
		}
	}
}

/**
 * Fix issue with tgmpa and WP multisite
 *
 * @since 1.0.0
 */
function themify_tgmpa_mu_fix( $links ) {
	if( is_multisite() ) {
		$links['install'] = '';
		$links['update'] = '';
	}

	return $links;
}
add_filter( 'tgmpa_notice_action_links', 'themify_tgmpa_mu_fix' );

/**
 * Hide plugin activation link on WP Multisite
 */
function themify_tgmpa_mu_hide_activate_link() {
	global $hook_suffix;

	if ( $hook_suffix === 'appearance_page_themify-install-plugins' && is_multisite() ) {
		echo '<style>.plugins .row-actions { display: none !important; }</style>';
	}
}
add_filter( 'admin_head', 'themify_tgmpa_mu_hide_activate_link' );
