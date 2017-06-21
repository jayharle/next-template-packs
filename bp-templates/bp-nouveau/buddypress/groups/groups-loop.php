<?php
/**
 * BuddyPress - Groups Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - bp_legacy_theme_object_filter().
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

bp_nouveau_before_loop(); ?>

<?php if ( bp_get_current_group_directory_type() ) : ?>
	<p class="current-group-type"><?php bp_current_group_directory_type_message() ?></p>
<?php endif; ?>

<?php if ( bp_has_groups( bp_ajax_querystring( 'groups' ) ) ) : ?>

	<?php bp_nouveau_pagination( 'top' ); ?>

	<ul id="groups-list" class="<?php bp_nouveau_loop_classes(); ?>">

	<?php while ( bp_groups() ) : bp_the_group(); ?>

		<li <?php bp_group_class( array( 'item-entry' ) ); ?> data-bp-item-id="<?php bp_group_id(); ?>" data-bp-item-component="groups">
			<div class="list-wrap">

				<?php if ( ! bp_disable_group_avatar_uploads() ) : ?>
					<div class="item-avatar">
						<a href="<?php bp_group_permalink(); ?>"><?php bp_group_avatar( bp_nouveau_avatar_args() ); ?></a>
					</div>
				<?php endif; ?>

				<div class="item">

					<div class="item-block">

						<h2 class="list-title groups-title"><?php bp_group_link(); ?></h2>

						<?php if ( bp_nouveau_group_has_meta() ) : ?>

							<p class="item-meta group-details"><?php bp_nouveau_group_meta(); ?></p>

						<?php endif; ?>

						<p class="last-activity item-meta"><?php printf( __( 'active %s', 'buddypress' ), bp_get_group_last_active() ); ?></p>
				<?php bp_nouveau_groups_loop_buttons( ); ?>

					</div>

					<div class="group-desc"><?php bp_nouveau_group_description_excerpt(); ?></div>

					<?php bp_nouveau_groups_loop_item(); ?>

				</div>


			</div>
		</li>

	<?php endwhile; ?>

	</ul>

	<?php bp_nouveau_pagination( 'bottom' ); ?>

<?php else:

	bp_nouveau_user_feedback( 'groups-loop-none' );

endif; ?>

<?php bp_nouveau_after_loop(); ?>
