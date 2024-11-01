<?php
/**
 * @package YD_Spread-parameter
 * @author Yann Dubois
 * @version 0.2.0
 */

/*
 Plugin Name: YD Spread Parameter
 Plugin URI: http://www.yann.com/en/wp-plugins/yd-spread-parameter
 Description: Tweaks URLs to keep and propagate a http get query parameter in all links site-wide ( like ?tpl=1 ). | Funded by <a href="http://www.wellcom.fr">Wellcom.fr</a>
 Author: Yann Dubois
 Version: 0.2.0
 Author URI: http://www.yann.com/
 */

/**
 * @copyright 2009  Yann Dubois  ( email : yann _at_ abc.fr )
 *
 *  Original development of this plugin was kindly funded by http://www.pressonline.com
 *  
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
/**
 Revision 0.1.0:
 - First beta release
 Revision 0.2.0:
 - Added support for forms (such as search form)
 - Spreading to subdomains is now an option
 */
/**
 *	TODO:
 *  - Test, debug, final release
 */

/** Install or reset plugin defaults **/
function yd_spreadparam_reset( $force ) {
	/** Init values **/
	$yd_spreadparam_version	= "0.2.0";
	$newoption				= 'plugin_yd_spreadparam';
	$newvalue				= '';
	$prev_options = get_option( $newoption );
	if( ( isset( $force ) && $force ) || !isset( $prev_options['plugin_version'] ) ) {
		// those default options are set-up at plugin first-install or manual reset only
		// they will not be changed when the plugin is just upgraded or deactivated/reactivated
		$newvalue['plugin_version'] 	= $yd_spreadparam_version;
		$newvalue[0]['debug'] 			= 0;
		$newvalue[0]['selected_params'] = 0;
		$newvalue[0]['param_list']		= array();
		$newvalue[0]['disable_backlink']= 0;
		$newvalue[0]['subdomain']		= 0;
		if( $prev_options ) {
			update_option( $newoption, $newvalue );
		} else {
			add_option( $newoption, $newvalue );
		}
	}
}
register_activation_hook(__FILE__, 'yd_spreadparam_reset');

/** Create Text Domain For Translations **/
add_action('init', 'yd_spreadparam_textdomain');
function yd_spreadparam_textdomain() {
	$plugin_dir = basename( dirname(__FILE__) );
	load_plugin_textdomain(
		'yd-spreadparam',
		PLUGINDIR . '/' . dirname( plugin_basename( __FILE__ ) ),
		dirname( plugin_basename( __FILE__ ) )
	); 
}

/** Create custom admin menu page **/
add_action('admin_menu', 'yd_spreadparam_menu');
function yd_spreadparam_menu() {
	add_options_page(
	__('YD Spread Parameters Settings',
		'yd-spreadparam'), 
	__('YD Spread Parameters Settings', 'yd-spreadparam'),
	8,
	__FILE__,
		'yd_spreadparam_settings'
		);
}

