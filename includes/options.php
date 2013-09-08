<?php	
class OT_Reviews_Settings{
	
	public function __construct() {
		add_action( 'admin_menu', array($this, 'admin_menu') );
		add_action( 'admin_init', array($this, 'admin_init') );
	}
	
	// initialize admin menu:
	function admin_menu() {
	    add_options_page( 'Out:think Reviews', 'Out:think Reviews', 'manage_options', 'ot_reviews', array($this, 'options_page') );
	}

	function admin_init() {
		$userinfo = (array)get_option('ot-plugin-validation');

		register_setting( 'otr-settings-group', 'ot-plugin-validation' );

	    add_settings_section( 'section-one', 'Registration Info', array($this, 'section_one_callback'), 'ot_reviews' );
		// adding the Username Field
		add_settings_field( 'user', 'Username', array($this, 'text_input'), 'ot_reviews', 'section-one', array(
		    'name' => 'ot-plugin-validation[user]',
		    'value' => $userinfo['user'],
		) );
		add_settings_field( 'email', 'Email', array($this, 'text_input'), 'ot_reviews', 'section-one', array(
		    'name' => 'ot-plugin-validation[email]',
		    'value' => $userinfo['email'],
		) );
	
	}
	function section_one_callback() { ?>
		<p>Enter your Out:think Group username and email to enable automatic updates of this plugin.</p>
		<?php		
	}

	function text_input( $args ) {
	    $name = esc_attr( $args['name'] );
	    $value = esc_attr( $args['value'] );
	    echo "<input type='text' name='$name' value='$value' />";
		echo $args['help'];
	}

	function cat_dropdown( $args ) {
	    $name = esc_attr( $args['name'] );
		$title = esc_attr( $args['title']);
		$value = esc_attr( $args['value'] );
		$ddargs = array(
			'show_option_none' => 'Select Category for '.$title,
			'hide_empty' => 0,
			'name' => $name,
			'orderby' => 'name'
		);
		if ($value != '0') {
			$ddargs['selected'] = $value;
		}
		wp_dropdown_categories($ddargs);
		echo $args['help'];
	}

	function options_page() { ?>
	    <div class="wrap">
	        <h2>Out:think Reviews Options</h2>
	        <form action="options.php" method="POST">
	            <?php settings_fields( 'otr-settings-group' ); ?>
	            <?php do_settings_sections( 'ot_reviews' ); ?>
	            <?php submit_button(); ?>
	        </form>
	    </div>
	    <?php
	}
}
$OT_Reviews_Settings = new OT_Reviews_Settings();