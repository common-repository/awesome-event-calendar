<?php
/**
 * @package Awesome Event calendar 
 * @version 1.1
 */
/*
Plugin Name: Awesome Event calendar 
Plugin URI: http://www.mummamart.com/wordpress-event/
Description: This plugin use to show events in calendar.
Author: Poorvi Nagar
Email: poorvinagar@gmail.com
Version: 1.1
Author URI: http://www.mummamart.com/wordpress-event/
*/


// Create Event Post type
function register_event_post_aec() {
 
    $labels = array(
        'name' => _x( 'Event', 'event_aec' ),
        'singular_name' => _x( 'Event', 'event_aec' ),
        'add_new' => _x( 'Add New', 'event_aec' ),
        'add_new_item' => _x( 'Add New Event', 'event_aec' ),
        'edit_item' => _x( 'Edit Event', 'event_aec' ),
        'new_item' => _x( 'New Event', 'event_aec' ),
        'view_item' => _x( 'View Event', 'event_aec' ),
        'search_items' => _x( 'Search Events', 'event_aec' ),
        'not_found' => _x( 'No Events found', 'event_aec' ),
        'not_found_in_trash' => _x( 'No Events found in Trash', 'event_aec' ),
        'parent_item_colon' => _x( 'Parent Event:', 'event_aec' ),
        'menu_name' => _x( 'Events', 'event_aec' ),
    );
 
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
        'description' => 'Events filterable by genre',
        'supports' => array( 'title', 'editor',  'thumbnail'),
        'taxonomies' => array( 'eventcategory_aec' ),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-format-audio',
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );
 
    register_post_type( 'event_aec', $args );
}
 
add_action( 'init', 'register_event_post_aec' );

function create_eventcategory_taxonomy_aec() {
 
$labels = array(
    'name' => _x( 'Categories', 'taxonomy general name' ),
    'singular_name' => _x( 'Category', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Categories' ),
    'popular_items' => __( 'Popular Categories' ),
    'all_items' => __( 'All Categories' ),
    'parent_item' => null,
    'parent_item_colon' => null,
    'edit_item' => __( 'Edit Category' ),
    'update_item' => __( 'Update Category' ),
    'add_new_item' => __( 'Add New Category' ),
    'new_item_name' => __( 'New Category Name' ),
    'separate_items_with_commas' => __( 'Separate categories with commas' ),
    'add_or_remove_items' => __( 'Add or remove categories' ),
    'choose_from_most_used' => __( 'Choose from the most used categories' ),
);
 
register_taxonomy('eventcategory_aec','events_aec', array(
    'label' => __('Event Category'),
    'labels' => $labels,
    'hierarchical' => true,
    'show_ui' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'event-category' ),
));
}
 
add_action( 'init', 'create_eventcategory_taxonomy_aec', 0 );

add_action( 'admin_init', 'events_create_aec' );
 
function events_create_aec() {
    add_meta_box('events_meta_aec', 'Events', 'events_meta_aec', 'event_aec');
}
 
function events_meta_aec () {
 

 
global $post;
$custom = get_post_custom($post->ID);
$meta_sd = $custom["events_startdate_aec"][0];


 
$clean_sd = date("Y-m-d H:i:s", $meta_sd);

 
// - security -
 
echo '<input type="hidden" name="events-nonce" id="events-nonce" value="' .
wp_create_nonce( 'events-nonce' ) . '" />';
 

 
?>
<?php
$pluginPath = plugin_dir_url( __FILE__ );
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-style', $pluginPath.'/stylesheet/jquery-ui.css');
?>
<div class="meta">
<ul>
    <li><label> Date </label><input name="events_startdate_aec" class="tfdate" value="<?php echo $clean_sd; ?>" /></li>
    
   
</ul>
</div>
<script>
jQuery(document).ready(function()
{
jQuery(".tfdate").datepicker({
    dateFormat: 'yy-m-d',
    
 
    });
});

</script>
<?php
}
add_action ('save_post', 'save_events_aec');
 
function save_events_aec(){
 
global $post;
 
// - still require nonce
 
if ( !wp_verify_nonce( $_POST['events-nonce'], 'events-nonce' )) {
    return $post->ID;
}
 
if ( !current_user_can( 'edit_post', $post->ID ))
    return $post->ID;
 
// - convert back to unix & update post
 
if(!isset($_POST["events_startdate_aec"])):
return $post;
endif;
$updatestartd = strtotime ( $_POST["events_startdate_aec"] . $_POST["events_starttime"] );
update_post_meta($post->ID, "events_startdate_aec", $updatestartd );

 
}



function showevent_aec(){
$pluginPath = plugin_dir_url( __FILE__ );
	wp_enqueue_script( 'eventjs', plugins_url( '/js/jquery.eventCalendar.js' , __FILE__ ) );
		wp_enqueue_style('event-style',  plugins_url('/stylesheet/event/eventCalendar.css' , __FILE__ ));
	wp_enqueue_style('events-styleresponsive',  plugins_url('/stylesheet/event/eventCalendar_theme_responsive.css' , __FILE__ ));

?>

<div id="eventCalendarOnlyOneDescription"></div>
<script>
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
					jQuery(document).ready(function($) {
						$("#eventCalendarOnlyOneDescription").eventCalendar({
							eventsjson: ajaxurl,
							jsonDateFormat: 'human',
							onlyOneDescription: false
						});
					});
				</script>
	 
<?php				
}

add_shortcode('event-calendar-aec', 'showevent_aec');
add_action( 'wp_ajax_eventaction', 'eventjsonFunction' );
add_action( 'wp_ajax_nopriv_eventaction', 'eventjsonFunction' );
function eventjsonFunction(){
$args = array(
		'post_type' => 'event_aec',
  		'post_status' => array('publish'),	
		'posts_per_page' => -1,
	);
	$eventlist = get_posts($args);
	  $data      = array();
        
        $i = 0;
	foreach ($eventlist as $event) {
		$custom = get_post_custom($event->ID);
$date = $custom["events_startdate_aec"][0];
		// $date = get_post_meta( $event->ID(), 'events_startdate' );
		 $url = get_permalink($event->ID) ;
            
            $data[$i]['date']        = date("Y-m-d H:i:s", $date);
            $data[$i]['type']        = "";
            $data[$i]['title']       = $event->post_title;
            $data[$i]['description'] = substr(html_entity_decode(strip_tags($event->post_content), ENT_QUOTES, 'UTF-8'), 0, 300);
            $data[$i]['url']         = $url;
         $i++;}
       
        echo json_encode($data);
		die();
		}
?>