function yd_spreadparam_settings() {
	global $wpdb;
	$support_url	= 'http://www.yann.com/en/wp-plugins/yd-spread-parameter';
	$yd_logo		= 'http://www.yann.com/yd-spreadparam-v020-logo.gif';
	$jstext			= preg_replace( "/'/", "\\'", __( 'This will disable the link in your blog footer. ' .
							'If you are using this plugin on your site and like it, ' .
							'did you consider making a donation?' .
							' -- Thanks.', 'yd-spreadparam' ) );
	?>
	<script type="text/javascript">
	<!--
	function donatemsg() {
		alert( '<?php echo $jstext ?>' );
	}
	//-->
	</script>
	<?php
	echo '<div class="wrap">';
	
	// ---
	// options/settings page header section: h2 title + warnings / updates
	// ---

	echo '<h2>' . __('YD Spread Parameters Settings', 'yd-spreadparam') . '</h2>';
	
	if( isset( $_GET["do"] ) ) {
		echo '<div class="updated">';
		if( isset( $_GET["debug"] ) && intval( $_GET["debug"] ) > 0 ) {
			echo '<p>' . __('Action:', 'yd-spreadparam') . ' '
			. __( 'I should now', 'yd-spreadparam' ) . ' ' . __( $_GET["do"], 'yd-spreadparam' ) . '.</p>';
		} else {
			unset( $_GET["debug"] );
		}
		if(			$_GET["do"] == __('Reset plugin settings', 'yd-spreadparam') ) {
			yd_spreadparam_reset( 'force' );
			echo '<p>' . __('Plugin settings are reset', 'yd-spreadparam') . '</p>';
		} elseif(	$_GET["do"] == __('Update plugin settings', 'yd-spreadparam') ) {
			yd_spreadparam_update_options();
			if( isset( $_GET["yd_spreadparam-add_new"] ) && $_GET["yd_spreadparam-add_new"] != '' ) {
				yd_spreadparam_add( $_GET["yd_spreadparam-add_new"] );
				if( isset( $_GET["debug"] ) ) echo '<p>' . __('New parameter added', 'yd-spreadparam') . '</p>';
			}
			echo '<p>' . __('Plugin settings are updated', 'yd-spreadparam') . '</p>';
		} elseif ( isset( $_GET['del_param'] ) ) {
			yd_spreadparam_del( $_GET['del_param'] );
			if( isset( $_GET["debug"] ) ) echo '<p>' . __('Parameter deleted', 'yd-spreadparam') . '</p>';
		}
		echo '</div>'; // / updated
	} else {
		echo '<div class="updated">';
		echo '<p>'
		. '<a href="' . $support_url . '" target="_blank" title="Plugin FAQ">';
		echo __('Welcome to the YD Spread Parameter Settings Page.', 'yd-spreadparam')
		. '</a></p>';
		echo '</div>'; // / updated
	}
	$options = get_option( 'plugin_yd_spreadparam' );
	$i = 0;
	if( ! is_array( $options ) ) {
		// Something went wrong
		echo '<div class="updated">'; //TODO: Replace with appropriate error / warning class (red/pink)
		echo __( 'Uh-oh. Looks like I lost my settings. Sorry.', 'yd-spreadparam' );
		echo '<form method="get" style="display:inline;">';
		echo '<input type="submit" name="do" value="' . __( 'Reset plugin settings', 'yd-spreadparam' ) . '"><br/>';
		echo '<input type="hidden" name="page" value="' . $_GET["page"] . '">';
		echo '</form>';
		echo '</div>'; // / updated
		return false;
	}
	
	// ---
	// Right sidebar
	// ---
	
	echo '<div class="metabox-holder has-right-sidebar">';
	echo '<div class="inner-sidebar">';
	echo '<div class="meta-box-sortabless ui-sortable">';

	// == Block 1 ==

	echo '<div class="postbox">';
	echo '<h3 class="hndle">' . __( 'Considered donating?', 'yd-spreadparam' ) . '</h3>';
	echo '<div class="inside" style="text-align:center;"><br/>';
	echo '<a href="' . $support_url . '" target="_blank" title="Plugin FAQ" border="0">'
	. '<img src="' . $yd_logo . '" alt="YD logo" /></a>'
	. '<br/><small>' . __( 'Enjoy this plugin?', 'yd-spreadparam' ) . '<br/>' . __( 'Help me improve it!', 'yd-spreadparam' ) . '</small><br/>'
	. '<form action="https://www.paypal.com/cgi-bin/webscr" method="post">'
	. '<input type="hidden" name="cmd" value="_s-xclick">'
	. '<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----'
	. '">'
	. '<input type="image" src="https://www.paypal.com/' . __( 'en_US', 'yd-spreadparam' ) . '/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">'
	. '<img alt="" border="0" src="https://www.paypal.com/' . __( 'en_US', 'yd-spreadparam' ) . '/i/scr/pixel.gif" width="1" height="1">'
	. '</form>'
	. '<small><strong>' . __( 'Thanks', 'yd-spreadparam' ) . ' - Yann.</strong></small><br/><br/>';
	
	//---
	echo '<form method="get" style="display:inline;">';
	//---
	
	echo '<table style="margin:10px;">';
	echo '<tr><td>' . __( 'Disable backlink in the blog footer:', 'yd-spreadparam' ) .
		'</td><td><input type="checkbox" name="yd_spreadparam-disable_backlink-0" value="1" ';
	if( $options[$i]["disable_backlink"] == 1 ) echo ' checked="checked" ';
	echo ' onclick="donatemsg()" ';
	echo ' /></td></tr>';
	echo '</table>';
	
	echo '</div>'; // / inside
	echo '</div>'; // / postbox
	
	// == Block 2 ==
	
	echo '<div class="postbox">';
	echo '<h3 class="hndle">' . __( 'Credits', 'yd-spreadparam' ) . '</h3>';
	echo '<div class="inside" style="padding:10px;">';
	echo '<b>' . __( 'Initial funding', 'yd-spreadparam' ) . '</b>';
	echo '<ul><li><a href="http://www.wellcom.fr">Wellcom</a></li></ul>';
	echo '<b>' . __( 'Translations', 'yd-spreadparam' ) . '</b>';
	echo '<ul>';
	echo '<li>' . __( 'English:', 'yd-spreadparam' ) . ' <a href="http://www.yann.com">Yann</a></li>';
	echo '<li>' . __( 'French:', 'yd-spreadparam' ) . ' <a href="http://www.yann.com">Yann</a></li>';
	echo '</ul>';
	echo __( 'If you want to contribute to a translation of this plugin, please drop me a line by ', 'yd-spreadparam' );
	echo '<a href="mailto:yann@abc.fr">' . __('e-mail', 'yd-spreadparam' ) . '</a> ';
	echo __( 'or leave a comment on the ', 'yd-spreadparam' );
	echo '<a href="' . $support_url . '">' . __( 'plugin\'s page', 'yd-spreadparam' ) . '</a>. ';
	echo __( 'You will get credit for your translation in the plugin file and the documentation page, ', 'yd-spreadparam' );
	echo __( 'as well as a link on this page and on my developers\' blog.', 'yd-spreadparam' );
		
	echo '</div>'; // / inside
	echo '</div>'; // / postbox
	
	// == Block 3 ==
	
	echo '<div class="postbox">';
	echo '<h3 class="hndle">' . __( 'Support' ) . '</h3>';
	echo '<div class="inside" style="padding:10px;">';
	echo '<b>' . __( 'Free support', 'yd-spreadparam' ) . '</b>';
	echo '<ul>';
	echo '<li>' . __( 'Support page:', 'yd-spreadparam' );
	echo ' <a href="' . $support_url . '">' . __( 'here.', 'yd-spreadparam' ) . '</a>';
	echo ' ' . __( '(use comments!)', 'yd-spreadparam' ) . '</li>';
	echo '</ul>';
	echo '<p><b>' . __( 'Professional consulting', 'yd-spreadparam' ) . '</b><br/>';
	echo __( 'I am available as an experienced free-lance Wordpress plugin developer and web consultant. ', 'yd-spreadparam' );
	echo __( 'Please feel free to <a href="mailto:yann@abc.fr">check with me</a> for any adaptation or specific implementation of this plugin. ', 'yd-spreadparam' );
	echo __( 'Or for any WP-related custom development or consulting work. Hourly rates available.', 'yd-spreadparam' ) . '</p>';
	echo '</div>'; // / inside
	echo '</div>'; // / postbox
	
	echo '</div>'; // / meta-box-sortabless ui-sortable
	echo '</div>'; // / inner-sidebar

	// ---
	// Main content area
	// ---
	
	echo '<div class="has-sidebar sm-padded">';
	echo '<div id="post-body-content" class="has-sidebar-content">';
	echo '<div class="meta-box-sortabless">';
	
	// == Main plugin options ==
	
	echo '<div class="postbox">';
	echo '<h3 class="hndle">' . __( 'Parameters to propagate:', 'yd-spreadparam' ) . '</h3>';
	echo '<div class="inside">';
	echo '<table style="margin:10px;">';
	
	// List of parameters
	echo '<tr><td valign="top">' . __('Replicate these parameters site-wide:', 'yd-spreadparam') .
		'</td><td><ul>';
	$paramlist = $options[$i]["param_list"];
	if( count( $paramlist ) > 0 ) {
		foreach ( (array) $paramlist as $param) {
			$disabled = '';
			echo '<tr>';
			echo '<th scope="row" align="right"><label for="' . $param . '">' . $param . '</label></th>';
			echo "	<td><input type=\"checkbox\" name=\"yd_spreadparam-selected_params-0[]\" value=\"$param\" ";
			if( is_array( $options[$i]["selected_params"] ) && in_array( $param, $options[$i]["selected_params"] ) )
				echo ' checked="checked" ';
			echo ' /></td>';
			echo '<td><a href="?page=' . urlencode( $_GET['page'] ) . '&do=&' 
				. 'debug=' . urlencode( $_GET['debug'] ) . '&'
				. 'del_param=' . urlencode( $param ) . '">[X]</a></td>';
			echo '</tr>';
		}
	} else {
		echo __( 'No parameter has been defined yet.', 'yd-spreadparam' );
	}
	echo '<tr>'
		. '<th scope="row" align="right"><label for="yd_spreadparam-add_new">' 
		. __( 'Add a parameter:', 'yd-spreadparam' ) . '</label></th>';
	echo  '<td><input type="text" name="yd_spreadparam-add_new" value="" size="10"></td>';
	echo '</tr>';
	echo '</table>';
	echo '</div>'; // / inside
	echo '</div>'; // / postbox
	
	// == Other settings ==

	echo '<div class="postbox">';
	echo '<h3 class="hndle">' . __( 'Other settings:', 'yd-spreadparam' ) . '</h3>';
	echo '<div class="inside">';
	echo '<table style="margin:10px;">';
	
	//subdomain
	echo "
		<tr>
			<th scope=\"row\" align=\"right\"><label for=\"yd_spreadparam-subdomain-0\">" 
			. __('Spread to subdomains:', 'yd-spreadparam') . "
			</label></th>";
	echo "	<td><input type=\"checkbox\" name=\"yd_spreadparam-subdomain-0\" value=\"1\" ";
	if( $options[$i]['subdomain'] == 1 )
		echo ' checked="checked" ';
	echo " /></td></tr>";
		
	//debug
	echo "
		<tr>
			<th scope=\"row\" align=\"right\"><label for=\"Debug\">" 
			. __('Show debug messages:', 'yd-spreadparam') . "
			</label></th>";
	echo "	<td><input type=\"checkbox\" name=\"debug\" value=\"1\" ";
	if( $_GET['debug'] == 1 )
		echo ' checked="checked" ';
	echo " /></td></tr>";
			
	//---
	
	echo '</table>';
	
	echo '</div>'; // / inside
	echo '</div>'; // / postbox
	
	echo '<div>';
	echo '<p class="submit">';
	echo '<input type="submit" name="do" value="' . __('Update plugin settings', 'yd-spreadparam') . '">';
	echo '<input type="hidden" name="page" value="' . $_GET["page"] . '">';
	echo '<input type="hidden" name="time" value="' . time() . '">';
	echo '</p>';
	echo '</form>';
	
	//---
	
	echo '<form method="get" style="display:inline;">';
	echo '<p class="submit">';
	echo '<input type="submit" name="do" value="' . __('Reset plugin settings', 'yd-spreadparam') . '">';
	echo '<input type="hidden" name="page" value="' . $_GET["page"] . '">';
	echo '</p>'; // / submit
	echo '</form>';
	echo '</div>'; // /
	
	echo '</div>'; // / meta-box-sortabless
	echo '</div>'; // / has-sidebar-content
	echo '</div>'; // / has-sidebar sm-padded
	echo '</div>'; // / metabox-holder has-right-sidebar
	echo '</div>'; // /wrap
}

