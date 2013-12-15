<?php
/*
Plugin Name: Bitcoin Tips (modified by Flowolf & Raam Dev)
Plugin URI: http://github.com/flowolf/bitcoin-tips
Description: Collects bitcoin tips for your content. Creates unique addresses per post (for stats purpose) and immediately forwards all user payments to your specified receiving address.
Version: 0.2.1
Author: Terk, Raam Dev, Flowolf
Author URI: http://github.com/flowolf/bitcoin-tips

License: MIT
*/

/**
 * License: The MIT License
 * Copyright (c) 2013 Terk
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR
 * A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH
 * THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

define('BITCOINTIPS_HOME_URL', 'http://github.com/flowolf/bitcoin-tips');
define('BITCOINTIPS_VERSION', '0.2.1');
#define('BITCOINTIPS_QRCODE_URL', 'https://chart.googleapis.com/chart?chs=120x120&cht=qr&chld=H|0&chl='); // + address
define('BITCOINTIPS_QRCODE_URL', plugins_url('/lib/qr.php?e=H&s=4&d=', __FILE__)); // + address

class Bitcointips {
  
  /**
   * Installs hooks
   */
  
  public function __construct() {
		if ( get_option('bitcointips_append') ) {
			add_filter('the_content', array($this, 'filter_content'));
		}
    add_action('wp_enqueue_scripts', array($this, 'register_css'));
    add_action('parse_request', array($this, 'parse_request'));
  }

  /**
   * Checks whether destination bitcoin address is configured
   */
  
  public function configured() {
    return strlen(get_option('bitcointips_address'));
  }

  /**
   * Get secret uniqid to verify Blockchain.info callbacks.
   */
  
  public function get_secret() {
    $secret = get_option('bitcointips_secret');
    if (!strlen($secret)) {
      $secret = uniqid('');
      add_option('bitcointips_secret', $secret);
    }
    return $secret;
  }

  /**
   * Registers CSS
   */
  
  public function register_css() {
    wp_register_style('bitcointips-style', plugins_url('style.css', __FILE__));
    wp_enqueue_style('bitcointips-style');
  }

  /**
   * Checks for for required state
   */

  public function check_state() {

	  if (!$this->configured()) {
      return FALSE;
    }

  	global $post;
	  if (!$post->ID) { return FALSE; }

	  $address = $this->get_post_address($post->ID);
	  if (!strlen($address)) {
	    return FALSE;
	  }

		return TRUE;
	
	}

  /**
   * Adds tip box at the end of post content
   */

  public function filter_content($content) {
    if (!$this->check_state()) {
      return $content;
    }

  	global $post;
	  $address = $this->get_post_address($post->ID);
    
    $qrcode = BITCOINTIPS_QRCODE_URL . $address;

    $output =
      '<div class="bitcointips-widget">' .
      '<div class="qrcode">' .
        '<a href="bitcoin:' . $address . '"><img src="' . $qrcode . '" width="120" height="120" /></a>'
    ;

    if (get_option('bitcointips_stats')) {
      $stats_count = (integer) get_post_meta($post->ID, 'bitcointips_count', true);
      $stats_avg = (integer) get_post_meta($post->ID, 'bitcointips_avg', true);
      $stats_sum = (integer) get_post_meta($post->ID, 'bitcointips_sum', true);
      if ($stats_count == 0) {
        $output .= 'No tips yet.<br />Be the first to tip!';
      } elseif ($stats_count == 1) {
        $output .= '1 tip so far<br />';
        $output .= $this->display_BTC($stats_sum);
      } else {
        $output .= $stats_count . ' tips so far<br />';
        $output .= $this->display_BTC($stats_sum) . '<br />';
        $output .= '(avg tip ' . $this->display_BTC($stats_avg, 5) .')';
      }
    }

    $output .= '</div>' .
      '<div class="contents">' .
        '<h2>' . get_option('bitcointips_label') . '</h2>' .
        '<p><a href="bitcoin:' . $address . '">' . $address . '</a></p>'
    ;

    if (strlen($text = get_option('bitcointips_text'))) {
      $output .= '<p>' . $text . '</p>';
    }
    $output .= '</div>';
    
    if (get_option('bitcointips_pluginad')) {
      $output .= '<div class="pluginhome"><a href="' . BITCOINTIPS_HOME_URL . '">Powered by Bitcoin Tips</a></div>';
    }
    
    $output .= '</div>';

    return $content . $output;
  }

 /**
   * Shows the Bitcoin Tip widget
   */

  public function show_widget() {
    if (!$this->check_state()) {
      return;
    }

  	global $post;
	  $address = $this->get_post_address($post->ID);

    $qrcode = BITCOINTIPS_QRCODE_URL . $address;


    $output =
      '<div class="bitcointips-widget">' .
      '<div class="qrcode">' .
        '<a href="bitcoin:' . $address . '"><img src="' . $qrcode . '" width="120" height="120" /></a>'
    ;

    if (get_option('bitcointips_stats')) {
      $stats_count = (integer) get_post_meta($post->ID, 'bitcointips_count', true);
      $stats_avg = (integer) get_post_meta($post->ID, 'bitcointips_avg', true);
      $stats_sum = (integer) get_post_meta($post->ID, 'bitcointips_sum', true);
      if ($stats_count == 0) {
        $output .= 'No tips yet.<br />Be the first to tip!';
      } elseif ($stats_count == 1) {
        $output .= '1 tip so far<br />';
        $output .= $this->display_BTC($stats_sum);
      } else {
        $output .= $stats_count . ' tips so far<br />';
        $output .= $this->display_BTC($stats_sum) . '<br />';
        $output .= '(avg tip ' . $this->display_BTC($stats_avg, 5) .')';
      }
    }

    $output .= '</div>' .
      '<div class="contents">' .
        '<h2>' . get_option('bitcointips_label') . '</h2>' .
        '<p><a href="bitcoin:' . $address . '">' . $address . '</a></p>'
    ;

    if (strlen($text = get_option('bitcointips_text'))) {
      $output .= '<p>' . $text . '</p>';
    }
    $output .= '</div>';
    
    if (get_option('bitcointips_pluginad')) {
      $output .= '<div class="pluginhome"><a href="' . BITCOINTIPS_HOME_URL . '">Powered by Bitcoin Tips</a></div>';
    }

    $output .= '</div>';

    return $output;
  }

  /**
   * Returns stats for given post
	 */

	public function get_stats($post_id) {
		
	  $stats_count = (integer) get_post_meta($post_id, 'bitcointips_count', true);
	  $stats_avg = (integer) get_post_meta($post_id, 'bitcointips_avg', true);
	  $stats_sum = (integer) get_post_meta($post_id, 'bitcointips_sum', true);

		$output = '<div class="bitcointips-stats">';

	  if ($stats_count == 0) {
	    $output .= 'No tips yet.<br />Be the first to tip!';
	  } elseif ($stats_count == 1) {
	    $output .= '1 tip so far<br />';
	    $output .= $this->display_BTC($stats_sum);
	  } else {
	    $output .= $stats_count . ' tips so far<br />';
	    $output .= $this->display_BTC($stats_sum) . '<br />';
	    $output .= '(avg tip ' . $this->display_BTC($stats_avg, 5) .')';
	  }
		$output .= '</div>';

		return $output;
	}

  /**
   * Returns post's unique tip jar address.
   * 
   * If post has defined multiple addresses, the last one is returned.
   * In the next versions, after user changes forward destination address,
   * all unique existing posts will get newly regenerated unique addresses
   * to get new addresses forwarding to the new destination address.
   * Old addresses will be kept to be included in stats.
   */
  
  protected function get_post_address($post_id) {
    $addresses = get_post_meta($post_id, 'bitcointips_address');
    if (!count($addresses)) {
      return $this->create_post_address($post_id);
    } else {
      return array_pop($addresses);
    }
  }

  /**
   * Returns post's unique tip jar address.
   */
  
  public function the_post_address($post_id) {
		return $this->get_post_address($post_id);
  }
  
  /**
   * Creates new unique tip jar address for post
   */
  
  protected function create_post_address($post_id) {
    if (!$this->configured()) { return false; }
    $address = get_option('bitcointips_address');
    $callback = urlencode(site_url() . '/?bitcointipped=true&secret=' . $this->get_secret());
    $api_call = 'https://blockchain.info/api/receive?method=create&address=' . $address . '&shared=false&callback=' . $callback;
    $result = $this->file_get_contents_curl($api_call);
    if (!strlen($result)) { return false; }
    $data = @json_decode($result);
    if (!$data) { return false; }
    if (!$data->input_address) { return false; }
    if ($data->destination != $address)  { return false; }
    if (add_post_meta($post_id, 'bitcointips_address', $data->input_address)) {
      return $data->input_address;
    }
  }
  
  /**
   * Parses request to detect and process Blockchain.info callback.
   * Stores info about new tips in database
   * @todo
   */
  
  public function parse_request(&$wp) {
    if (!array_key_exists('bitcointipped', $_GET) || $_GET['bitcointipped'] != 'true') {
      return;
    }
    if (!array_key_exists('secret', $_GET) || $_GET['secret'] != $this->get_secret()) {
      return;
    }
    
    $req_fields = array('value', 'input_address', 'input_transaction_hash', 'destination_address', 'transaction_hash');
    foreach ($req_fields as $field) {
      if (!array_key_exists($field, $_GET)) { return; }
    }
    
    $input_address = $_GET['input_address'];
    $destination_address = $_GET['destination_address'];
    $transaction_hash = $_GET['transaction_hash'];
    $input_transaction_hash = $_GET['input_transaction_hash'];
    $value = $_GET['value'];

    global $wpdb;
    $post_id = $wpdb->get_var($wpdb->prepare(
      'SELECT post_id FROM ' . $wpdb->postmeta . ' WHERE meta_key = %s AND meta_value = %s',
      'bitcointips_address', $input_address
    ));

    $result = $wpdb->replace(bitcointips_dbtable(), array(
      'value' => $value,
      'post_id' => $post_id,
      'created_at' => date('Y-m-d H:i:s'),
      'input_address' => $input_address,
      'destination_address' => $destination_address,
      'transaction_hash' => $transaction_hash,
      'input_transaction_hash' => $input_transaction_hash,
    ));

    $this->update_post_stats($post_id);

    if (false !== $result) {
      echo '*ok*';
    }
    
    if ($result == 1 && get_option('bitcointips_notify') && strlen($email = get_option('bitcointips_email'))) {
      $subject = '[' . get_bloginfo('name') . '] ' . $this->display_BTC($value) . ' tip for your post: ' . get_the_title($post_id);
      $body =
        'You just received a tip of ' . $this->display_BTC($value) . ' for your post at ' . get_permalink($post_id) . "\n\n" .
        'View the tip at: http://blockchain.info/tx/' . $input_transaction_hash
      ;
      wp_mail($email, $subject, $body);
    }
    
    exit;
  } 

  /**
   * Update post tips stats
   */
  
  function update_post_stats($post_id) {
    $post_id = (integer) $post_id;
    global $wpdb;
    $row = $wpdb->get_row('SELECT post_id, SUM(value) AS sum, ROUND(AVG(value)) AS avg, COUNT(value) AS num FROM wp_bitcointips WHERE post_id = ' . $post_id . ' GROUP BY post_id');
    if ($row) {
      $sum = (integer) $row->sum;
      $avg = (integer) $row->avg;  
      $count = (integer) $row->num;    
    } else {
      $sum = 0;
      $avg = 0;
      $count = 0;
    }
    update_post_meta($post_id, 'bitcointips_sum', $sum);
    update_post_meta($post_id, 'bitcointips_avg', $avg);
    update_post_meta($post_id, 'bitcointips_count', $count);
  }
  
  /**
   * Return human-readable BTC amount
   */
  
  function display_BTC($amount, $precision = null) {
    $value = $amount / 100000000;
    if ($precision !== null) {
      $value = round($value, $precision);
    }
    return $value . ' BTC';
  }

	/**
	 * Function to replace file_get_contents with better supported cURL
	 */

	function file_get_contents_curl($url) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_URL, $url);

		$data = curl_exec($ch);
		curl_close($ch);

		return $data;
	}

}

