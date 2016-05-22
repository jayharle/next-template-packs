<?php
/**
 * BuddyPress - Activity Stream Comment
 *
 * This template is used by bp_activity_comments() functions to show
 * each activity.
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */

/**
 * Fires before the display of an activity comment.
 *
 * @since 1.5.0
 */
do_action( 'bp_before_activity_comment' ); ?>

<li id="acomment-<?php bp_activity_comment_id(); ?>" data-bp-activity-comment-id="<?php bp_activity_comment_id(); ?>">
	<div class="acomment-avatar">
		<a href="<?php bp_activity_comment_user_link(); ?>">
			<?php bp_activity_avatar( 'type=thumb&user_id=' . bp_get_activity_comment_user_id() ); ?>
		</a>
	</div>

	<div class="acomment-meta">
		<?php
		/* translators: 1: user profile link, 2: user name, 3: activity permalink, 4: activity recorded date, 5: activity timestamp, 6: activity human time since */
		printf( __( '<a href="%1$s">%2$s</a> replied <a href="%3$s" class="activity-time-since"><time class="time-since" datetime="%4$s" data-bp-timestamp="%5$d">%6$s</time></a>', 'bp-next' ),
			esc_url( bp_get_activity_comment_user_link() ),
			esc_html( bp_get_activity_comment_name() ),
			esc_url( bp_get_activity_comment_permalink() ),
			esc_attr( bp_get_activity_comment_date_recorded_raw() ),
			esc_attr( strtotime( bp_get_activity_comment_date_recorded_raw() ) ),
			esc_attr( bp_get_activity_comment_date_recorded() )
		);
		?>
	</div>

	<div class="acomment-content"><?php bp_activity_comment_content(); ?></div>

	<div class="acomment-options">

		<?php if ( is_user_logged_in() && bp_activity_can_comment_reply( bp_activity_current_comment() ) ) : ?>

			<a href="#acomment-<?php bp_activity_comment_id(); ?>" class="acomment-reply bp-primary-action" id="acomment-reply-<?php bp_activity_id(); ?>-from-<?php bp_activity_comment_id(); ?>"><?php _e( 'Reply', 'bp-next' ); ?></a>

		<?php endif; ?>

		<?php if ( bp_activity_user_can_delete() ) : ?>

			<a href="<?php bp_activity_comment_delete_link(); ?>" class="delete acomment-delete confirm bp-secondary-action" rel="nofollow"><?php _e( 'Delete', 'bp-next' ); ?></a>

		<?php endif; ?>

		<?php

		/**
		 * Fires after the defualt comment action options display.
		 *
		 * @since 1.6.0
		 */
		do_action( 'bp_activity_comment_options' ); ?>

	</div>

	<?php bp_activity_recurse_comments( bp_activity_current_comment() ); ?>
</li>

<?php

/**
 * Fires after the display of an activity comment.
 *
 * @since 1.5.0
 */
do_action( 'bp_after_activity_comment' ); ?>