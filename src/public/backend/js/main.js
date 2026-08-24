(function () {
	"use strict";

	var treeviewMenu = $('.app-menu');

	// Toggle Sidebar & Persist state to localStorage
	$('[data-toggle="sidebar"]').click(function(event) {
		event.preventDefault();
		$('.app').toggleClass('sidenav-toggled');
		try {
			if (window.innerWidth >= 768) {
				localStorage.setItem('sidebar_collapsed', $('.app').hasClass('sidenav-toggled') ? '1' : '0');
			}
		} catch (e) {}
	});

	// Apply persisted sidebar state on initial load
	try {
		if (localStorage.getItem('sidebar_collapsed') === '1' && window.innerWidth >= 768) {
			$('.app').addClass('sidenav-toggled');
		}
	} catch (e) {}

	// Smart Dynamic Flyout Positioning for Mini Sidebar
	$(document).on('mouseenter', '.sidebar-mini.sidenav-toggled .app-sidebar .app-menu > li', function() {
		if (window.innerWidth < 768) return;
		var $li = $(this);
		var rect = this.getBoundingClientRect();
		var isTreeview = $li.hasClass('treeview');
		var $label = $li.find('> .app-menu__item > .app-menu__label');
		var $menu = $li.find('> .treeview-menu');

		// Temporarily suppress native browser title popup while hovering mini-sidebar
		var $link = $li.find('> .app-menu__item');
		if ($link.attr('title')) {
			$link.data('saved-title', $link.attr('title')).removeAttr('title');
		}

		if (isTreeview && $menu.length) {
			var topPos = rect.top;
			var menuHeight = $menu.outerHeight() || 150;

			// Prevent overflowing below screen viewport
			if (topPos + menuHeight > window.innerHeight) {
				topPos = Math.max(10, window.innerHeight - menuHeight - 10);
			}

			$menu.css({
				'top': topPos + 'px',
				'left': '50px'
			});
		} else if ($label.length) {
			$label.css({
				'top': rect.top + 'px',
				'left': '50px'
			});
		}
	}).on('mouseleave', '.sidebar-mini.sidenav-toggled .app-sidebar .app-menu > li', function() {
		// Restore title on mouseleave
		var $link = $(this).find('> .app-menu__item');
		if ($link.data('saved-title')) {
			$link.attr('title', $link.data('saved-title'));
		}
		// Clean up inline styles
		$(this).find('> .app-menu__item > .app-menu__label, > .treeview-menu').removeAttr('style');
	});

	// Hover Flyout for Sidebar List Header in Mini Mode
	$(document).on('mouseenter', '.sidebar-mini.sidenav-toggled .app-sidebar .sidebar-list-header', function() {
		if (window.innerWidth < 768) return;
		var li = this;
		var rect = li.getBoundingClientRect();
		var $label = $(li).find('.sidebar-header-label');

		var $el = $(li);
		if ($el.attr('title')) {
			$el.data('saved-title', $el.attr('title')).removeAttr('title');
		}

		if ($label.length) {
			$label.css({
				'top': rect.top + 'px',
				'left': '50px'
			});
		}
	}).on('mouseleave', '.sidebar-mini.sidenav-toggled .app-sidebar .sidebar-list-header', function() {
		var $el = $(this);
		if ($el.data('saved-title')) {
			$el.attr('title', $el.data('saved-title'));
		}
		$(this).find('.sidebar-header-label').removeAttr('style');
	});

	// Activate sidebar treeview toggle
	$("[data-toggle='treeview']").click(function(event) {
		event.preventDefault();
		if(!$(this).parent().hasClass('is-expanded')) {
			treeviewMenu.find("[data-toggle='treeview']").parent().removeClass('is-expanded');
		}
		$(this).parent().toggleClass('is-expanded');
	});

	// Set initial active toggle
	$("[data-toggle='treeview.'].is-expanded").parent().toggleClass('is-expanded');

	//Activate bootstrip tooltips
	$("[data-toggle='tooltip']").tooltip();

})();