new Bitcointips();

/**
 * Plugin's [bitcointips] shortcode, with optional output="" attribute
 *
 * [bitcointips] will return the full widget using settings defined in plugin config
 * [bitcointips output="qrcode"] will return only the unique post QR code
 * [bitcointips output="address"] will return only the unique post bitcoin address
 */

function bitcointips_shortcode( $attributes ) {
	
	// Catch output="" shortcode attribute to allow for outputting specific data
	extract( shortcode_atts( array( 'output' => NULL ), $attributes ) );
	
	$bitcointips = new Bitcointips();
	
	if (!$bitcointips->check_state()) {
    return "An error occurred: Either Bitcoin Tips has not been configured or this shortcode is being used somewhere other than a post or page. Please see Settings -> Bitcoin Tips.";
  }
	
	global $post;

	switch( $output ) {

		case 'qrcode':
		
		  $address = $bitcointips->the_post_address($post->ID);
                        $qrcode = BITCOINTIPS_QRCODE_URL . $address;
			$qrcode_html = '<div class="qrcode">' .
		    '<a href="bitcoin:' . $address . '"><img src="' . $qrcode . '" width="120" height="120" /></a>' . 
				'</div>';
			$return_data = $qrcode_html;
			break;

		case 'address':
		
		  $address = $bitcointips->the_post_address($post->ID);
			$address_html = '<a href="bitcoin:' . $address . '">' . $address . '</a>';
			$return_data = $address_html;
			break;

		case 'stats':
		
			$return_data = $bitcointips->get_stats($post->ID);
			break;

		default: // By default, show full widget using settings defined in plugin config
		
			$return_data = $bitcointips->show_widget();
			break;
	}
	
	return $return_data;
}
add_shortcode('bitcointips', 'bitcointips_shortcode');

