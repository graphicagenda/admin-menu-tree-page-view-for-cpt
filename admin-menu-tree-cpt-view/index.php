<?php
/*
Plugin Name: Admin Menu Tree *CPT* View
Plugin URI: http://eskapism.se/code-playground/admin-menu-tree-page-view/
Description: Adds a tree of all your pages or custom posts. Use drag & drop to reorder your pages, and edit, view, add, and search your pages.
Version: 0.6
Author: Pär Thernström
Author URI: http://eskapism.se/
License: GPL2
*/

/*  Copyright 2010  Pär Thernström (email: par.thernstrom@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
Admin Menu Tree Page View
admin-menu-tree-page-view
*/
add_action("admin_head", "admin_menu_tree_*cpt*_view_admin_head");
add_action('admin_menu', 'admin_menu_tree_*cpt*_view_admin_menu');
add_action("admin_init", "admin_menu_tree_*cpt*_view_admin_init");
add_action('wp_ajax_admin_menu_tree_page_view_add_page', 'admin_menu_tree_*cpt*_view_add_page');

function admin_menu_tree_*cpt*_view_admin_init() {

	define( "admin_menu_tree_*cpt*_view_VERSION", "0.6" );
	define( "admin_menu_tree_*cpt*_view_URL", WP_PLUGIN_URL . '/admin-menu-tree-page-view/' );

	wp_enqueue_style("admin_menu_tree_*cpt*_view_styles", admin_menu_tree_page_view_URL . "styles.css", false, admin_menu_tree_page_view_VERSION);
	/*wp_enqueue_script("jquery.highlight", admin_menu_tree_page_view_URL . "jquery.highlight.js", array("jquery"));
	wp_enqueue_script("admin_menu_tree_*cpt*_view", admin_menu_tree_page_view_URL . "scripts.js", array("jquery"));
*/
	$oLocale = array(
		"Edit" => __("Edit", 'admin-menu-tree-page-view'),
		"View" => __("View", 'admin-menu-tree-page-view'),
		"Add_new_page_here" => __("Add new page here", 'admin-menu-tree-page-view'),
		"Add_new_page_inside" => __("Add new page inside", 'admin-menu-tree-page-view'),
		"Untitled" => __("Untitled", 'admin-menu-tree-page-view'),
	);
	wp_localize_script( "admin_menu_tree_*cpt*_view", 'amtpv_l10n', $oLocale);
}

function admin_menu_tree_*cpt*_view_admin_head() {

}

function admin_menu_tree_*cpt*_view_get_pages($args) {

	#$pages = get_pages($args);

	$defaults = array(
    	"post_type" => "*cpt*",
		"parent" => "0",
		"post_parent" => "0",
		"numberposts" => "-1",
		"orderby" => "title",
		"order" => "ASC",
		"post_status" => "any"
	);
	$args = wp_parse_args( $args, $defaults );

	$pages = get_posts($args);
	$output = "";
	foreach ($pages as $one_page) {
		$edit_link = get_edit_post_link($one_page->ID);
		$title = get_the_title($one_page->ID);
		$class = "";
		if (isset($_GET["action"]) && $_GET["action"] == "edit" && isset($_GET["post"]) && $_GET["post"] == $one_page->ID) {
			$class = "current";
		}
		$status_span = "";
		if ($one_page->post_password) {
			$status_span .= "<span class='admin-menu-tree-page-view-protected'></span>";
		}
		if ($one_page->post_status != "publish") {
			$status_span .= "<span class='admin-menu-tree-page-view-status admin-menu-tree-page-view-status-{$one_page->post_status}'>".__(ucfirst($one_page->post_status))."</span>";
		}

		$output .= "<li class='$class'>";
		$output .= "<a href='$edit_link'>$status_span";
		$output .= $title;

		
		// add the view link, hidden, used in popup
		$permalink = get_permalink($one_page->ID);
		$output .= "<span class='admin-menu-tree-page-view-view-link'>$permalink</span>";
		
		$output .= "<span class='admin-menu-tree-page-view-edit'></span>";

		$output .= "</a>";

		// now fetch child articles
		#print_r($one_page);
		$args_childs = $args;
		$args_childs["parent"] = $one_page->ID;
		$args_childs["post_parent"] = $one_page->ID;
		$args_childs["child_of"] = $one_page->ID;
		#echo "<pre>";print_r($args_childs);
		$output .= admin_menu_tree_*cpt*_view_get_pages($args_childs);
		
		$output .= "</li>";
	}
	
	// if this is a child listing, add ul
	if (isset($args["child_of"]) && $args["child_of"]) {
		$output = "<ul class='admin-menu-tree-page-tree_childs'>$output</ul>";
	}
	
	return $output;
}