/** Update display options of the options admin page **/
function yd_spreadparam_update_options(){
	$to_update = Array(
		'selected_params',
		'disable_backlink',
		'subdomain'
	);
	yd_update_options_nostrip_array_s( 'plugin_yd_spreadparam', 0, $to_update, $_GET, 'yd_spreadparam-' );
}

/** Add links on the plugin page (short description) **/
add_filter( 'plugin_row_meta', 'yd_spreadparam_links' , 10, 2 );
function yd_spreadparam_links( $links, $file ) {
	$base = plugin_basename(__FILE__);
	if ( $file == $base ) {
		$links[] = '<a href="options-general.php?page=yd-spread-parameter%2F' . basename( __FILE__ ) . '">' . __('Settings') . '</a>';
		$links[] = '<a href="http://www.yann.com/en/wp-plugins/yd-spread-parameter">' . __('Support') . '</a>';
	}
	return $links;
}
function yd_spreadparam_action_links( $links ) {
	$settings_link = '<a href="options-general.php?page=yd-spread-parameter%2F' . basename( __FILE__ ) . '">' . __('Settings') . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'yd_spreadparam_action_links', 10, 4 );

function yd_spreadparam_linkware() {
	$options = get_option( 'plugin_yd_spreadparam' );
	$i = 0;
	if( $options[$i]['disable_backlink'] ) echo "<!--\n";
	echo '<p style="text-align:center" class="yd_linkware"><small><a href="http://www.yann.com/en/wp-plugins/yd-spread-parameter">with Spread Parameter plugin by YD Wordpress Development</a></small></p>';
	if( $options[$i]['disable_backlink'] ) echo "\n-->";
}
add_action('wp_footer', 'yd_spreadparam_linkware');