/**
 * Plugin's database table name
 */

function bitcointips_dbtable() {
  global $wpdb;
  return $wpdb->prefix . "bitcointips";
}

/**
 * After script is activated in Wordpress, run installation tasks (create DB tables).
 */

register_activation_hook(__FILE__, 'bitcointips_install');

function bitcointips_install() {
  global $wpdb;
  $sql = 'CREATE TABLE ' . bitcointips_dbtable() . ' (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_id BIGINT UNSIGNED NULL,
  value BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  input_address VARCHAR(34) NULL DEFAULT NULL,
  destination_address VARCHAR(34) NULL DEFAULT NULL,
  transaction_hash VARCHAR(64) NULL DEFAULT NULL,
  input_transaction_hash VARCHAR(64) NULL DEFAULT NULL,
  KEY post_id (post_id),
  KEY created_at (created_at),
  UNIQUE KEY transaction (input_address, input_transaction_hash, transaction_hash),
  PRIMARY KEY  (id)
  )';
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
  dbDelta($sql);
  update_option('bitcointips_version', BITCOINTIPS_VERSION);
}

/**
 * Check if plugin has been updated before last changes in database schema
 */

add_action('plugins_loaded', 'bitcointips_update_check');

function bitcointips_update_check() {
  if (get_option('bitcointips_version') != BITCOINTIPS_VERSION) {
    bitcointips_install();
  }
}

/**
 * If we're inside admin area, include admin interface.
 */

if (is_admin()) {
  include(plugin_dir_path(__FILE__) . 'admin.php');
}
