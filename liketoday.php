<?php
/*
Plugin Name: LikeToday
Version: 0.1
Plugin URI: https://github.com/monotropos/liketoday
Description: Show posts (titles and URLs) posted some years ago on today's date.
Author: Apostolos P. Tsompanopoulos
Author URI: https://www.aptlogs.com/
Requires at least: 4.7
Requires PHP: 7.0
Tested up to: 6.2
*/

/* Version check */
function liketoday_url( $path = '' ) {
	global $wp_version;
	if ( version_compare( $wp_version, '2.8', '<' ) ) { // Using WordPress 2.7
		$folder = dirname( plugin_basename( __FILE__ ) );
		if ( '.' != $folder ) $path = path_join( ltrim( $folder, '/' ), $path );
		return plugins_url( $path );
	}
	return plugins_url( $path, __FILE__ );
}

// Create the widget
class liketoday_widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'liketoday_widget',									// Base ID of your widget
			__('LikeToday Widget', 'liketoday_widget_domain'),	// Widget name will appear in UI
			array( 'description' => __( 'Simple widget to show posts from today’s date', 'liketoday_widget_domain' ), ) // Widget description
		);
	}

	// Creating widget front-end
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];

		$tmon = date("m");
		$tday = date("d");
		$argsQ = array(
			'monthnum' => $tmon,
			'day' => $tday,
			'orderby' => 'rand',
			'posts_per_page' => '3',
		);
		$query = new WP_Query( $argsQ );

		// The Loop.
		if ( $query->have_posts() ) {
			$msg = '<ul>';
			while ( $query->have_posts() ) {
				$query->the_post();
				$msg .= '<li><a href="'. get_the_permalink() .'">' . esc_html( get_the_title() ) . '</a><br/>'. get_the_date() .'</li>';
			}
			$msg .= '</ul>';
		} else {
			$msg = 'Sorry, no posts posted on a date like today.';
		}
		echo __( $msg, 'liketoday_widget_domain' );
		// Restore original Post Data.
		wp_reset_postdata();

		echo $args['after_widget'];
	}

	// Widget back-end
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title for widget', 'liketoday_widget_domain' );
		}
		// Widget admin form
		?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
	<input id="<?php echo $this->get_field_id( 'title' ) ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<?php
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}

	// Class liketoday_widget ends here
}

// Register and load the widget
function liketoday_load_widget() {
	register_widget( 'liketoday_widget' );
}
add_action( 'widgets_init', 'liketoday_load_widget' );

?>