// ============================ Plugin specific functions ============================

function yd_spreadparam_add( $newparam ) {
	$options = get_option( 'plugin_yd_spreadparam' );
	$i = 0;
	if( isset( $_GET["debug"] ) ) echo 'New param: ' . $newparam . '<br/>';
	$paramlist = $options[$i]["param_list"];
	if( array_search( $newparam, $paramlist ) === false ) $paramlist[] = $newparam;
	$fields = array();
	$fields["yd_spreadparam-param_list-0"] = $paramlist;
	if( isset( $_GET["debug"] ) ) {
		echo "<pre>Paramlist: \n";
		var_dump( $paramlist );
		echo '</pre>';
	}
	$to_update = Array(
		'param_list'
	);
	yd_update_options_nostrip_array_s( 'plugin_yd_spreadparam', 0, $to_update, $fields, 'yd_spreadparam-' );
}

function yd_spreadparam_del( $param ) {
	$options = get_option( 'plugin_yd_spreadparam' );
	$i = 0;
	if( isset( $_GET["debug"] ) ) echo 'Delete param: ' . $param . '<br/>';
	$paramlist = $options[$i]["param_list"];
	if( ( $key = array_search( $param, $paramlist ) ) !== false ) unset( $paramlist[ $key ] );
	$paramlist = array_values( $paramlist );
	//echo "key: $key<br/>";
	$fields = array();
	$fields["yd_spreadparam-param_list-0"] = $paramlist;
	if( isset( $_GET["debug"] ) ) {
		echo "<pre>Paramlist: \n";
		var_dump( $paramlist );
		echo '</pre>';
	}
	$to_update = Array(
		'param_list'
	);
	yd_update_options_nostrip_array_s( 'plugin_yd_spreadparam', 0, $to_update, $fields, 'yd_spreadparam-' );
}

