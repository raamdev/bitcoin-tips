<?php

class BitcointipsSettings {

  /**
   * Installs hooks
   */

  public function __construct() {
    add_action('admin_init', array($this, 'register_settings'));
    add_action('admin_menu', array($this, 'menu'));
  }

  /**
   * Defines Wordpress Admin menu item
   */
  
  public function menu() {
    add_options_page('Bitcoin Tips', 'Bitcoin Tips', 'manage_options', 'bitcointips', array($this, 'show_options_page'));
  }


  public function show_options_page() {
    if (!current_user_can('manage_options')) {
      wp_die(__('You do not have sufficient permissions to access this page.'));
  	}
  
    ?>
      <style type="text/css">
        .bitcointips-width { width: 100%; max-width: 600px; }
        .bitcointips-settings th { font-weight: bold; text-align: right; }
      </style>
    <?php
    echo '<div class="wrap bitcointips-settings">';
    screen_icon();
  	echo '<h2>Bitcoin Tips</h2>';
  	?>
      <p>
        Bitcoin Tips plugin allows you collecting bitcoin tips from your readers. Every post gets its unique bitcoin address
        for tips. This allows for detailed stats of tips per post, so you know which texts your readers appreciate the most.
        All tips are immediately forwarded to your defined bitcoin address, so  tips from all posts
        are eventually sent to a single address. The plugin uses Blockchain.info API to collect your tips (this adds some
        level of security, as even if your Wordpress is hacked, your received tips are secure as there are no bitcoin
        private keys stored in your Wordpress).
      </p>
      <p>
    	 Both plugin and Blockchain.info API are free to use, though <strong>if you like this plugin, donations are welcome in the form of tips on the
  	 <a href="<?php echo BITCOINTIPS_HOME_URL; ?>" target="_blank">plugin home page</a></strong>.
  	 </p>
  	<?php
  	echo '<form method="post" action="options.php">';
  	settings_fields('bitcointips');
  	do_settings_sections('bitcointips');
  	submit_button();
  	echo '</form>';
  	echo '</div>';
  }

  /**
   * Adds settings field definition
   */

  protected function add_field($id, $label) {
    add_settings_field('bitcointips_' . $id, $label, array($this, 'show_field_' . $id), 'bitcointips', 'bitcointips');
    register_setting('bitcointips', 'bitcointips_' . $id);
  }

  /**
   * Defines field settings
   */
  
  public function register_settings() {
    add_settings_section('bitcointips', 'Your Bitcoin Tips Settings', array($this, 'show_settings'), 'bitcointips');
    $this->add_field('address', 'Destination address to forward tips');
    $this->add_field('label', 'Tip box label');
    $this->add_field('text', 'Tip box text');
    $this->add_field('stats', 'Show public stats');
    $this->add_field('pluginad', 'Display link to plugin home page');
    $this->add_field('notify', 'Email notifications');
    $this->add_field('email', 'Email address');
  }
  
  /**
   * Displays info above settings form
   */
  
  public function show_settings() {
    $configured = strlen(get_option('bitcointips_address'));
    if (!$configured) {
      echo '<p style="color: red; font-weight: bold;">Bitcoin Tips plugin is not configured yet. You need to provide your bitcoin address where all tips will be forwarded.</p>';
    }
  }

  /**
   * Displays address field 
   */
  
  public function show_field_address() {
    echo 
      '<input name="bitcointips_address" type="text" value="' . get_option('bitcointips_address') . '" size="34" /><br />',
      '<p class="description bitcointips-width">',
        '<strong>Important:</strong> If you change this address to a new one, only tips for posts created after the change ',
        'will be sent to the new address and tips for previous posts will still be sent the the previous address. ',
        'This will be changed in one of next versions.',
      '</p>'
    ;
  }
  
  /**
   * Displays label field 
   */
  
  public function show_field_label() {
    $value = get_option('bitcointips_label', 'Like this post? Tip me with bitcoin!');
    echo '<input name="bitcointips_label" type="text" class="bitcointips-width" value="' . $value . '" />';
  }
  
  /**
   * Displays text field
   */
  
  public function show_field_text() {
    $default = 'If you enjoyed reading this post, please consider tipping me using Bitcoin. Each post gets its own unique Bitcoin address so by tipping you\'re not only making my continued efforts possible but telling me what you liked.';
    echo
      '<textarea name="bitcointips_text" class="bitcointips-width">' . get_option('bitcointips_text', $default) . '</textarea>',
      '<p class="description bitcointips-width">',
        'Optional text for the tip box. You can explain here what bitcoins are - if your readers might not know that - ',
        'or simply convince your readers why they should consider tipping you.',
      '</p>'
    ;
  }
  
  /**
   * Displays stats checkbox
   */
  
  public function show_field_stats() {
    echo
      '<p class="description bitcointips-width">',
        '<input name="bitcointips_stats" type="checkbox" style="margin-right: 10px;" ' . checked( 'on', get_option('bitcointips_stats', 'on'), false )  . ' />',
        'Displaying total sum, average value and number of tips for each post in the tipping widget.',
      '</p>'
    ;
  }
  
  /**
   * Displays plugin link checkbox
   */
  
  public function show_field_pluginad() {
    echo
      '<p class="description bitcointips-width">',
        '<input name="bitcointips_pluginad" type="checkbox" style="margin-right: 10px;" ' . checked( 'on', get_option('bitcointips_pluginad', 'on'), false )  . ' />',
        'If enabled, a short link to plugin home page is displayed in the tip box. ',
        'Leave this enabled if you want to support bitcoin and bitcoin tipping adoption. ',
      '</p>'
    ;
  }

  /**
   * Displays notify checkbox
   */
  
  public function show_field_notify() {
    echo
      '<p class="description bitcointips-width">',
        '<input name="bitcointips_notify" type="checkbox" style="margin-right: 10px;" ' . checked( 'on', get_option('bitcointips_notify', 'on'), false )  . ' />',
        'You will get email notification on each tip detected if this is turned on.',
      '</p>'
    ;
  }
  
  /**
   * Displays email address field 
   */
  
  public function show_field_email() {
    $value = get_option('bitcointips_email');
    if (!strlen($value)) {
      $current_user = wp_get_current_user();
      $value = $current_user->user_email;
    }
    echo 
      '<input name="bitcointips_email" type="text" value="' . $value . '" size="34" /><br />',
      '<p class="description bitcointips-width">',
        'Address to use for email notifications',
      '</p>'
    ;
  }
  
  
}

new BitcointipsSettings();