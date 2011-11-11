
jQuery(function($) {

	setTimeout(function() {
		jQuery("#toplevel_page_admin-menu-tree-page-tree_main").addClass("wp-menu-open");
	}, 100);
	

	// show menu when menu icon is clicked
	jQuery(".admin-menu-tree-page-view-edit").click(function() {
		
		var $this = $(this);
						
		// check if this tree has a menu div defined
		var wpsubmenu = $(this).closest("div.wp-submenu");
		if (wpsubmenu.length == 1) {
			
			var div_popup = wpsubmenu.find(".admin-menu-tree-page-view-popup");
			var do_show = true;
			if (div_popup.length == 0) {
				// no menu div yet, create it
				var html = "";
				html += "<div class='admin-menu-tree-page-view-popup'><span class='admin-menu-tree-page-view-popup-arrow'></span><span class='admin-menu-tree-page-view-popup-page'></span>";
				html += "<ul>";
				html += "<li class='admin-menu-tree-page-view-popup-edit'><a href=''>"+amtpv_l10n.Edit+"</a></li>";
				html += "<li class='admin-menu-tree-page-view-popup-view'><a href=''>"+amtpv_l10n.View+"</a></li>";
				html += "<li class='admin-menu-tree-page-view-popup-add-here'><a href=''>"+amtpv_l10n.Add_new_page_here+"</a></li>";
				html += "<li class='admin-menu-tree-page-view-popup-add-inside'><a href=''>"+amtpv_l10n.Add_new_page_inside+"</a></li>";
				html += "</ul></div>";
				var div_popup = $(html).appendTo(wpsubmenu);
				div_popup.show(); // must do this..
				div_popup.hide(); // ..or fade does not work first time
			} else {
				if (div_popup.is(":visible")) {
					//do_show = false;
				}
			}
			
			var a = $this.closest("a");
			var link_text = a.text();
			if (div_popup.find(".admin-menu-tree-page-view-popup-page").text() == link_text) {
				do_show = false;
			}
			div_popup.find(".admin-menu-tree-page-view-popup-page").text( link_text );
			var offset = $this.offset();
			offset.top = (offset.top-3);
			offset.left = (offset.left-3);

			// store post_id
			var post_id = a.attr("href").match(/post=([\w]+)/);
			post_id = post_id[1];
			div_popup.data("admin-menu-tree-page-view-current-post-id", post_id);

			// setup edit and view links
			var edit_link = "post.php?post="+post_id+"&action=edit";
			div_popup.find(".admin-menu-tree-page-view-popup-edit a").attr("href", edit_link);
			
			// view link, this is probably not such a safe way to this this. but let's try! :)
			var view_link = $this.closest("li").find(".admin-menu-tree-page-view-view-link").text();
			div_popup.find(".admin-menu-tree-page-view-popup-view a").attr("href", view_link);
			
			if (do_show) {
				//console.log("show");
				div_popup.fadeIn("fast");
			} else {
				// same popup, so close it
				//console.log("hide");
				div_popup.fadeOut("fast");
				div_popup.find(".admin-menu-tree-page-view-popup-page").text("");
			}
			
			div_popup.offset( offset ); // must be last or position gets wrong somehow
			
		}
		
		return false;
	});
	
	// hide menu
	$(".admin-menu-tree-page-view-popup-arrow").live("click", function() {
		$(this).closest(".admin-menu-tree-page-view-popup").fadeOut("fast");
		return false;
	});
	
	// add page
	$(".admin-menu-tree-page-view-popup-add-here, .admin-menu-tree-page-view-popup-add-inside").live("click", function() {
		var div_popup = $(this).closest(".admin-menu-tree-page-view-popup");
		var post_id = div_popup.data("admin-menu-tree-page-view-current-post-id");
		
		var type = "after";
		if ($(this).hasClass("admin-menu-tree-page-view-popup-add-inside")) {
			type = "inside";
		}
		
		var page_title = prompt("Enter name of new page", amtpv_l10n.Untitled);
		if (page_title) {
			
			var data = {
				"action": 'admin_menu_tree_page_view_add_page',
				"pageID": post_id,
				"type": type,
				"page_title": page_title,
				"post_type": "page"
			};
			jQuery.post(ajaxurl, data, function(response) {
				if (response != "0") {
					document.location = response;
				}
			});
			return false;
		
		} else {
			return false;
		}
		
	});
	
	// search/filter pages
	$(".admin-menu-tree-page-filter input").keyup(function(e) {
		var ul = $(this).closest(".admin-menu-tree-page-tree");
		ul.find("li").hide();
		ul.find(".admin-menu-tree-page-tree_headline,.admin-menu-tree-page-filter").show();
		var s = $(this).val();
		var selector = "li:AminMenuTreePageContains('"+s+"')";
		var hits = ul.find(selector);
		if (hits.length > 0 || s != "") {
			ul.find(".admin-menu-tree-page-filter-reset").fadeIn("fast");
			ul.unhighlight();
		}
		if (s == "") {
			ul.find(".admin-menu-tree-page-filter-reset").fadeOut("fast");
		}
		ul.highlight(s);
		hits.show();
	});

	// clear/reset filter and show all pages again
	$(".admin-menu-tree-page-filter-reset").click(function() {
		var $t = $(this);
		var ul = $t.closest(".admin-menu-tree-page-tree");
		ul.find("li").fadeIn("fast");
		$t.fadeOut("fast");
		$t.closest(".admin-menu-tree-page-filter").find("input").val("").focus();
		ul.unhighlight();
	});
	
	// label = hide in and focus input
	$(".admin-menu-tree-page-filter label, .admin-menu-tree-page-filter input").click(function() {
		var $t = $(this);
		$t.closest(".admin-menu-tree-page-filter").find("label").hide();
		$t.closest(".admin-menu-tree-page-filter").find("input").focus();
	});

});
// http://stackoverflow.com/questions/187537/is-there-a-case-insensitive-jquery-contains-selector
jQuery.expr[':'].AminMenuTreePageContains = function(a,i,m){
     return (a.textContent || a.innerText || "").toLowerCase().indexOf(m[3].toLowerCase())>=0;
};