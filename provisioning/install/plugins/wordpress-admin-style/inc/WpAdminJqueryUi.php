<?php
/**
 * WpAdminJqueryUi
 *
 * @package WP2Static
 */

// avoid direct calls to this file, because now WP core and framework has been used.
! defined( 'ABSPATH' ) and exit;

add_action(
	'init',
	array( WpAdminJqueryUi::get_instance(), 'plugin_setup' )
);

class WpAdminJqueryUi {

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook admin_init
	 * @since   05/02/2013
	 */
	public static function get_instance() {

		static $instance;

		if ( NULL === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook  admin_init
	 * @since    05/02/2013
	 * @return   void
	 */
	public function plugin_setup() {

		add_action( 'admin_menu', array( $this, 'register_submenu' ) );

	}

	/**
	 * Constructor.
	 * Intentionally left empty and public.
	 *
	 * @see    plugin_setup()
	 * @since  05/02/2013
	 */
	public function __construct() {
	}

	public function register_submenu() {

		$hook = add_submenu_page(
			'WordPress_Admin_Style',
			__( 'jQuery UI Demo' ),
			__( 'jQuery UI' ),
			'manage_options',
			'wp-admin-jquery-ui',
			array( $this, 'get_jquery_ui_demo' )
		);
		add_action( 'load-' . $hook, array( $this, 'register_scripts' ) );
	}

	public function get_jquery_ui_demo() {

		?>
		<div class="wrap">
			<h1>jQuery UI Demo</h1>

			<h2><span><?php _e( 'MiniMenu', 'WpAdminStyle' ); ?></span></h2>
			<div class="inside">

				<table class="widefat" cellspacing="0">
					<tr>
						<td class="row-title"><a href="#accordion"><?php _e( 'Accordion', 'WpAdminStyle' ); ?></a></td>
					</tr>
					<tr class="alternate">
						<td class="row-title"><a href="#tabs"><?php _e( 'Tabs', 'WpAdminStyle' ); ?></a></td>
					</tr>
					<tr>
						<td class="row-title"><a href="#anker_dialog"><?php _e( 'Dialog', 'WpAdminStyle' ); ?></a></td>
					</tr>
					<tr class="alternate">
						<td class="row-title"><a href="#overlay_shadow"><?php _e( 'Overlay and Shadow Classes',
						                                                          'WpAdminStyle' ); ?></a></td>
					</tr>
					<tr>
						<td class="row-title"><a href="#icons"><?php _e( 'Framework Icons', 'WpAdminStyle' ); ?></a>
						</td>
					</tr>
					<tr class="alternate">
						<td class="row-title"><a href="#slider"><?php _e( 'Slider', 'WpAdminStyle' ); ?></a></td>
					</tr>
					<tr>
						<td class="row-title"><a href="#datepicker"><?php _e( 'Datepicker', 'WpAdminStyle' ); ?></a>
						</td>
					</tr>
					<tr class="alternate">
						<td class="row-title"><a href="#anker_autocomplete"><?php _e( 'Autocomplete',
						                                                              'WpAdminStyle' ); ?></a></td>
					</tr>
					<tr>
						<td class="row-title"><a href="#progressbar"><?php _e( 'Progressbar', 'WpAdminStyle' ); ?></a>
						</td>
					</tr>
					<tr class="alternate">
						<td class="row-title"><a href="#anker_highlight"><?php _e( 'Highlight / Error',
						                                                           'WpAdminStyle' ); ?></a></td>
					</tr>
				</table>
			
			</div> <!-- .inside -->

			<!-- Accordion -->
			<h2>Accordion</h2>
			<div id="accordion">
				<div>
					<h3><a href="#">First</a></h3>
					<div>Lorem ipsum dolor sit amet. <a
							href="#">Lorem ipsum</a> dolor sit amet. Lorem ipsum dolor sit amet.
					</div>
				</div>
				<div>
					<h3><a href="#">Second</a></h3>
					<div>Phasellus mattis tincidunt nibh.</div>
				</div>
				<div>
					<h3><a href="#">Third</a></h3>
					<div>Nam dui erat, auctor a, dignissim quis.</div>
				</div>
			</div>

			<p><a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);"
					style="margin:3px 0 0 30px;"><?php _e( 'scroll to top',
			                                               'WpAdminStyle' ); ?></a><br class="clear" /></p>

			<!-- Tabs -->
			<h2>Tabs</h2>
			<div id="tabs">
				<ul>
					<li><a href="#tabs-1">First</a></li>
					<li><a href="#tabs-2">Second</a></li>
					<li><a href="#tabs-3">Third</a></li>
				</ul>
				<div
					id="tabs-1">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
				</div>
				<div
					id="tabs-2">Phasellus mattis tincidunt nibh. Cras orci urna, blandit id, pretium vel, aliquet ornare, felis. Maecenas scelerisque sem non nisl. Fusce sed lorem in enim dictum bibendum.
				</div>
				<div
					id="tabs-3">Nam dui erat, auctor a, dignissim quis, sollicitudin eu, felis. Pellentesque nisi urna, interdum eget, sagittis et, consequat vestibulum, lacus. Mauris porttitor ullamcorper augue.
				</div>
			</div>

			<p><a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);"
					style="margin:3px 0 0 30px;"><?php _e( 'scroll to top',
			                                               'WpAdminStyle' ); ?></a><br class="clear" /></p>

			<!-- Dialog NOTE: Dialog is not generated by UI in this demo so it can be visually styled in themeroller-->
			<h2 id="anker_dialog">Dialog</h2>
			<p><a href="#" id="dialog_link" class="ui-state-default ui-corner-all"><span
						class="ui-icon ui-icon-newwin"></span>Open Dialog</a></p>

			<p><a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);"
					style="margin:3px 0 0 30px;"><?php _e( 'scroll to top',
			                                               'WpAdminStyle' ); ?></a><br class="clear" /></p>

