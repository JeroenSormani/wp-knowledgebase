<?php
/*
	Plugin Name: WP Knowledgebase
	Plugin URI: http://wordpress.org/plugins/wp-knowledgebase
	Description: Simple and flexible knowledgebase plugin for WordPress
	Author: Enigma Plugins
	Version: 1.1.4
	Author URI: http://enigmaplugins.com
	Requires at least: 2.7
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'KBE_PLUGIN_VERSION', '1.1.4' );

//=========> Create language folder
function kbe_plugin_load_textdomain() {
	load_plugin_textdomain( 'kbe', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'kbe_plugin_load_textdomain' );

//  Require File kbe_articles.php
require 'includes/kbe-articles.php';
require 'includes/kbe-template-functions.php';
require 'includes/kbe-core-functions.php';

//  Require Category Widget file
require 'includes/widgets/kbe-widget-category.php';
//  Require Articles Widget file
require 'includes/widgets/kbe-widget-article.php';
//  Require Search Articles Widget file
require 'includes/widgets/kbe-widget-search.php';
//  Require Tags Widget file
require 'includes/widgets/kbe-widget-tags.php';


// Include admin file(s)
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	require 'includes/admin/kbe-admin-functions.php';
}

//=========> Create Hooks for WP Knowledgebase
function wp_kbe_hooks( $kbe_networkwide ) {
	kbe_articles();
	kbe_taxonomies();
	kbe_custom_tags();
	flush_rewrite_rules();

	global $wpdb;
	/*Create "term_order" Field in "wp_terms" Table for sortable order*/
	$term_order_qry = $wpdb->query( "SHOW COLUMNS FROM $wpdb->terms LIKE 'terms_order'" );
	if ( $term_order_qry == 0 ) {
		$wpdb->query( "ALTER TABLE $wpdb->terms ADD `terms_order` INT(4) NULL DEFAULT '0'" );
	}

	$kbe_optSlugSql = $wpdb->get_results( "Select * From {$wpdb->options} Where option_name like '%kbe_plugin_slug%'" );

	if ( ! $kbe_optSlugSql ) {
		add_option( 'kbe_plugin_slug', 'knowledgebase', '', 'yes' );
	}

	$kbe_optPageSql = $wpdb->get_results( "Select * From {$wpdb->options} Where option_name like '%kbe_article_qty%'" );

	if ( ! $kbe_optPageSql ) {
		add_option( 'kbe_article_qty', '5', '', 'yes' );
	}

	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		// check if it is a network activation - if so, run the activation function for each blog id
		if ( $kbe_networkwide ) {
			$kbe_old_blog = $wpdb->blogid;
			// Get all blog ids
			$kbe_blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $kbe_blog_ids as $kbe_blog_id ) {
				switch_to_blog( $kbe_blog_id );
			}
			switch_to_blog( $kbe_old_blog );
			return;
		}
	}

	// serialize settings data
	$kbe_settings = get_option( 'kbe_settings' );

	$kbe_article_qty         = get_option( 'kbe_article_qty' );
	$kbe_plugin_slug         = get_option( 'kbe_plugin_slug' );
	$kbe_search_setting      = get_option( 'kbe_search_setting' );
	$kbe_breadcrumbs_setting = get_option( 'kbe_breadcrumbs_setting' );
	$kbe_sidebar_home        = get_option( 'kbe_sidebar_home' );
	$kbe_sidebar_inner       = get_option( 'kbe_sidebar_inner' );
	$kbe_comments_setting    = get_option( 'kbe_comments_setting' );
	$kbe_bgcolor             = get_option( 'kbe_bgcolor' );

	if ( $kbe_article_qty || $kbe_plugin_slug || $kbe_search_setting || $kbe_breadcrumbs_setting || $kbe_sidebar_home
		|| $kbe_sidebar_inner || $kbe_comments_setting || $kbe_bgcolor ) {
		$kbe_settings_arr = array(
			'kbe_plugin_slug'         => $kbe_plugin_slug,
			'kbe_article_qty'         => $kbe_article_qty,
			'kbe_search_setting'      => $kbe_search_setting,
			'kbe_breadcrumbs_setting' => $kbe_breadcrumbs_setting,
			'kbe_sidebar_home'        => $kbe_sidebar_home,
			'kbe_sidebar_inner'       => $kbe_sidebar_inner,
			'kbe_comments_setting'    => $kbe_comments_setting,
			'kbe_bgcolor'             => $kbe_bgcolor,
		);
		$kbe_settings_ser = serialize( $kbe_settings_arr );

		add_option( 'kbe_settings', $kbe_settings_ser, '', 'yes' );

		delete_option( 'kbe_article_qty' );
		delete_option( 'kbe_plugin_slug' );
		delete_option( 'kbe_search_setting' );
		delete_option( 'kbe_breadcrumbs_setting' );
		delete_option( 'kbe_sidebar_home' );
		delete_option( 'kbe_sidebar_inner' );
		delete_option( 'kbe_comments_setting' );
		delete_option( 'kbe_bgcolor' );
	}
}

register_activation_hook( __FILE__, 'wp_kbe_hooks' );

//=========> Define plugin path
define( 'WP_KNOWLEDGEBASE', plugin_dir_url( __FILE__ ) );

//  define options values
$kbe_settings = get_option( 'kbe_settings' );
if ( isset( $kbe_settings['kbe_article_qty'] ) ) {
	define( 'KBE_ARTICLE_QTY', $kbe_settings['kbe_article_qty'] );
}
define( 'KBE_PLUGIN_SLUG', isset( $kbe_settings['kbe_plugin_slug'] ) ? $kbe_settings['kbe_plugin_slug'] : 'knowledgebase' );

if ( isset( $kbe_settings['kbe_search_setting'] ) ) {
	define( 'KBE_SEARCH_SETTING', $kbe_settings['kbe_search_setting'] );
}
if ( isset( $kbe_settings['kbe_breadcrumbs_setting'] ) ) {
	define( 'KBE_BREADCRUMBS_SETTING', $kbe_settings['kbe_breadcrumbs_setting'] );
}
if ( isset( $kbe_settings['kbe_sidebar_home'] ) ) {
	define( 'KBE_SIDEBAR_HOME', $kbe_settings['kbe_sidebar_home'] );
}
if ( isset( $kbe_settings['kbe_sidebar_inner'] ) ) {
	define( 'KBE_SIDEBAR_INNER', $kbe_settings['kbe_sidebar_inner'] );
}
if ( isset( $kbe_settings['kbe_comments_setting'] ) ) {
	define( 'KBE_COMMENT_SETTING', $kbe_settings['kbe_comments_setting'] );
}
if ( isset( $kbe_settings['kbe_bgcolor'] ) ) {
	define( 'KBE_BG_COLOR', $kbe_settings['kbe_bgcolor'] );
}
define( 'KBE_LINK_STRUCTURE', get_option( 'permalink_structure' ) );
define( 'KBE_POST_TYPE', 'kbe_knowledgebase' );
define( 'KBE_POST_TAXONOMY', 'kbe_taxonomy' );
define( 'KBE_POST_TAGS', 'kbe_tags' );

//=========> Get Knowledgebase title
global $wpdb;
$getSql = $wpdb->get_results( "Select ID From $wpdb->posts Where post_content Like '%[kbe_knowledgebase]%' And post_type <> 'revision'" );

foreach ( $getSql as $getRow ) {
	$pageId = $getRow->ID;
}
define( 'KBE_PAGE_TITLE', $pageId );



require 'includes/migrations/class-abstract-migration.php';
require 'includes/migrations/migration-install.php';
