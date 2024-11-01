<?php
/*
Plugin Name: Widget for Contact form 7
Plugin URI: https://wordpress.org/plugins/widget-for-contact-form-7/
Description: Create widget for contact form 7 plugin for easy selecting forms.
Author: Oleh Odeshchak
Author URI: http://thewpdev.org/
Version: 1.0.2
Text Domain: wpc7w
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! function_exists( 'wfcf7_fs' ) ) {
    // Create a helper function for easy SDK access.
    function wfcf7_fs() {
        global $wfcf7_fs;

        if ( ! isset( $wfcf7_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $wfcf7_fs = fs_dynamic_init( array(
                'id'                  => '4510',
                'slug'                => 'widget-for-contact-form-7',
                'type'                => 'plugin',
                'public_key'          => 'pk_2465ee7eed0d5ef261c01cc425779',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'first-path'     => 'plugins.php',
                    'account'        => false,
                    'support'        => false,
                ),
            ) );
        }

        return $wfcf7_fs;
    }

    // Init Freemius.
    wfcf7_fs();
    // Signal that SDK was initiated.
    do_action( 'wfcf7_fs_loaded' );
}

if ( ! defined( 'ABSPATH' ) ) {
	return;
}

class Wpc7w_ContactForm_Widget extends WP_Widget {
	public function __construct() {
		parent::__construct(
			'wpc7w_contact_form',
			esc_html__( 'Widget for Contact form 7', 'wpc7w' ),
			array( 'description' => esc_html__( 'View contact form 7 form in widget', 'wpc7w' ), )
		);
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['form'] = strip_tags( $new_instance['form'] );
		return $instance;
	}

	public function form( $instance ) {
		$instance['title'] = ( isset( $instance['title'] ) && ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$instance['form'] = ( isset( $instance['form'] ) && ! empty( $instance['form'] ) ) ? $instance['form'] : '';
		
		// Get contact forms
		$cf7_forms = array( '- Select form -' => 'none' );
	
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); // Require plugin.php to use is_plugin_active() below
		}
		
		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			global $wpdb;
			$db_cf7froms  = $wpdb->get_results("SELECT ID, post_title FROM $wpdb->posts WHERE post_type = 'wpcf7_contact_form'");
			
			if ( $db_cf7froms ) {
				foreach ( $db_cf7froms as $cform ) {
					$cf7_forms[$cform->post_title] = $cform->ID;
				}
			}
		}

		?>
		<p>
			<label for="<?php print $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title', 'wpc7w' ); ?></label>
			<input class="widefat" id="<?php print $this->get_field_id( 'title' ); ?>" 
				name="<?php print $this->get_field_name( 'title' ); ?>" type="text" 
				value="<?php print $instance['title']; ?>" />
		</p>
		<p>
			<label for="<?php print $this->get_field_id( 'form' ); ?>"><?php esc_html_e( 'Select contact form', 'wpc7w' ); ?></label>
			<select class="widefat" id="<?php print $this->get_field_id( 'form' ); ?>" name="<?php print $this->get_field_name( 'form' ); ?>">
				<?php foreach ($cf7_forms as $key => $form) { ?>
					<option value="<?php echo $form; ?>" <?php selected($form, $instance['form'] ); ?>><?php echo $key; ?></option>
				<?php } ?>
			</select>
		</p>
		<?php
	}

	public function widget( $args, $instance ) {

		/** This filter is documented in wp-includes/default-widgets.php */
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$form = empty( $instance['form'] ) ? '' : $instance['form'];

		print $args['before_widget'];
		if ( $title ) {
			print $args['before_title'] . $title . $args['after_title'];
		}
				
		if ( ! empty( $form ) && $form != 'none' ) {
			print '<div class="contact-form">' . do_shortcode( '[contact-form-7 id="' . $form . '"]' ) . '</div>';
		}
		print $args['after_widget'];
	}
}

add_action( 'widgets_init', function() {
	register_widget( 'Wpc7w_ContactForm_Widget' );
});