			<h2 id="overlay_shadow">Overlay and Shadow Classes <em>(not currently used in UI widgets)</em></h2>
			<div style="position: relative; width: 90%; height: 200px; padding:1% 4%; overflow:hidden;"
				class="fakewindowcontain">
				<p>Lorem ipsum dolor sit amet, Nulla nec tortor. Donec id elit quis purus consectetur consequat. </p>
				<p>Nam congue semper tellus. Sed erat dolor, dapibus sit amet, venenatis ornare, ultrices ut, nisi. Aliquam ante. Suspendisse scelerisque dui nec velit. Duis augue augue, gravida euismod, vulputate ac, facilisis id, sem. Morbi in orci. </p>
				<p>Nulla purus lacus, pulvinar vel, malesuada ac, mattis nec, quam. Nam molestie scelerisque quam. Nullam feugiat cursus lacus.orem ipsum dolor sit amet, consectetur adipiscing elit. Donec libero risus, commodo vitae, pharetra mollis, posuere eu, pede. Nulla nec tortor. Donec id elit quis purus consectetur consequat. </p>
				<p>Nam congue semper tellus. Sed erat dolor, dapibus sit amet, venenatis ornare, ultrices ut, nisi. Aliquam ante. Suspendisse scelerisque dui nec velit. Duis augue augue, gravida euismod, vulputate ac, facilisis id, sem. Morbi in orci. Nulla purus lacus, pulvinar vel, malesuada ac, mattis nec, quam. Nam molestie scelerisque quam. </p>
				<p>Nullam feugiat cursus lacus.orem ipsum dolor sit amet, consectetur adipiscing elit. Donec libero risus, commodo vitae, pharetra mollis, posuere eu, pede. Nulla nec tortor. Donec id elit quis purus consectetur consequat. Nam congue semper tellus. Sed erat dolor, dapibus sit amet, venenatis ornare, ultrices ut, nisi. Aliquam ante. </p>
				<p>Suspendisse scelerisque dui nec velit. Duis augue augue, gravida euismod, vulputate ac, facilisis id, sem. Morbi in orci. Nulla purus lacus, pulvinar vel, malesuada ac, mattis nec, quam. Nam molestie scelerisque quam. Nullam feugiat cursus lacus.orem ipsum dolor sit amet, consectetur adipiscing elit. Donec libero risus, commodo vitae, pharetra mollis, posuere eu, pede. Nulla nec tortor. Donec id elit quis purus consectetur consequat. Nam congue semper tellus. Sed erat dolor, dapibus sit amet, venenatis ornare, ultrices ut, nisi. </p>

				<!-- ui-dialog -->
				<div class="ui-overlay">
					<div class="ui-widget-overlay"></div>
					<div class="ui-widget-shadow ui-corner-all"
						style="width: 302px; height: 152px; position: absolute; left: 50px; top: 30px;"></div>
				</div>
				<div style="position: absolute; width: 280px; height: 130px;left: 50px; top: 30px; padding: 10px;"
					class="ui-widget ui-widget-content ui-corner-all">
					<div class="ui-dialog-content ui-widget-content" style="background: none; border: 0;">
						<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
					</div>
				</div>
			</div>

