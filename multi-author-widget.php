<?php
/**
 * Plugin Name: Multi Author Widget
 * Plugin URI: http://foxnet.fi/en
 * Description: Register Widget to show authors in a sidebar.
 * Version: 0.1
 * Author: Sami Keijonen
 * Author URI: http://foxnet.fi/en
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU 
 * General Public License version 2, as published by the Free Software Foundation.  You may NOT assume 
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without 
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package MultiAuthorWidget
 * @version 0.1
 * @author Sami Keijonen <sami.keijonen@foxnet.fi>
 * @copyright Copyright (c) 2012, Sami Keijonen
 * @link http://justintadlock.com/archives/2009/05/26/the-complete-guide-to-creating-widgets-in-wordpress-28
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

/**
 * Multi Author Widget class.
 *
 * @since 0.1.0
 */
class  Multi_Author_Widget extends WP_Widget {

	/**
	 * Set up the widget's unique name, ID, class, description, and other options.
	 *
	 * @since 0.1.0
	 */
	function __construct() {

		/* Set up the widget options. */
		$widget_options = array(
			'classname' => 'multi-author-widget',
			'description' => esc_html__( 'Displays authors.', 'multi-author-widget' )
		);

		/* Set up the widget control options. */
		$control_options = array(
			'width' => 200,
			'height' => 350,
			'id_base' => 'multi-author-widget'
		);

		/* Create the widget. */
		$this->WP_Widget(
			'multi-author-widget',								// $this->id_base
			__( 'Multi Author Widget', 'multi-author-widget' ),	// $this->name
			$widget_options,									// $this->widget_options
			$control_options									// $this->control_options
		);
	}
	
	/**
	 * Outputs the widget based on the arguments input through the widget controls.
	 *
	 * @since 0.1.0
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Get the avatar size. */
		$avatar_size = absint( $instance['avatar_size'] );

		/* Open the before widget HTML. */
		echo $before_widget;

		/* Output the widget title. */
		if ( $instance['title'] )
			echo $before_title . apply_filters( 'widget_title',  $instance['title'], $instance, $this->id_base ) . $after_title;
		
		/* Get only users by role, which user wants. */
		$users = get_users( array( 'role' => $instance['role'] ) );

			foreach ( $users as $author ) :
			
			/* Get the author ID. */
			$id = $author->ID;
			
			?>

				<div id="hcard-<?php echo str_replace( ' ', '-', get_the_author_meta( 'user_nicename', $id ) ); ?>" class="author-profile vcard clear">

					<a href="<?php echo get_author_posts_url( $id ); ?>" title="<?php the_author_meta( 'display_name', $id ); ?>"><?php the_author_meta( 'display_name', $id ); ?></a>
					<?php 
					/* If user post count is selected and user has posts, show it. */
					if ( $instance['show_post_count'] && count_user_posts( $id ) > 0 )
						printf( __( '(%d)', 'multi-author-widget' ), count_user_posts( $id ) );
					?>
					
					<a href="<?php echo get_author_posts_url( $id ); ?>" title="<?php the_author_meta( 'display_name', $id ); ?>">
						<?php
						/* Output the authors gravatar if selected. */
						if ( $instance['show_gravatar'] ) {
							$avatar = get_avatar( get_the_author_meta( 'user_email', $id ), $avatar_size, '', get_the_author_meta( 'display_name', $id ) );
							echo str_replace( "class='", "class='{$instance['avatar_align']} ", $avatar );
						}
						?>
					</a>

					<?php
					/* Show bio if selected. */
					if ( $instance['show_bio'] )
						echo wpautop( get_the_author_meta( 'description', $id ) );
					?>

				</div><!-- .author-profile .vcard -->
			
			<?php endforeach; ?>
			
			<div style="clear:both;">&nbsp;</div>
			
