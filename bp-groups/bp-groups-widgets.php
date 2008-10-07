<?php

/* Register widgets for groups component */
function groups_register_widgets() {
	global $current_blog;
	
	/* Only allow these widgets on the main site blog */
	if ( (int)$current_blog->blog_id == 1 ) {
		
		/* Site welcome widget */
		register_sidebar_widget( __('Groups'), 'groups_widget_groups_list');
		register_widget_control( __('Groups'), 'groups_widget_groups_list_control' );
		
		/* Include the javascript needed for activated widgets only */
		if ( is_active_widget( 'groups_widget_groups_list' ) )
			wp_enqueue_script( 'groups_widget_groups_list-js', site_url() . '/wp-content/mu-plugins/bp-groups/js/widget-groups.js', array('jquery', 'jquery-livequery-pack') );		
	}
}
add_action( 'plugins_loaded', 'groups_register_widgets' );


/*** GROUPS WIDGET *****************/

function groups_widget_groups_list($args) {
	global $current_blog, $bp;
	
    extract($args);
	$options = get_blog_option( $current_blog->blog_id, 'groups_widget_groups_list' );
?>
	<?php echo $before_widget; ?>
	<?php echo $before_title
		. $widget_name 
		. $after_title; ?>
	
	<?php $groups = BP_Groups_Group::get_newest( $options['max_groups'] ); ?>
	
	<?php if ( $groups ) : ?>
		<div class="item-options" id="groups-list-options">
			<img id="ajax-loader-groups" src="<?php echo $bp['groups']['image_base'] ?>/ajax-loader.gif" height="7" alt="Loading" style="display: none;" />
			<a href="<?php echo site_url() . '/groups' ?>" id="newest-groups" class="selected"><?php _e("Newest") ?></a> | 
			<a href="<?php echo site_url() . '/groups' ?>" id="recently-active-groups"><?php _e("Active") ?></a> | 
			<a href="<?php echo site_url() . '/groups' ?>" id="popular-groups"><?php _e("Popular") ?></a>
		</div>
		<ul id="groups-list" class="item-list">
			<?php foreach ( $groups as $group ) : ?>
				<?php $group = new BP_Groups_Group( $group->id, false ) ?>
				<li>
					<div class="item-avatar">
						<img src="<?php echo $group->avatar_thumb; ?>" alt="<?php echo $group->name ?> Avatar" class="avatar" />
					</div>

					<div class="item">
						<div class="item-title"><?php echo $group->name ?></div>
						<div class="item-meta"><span class="activity"><?php echo bp_core_get_last_activity( $group->date_created, __('created '), __(' ago') ) ?></span></div>
					</div>
				</li>
				<?php $counter++; ?>	
			<?php endforeach; ?>
		</ul>
		
		<?php 
		if ( function_exists('wp_nonce_field') )
			wp_nonce_field( 'groups_widget_groups_list', '_wpnonce-groups' );
		?>
		
		<input type="hidden" name="groups_widget_groups_list_max_groups" id="groups_widget_groups_list_max_groups" value="<?php echo $options['max_groups'] ?>" />
		
	<?php else: ?>
		<div class="widget-error">
			<?php _e('There are no groups to display.') ?>
		</div>
	<?php endif; ?>
	
	<?php echo $after_widget; ?>
<?php
}

function groups_widget_groups_list_control() {
	global $current_blog;
	
	$options = $newoptions = get_blog_option( $current_blog->blog_id, 'groups_widget_groups_list');

	if ( $_POST['groups-widget-groups-list-submit'] ) {
		$newoptions['max_groups'] = strip_tags( stripslashes( $_POST['groups-widget-groups-list-max'] ) );
	}
	
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_blog_option( $current_blog->blog_id, 'groups_widget_groups_list', $options );
	}

	$max_groups = attribute_escape( $options['max_groups'] );
?>
		<p><label for="groups-widget-groups-list-max"><?php _e('Maximum number of groups to show:'); ?><br /> <input class="widefat" id="groups-widget-groups-list-max" name="groups-widget-groups-list-max" type="text" value="<?php echo $max_groups; ?>" style="width: 30%" /></label></p>
		<input type="hidden" id="groups-widget-groups-list-submit" name="groups-widget-groups-list-submit" value="1" />
<?php
}