			<p><a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);"
					style="margin:3px 0 0 30px;"><?php _e( 'scroll to top',
			                                               'WpAdminStyle' ); ?></a><br class="clear" /></p>

			<!-- ui-dialog -->
			<div id="dialog" title="Dialog Title">
				<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
			</div>

			<h2>Framework Icons (content color preview)</h2>
			<ul id="icons" class="ui-widget ui-helper-clearfix">

				<li class="ui-state-default ui-corner-all" title=".ui-icon-carat-1-n"><span
						class="ui-icon ui-icon-carat-1-n"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-carat-1-ne"><span
						class="ui-icon ui-icon-carat-1-ne"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-carat-1-e"><span
						class="ui-icon ui-icon-carat-1-e"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-carat-1-se"><span
						class="ui-icon ui-icon-carat-1-se"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-carat-1-s"><span
						class="ui-icon ui-icon-carat-1-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-carat-1-sw"><span
						class="ui-icon ui-icon-carat-1-sw"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-carat-1-w"><span
						class="ui-icon ui-icon-carat-1-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-carat-1-nw"><span
						class="ui-icon ui-icon-carat-1-nw"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-carat-2-n-s"><span
						class="ui-icon ui-icon-carat-2-n-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-carat-2-e-w"><span
						class="ui-icon ui-icon-carat-2-e-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-triangle-1-n"><span
						class="ui-icon ui-icon-triangle-1-n"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-triangle-1-ne"><span
						class="ui-icon ui-icon-triangle-1-ne"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-triangle-1-e"><span
						class="ui-icon ui-icon-triangle-1-e"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-triangle-1-se"><span
						class="ui-icon ui-icon-triangle-1-se"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-triangle-1-s"><span
						class="ui-icon ui-icon-triangle-1-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-triangle-1-sw"><span
						class="ui-icon ui-icon-triangle-1-sw"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-triangle-1-w"><span
						class="ui-icon ui-icon-triangle-1-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-triangle-1-nw"><span
						class="ui-icon ui-icon-triangle-1-nw"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-triangle-2-n-s"><span
						class="ui-icon ui-icon-triangle-2-n-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-triangle-2-e-w"><span
						class="ui-icon ui-icon-triangle-2-e-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-1-n"><span
						class="ui-icon ui-icon-arrow-1-n"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-1-ne"><span
						class="ui-icon ui-icon-arrow-1-ne"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-1-e"><span
						class="ui-icon ui-icon-arrow-1-e"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-1-se"><span
						class="ui-icon ui-icon-arrow-1-se"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-1-s"><span
						class="ui-icon ui-icon-arrow-1-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-1-sw"><span
						class="ui-icon ui-icon-arrow-1-sw"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-1-w"><span
						class="ui-icon ui-icon-arrow-1-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-1-nw"><span
						class="ui-icon ui-icon-arrow-1-nw"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-2-n-s"><span
						class="ui-icon ui-icon-arrow-2-n-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-2-ne-sw"><span
						class="ui-icon ui-icon-arrow-2-ne-sw"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-2-e-w"><span
						class="ui-icon ui-icon-arrow-2-e-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-2-se-nw"><span
						class="ui-icon ui-icon-arrow-2-se-nw"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowstop-1-n"><span
						class="ui-icon ui-icon-arrowstop-1-n"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowstop-1-e"><span
						class="ui-icon ui-icon-arrowstop-1-e"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowstop-1-s"><span
						class="ui-icon ui-icon-arrowstop-1-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowstop-1-w"><span
						class="ui-icon ui-icon-arrowstop-1-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthick-1-n"><span
						class="ui-icon ui-icon-arrowthick-1-n"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthick-1-ne"><span
						class="ui-icon ui-icon-arrowthick-1-ne"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthick-1-e"><span
						class="ui-icon ui-icon-arrowthick-1-e"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthick-1-se"><span
						class="ui-icon ui-icon-arrowthick-1-se"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthick-1-s"><span
						class="ui-icon ui-icon-arrowthick-1-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthick-1-sw"><span
						class="ui-icon ui-icon-arrowthick-1-sw"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthick-1-w"><span
						class="ui-icon ui-icon-arrowthick-1-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthick-1-nw"><span
						class="ui-icon ui-icon-arrowthick-1-nw"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthick-2-n-s"><span
						class="ui-icon ui-icon-arrowthick-2-n-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthick-2-ne-sw"><span
						class="ui-icon ui-icon-arrowthick-2-ne-sw"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthick-2-e-w"><span
						class="ui-icon ui-icon-arrowthick-2-e-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthick-2-se-nw"><span
						class="ui-icon ui-icon-arrowthick-2-se-nw"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthickstop-1-n"><span
						class="ui-icon ui-icon-arrowthickstop-1-n"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthickstop-1-e"><span
						class="ui-icon ui-icon-arrowthickstop-1-e"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthickstop-1-s"><span
						class="ui-icon ui-icon-arrowthickstop-1-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowthickstop-1-w"><span
						class="ui-icon ui-icon-arrowthickstop-1-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowreturnthick-1-w"><span
						class="ui-icon ui-icon-arrowreturnthick-1-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowreturnthick-1-n"><span
						class="ui-icon ui-icon-arrowreturnthick-1-n"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowreturnthick-1-e"><span
						class="ui-icon ui-icon-arrowreturnthick-1-e"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowreturnthick-1-s"><span
						class="ui-icon ui-icon-arrowreturnthick-1-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowreturn-1-w"><span
						class="ui-icon ui-icon-arrowreturn-1-w"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowreturn-1-n"><span
						class="ui-icon ui-icon-arrowreturn-1-n"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowreturn-1-e"><span
						class="ui-icon ui-icon-arrowreturn-1-e"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowreturn-1-s"><span
						class="ui-icon ui-icon-arrowreturn-1-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowrefresh-1-w"><span
						class="ui-icon ui-icon-arrowrefresh-1-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowrefresh-1-n"><span
						class="ui-icon ui-icon-arrowrefresh-1-n"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowrefresh-1-e"><span
						class="ui-icon ui-icon-arrowrefresh-1-e"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrowrefresh-1-s"><span
						class="ui-icon ui-icon-arrowrefresh-1-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-4"><span
						class="ui-icon ui-icon-arrow-4"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-arrow-4-diag"><span
						class="ui-icon ui-icon-arrow-4-diag"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-extlink"><span
						class="ui-icon ui-icon-extlink"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-newwin"><span
						class="ui-icon ui-icon-newwin"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-refresh"><span
						class="ui-icon ui-icon-refresh"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-shuffle"><span
						class="ui-icon ui-icon-shuffle"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-transfer-e-w"><span
						class="ui-icon ui-icon-transfer-e-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-transferthick-e-w"><span
						class="ui-icon ui-icon-transferthick-e-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-folder-collapsed"><span
						class="ui-icon ui-icon-folder-collapsed"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-folder-open"><span
						class="ui-icon ui-icon-folder-open"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-document"><span
						class="ui-icon ui-icon-document"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-document-b"><span
						class="ui-icon ui-icon-document-b"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-note"><span
						class="ui-icon ui-icon-note"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-mail-closed"><span
						class="ui-icon ui-icon-mail-closed"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-mail-open"><span
						class="ui-icon ui-icon-mail-open"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-suitcase"><span
						class="ui-icon ui-icon-suitcase"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-comment"><span
						class="ui-icon ui-icon-comment"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-person"><span
						class="ui-icon ui-icon-person"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-print"><span
						class="ui-icon ui-icon-print"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-trash"><span
						class="ui-icon ui-icon-trash"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-locked"><span
						class="ui-icon ui-icon-locked"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-unlocked"><span
						class="ui-icon ui-icon-unlocked"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-bookmark"><span
						class="ui-icon ui-icon-bookmark"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-tag"><span
						class="ui-icon ui-icon-tag"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-home"><span
						class="ui-icon ui-icon-home"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-flag"><span
						class="ui-icon ui-icon-flag"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-calculator"><span
						class="ui-icon ui-icon-calculator"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-cart"><span
						class="ui-icon ui-icon-cart"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-pencil"><span
						class="ui-icon ui-icon-pencil"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-clock"><span
						class="ui-icon ui-icon-clock"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-disk"><span
						class="ui-icon ui-icon-disk"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-calendar"><span
						class="ui-icon ui-icon-calendar"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-zoomin"><span
						class="ui-icon ui-icon-zoomin"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-zoomout"><span
						class="ui-icon ui-icon-zoomout"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-search"><span
						class="ui-icon ui-icon-search"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-wrench"><span
						class="ui-icon ui-icon-wrench"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-gear"><span
						class="ui-icon ui-icon-gear"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-heart"><span
						class="ui-icon ui-icon-heart"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-star"><span
						class="ui-icon ui-icon-star"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-link"><span
						class="ui-icon ui-icon-link"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-cancel"><span
						class="ui-icon ui-icon-cancel"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-plus"><span
						class="ui-icon ui-icon-plus"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-plusthick"><span
						class="ui-icon ui-icon-plusthick"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-minus"><span
						class="ui-icon ui-icon-minus"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-minusthick"><span
						class="ui-icon ui-icon-minusthick"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-close"><span
						class="ui-icon ui-icon-close"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-closethick"><span
						class="ui-icon ui-icon-closethick"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-key"><span
						class="ui-icon ui-icon-key"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-lightbulb"><span
						class="ui-icon ui-icon-lightbulb"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-scissors"><span
						class="ui-icon ui-icon-scissors"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-clipboard"><span
						class="ui-icon ui-icon-clipboard"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-copy"><span
						class="ui-icon ui-icon-copy"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-contact"><span
						class="ui-icon ui-icon-contact"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-image"><span
						class="ui-icon ui-icon-image"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-video"><span
						class="ui-icon ui-icon-video"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-script"><span
						class="ui-icon ui-icon-script"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-alert"><span
						class="ui-icon ui-icon-alert"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-info"><span
						class="ui-icon ui-icon-info"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-notice"><span
						class="ui-icon ui-icon-notice"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-help"><span
						class="ui-icon ui-icon-help"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-check"><span
						class="ui-icon ui-icon-check"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-bullet"><span
						class="ui-icon ui-icon-bullet"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-radio-off"><span
						class="ui-icon ui-icon-radio-off"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-radio-on"><span
						class="ui-icon ui-icon-radio-on"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-pin-w"><span
						class="ui-icon ui-icon-pin-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-pin-s"><span
						class="ui-icon ui-icon-pin-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-play"><span
						class="ui-icon ui-icon-play"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-pause"><span
						class="ui-icon ui-icon-pause"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-seek-next"><span
						class="ui-icon ui-icon-seek-next"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-seek-prev"><span
						class="ui-icon ui-icon-seek-prev"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-seek-end"><span
						class="ui-icon ui-icon-seek-end"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-seek-first"><span
						class="ui-icon ui-icon-seek-first"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-stop"><span
						class="ui-icon ui-icon-stop"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-eject"><span
						class="ui-icon ui-icon-eject"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-volume-off"><span
						class="ui-icon ui-icon-volume-off"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-volume-on"><span
						class="ui-icon ui-icon-volume-on"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-power"><span
						class="ui-icon ui-icon-power"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-signal-diag"><span
						class="ui-icon ui-icon-signal-diag"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-signal"><span
						class="ui-icon ui-icon-signal"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-battery-0"><span
						class="ui-icon ui-icon-battery-0"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-battery-1"><span
						class="ui-icon ui-icon-battery-1"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-battery-2"><span
						class="ui-icon ui-icon-battery-2"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-battery-3"><span
						class="ui-icon ui-icon-battery-3"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-plus"><span
						class="ui-icon ui-icon-circle-plus"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-minus"><span
						class="ui-icon ui-icon-circle-minus"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-close"><span
						class="ui-icon ui-icon-circle-close"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-triangle-e"><span
						class="ui-icon ui-icon-circle-triangle-e"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-triangle-s"><span
						class="ui-icon ui-icon-circle-triangle-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-triangle-w"><span
						class="ui-icon ui-icon-circle-triangle-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-triangle-n"><span
						class="ui-icon ui-icon-circle-triangle-n"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-arrow-e"><span
						class="ui-icon ui-icon-circle-arrow-e"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-arrow-s"><span
						class="ui-icon ui-icon-circle-arrow-s"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-arrow-w"><span
						class="ui-icon ui-icon-circle-arrow-w"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-arrow-n"><span
						class="ui-icon ui-icon-circle-arrow-n"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-zoomin"><span
						class="ui-icon ui-icon-circle-zoomin"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-zoomout"><span
						class="ui-icon ui-icon-circle-zoomout"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circle-check"><span
						class="ui-icon ui-icon-circle-check"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circlesmall-plus"><span
						class="ui-icon ui-icon-circlesmall-plus"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circlesmall-minus"><span
						class="ui-icon ui-icon-circlesmall-minus"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-circlesmall-close"><span
						class="ui-icon ui-icon-circlesmall-close"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-squaresmall-plus"><span
						class="ui-icon ui-icon-squaresmall-plus"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-squaresmall-minus"><span
						class="ui-icon ui-icon-squaresmall-minus"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-squaresmall-close"><span
						class="ui-icon ui-icon-squaresmall-close"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-grip-dotted-vertical"><span
						class="ui-icon ui-icon-grip-dotted-vertical"></span></li>

				<li class="ui-state-default ui-corner-all" title=".ui-icon-grip-dotted-horizontal"><span
						class="ui-icon ui-icon-grip-dotted-horizontal"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-grip-solid-vertical"><span
						class="ui-icon ui-icon-grip-solid-vertical"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-grip-solid-horizontal"><span
						class="ui-icon ui-icon-grip-solid-horizontal"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-gripsmall-diagonal-se"><span
						class="ui-icon ui-icon-gripsmall-diagonal-se"></span></li>
				<li class="ui-state-default ui-corner-all" title=".ui-icon-grip-diagonal-se"><span
						class="ui-icon ui-icon-grip-diagonal-se"></span></li>
			</ul>

			<p><a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);"
					style="margin:3px 0 0 30px;"><?php _e( 'scroll to top',
			                                               'WpAdminStyle' ); ?></a><br class="clear" /></p>

			<!-- Slider -->
			<h2>Slider</h2>
			<div id="slider"></div>

			<p><a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);"
					style="margin:3px 0 0 30px;"><?php _e( 'scroll to top',
			                                               'WpAdminStyle' ); ?></a><br class="clear" /></p>

			<!-- Datepicker -->
			<h2>Datepicker</h2>
			<div id="datepicker"></div>

			<p><a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);"
					style="margin:3px 0 0 30px;"><?php _e( 'scroll to top',
			                                               'WpAdminStyle' ); ?></a><br class="clear" /></p>

			<!-- Autocomplete -->
			<h2 id="anker_autocomplete" class="demoAutocomplete">Autocomplete</h2>
			<label for="autocomplete">Tags: </label>
			<input id="autocomplete" type="text" />

			<p><a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);"
					style="margin:3px 0 0 30px;"><?php _e( 'scroll to top',
			                                               'WpAdminStyle' ); ?></a><br class="clear" /></p>

			<!-- Autocomplete -->
			<h2 class="demoAutocompleteCore">Autocomplete (Core style) - needs work</h2>
			<label for="autocomplete-core">Tags: </label>
			<input id="autocomplete-core" type="text" />

			<p><a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);"
					style="margin:3px 0 0 30px;"><?php _e( 'scroll to top',
			                                               'WpAdminStyle' ); ?></a><br class="clear" /></p>

			<!-- Progressbar -->
			<h2>Progressbar</h2>
			<div id="progressbar"></div>

			<p><a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);"
					style="margin:3px 0 0 30px;"><?php _e( 'scroll to top',
			                                               'WpAdminStyle' ); ?></a><br class="clear" /></p>

			<!-- Highlight / Error -->
			<h2 id="anker_highlight">Highlight / Error</h2>
			<div class="ui-widget">
				<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
					<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
						<strong>Hey!</strong> Sample ui-state-highlight style.</p>
				</div>
			</div>
			<br />
			<div class="ui-widget">
				<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
					<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
						<strong>Alert:</strong> Sample ui-state-error style.</p>
				</div>
			</div>

			<p><a class="alignright button" href="javascript:void(0);" onclick="window.scrollTo(0,0);"
					style="margin:3px 0 0 30px;"><?php _e( 'scroll to top',
			                                               'WpAdminStyle' ); ?></a><br class="clear" /></p>
		
		</div><!-- .wrap -->
		<?php
	}

	public function register_scripts() {

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-accordion' );
		wp_enqueue_script( 'jquery-ui-autocomplete' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-progressbar' );
		wp_enqueue_script( 'jquery-ui-slider' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-demo', plugin_dir_url( __FILE__ ) . '../js/jquery-ui-demo.js',
		                   array( 'jquery-ui-core' ) );

		wp_enqueue_style( 'jquery-ui-demo', plugin_dir_url( __FILE__ ) . '../css/jquery-ui-demo.css' );

		if ( 'classic' === get_user_option( 'admin_color' ) ) {
			wp_enqueue_style( 'jquery-ui-css', plugin_dir_url( __FILE__ ) . '../css/jquery-ui-classic.css' );
		} else {
			wp_enqueue_style( 'jquery-ui-css', plugin_dir_url( __FILE__ ) . '../css/jquery-ui-fresh.css' );
		}
	}

} // end class