/** Filter function(s) **/
function yd_spreadparam_link_filter( $html ) {
	$options = get_option( 'plugin_yd_spreadparam' );
	$i = 0;
	$paramlist = $options[$i]["selected_params"];
	foreach( (array)$paramlist as $param ) {
		if( isset( $_GET[$param] ) ) {
			if( preg_match( '/\?.*\b' . $param . '\b/', $html  ) ) continue; // it's already there
			if( preg_match( '/\?/', $html ) ) {
				$html .= '&' . urlencode( $param ) . '=' . $_GET[$param];
			} else {
				$html .= '?' . urlencode( $param ) . '=' . $_GET[$param];
			}
		}
	}
	return $html;
}
add_filter( 'attachment_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'author_feed_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'author_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'comment_reply_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'day_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'feed_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'get_comment_author_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'get_comment_author_url_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'month_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'page_link', 'yd_spreadparam_link_filter', 1, 2 );
add_filter( 'post_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'the_permalink', 'yd_spreadparam_link_filter', 10 );
add_filter( 'year_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'tag_link', 'yd_spreadparam_link_filter', 10 );

add_filter( 'post_comments_feed_link', 'yd_spreadparam_link_filter', 10 ); 
add_filter( 'category_feed_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'category_link', 'yd_spreadparam_link_filter', 10 );
add_filter( 'admin_url', 'yd_spreadparam_link_filter', 10 );
add_filter( 'plugins_url', 'yd_spreadparam_link_filter', 10 );

add_filter( 'register', 'yd_spreadparam_link_filter', 10 );

/**
function yd_spreadparam_canonical_filter( $url ) {

	return $url;
}
add_filter( 'aioseop_canonical_url', 'yd_spreadparam_canonical_filter', 10 );
**/

// ============================ Generic YD WP functions ==============================

include( 'yd-wp-lib.inc.php' );

if( !function_exists( 'yd_update_options_nostrip_array_s' ) ) {
	function yd_update_options_nostrip_array_s( $option_key, $number, $to_update, $fields, $prefix ) {
		$options = $newoptions = get_option( $option_key );
		/**
		echo '<pre>';
		echo "fields: \n";
		var_dump( $fields );
		echo '</pre>';
		**/
		foreach( $to_update as $key ) {
			// reset the value
			if( is_array( $newoptions[$number][$key] ) ) {
				$newoptions[$number][$key] = array();
			} else {
				$newoptions[$number][$key] = '';
			}
			/**
			echo $key . ': ';
			var_dump( $fields[$prefix . $key . '-' . $number] );
			**/
			if( !is_array( $fields[$prefix . $key . '-' . $number] ) ) {
				$value = html_entity_decode( stripslashes( $fields[$prefix . $key . '-' . $number] ) );
				$newoptions[$number][$key] = $value;
			} else {
				//it's a multi-valued field, make an array...
				if( !is_array( $newoptions[$number][$key] ) )
					$newoptions[$number][$key] = array( $newoptions[$number][$key] );
				foreach( $fields[$prefix . $key . '-' . $number] as $v )
					$newoptions[$number][$key][] = html_entity_decode( stripslashes( $v ) );	
			}
			//echo $key . " = " . $prefix . $key . '-' . $number . " = " . $newoptions[$number][$key] . "<br/>";
		}
		//echo '</pre>';
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option( $option_key, $options );
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

// =========================================== SPREADPARAM LINKS CLASS ===========================================
/**
Part of this code was directly inspired by code originally authored by Mesoconcepts (http://www.mesoconcepts.com),
 and is distributed under the terms of the GPL license, v.2.
http://www.opensource.org/licenses/gpl-2.0.php
Original author: Denis de Bernardy
** thanks to him! **
Original plugin URI: http://www.semiologic.com/software/publishing/external-links/
Original classname: external_links
**/

yd_spreadparam_links::init();
class yd_spreadparam_links
{
	#
	# init()
	#
	
	function init()
	{
		add_action('wp_head', array('yd_spreadparam_links', 'wp_head'));
		
	} # init()
	
	#
	# wp_head()
	#
	
	function wp_head()
	{
		$GLOBALS['did_yd_spreadparam_links'] = false;
		ob_start(array('yd_spreadparam_links', 'filter'));
		add_action('wp_footer', array('yd_spreadparam_links', 'ob_flush'), 1000000000);
		
	} # wp_head()
	
	#
	# filter()
	#
	
	function filter($buffer)
	{
		# escape head
		//TODO: replace links on logo, etc.
		$buffer = preg_replace_callback(
			"/
			^.*
			<\s*\/\s*head\s*>		# everything up to where the body starts
			/isUx",
			array('yd_spreadparam_links', 'escape'),
			$buffer
			);

		# escape scripts
		$buffer = preg_replace_callback(
			"/
			<\s*script				# script tag
				(?:\s[^>]*)?		# optional attributes
				>
			.*						# script code
			<\s*\/\s*script\s*>		# end of script tag
			/isUx",
			array('yd_spreadparam_links', 'escape'),
			$buffer
			);

		# escape objects
		$buffer = preg_replace_callback(
			"/
			<\s*object				# object tag
				(?:\s[^>]*)?		# optional attributes
				>
			.*						# object code
			<\s*\/\s*object\s*>		# end of object tag
			/isUx",
			array('yd_spreadparam_links', 'escape'),
			$buffer
			);

		global $spread_to_subdomain;
		$options = get_option( 'plugin_yd_spreadparam' );
		$i = 0;
		$spread_to_subdomain = $options[$i]["subdomain"];
		
		global $site_host;

		$site_host = trailingslashit(get_option('home'));
		$site_host = preg_replace("~^https?://~i", "", $site_host);
		if( $spread_to_subdomain ) {
			$site_host = preg_replace("~^www\.~i", "", $site_host);
			//$site_host = preg_replace("~^pol\.~i", "", $site_host);
			//TODO: get rid of subdomains other than www
		}
		$site_host = preg_replace("~/.*$~", "", $site_host);

		$buffer = preg_replace_callback(
			"/
			<\s*a					# ancher tag
				(?:\s[^>]*)?		# optional attributes
				\s*href\s*=\s*		# href=...
				(
					\"[^\"]*\"		# double quoted link
				|
					'[^']*'			# single quoted link
				|
					[^'\"]\S*		# non-quoted link
				)
				(?:\s[^>]*)?		# optional attributes
				\s*>
			/isUx",
			array('yd_spreadparam_links', 'filter_callback'),
			$buffer
			);

		//GET forms
		$buffer = preg_replace_callback( 
			'|<\s*/form\s*>|isUx',
			array('yd_spreadparam_links', 'form_callback'),
			$buffer
		);
			
		# unescape anchors
		$buffer = yd_spreadparam_links::unescape($buffer);
		
		$GLOBALS['did_yd_spreadparam_links'] = true;

		return $buffer;
	} # filter()
	
	function form_callback( $input ) {
		$options = get_option( 'plugin_yd_spreadparam' );
		$i = 0;
		$anchor = '';
		$paramlist = $options[$i]["selected_params"];
		foreach( (array)$paramlist as $param ) {
			if( isset( $_GET[$param] ) ) {
				$anchor .= '<input type="hidden" name="' .
					preg_replace( '/"/', '&quot;', $param ) .
					'" value="' .
					preg_replace( '/"/', '&quot;', $_GET[$param] ) .
					'"/>';
			}
		}
		$anchor .= $input[0]; // append original </form>
		return $anchor;
	}
	
	#
	# filter_callback()
	#
	
	function filter_callback($input)
	{
		global $site_host;
		global $spread_to_subdomain;

		$anchor = $input[0];
		$link = $input[1];

	#	echo '<pre>';
	#	var_dump(
	#		get_option('yd_spreadparam_links_params'),
	#		htmlspecialchars($link),
	#		htmlspecialchars($anchor)
	#		);
	#	echo '</pre>';

		if ( ( strpos($link, '://') !== false
				&& (
					( $spread_to_subdomain
					&& preg_match(
						"/
							https?:\/\/
							(?:www\.)?
							" . str_replace('.', '\.', $site_host) . "
						/ix",
						$link
						)
					)
					||
					( !$spread_to_subdomain
					&& preg_match(
						"/https?:\/\/" . str_replace('.', '\.', $site_host) . "
						/ix",
						$link
						)
					)
				)
			)
			&& !preg_match("/
					\/
					(?:go|get)
					(?:\.|\/)
					/ix",
					$link
					)
			)
		{
			$modified_link = trim( $link, '\'" ' );
			$modified_link = '"' . yd_spreadparam_link_filter( $modified_link ) . '"';
			$anchor = str_replace( $link, $modified_link, $anchor);
			//$anchor = 'link: ' . $link . ' - ' . 'mod: ' . $modified_link . '<br/>';
			//$anchor = 'zorglub';
		}

		return $anchor;
	} # filter_callback()
	
	#
	# escape()
	#

	function escape($input)
	{
		global $escaped_yd_spreadparam_links;

		#echo '<pre>';
		#var_dump($input);
		#echo '</pre>';

		$tag_id = '--escaped_yd_spreadparam_link:' . md5($input[0]) . '--';
		$escaped_yd_spreadparam_links[$tag_id] = $input[0];

		return $tag_id;
	} # escape()
	
	#
	# unescape()
	#

	function unescape($input)
	{
		global $escaped_yd_spreadparam_links;

		$find = array();
		$replace = array();

		foreach ( (array) $escaped_yd_spreadparam_links as $key => $val )
		{
			$find[] = $key;
			$replace[] = $val;
		}

		return str_replace($find, $replace, $input);
	} # unescape()
	
	#
	# ob_flush()
	#
	
	function ob_flush()
	{
		$i = 0;
		
		while ( !$GLOBALS['did_yd_spreadparam_links'] && $i++ < 100 )
		{
			@ob_end_flush();
		}
	} # ob_flush()
} # yd_spreadparam_links
?>