function admin_menu_tree_*cpt*_view_admin_menu() {

	load_plugin_textdomain('admin-menu-tree-page-view', false, "/admin-menu-tree-page-view/languages");

	// add main menu
	#add_menu_page( "title", "Simple Menu Pages", "edit_pages", "admin-menu-tree-page-tree_main", "bonnyFunction", null, 5);

	// end link that is written automatically by WP, and begin ul
	$output = "
		</a>
		<ul class='admin-menu-tree-page-tree'>
		<li class='admin-menu-tree-page-tree_headline'>" . __("Pages", 'admin-menu-tree-page-view') . "</li>
		<li class='admin-menu-tree-page-filter'>
			<label>".__("Search", 'admin-menu-tree-page-view')."</label>
			<input type='text' class='' />
			<div class='admin-menu-tree-page-filter-reset' title='".__("Reset search and show all pages", 'admin-menu-tree-page-view')."'></div>
		</li>
		";

	// get root items
	$args = array(
		"echo" => 0,
		"sort_order" => "ASC",
		"sort_column" => "menu_order",
		"parent" => 0
	);

	$output .= admin_menu_tree_*cpt*_view_get_pages($args);
	
	// end our ul and add the a-tag that wp automatically will close
	$output .= "
		</ul>
		<a href='#'>
	";

	// add subitems to main menu
	add_submenu_page("edit.php?post_type=*cpt*", "Admin Menu Tree Page View", $output, "edit_pages", "admin-menu-tree-page-tree", "admin_menu_tree_*cpt*_page");

}

function admin_menu_tree_*cpt*_page() {
	?>
	
	<h2>Admin Menu Tree Page View</h2>
	<p>Nothing to see here. Move along! :)</p>
	
	<?php
}



/**
 * Code from plugin CMS Tree Page View
 * http://wordpress.org/extend/plugins/cms-tree-page-view/
 * Used with permission! :)
 */
function admin_menu_tree_*cpt*_view_add_page() {

	global $wpdb;

	/*
	(
	[action] => cms_tpv_add_page 
	[pageID] => cms-tpv-1318
	type
	)
	*/
	$type = $_POST["type"];
	$pageID = (int) $_POST["pageID"];
	#$pageID = str_replace("cms-tpv-", "", $pageID);
	$page_title = trim($_POST["page_title"]);
	$post_type = $_POST["post_type"];
	$wpml_lang = $_POST["wpml_lang"];
	if (!$page_title) { $page_title = __("New page", 'cms-tree-page-view'); }

	$ref_post = get_post($pageID);

	if ("after" == $type) {

		/*
			add page under/below ref_post
		*/

		// update menu_order of all pages below our page
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = menu_order+2 WHERE post_parent = %d AND menu_order >= %d AND id <> %d ", $ref_post->post_parent, $ref_post->menu_order, $ref_post->ID ) );		
		
		// create a new page and then goto it
		$post_new = array();
		$post_new["menu_order"] = $ref_post->menu_order+1;
		$post_new["post_parent"] = $ref_post->post_parent;
		$post_new["post_type"] = "page";
		$post_new["post_status"] = "draft";
		$post_new["post_title"] = $page_title;
		$post_new["post_content"] = "";
		$post_new["post_type"] = $post_type;
		$newPostID = wp_insert_post($post_new);

	} else if ( "inside" == $type ) {

		/*
			add page inside ref_post
		*/

		// update menu_order, so our new post is the only one with order 0
		$wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET menu_order = menu_order+1 WHERE post_parent = %d", $ref_post->ID) );		

		$post_new = array();
		$post_new["menu_order"] = 0;
		$post_new["post_parent"] = $ref_post->ID;
		$post_new["post_type"] = "*cpt*";
		$post_new["post_status"] = "draft";
		$post_new["post_title"] = $page_title;
		$post_new["post_content"] = "";
		$post_new["post_type"] = $post_type;
		$newPostID = wp_insert_post($post_new);

	}
	
	if ($newPostID) {
		// return editlink for the newly created page
		$editLink = get_edit_post_link($newPostID, '');
		if ($wpml_lang) {
			$editLink = add_query_arg("lang", $wpml_lang, $editLink);
		}
		echo $editLink;
	} else {
		// fail, tell js
		echo "0";
	}
	#print_r($post_new);
	exit;
}