		<?php
		/* Close the after widget HTML. */
		echo $after_widget;
	}
	
	/**
	 * Updates the widget control options for the particular instance of the widget.
	 *
	 * @since 0.1.0
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Set the instance to the new instance. */
		$instance = $new_instance;

		/* Strip tags from elements that don't need them. */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['role'] = strip_tags( $new_instance['role'] );
		$instance['show_post_count'] = strip_tags( $new_instance['show_post_count'] );
		$instance['show_bio'] = strip_tags( $new_instance['show_bio'] );
		$instance['show_gravatar'] = strip_tags( $new_instance['show_gravatar'] );
		$instance['avatar_size'] = strip_tags( $new_instance['avatar_size'] );
		$instance['avatar_align'] = strip_tags( $new_instance['avatar_align'] );
		
		return $instance;
		
	}
	
	/**
	 * Displays the widget control options in the Widgets admin screen.
	 *
	 * @since 0.1.0
	 */
	function form( $instance ) {

		/* Set up the defaults. */
		$defaults = array(
			'title' 			=> __( 'Authors', 'multi-author-widget' ),
			'role' 				=> 'editor',
			'show_post_count'	=> 1,
			'show_bio' 			=> 1,
			'show_gravatar'		=> 1,
			'avatar_size'		=> '60',
			'avatar_align'		=> 'alignleft'
	
		);

		$instance = wp_parse_args( (array) $instance, $defaults );
		
		?>

			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'multi-author-widget' ); ?></label>
				<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" />
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'role' ); ?>"><?php _e( 'Role:', 'multi-author-widget' ); ?></label> 
				<select class="widefat" id="<?php echo $this->get_field_id( 'role' ); ?>" name="<?php echo $this->get_field_name( 'role' ); ?>">
				
					<?php wp_dropdown_roles( $instance['role'] ); // Dropdown list of roles. ?>

				</select>
			</p>
			
			<p>
				<input type="checkbox" value="1" <?php checked( '1', $instance['show_post_count'] ); ?> id="<?php echo $this->get_field_id( 'show_post_count' ); ?>" name="<?php echo $this->get_field_name( 'show_post_count' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_post_count' ); ?>"><?php _e( 'Display Post Count?' , 'multi-author-widget' ); ?></label> 
			</p>

			<p>
				<input type="checkbox" value="1" <?php checked( '1', $instance['show_bio'] ); ?> id="<?php echo $this->get_field_id( 'show_bio' ); ?>" name="<?php echo $this->get_field_name( 'show_bio' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_bio' ); ?>"><?php _e( 'Display Author Bio?', 'multi-author-widget' ); ?></label> 
			</p>

			<p>
				<input type="checkbox" value="1" <?php checked( '1', $instance['show_gravatar'] ); ?> id="<?php echo $this->get_field_id( 'show_gravatar' ); ?>" name="<?php echo $this->get_field_name( 'show_gravatar' ); ?>" />
				<label for="<?php echo $this->get_field_id( 'show_gravatar' ); ?>"><?php _e( 'Display Author Gravatar?' , 'multi-author-widget' ); ?></label> 
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'avatar_size' ); ?>"><?php _e( 'Avatar Size:', 'multi-author-widget' ); ?></label>
				<input style="float:right;width:66px;" type="text" class="widefat" id="<?php echo $this->get_field_id( 'avatar_size' ); ?>" name="<?php echo $this->get_field_name( 'avatar_size' ); ?>" value="<?php echo $instance['avatar_size']; ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id( 'avatar_align' ); ?>"><?php _e( 'Avatar Alignment:', 'multi-author-widget' ); ?></label> 
				<select style="float:right;max-width:66px;" class="widefat" id="<?php echo $this->get_field_id( 'avatar_align' ); ?>" name="<?php echo $this->get_field_name( 'avatar_align' ); ?>">
					<?php foreach ( array( 'alignnone' => __( 'None', 'multi-author-widget'), 'alignleft' => __( 'Left', 'multi-author-widget' ), 'alignright' => __( 'Right', 'multi-author-widget' ), 'aligncenter' => __( 'Center', 'multi-author-widget' ) ) as $option_value => $option_label ) { ?>
						<option value="<?php echo $option_value; ?>" <?php selected( $instance['avatar_align'], $option_value ); ?>><?php echo $option_label; ?></option>
					<?php } ?>
				</select>
			</p>

		<div style="clear:both;">&nbsp;</div>
	<?php
	}

}

/* register Multi Author Widget. */
add_action( 'widgets_init', create_function( '', 'register_widget( "Multi_Author_Widget" );' ) );

?>