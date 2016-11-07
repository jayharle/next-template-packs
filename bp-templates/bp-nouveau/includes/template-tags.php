<?php
/**
 * Common template tags
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Fire specific hooks at various places of templates
 *
 * @since 1.0.0
 *
 * @param array $pieces The list of terms of the hook to join.
 */
function bp_nouveau_hook( $pieces = array() ) {
	if ( empty( $pieces ) ) {
		return;
	}

	$bp_prefix = reset( $pieces );
	if ( 'bp' !== $bp_prefix ) {
		array_unshift( $pieces, 'bp' );
	}

	$hook = join( '_', $pieces );

	do_action( $hook );
}

/**
 * Fire plugin hooks in the plugins.php template (Groups and Members single items)
 *
 * @since 1.0.0
 *
 * @param string The suffix of the hook.
 */
function bp_nouveau_plugin_hook( $suffix = '' ) {
	if ( empty( $suffix ) ) {
		return;
	}

	/**
	 * Fires and displays content/title for plugins using the BP_Group_Extension.
	 *
	 * @since 1.0.0 (BuddyPress)
	 */
	return bp_nouveau_hook( array(
		'bp',
		'template',
		$suffix,
	) );
}

/**
 * Fire friend hooks
 *
 * @todo Move this into bp-nouveau/includes/friends/template-tags.php
 *       once we'll need other friends template tags.
 *
 * @since 1.0.0
 *
 * @param string The suffix of the hook.
 */
function bp_nouveau_friend_hook( $suffix = '' ) {
	if ( empty( $suffix ) ) {
		return;
	}

	/**
	 * @since 1.1.0 (BuddyPress)
	 */
	return bp_nouveau_hook( array(
		'bp',
		'friend',
		$suffix,
	) );
}

/**
 * Add classes to style the template notice/feedback message
 *
 * @since  1.0.0
 *
 * @return string Css class Output
 */
function bp_nouveau_template_message_classes() {
	$classes = array( 'bp-feedback', 'bp-messages' );

	if ( ! empty( bp_nouveau()->template_message['message'] ) ) {
		$classes[] = 'bp-template-notice';
	}

	$classes[] = bp_nouveau_get_template_message_type();
	echo join( ' ', array_map( 'sanitize_html_class', $classes ) );
}

	/**
	 * Get the template notice/feedback message type
	 *
	 * @since 1.0.0
	 *
	 * @return string the type of the notice. Defaults to error
	 */
	function bp_nouveau_get_template_message_type() {
		$bp_nouveau = bp_nouveau();
		$type       = 'error';

		if ( ! empty( $bp_nouveau->template_message['type'] ) ) {
			$type = $bp_nouveau->template_message['type'];
		} elseif ( ! empty( $bp_nouveau->user_feedback['type'] ) ) {
			$type = $bp_nouveau->user_feedback['type'];
		}

		return $type;
	}

/**
 * Checks if a template notice/feedback message is set
 *
 * @since 1.0.0
 *
 * @return bool True if a template notice is set. False otherwise.
 */
function bp_nouveau_has_template_message() {
	$bp_nouveau = bp_nouveau();

	if ( empty( $bp_nouveau->template_message['message'] ) && empty( $bp_nouveau->user_feedback ) ) {
		return false;
	}

	return true;
}

/**
 * Checks if the template notice/feedback message needs a dismiss button
 *
 * @since 1.0.0
 *
 * @return bool True if a template notice needs a dismiss button. False otherwise.
 */
function bp_nouveau_has_dismiss_button() {
	$bp_nouveau = bp_nouveau();

	if ( ! empty( $bp_nouveau->template_message['message'] ) || ! empty( $bp_nouveau->user_feedback['dismiss'] ) ) {
		return true;
	}

	return false;
}

/**
 * Ouptut the dismiss type.
 *
 * @since 1.0.0
 *
 * @return string The dismiss type.
 */
function bp_nouveau_dismiss_button_type() {
	$bp_nouveau = bp_nouveau();
	$type = 'clear';

	if ( ! empty( $bp_nouveau->user_feedback['dismiss'] ) ) {
		$type = $bp_nouveau->user_feedback['dismiss'];
	}

	echo esc_attr( $type );
}

/**
 * Displays a template notice/feedback message.
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_template_message() {
	echo bp_nouveau_get_template_message();
}

	/**
	 * Get the template notice/feedback message and make sure core filter is applied.
	 *
	 * @since  1.0.0
	 *
	 * @return string HTML Output.
	 */
	function bp_nouveau_get_template_message() {
		$bp_nouveau = bp_nouveau();

		if ( ! empty( $bp_nouveau->user_feedback['message'] ) ) {
			$user_feedback = $bp_nouveau->user_feedback['message'];
			foreach ( array( 'wp_kses_data', 'wp_unslash', 'wptexturize', 'convert_smilies', 'convert_chars' ) as $filter ) {
				$user_feedback = call_user_func( $filter, $user_feedback );
			}

			return $user_feedback;
		} elseif ( ! empty( $bp_nouveau->template_message['message'] ) ) {
			/**
			 * Filters the 'template_notices' feedback message content.
			 *
			 * @since 1.5.5 (BuddyPress)
			 *
			 * @param string $template_message Feedback message content.
			 * @param string $type             The type of message being displayed.
			 *                                 Either 'updated' or 'error'.
			 */
			return apply_filters( 'bp_core_render_message_content', $bp_nouveau->template_message['message'], bp_nouveau_get_template_message_type() );
		}
	}

/**
 * Template tag to display feedback notices to users, if there are to display
 *
 * @since 1.0.0
 *
 * @return HTML Output.
 */
function bp_nouveau_template_notices() {
	$bp         = buddypress();
	$bp_nouveau = bp_nouveau();

	if ( ! empty( $bp->template_message ) ) {
		// Clone BuddyPress template message to avoid altering it.
		$template_message = array( 'message' => $bp->template_message );

		if ( ! empty( $bp->template_message_type ) ) {
			$template_message['type'] = $bp->template_message_type;
		}

		$bp_nouveau->template_message = $template_message;


		bp_get_template_part( 'common/notices/template-notices' );

		// Reset just after rendering it.
		$bp_nouveau->template_message = array();

		/**
		 * Fires after the display of any template_notices feedback messages.
		 *
		 * @since 1.1.0 (BuddyPress)
		 */
		do_action( 'bp_core_render_message' );
	}

	/**
	 * Fires towards the top of template pages for notice display.
	 *
	 * @since 1.0.0 (BuddyPress)
	 */
	do_action( 'template_notices' );
}

/**
 * Displays a feedback message to the user.
 *
 * @since 1.0.0
 *
 * @param  string  $feedback_id The ID of the message to display
 * @return string  HTML Output.
 */
function bp_nouveau_user_feedback( $feedback_id = '' ) {
	if ( ! isset( $feedback_id ) ) {
		return '';
	}

	$bp_nouveau = bp_nouveau();
	$feedback   = bp_nouveau_get_user_feedback( $feedback_id );

	if ( ! $feedback ) {
		return;
	}

	if ( ! empty( $feedback['before'] ) ) {
		do_action( $feedback['before'] );
	}

	$bp_nouveau->user_feedback = $feedback;

	/**
	 * Filter here if you wish to use a different templates than the notice one.
	 *
	 * @since 1.0.0
	 *
	 * @param string path to your template part.
	 */
	bp_get_template_part( apply_filters( 'bp_nouveau_user_feedback_template', 'common/notices/template-notices' ) );

	if ( ! empty( $feedback['after'] ) ) {
		do_action( $feedback['after'] );
	}

	// Reset the feedback message.
	$bp_nouveau->user_feedback =array();
}

/**
 * Template tag to wrap the before component loop
 *
 * @since  1.0.0
 */
function bp_nouveau_before_loop() {
	$component = bp_current_component();

	if ( bp_is_group() ) {
		$component = bp_current_action();
	}

	/**
	 * Fires before the start of the component loop.
	 *
	 * @since 1.2.0
	 */
	do_action( "bp_before_{$component}_loop" );
}

/**
 * Template tag to wrap the after component loop
 *
 * @since  1.0.0
 */
function bp_nouveau_after_loop() {
	$component = bp_current_component();

	if ( bp_is_group() ) {
		$component = bp_current_action();
	}

	/**
	 * Fires after the finish of the component loop.
	 *
	 * @since 1.2.0
	 */
	do_action( "bp_after_{$component}_loop" );
}

/**
 * Pagination for loops
 *
 * @since 1.0.0
 */
function bp_nouveau_pagination( $position = null ) {
	$component = bp_current_component();

	if ( ! bp_is_active( $component ) ) {
		return;
	}

	$screen          = 'dir';
	$pagination_type = $component;

	if ( bp_is_user() ) {
		$screen = 'user';
	} elseif ( bp_is_group() ) {
		$screen          = 'group';
		$pagination_type = bp_current_action();

		if ( bp_is_group_admin_page() ) {
			$pagination_type = bp_action_variable( 0 );
		}
	}

	switch( $pagination_type ) {

		case 'blogs' :

			$pag_count   = bp_get_blogs_pagination_count();
			$pag_links   = bp_get_blogs_pagination_links();
			$top_hook    = 'bp_before_directory_blogs_list';
			$bottom_hook = 'bp_after_directory_blogs_list';
			$page_arg    = $GLOBALS['blogs_template']->pag_arg;

		break;

		case 'members'        :
		case 'friends'        :
		case 'manage-members' :

			$pag_count   = bp_get_members_pagination_count();
			$pag_links   = bp_get_members_pagination_links();

			// Groups single items are not using these hooks
			if ( ! bp_is_group() ) {
				$top_hook    = 'bp_before_directory_members_list';
				$bottom_hook = 'bp_after_directory_members_list';
			}

			$page_arg    = $GLOBALS['members_template']->pag_arg;

		break;

		case 'groups' :

			$pag_count   = bp_get_groups_pagination_count();
			$pag_links   = bp_get_groups_pagination_links();
			$top_hook    = 'bp_before_directory_groups_list';
			$bottom_hook = 'bp_after_directory_groups_list';
			$page_arg    = $GLOBALS['groups_template']->pag_arg;

		break;

		case 'notifications' :

			$pag_count   = bp_get_notifications_pagination_count();
			$pag_links   = bp_get_notifications_pagination_links();
			$top_hook    = '';
			$bottom_hook = '';
			$page_arg    = buddypress()->notifications->query_loop->pag_arg;

		break;

		case 'membership-requests' :

			$pag_count   = bp_get_group_requests_pagination_count();
			$pag_links   = bp_get_group_requests_pagination_links();
			$top_hook    = '';
			$bottom_hook = '';
			$page_arg    = $GLOBALS['requests_template']->pag_arg;

		break;
	}

	$count_class = sprintf( '%1$s-%2$s-count-%3$s', $pagination_type, $screen, $position );
	$links_class = sprintf( '%1$s-%2$s-links-%3$s', $pagination_type, $screen, $position );
	?>

	<?php if ( 'bottom' === $position && isset( $bottom_hook ) ) {
		/**
		 * Fires after the component directory list.
		 *
		 * @since 1.1.0
		 */
		do_action( $bottom_hook );
	};?>

	<div class="bp-pagination <?php echo sanitize_html_class( $position ); ?>" data-bp-pagination="<?php echo esc_attr( $page_arg ); ?>">

		<?php if ( $pag_count ) : ?>
			<div class="pag-count <?php echo sanitize_html_class( $count_class ); ?>">

				<p class="pag-data">
					<?php echo $pag_count; ?>
				</p>

			</div>
		<?php endif; ?>

		<?php if ( $pag_links ) : ?>
			<div class="bp-pagination-links <?php echo sanitize_html_class( $links_class ); ?>">

				<p class="pag-data">
					<?php echo $pag_links; ?>
				</p>

			</div>
		<?php endif; ?>

	</div>

	<?php if ( 'top' === $position && isset( $top_hook ) ) {
		/**
		 * Fires before the component directory list.
		 *
		 * @since 1.1.0 (BuddyPress)
		 */
		do_action( $top_hook );
	};?>

	<?php
	return;
}

/**
 * Display the component's loop classes
 *
 * @since  1.0.0
 *
 * @return string CSS classes
 */
function bp_nouveau_loop_classes() {
	echo bp_nouveau_get_loop_classes();
}

	/**
	 * Get the component's loop classes
	 *
	 * @since  1.0.0
	 *
	 * @return string space separated value of classes.
	 */
	function bp_nouveau_get_loop_classes() {
		$bp_nouveau = bp_nouveau();

		/**
		* @todo: this function could do with passing args so we can pass simple strings in  or array of strings
		*/

		// The $component is faked if it's the single group member loop
		if( !bp_is_directory() && ( bp_is_group() && 'members' === bp_current_action() ) ) :
			$component  = 'members_group';
		elseif( ! bp_is_directory() && ( bp_is_user() && 'my-friends' === bp_current_action() ) ):
			$component  = 'members_friends';
		else :
			$component  = sanitize_key( bp_current_component() );
		endif;

			// if this is friends loop we still need the general members-list class
			$members_loop_class = '';
			if( bp_is_user() && 'my-friends' === bp_current_action() ) {
				$member_loop_class = 'members-list';
			}

		$classes = array(
			'item-list',
			$member_loop_class,
			sprintf( '%s-list', str_replace('_', '-', $component ) ),
			'bp-list',
		);

		$available_components = array(
			'members' => true,
			'groups'  => true,
			'blogs'   => true,
			// technically not a component but allows us to check
			// the single group members loop as a seperate loop.
			'members_group' => true,
			'members_friends' => true,
		);

		// Only the available components supports custom layouts.
		if ( ! empty( $available_components[ $component ] ) && ( bp_is_directory() || bp_is_group() || bp_is_user() ) ) {
			$customizer_option = sprintf( '%s_layout', $component );
			$layout_prefs      = bp_nouveau_get_temporary_setting( $customizer_option, bp_nouveau_get_appearance_settings( $customizer_option ) );

			if ( ! empty( $layout_prefs ) && (int) $layout_prefs > 1 ) {
				$grid_classes = bp_nouveau_customizer_grid_choices( 'classes' );

				if ( isset( $grid_classes[ $layout_prefs ] ) ) {
					$classes = array_merge( $classes, array(
						'grid',
						$grid_classes[ $layout_prefs ],
					) );
				}

				// Set the global for a later use.
				$bp_nouveau->{$component}->loop_layout = $layout_prefs;
			}
		}

		/**
		 * Filter here to edit/add classes.
		 *
		 * NB: you can also directly add classes into the template parts.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $classes   The list of classes.
		 * @param string $component The current component's loop.
		 */
		$class_list = (array) apply_filters( 'bp_nouveau_get_loop_classes', $classes, $component );

		return join( ' ', array_map( 'sanitize_html_class', $class_list ) );
	}

/**
 * Checks if the layout preferences is set to grid (2 or more columns).
 *
 * @since  1.0.0
 *
 * @return bool True if loop is displayed in grid mod. False otherwise.
 */
function bp_nouveau_loop_is_grid() {
	$bp_nouveau = bp_nouveau();
	$component  = sanitize_key( bp_current_component() );

	return ! empty( $bp_nouveau->{$component}->loop_layout ) && $bp_nouveau->{$component}->loop_layout > 1;
}

/**
 * Returns the number of columns of the layout preferences.
 *
 * @since 1.0.0
 *
 * @return int The number of columns.
 */
function bp_nouveau_loop_get_grid_columns() {
	$bp_nouveau = bp_nouveau();
	$component  = sanitize_key( bp_current_component() );

	$columns = 1;

	if ( ! empty( $bp_nouveau->{$component}->loop_layout ) ) {
		$columns = (int) $bp_nouveau->{$component}->loop_layout;
	}

	return apply_filters( 'bp_nouveau_loop_get_grid_columns', $columns );
}

/**
 * Get the full size avatar args.
 *
 * @since  1.0.0
 *
 * @return array The avatar arguments.
 */
function bp_nouveau_avatar_args() {
	return apply_filters( 'bp_nouveau_avatar_args',  array(
		'type'   => 'full',
		'width'  => bp_core_avatar_full_width(),
		'height' => bp_core_avatar_full_height(),
	) );
}

/** Template Tags for BuddyPress navigations **********************************/

/**
 * This is the BP Nouveau Navigation Loop.
 *
 * It can be used by any object using the
 * BP_Core_Nav API introduced in BuddyPress 2.6.0.
 */

/**
 * Init the Navigation Loop and checks it has items.
 *
 * @since  1.0.0
 *
 * @param  array  $args {
 *     Array of arguments.
 *
 *     @type string $type                    The type of Nav to get (primary or secondary)
 *                                           Default 'primary'. Required.
 *     @type string $object                  The object to get the nav for (eg: 'directory', 'group_manage',
 *                                           or any custom object). Default ''. Optional
 *     @type bool   $user_has_access         Used by the secondary member's & group's nav. Default true. Optional.
 *     @type bool   $show_for_displayed_user Used by the primary member's nav. Default true. Optional.
 * }
 * @return bool         True if the Nav contains items. False otherwise.
 */
function bp_nouveau_has_nav( $args = array() ) {
	$bp_nouveau = bp_nouveau();

	$n = wp_parse_args( $args, array(
		'type'                    => 'primary',
		'object'                  => '',
		'user_has_access'         => true,
		'show_for_displayed_user' => true,
	) );

	if ( empty( $n['type'] ) ) {
		return false;
	}

	$nav                       = array();
	$bp_nouveau->displayed_nav = '';
	$bp_nouveau->object_nav    = $n['object'];

	if ( bp_is_directory() || 'directory' === $bp_nouveau->object_nav ) {
		$bp_nouveau->displayed_nav = 'directory';
		$nav                       = $bp_nouveau->directory_nav->get_primary();

	// So far it's only possible to build a Group nav when displaying it.
	} elseif ( bp_is_group() ) {
		$bp_nouveau->displayed_nav = 'groups';
		$parent_slug               = bp_get_current_group_slug();
		$group_nav                 = buddypress()->groups->nav;

		if ( 'group_manage' === $bp_nouveau->object_nav && bp_is_group_admin_page() ) {
			$parent_slug .= '_manage';

		/**
		 * If it's not the Admin tabs, reorder the Group's nav according to the
		 * customizer setting.
		 */
		} else {
			bp_nouveau_set_nav_item_order( $group_nav, bp_nouveau_get_appearance_settings( 'group_nav_order' ), $parent_slug );
		}

		$nav = $group_nav->get_secondary( array(
			'parent_slug'     => $parent_slug,
			'user_has_access' => (bool) $n['user_has_access'],
		) );

	// Build the nav for the displayed user
	} elseif ( bp_is_user() ) {
		$bp_nouveau->displayed_nav = 'personal';
		$user_nav                  = buddypress()->members->nav;

		if ( 'secondary' === $n['type'] ) {
			$nav = $user_nav->get_secondary( array(
				'parent_slug'     => bp_current_component(),
				'user_has_access' => (bool) $n['user_has_access'],
			) );
		} else {
			$args = array();

			if ( true === (bool) $n['show_for_displayed_user'] && ! bp_is_my_profile() ) {
				$args = array( 'show_for_displayed_user' => true );
			}

			// Reorder the user's primary nav according to the customizer setting.
			bp_nouveau_set_nav_item_order( $user_nav, bp_nouveau_get_appearance_settings( 'user_nav_order' ) );

			$nav = $user_nav->get_primary( $args );
		}

	} elseif ( ! empty( $bp_nouveau->object_nav ) ) {
		$bp_nouveau->displayed_nav = $bp_nouveau->object_nav;

		/**
		 * Use the filter to use your specific Navigation.
		 * Use the $n param to check for your custom object
		 *
		 * @since 1.0.0
		 *
		 * @param  array $nav The list of item navigations generated
		 *                    by the BP_Core_Nav API.
		 * @param  array $n   The arguments of the Navigation loop.
		 */
		$nav = apply_filters( 'bp_nouveau_get_nav', $nav, $n );
	}

	$bp_nouveau->sorted_nav = array_values( $nav );

	if ( 0 === count( $bp_nouveau->sorted_nav ) || ! $bp_nouveau->displayed_nav ) {
		unset( $bp_nouveau->sorted_nav, $bp_nouveau->displayed_nav, $bp_nouveau->object_nav );

		return false;
	}

	$bp_nouveau->current_nav_index = 0;
	return true;
}

/**
 * Checks there are still nav items to display.
 *
 * @since  1.0.0
 *
 * @return bool True if there are still items to display.
 *              False otherwise.
 */
function bp_nouveau_nav_items() {
	$bp_nouveau = bp_nouveau();

	if ( isset( $bp_nouveau->sorted_nav[ $bp_nouveau->current_nav_index ] ) ) {
		return true;
	}

	$bp_nouveau->current_nav_index = 0;
	unset( $bp_nouveau->current_nav_item );

	return false;
}

/**
 * Sets the current nav item and prepare the navigation loop
 * to iterate to next one.
 *
 * @since  1.0.0
 */
function bp_nouveau_nav_item() {
	$bp_nouveau = bp_nouveau();

	$bp_nouveau->current_nav_item   = $bp_nouveau->sorted_nav[ $bp_nouveau->current_nav_index ];
	$bp_nouveau->current_nav_index += 1;
}

/**
 * Displays the nav item ID.
 *
 * @since 1.0.0
 */
function bp_nouveau_nav_id() {
	echo bp_nouveau_get_nav_id();
}

	/**
	 * Retrieve the ID attribute of the current nav item.
	 *
	 * @since  1.0.0
	 *
	 * @return string the ID attribute.
	 */
	function bp_nouveau_get_nav_id() {
		$bp_nouveau = bp_nouveau();
		$nav_item   = $bp_nouveau->current_nav_item;

		if ( 'directory' === $bp_nouveau->displayed_nav ) {
			$id = sprintf( '%1$s-%2$s', $nav_item->component, $nav_item->slug );
		} elseif ( 'groups' === $bp_nouveau->displayed_nav || 'personal' ===  $bp_nouveau->displayed_nav ) {
			$id = sprintf( '%1$s-%2$s-li', $nav_item->css_id, $bp_nouveau->displayed_nav );
		} else {
			$id = $nav_item->slug;
		}

		/**
		 * Filter here to edit the ID attribute of the nav.
		 *
		 * @since 1.0.0
		 *
		 * @param string $id       The ID attribute of the nav.
		 * @param object $nav_item The current nav item object.
		 * @param string $value    The current nav in use (eg: 'directory', 'groups', 'personal', etc..).
		 */
		return esc_attr( apply_filters( 'bp_nouveau_get_nav_id', $id, $nav_item, $bp_nouveau->displayed_nav ) );
	}

/**
 * Displays the nav item classes.
 *
 * @since 1.0.0
 */
function bp_nouveau_nav_classes() {
	echo bp_nouveau_get_nav_classes();
}

	/**
	 * Retrieve a space separated list of classes for the current nav item.
	 *
	 * @since  1.0.0
	 *
	 * @return string the list of classes.
	 */
	function bp_nouveau_get_nav_classes() {
		$bp_nouveau = bp_nouveau();
		$nav_item   = $bp_nouveau->current_nav_item;

		$classes = array();
		if ( 'directory' === $bp_nouveau->displayed_nav && ! empty( $nav_item->li_class ) ) {
			$classes = (array) $nav_item->li_class;
		} elseif ( 'groups' === $bp_nouveau->displayed_nav || 'personal' === $bp_nouveau->displayed_nav ) {
			$classes  = array( 'bp-' . $bp_nouveau->displayed_nav . '-tab' );
			$selected = bp_current_action();

			// User's primary nav
			if ( ! empty( $nav_item->primary ) ) {
				$selected = bp_current_component();

			// Group Admin Tabs.
			} elseif ( 'group_manage' === $bp_nouveau->object_nav ) {
				$selected = bp_action_variable( 0 );
				$classes  = array( 'bp-' . $bp_nouveau->displayed_nav . '-admin-tab' );

			// If we are here, it's the member's subnav
			} elseif ( 'personal' === $bp_nouveau->displayed_nav ) {
				$classes  = array( 'bp-' . $bp_nouveau->displayed_nav . '-sub-tab' );
			}

			if ( $nav_item->slug === $selected ) {
				$classes = array_merge( $classes, array( 'current', 'selected' ) );
			}
		}

		if ( ! empty( $classes ) ) {
			$classes = array_map( 'sanitize_html_class', $classes );
		}

		/**
		 * Filter here to edit/add classes.
		 *
		 * NB: you can also directly add classes into the template parts.
		 *
		 * @since 1.0.0
		 *
		 * @param string $value    A space separated list of classes.
		 * @param array  $classes  The list of classes.
		 * @param object $nav_item The current nav item object.
		 * @param string $value    The current nav in use (eg: 'directory', 'groups', 'personal', etc..).
		 */
		$classes_list = apply_filters( 'bp_nouveau_get_classes', join( ' ', $classes ), $classes, $nav_item, $bp_nouveau->displayed_nav );

		if ( empty( $classes_list ) ) {
			return;
		}

		return esc_attr( $classes_list );
	}

/**
 * Displays the nav item scope.
 *
 * @since 1.0.0
 */
function bp_nouveau_nav_scope() {
	echo bp_nouveau_get_nav_scope();
}

	/**
	 * Retrieve the specific scope for the current nav item.
	 *
	 * @since  1.0.0
	 *
	 * @return string the specific scope of the nav.
	 */
	function bp_nouveau_get_nav_scope() {
		$bp_nouveau = bp_nouveau();
		$nav_item   = $bp_nouveau->current_nav_item;

		$scope = array();
		if ( 'directory' === $bp_nouveau->displayed_nav ) {
			$scope = array( 'data-bp-scope' => $nav_item->slug );
		} elseif ( 'personal' === $bp_nouveau->displayed_nav && ! empty( $nav_item->secondary ) ) {
			$scope = array( 'data-bp-user-scope' => $nav_item->slug );
		} else {
			/**
			 * Filter here to add your own scope.
			 *
			 * @since 1.0.0
			 *
			 * @param string $scope    An array containing the key and the value for your scope.
			 * @param object $nav_item The current nav item object.
			 * @param string $value    The current nav in use (eg: 'directory', 'groups', 'personal', etc..).
			 */
			$scope = apply_filters( 'bp_nouveau_set_nav_scope', $scope, $nav_item, $bp_nouveau->displayed_nav );
		}

		if ( empty( $scope ) ) {
			return;
		}

		return bp_get_form_field_attributes( 'scope', $scope );
	}

/**
 * Displays the nav item link.
 *
 * @since 1.0.0
 */
function bp_nouveau_nav_link() {
	echo bp_nouveau_get_nav_link();
}

	/**
	 * Retrieve the link for the current nav item.
	 *
	 * @since  1.0.0
	 *
	 * @return string The link for the nav item.
	 */
	function bp_nouveau_get_nav_link() {
		$bp_nouveau = bp_nouveau();
		$nav_item   = $bp_nouveau->current_nav_item;

		$link = '#';
		if ( ! empty( $nav_item->link ) ) {
			$link = $nav_item->link;
		}

		if ( 'personal' === $bp_nouveau->displayed_nav && ! empty( $nav_item->primary ) ) {
			if ( bp_loggedin_user_domain() ) {
				$link = str_replace( bp_loggedin_user_domain(), bp_displayed_user_domain(), $link );
			} else {
				$link = trailingslashit( bp_displayed_user_domain() . $link );
			}
		}

		/**
		 * Filter here to edit the link of the nav.
		 *
		 * @since 1.0.0
		 *
		 * @param string $link     The link for the nav item.
		 * @param object $nav_item The current nav item object.
		 * @param string $value    The current nav in use (eg: 'directory', 'groups', 'personal', etc..).
		 */
		return esc_url( apply_filters( 'bp_nouveau_get_nav_link', $link, $nav_item, $bp_nouveau->displayed_nav ) );
	}

/**
 * Displays the nav item link ID.
 *
 * @since 1.0.0
 */
function bp_nouveau_nav_link_id() {
	echo bp_nouveau_get_nav_link_id();
}

	/**
	 * Retrieve the id attribute of the link for the current nav item.
	 *
	 * @since  1.0.0
	 *
	 * @return string The link id for the nav item.
	 */
	function bp_nouveau_get_nav_link_id() {
		$bp_nouveau = bp_nouveau();
		$nav_item   = $bp_nouveau->current_nav_item;

		$link_id = '';
		if ( ( 'groups' === $bp_nouveau->displayed_nav || 'personal' === $bp_nouveau->displayed_nav ) && ! empty( $nav_item->css_id ) ) {
			$link_id = $nav_item->css_id;

			if ( ! empty( $nav_item->primary ) && 'personal' === $bp_nouveau->displayed_nav ) {
				$link_id = 'user-' . $link_id;
			}
		} else {
			$link_id = $nav_item->slug;
		}

		/**
		 * Filter here to edit the link id attribute of the nav.
		 *
		 * @since 1.0.0
		 *
		 * @param string $link_id  The link id attribute for the nav item.
		 * @param object $nav_item The current nav item object.
		 * @param string $value    The current nav in use (eg: 'directory', 'groups', 'personal', etc..).
		 */
		return esc_attr( apply_filters( 'bp_nouveau_get_nav_link_id', $link_id, $nav_item, $bp_nouveau->displayed_nav ) );
	}

/**
 * Displays the nav item link title.
 *
 * @since 1.0.0
 */
function bp_nouveau_nav_link_title() {
	echo bp_nouveau_get_nav_link_title();
}

	/**
	 * Retrieve the title attribute of the link for the current nav item.
	 *
	 * @since  1.0.0
	 *
	 * @return string The link title for the nav item.
	 */
	function bp_nouveau_get_nav_link_title() {
		$bp_nouveau = bp_nouveau();
		$nav_item   = $bp_nouveau->current_nav_item;

		$title = '';
		if ( 'directory' === $bp_nouveau->displayed_nav && ! empty( $nav_item->title ) ) {
			$title = $nav_item->title;
		} elseif ( ( 'groups' === $bp_nouveau->displayed_nav || 'personal' === $bp_nouveau->displayed_nav ) && ! empty( $nav_item->name ) ) {
			$title = $nav_item->name;
		}

		/**
		 * Filter here to edit the link title attribute of the nav.
		 *
		 * @since 1.0.0
		 *
		 * @param string $title    The link title attribute for the nav item.
		 * @param object $nav_item The current nav item object.
		 * @param string $value    The current nav in use (eg: 'directory', 'groups', 'personal', etc..).
		 */
		return esc_attr( apply_filters( 'bp_nouveau_get_nav_link_title', $title, $nav_item, $bp_nouveau->displayed_nav ) );
	}

/**
 * Displays the nav item link html text.
 *
 * @since 1.0.0
 */
function bp_nouveau_nav_link_text() {
	echo bp_nouveau_get_nav_link_text();
}

	/**
	 * Retrieve the html text of the link for the current nav item.
	 *
	 * @since  1.0.0
	 *
	 * @return string The html text for the nav item.
	 */
	function bp_nouveau_get_nav_link_text() {
		$bp_nouveau = bp_nouveau();
		$nav_item   = $bp_nouveau->current_nav_item;

		$link_text = '';
		if ( 'directory' === $bp_nouveau->displayed_nav && ! empty( $nav_item->text ) ) {
			$link_text = $nav_item->text;
		} elseif ( ( 'groups' === $bp_nouveau->displayed_nav || 'personal' === $bp_nouveau->displayed_nav ) && ! empty( $nav_item->name ) ) {
			$link_text = _bp_strip_spans_from_title( $nav_item->name );
		}

		/**
		 * Filter here to edit the html text of the nav.
		 *
		 * @since 1.0.0
		 *
		 * @param string $link_text The html text of the nav item.
		 * @param object $nav_item  The current nav item object.
		 * @param string $value     The current nav in use (eg: 'directory', 'groups', 'personal', etc..).
		 */
		return esc_html( apply_filters( 'bp_nouveau_get_nav_link_text', $link_text, $nav_item, $bp_nouveau->displayed_nav ) );
	}

/**
 * Checks if the nav item has a count attribute.
 *
 * @since 1.0.0
 */
function bp_nouveau_nav_has_count() {
	$bp_nouveau = bp_nouveau();
	$nav_item   = $bp_nouveau->current_nav_item;

	$count = false;
	if ( 'directory' === $bp_nouveau->displayed_nav ) {
		$count = $nav_item->count;
	} elseif ( 'groups' === $bp_nouveau->displayed_nav && 'members' === $nav_item->slug ) {
		$count = 0 !== (int) groups_get_current_group()->total_member_count;
	} elseif ( 'personal' === $bp_nouveau->displayed_nav && ! empty( $nav_item->primary ) ) {
		$count = (bool) strpos( $nav_item->name, '="count"' );
	}

	/**
	 * Filter here to edit whether the nav has a count attribute.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $value     True if the nav has a count attribute. False otherwise
	 * @param object $nav_item  The current nav item object.
	 * @param string $value     The current nav in use (eg: 'directory', 'groups', 'personal', etc..).
	 */
	return apply_filters( 'bp_nouveau_nav_has_count', false !== $count, $nav_item, $bp_nouveau->displayed_nav );
}

/**
 * Displays the nav item count attribute.
 *
 * @since 1.0.0
 */
function bp_nouveau_nav_count() {
	echo bp_nouveau_get_nav_count();
}

	/**
	 * Retrieve the count attribute for the current nav item.
	 *
	 * @since  1.0.0
	 *
	 * @return string The count attribute for the nav item.
	 */
	function bp_nouveau_get_nav_count() {
		$bp_nouveau = bp_nouveau();
		$nav_item   = $bp_nouveau->current_nav_item;

		$count = 0;
		if ( 'directory' === $bp_nouveau->displayed_nav ) {
			$count = $nav_item->count;
		} elseif ( 'groups' === $bp_nouveau->displayed_nav && 'members' === $nav_item->slug ) {
			$count = number_format( groups_get_current_group()->total_member_count );

		/**
		 * imho BuddyPress shouldn't add html tags inside Nav attributes...
		 */
		} elseif ( 'personal' === $bp_nouveau->displayed_nav && ! empty( $nav_item->primary ) ) {
			preg_match( '/\<span.*\>(.?)\<\/span\>/', $nav_item->name, $match );

			if ( ! empty( $match[1] ) ) {
				$count = number_format( $match[1] );
			}
		}

		/**
		 * Filter here to edit the count attribute for the nav item.
		 *
		 * @since 1.0.0
		 *
		 * @param string $count    The count attribute for the nav item.
		 * @param object $nav_item The current nav item object.
		 * @param string $value    The current nav in use (eg: 'directory', 'groups', 'personal', etc..).
		 */
		return esc_attr( apply_filters( 'bp_nouveau_get_nav_count', $count, $nav_item, $bp_nouveau->displayed_nav ) );
	}

/** Template tags specific to the Directory navs ******************************/

/**
 * Displays the directory nav class.
 *
 * @since 1.0.0
 */
function bp_nouveau_directory_type_navs_class() {
	echo bp_nouveau_get_directory_type_navs_class();
}

	/**
	 * Gets the directory nav class.
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_directory_type_navs_class() {
		$class = sprintf( '%s-type-navs', bp_current_component() );

		return sanitize_html_class( $class );
	}

/**
 * Displays the directory nav item list class.
 *
 * @since 1.0.0
 */
function bp_nouveau_directory_list_class() {
	echo bp_nouveau_get_directory_list_class();
}

	/**
	 * Gets the directory nav item list class.
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_directory_list_class() {
		$class = sprintf( '%s-nav', bp_current_component() );

		return sanitize_html_class( $class );
	}

/**
 * Displays the directory nav item object (data-bp attribute).
 *
 * @since 1.0.0
 */
function bp_nouveau_directory_nav_object() {
	echo bp_nouveau_get_directory_nav_object();
}

	/**
	 * Gets the directory nav item object.
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_directory_nav_object() {
		$nav_item = bp_nouveau()->current_nav_item;

		if ( ! $nav_item->component ) {
			return;
		}

		return esc_attr( $nav_item->component );
	}

/** Template tags for the single item navs ***********************************/

/**
 * Output main BuddyPress container classes
 *
 * @since  1.0.0
 *
 * @return string CSS classes
 */
function bp_nouveau_buddypress_classes() {
	echo bp_nouveau_get_buddypress_classes();
}

	/**
	 * Returns the main BuddyPress container classes
	 *
	 * @since  1.0.0
	 *
	 * @return string CSS classes
	 */
	function bp_nouveau_get_buddypress_classes() {
		$classes    = array( 'buddypress' );
		$component  = bp_current_component();
		$bp_nouveau = bp_nouveau();

		if ( bp_is_user() ) {
			$customizer_option = 'user_nav_display';
			$component         = 'members';
			$user_type = bp_get_member_type( bp_displayed_user_id() );
			$member_type_class = ( $user_type )? $user_type : '';

		} elseif ( bp_is_group() ) {
			$customizer_option = 'group_nav_display';

		} elseif ( bp_is_directory() ) {

			switch ( $component ) {
				case 'activity':
					$customizer_option = 'activity_dir_layout';
					break;
				case 'members':
					$customizer_option = 'members_dir_layout';
					break;
				case 'groups':
					$customizer_option = 'groups_dir_layout';
					break;
				case 'blogs':
					$customizer_option = 'sites_dir_layout';
					break;

				default:
					$customizer_option = '';
					break;
			}

		} else {
			$customizer_option = apply_filters( 'bp_nouveau_single_item_display_settings_id', '' );
			$member_type_class = '';
		}

		// Add classes according to site owners preferences.

		if ( $general_settings = bp_nouveau_get_temporary_setting( 'avatar_style', bp_nouveau_get_appearance_settings( 'avatar_style' ) ) ) {
			$classes[] = 'round-avatars';
		}

		if( $member_type_class ) {
			$classes[] = $member_type_class;
		}

		if( $group_type_class ) {
			$classes[] = $group_type_class;
		}

		if ( ! empty( $customizer_option ) ) {
			$layout_prefs  = bp_nouveau_get_temporary_setting( $customizer_option, bp_nouveau_get_appearance_settings( $customizer_option ) );

			if ( ! empty( $layout_prefs ) && (int) $layout_prefs === 1 && ( bp_is_user() || bp_is_group() ) ) {
				$classes[] = 'bp-vertical-nav';

				// Set the global for a later use.
				$bp_nouveau->{$component}->single_primary_nav_layout = $layout_prefs;
			}

			if ( ! empty( $layout_prefs ) && bp_is_directory() ) {
				$classes[] = 'bp-dir-nav-vert';
			}
		}

		$class = array_map( 'sanitize_html_class', $classes );

		return apply_filters( 'bp_nouveau_get_buddypress_classes', join( ' ', $class ), $classes );
	}

/**
 * Output single item nav container classes
 *
 * @since  1.0.0
 *
 * @return string CSS classes
 */
function bp_nouveau_single_item_nav_classes() {
	echo bp_nouveau_get_single_item_nav_classes();
}

	/**
	 * Returns the single item nav container classes
	 *
	 * @since  1.0.0
	 *
	 * @return string CSS classes
	 */
	function bp_nouveau_get_single_item_nav_classes() {
		$classes    = array( 'main-navs', 'no-ajax', 'bp-navs', 'single-screen-navs' );
		$component  = bp_current_component();
		$bp_nouveau = bp_nouveau();

		if ( bp_is_user() ) {
			$component = 'members';
			$menu_type = 'users-nav';
		} else {
			$menu_type = 'groups-nav';
		}

		if ( ! empty( $bp_nouveau->{$component}->single_primary_nav_layout ) && (int) $bp_nouveau->{$component}->single_primary_nav_layout === 1 ) {
			$classes[] = 'vertical';
		}

		$classes[] = $menu_type;

		$class = array_map( 'sanitize_html_class', $classes );

		return apply_filters( 'bp_nouveau_get_single_item_nav_classes', join( ' ', $class ), $classes );
	}


/** Template tags for the object search **************************************/

/**
 * Get the search primary object
 *
 * @since 1.0.0
 *
 * @param  string $object The primary object.. Optionnal.
 * @return string The primary object.
 */
function bp_nouveau_get_search_primary_object( $object = '' ) {
	if ( bp_is_user() ) {
		$object = 'member';
	} elseif ( bp_is_group() ) {
		$object = 'group';
	} elseif ( bp_is_directory() ) {
		$object = 'dir';
	} else {
		$object = apply_filters( 'bp_nouveau_get_search_primary_object', $object );
	}

	return $object;
}

/**
 * Get The list of search objects (Primary + secondary)
 *
 * @since 1.0.0
 *
 * @param  array $objects The list of objects. Optionnal.
 * @return array The list of objects.
 */
function bp_nouveau_get_search_objects( $objects = array() ) {
	$primary = bp_nouveau_get_search_primary_object();

	if ( ! $primary ) {
		return $objects;
	}

	$objects = array(
		'primary' => $primary,
	);

	if ( 'member' === $primary || 'dir' === $primary ) {
		$objects['secondary'] = bp_current_component();
	} elseif ( 'group' === $primary ) {
		$objects['secondary'] = bp_current_action();
	} else {
		$objects = apply_filters( 'bp_nouveau_get_search_objects', $objects );
	}

	return $objects;
}

/**
 * Output the search form container classes.
 *
 * @since 1.0.0
 *
 * @return string CSS classes.
 */
function bp_nouveau_search_container_class() {
	$objects = bp_nouveau_get_search_objects();

	echo join( '-search ', array_map( 'sanitize_html_class', $objects ) ) . '-search';
}

/**
 * Output the search form data-bp attribute.
 *
 * @since 1.0.0
 *
 * @param  string $attr The data-bp attribute.
 * @return string The data-bp attribute.
 */
function bp_nouveau_search_object_data_attr( $attr = '' ) {
	$objects = bp_nouveau_get_search_objects();

	if ( ! isset( $objects['secondary'] ) ) {
		return $attr;
	}

	if ( bp_is_active( 'groups' ) && bp_is_group_members() ) {
		$attr = join( '_', $objects );
	} else {
		$attr = $objects['secondary'];
	}

	echo esc_attr( $attr );
}

/**
 * Output a selector ID.
 *
 * @since 1.0.0
 *
 * @param  string $suffix A string to append at the end of the ID.
 * @param  string $sep    The separator to use between each token.
 * @return string The selector ID.
 */
function bp_nouveau_search_selector_id( $suffix = '', $sep = '-' ) {
	$id = join( $sep, array_merge( bp_nouveau_get_search_objects(), (array) $suffix ) );

	echo esc_attr( $id );
}

/**
 * Output the name attribute of a selector.
 *
 * @since 1.0.0
 *
 * @param  string $suffix A string to append at the end of the name.
 * @param  string $sep    The separator to use between each token.
 * @return string The name attribute of a selector.
 */
function bp_nouveau_search_selector_name( $suffix = '', $sep = '_' ) {
	$objects = bp_nouveau_get_search_objects();

	if ( isset( $objects['secondary'] ) && empty( $suffix ) ) {
		$name = bp_core_get_component_search_query_arg( $objects['secondary'] );
	} else {
		$name = join( $sep, array_merge( $objects, (array) $suffix ) );
	}

	echo esc_attr( $name );
}

/**
 * Output the default search text for the search object
 *
 * @since 1.0.0
 *
 * @param  string $text    The default search text for the search object.
 * @param  string $is_attr True if it's to be output inside an attribute. False Otherwise.
 * @return string The default search text.
 */
function bp_nouveau_search_default_text( $text = '', $is_attr = true ) {
	$objects = bp_nouveau_get_search_objects();

	if ( ! empty( $objects['secondary'] ) ) {
		$text = bp_get_search_default_text( $objects['secondary'] );
	}

	if ( $is_attr ) {
		echo esc_attr( $text );
	} else {
		echo esc_html( $text );
	}
}

/**
 * Get the search form template part and fire some do_actions if needed.
 *
 * @since 1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_search_form() {
	bp_get_template_part( 'common/search/search-form' );

	$objects = bp_nouveau_get_search_objects();

	if ( empty( $objects['primary'] ) || empty( $objects['secondary'] ) ) {
		return;
	}

	if ( 'dir' === $objects['primary'] ) {

		if ( 'activity' === $objects['secondary'] ) {
			/**
			 * Fires before the display of the activity syndication options.
			 *
			 * @since 1.2.0 (BuddyPress)
			 */
			do_action( 'bp_activity_syndication_options' );

		} elseif ( 'blogs' === $objects['secondary'] ) {
			/**
			 * Fires inside the unordered list displaying blog sub-types.
			 *
			 * @since 1.5.0 (BuddyPress)
			 */
			do_action( 'bp_blogs_directory_blog_sub_types' );

		} elseif ( 'groups' === $objects['secondary'] ) {
			/**
			 * Fires inside the groups directory group types.
			 *
			 * @since 1.2.0 (BuddyPress)
			 */
			do_action( 'bp_groups_directory_group_types' );

		} elseif ( 'members' === $objects['secondary'] ) {
			/**
			 * Fires inside the members directory member sub-types.
			 *
			 * @since 1.5.0 (BuddyPress)
			 */
			do_action( 'bp_members_directory_member_sub_types' );
		}

	} elseif ( 'group' === $objects['primary'] && 'activity' === $objects['secondary'] ) {
		/**
		 * Fires inside the syndication options list, after the RSS option.
		 *
		 * @since 1.2.0 (BuddyPress)
		 */
		do_action( 'bp_group_activity_syndication_options' );
	}
}

/** Template tags for the directory filters **********************************/

function bp_nouveau_directory_filter_container_id() {
	echo bp_nouveau_get_directory_filter_container_id();
}

	function bp_nouveau_get_directory_filter_container_id() {
		$ids = array(
			'members'  => 'members-order-select',
			'activity' => 'activity-filter-select',
			'groups'   => 'groups-order-select',
			'blogs'    => 'blogs-order-select',
		);

		$component = bp_current_component();

		if ( isset( $ids[ $component ] ) ) {
			return esc_attr( apply_filters( 'bp_nouveau_get_directory_filter_container_id', $ids[ $component ] ) );
		}
	}

function bp_nouveau_directory_filter_id() {
	echo bp_nouveau_get_directory_filter_id();
}

	function bp_nouveau_get_directory_filter_id() {
		$ids = array(
			'members'  => 'members-order-by',
			'activity' => 'activity-filter-by',
			'groups'   => 'groups-order-by',
			'blogs'    => 'blogs-order-by',
		);

		$component = bp_current_component();

		if ( isset( $ids[ $component ] ) ) {
			return esc_attr( apply_filters( 'bp_nouveau_get_directory_filter_id', $ids[ $component ] ) );
		}
	}

function bp_nouveau_directory_filter_label() {
	echo bp_nouveau_get_directory_filter_label();
}

	function bp_nouveau_get_directory_filter_label() {
		$component = bp_current_component();

		$label = __( 'Order By:', 'bp-nouveau' );

		if ( 'activity' === $component ) {
			$label = __( 'Show:', 'bp-nouveau' );
		}

		return esc_html( apply_filters( 'bp_nouveau_get_directory_filter_label', $label ) );
	}

function bp_nouveau_directory_filter_component() {
	echo esc_attr( bp_current_component() );
}

function bp_nouveau_filter_options() {
	echo bp_nouveau_get_filter_options();
}

	function bp_nouveau_get_filter_options() {
		$filters = bp_nouveau_get_component_filters();
		$output = '';

		foreach ( $filters as $key => $value ) {
			$output .= sprintf( '<option value="%1$s">%2$s</option>%3$s',
				esc_attr( $key ),
				esc_html( $value ),
				"\n"
			);
		}

		return $output;
	}

/** Template tags for the customizer ******************************************/

/**
 * Get a link to reach a specific section into the customizer
 *
 * @since  1.0.0
 *
 * @param  array  $args The argument to customize the Customizer link
 * @return string HTML Output
 */
function bp_nouveau_get_customizer_link( $args = array() ) {
	$r = bp_parse_args( $args, array(
		'capability' => 'bp_moderate',
		'object'     => 'user',
		'item_id'    => 0,
		'autofocus'  => '',
		'text'       => '',
	), 'nouveau_get_customizer_link' );

	if ( empty( $r['capability'] ) || empty( $r['autofocus'] ) || empty( $r['text'] ) ) {
		return '';
	}

	if ( ! bp_current_user_can( $r['capability'] ) ) {
		return '';
	}

	if ( bp_is_user() ) {
		$url = rawurlencode( bp_displayed_user_domain() );
	} elseif ( bp_is_group() ) {
		$url = rawurlencode( bp_get_group_permalink( groups_get_current_group() ) );
	} elseif ( ! empty( $r['object'] ) && ! empty( $r['item_id'] ) ) {
		if ( 'user' === $r['object'] ) {
			$url = rawurlencode( bp_core_get_user_domain( $r['item_id'] ) );
		} elseif ( 'group' === $r['object'] ) {
			$group = groups_get_group( array( 'group_id' => $r['item_id'] ) );

			if ( ! empty( $group->id ) ) {
				$url = rawurlencode( bp_get_group_permalink( $group ) );
			}
		}
	}

	if ( empty( $url ) ) {
		return '';
	}

	$customizer_link = add_query_arg( array(
		'autofocus[section]' => $r['autofocus'],
		'url'                => $url,
	), admin_url( 'customize.php' ) );

	return sprintf( '<a href="%1$s">%2$s</a>', esc_url( $customizer_link ), $r['text'] );
}

/** Template tags for signup forms *******************************************/

/**
 * Fire specific hooks into the register template
 *
 * @since 1.0.0
 *
 * @param string $when    'before' or 'after'
 * @param string $prefix  Use it to add terms before the hook name
 */
function bp_nouveau_signup_hook( $when = '', $prefix = '' ) {
	$hook = array( 'bp' );

	if ( ! empty( $when ) ) {
		$hook[] = $when;
	}

	if ( ! empty( $prefix ) ) {
		if ( 'page' === $prefix ) {
			$hook[] = 'register';
		} elseif ( 'steps' === $prefix  ) {
			$hook[] = 'signup';
		}

		$hook[] = $prefix;
	}

	if ( 'page' !== $prefix && 'steps' !== $prefix ) {
		$hook[] = 'fields';
	}

	/**
	 * @since 1.1.0 (BuddyPress)
	 * @since 1.2.4 (BuddyPress) Adds the 'bp_before_signup_profile_fields' action hook
	 * @since 1.9.0 (BuddyPress) Adds the 'bp_signup_profile_fields' action hook
	 */
	return bp_nouveau_hook( $hook );
}

/**
 * Fire specific hooks into the activate template
 *
 * @since 1.0.0
 *
 * @param string $when    'before' or 'after'
 * @param string $prefix  Use it to add terms before the hook name
 */
function bp_nouveau_activation_hook( $when = '', $suffix = '' ) {
	$hook = array( 'bp' );

	if ( ! empty( $when ) ) {
		$hook[] = $when;
	}

	$hook[] = 'activate';

	if ( ! empty( $suffix ) ) {
		$hook[] = $suffix;
	}

	if ( 'page' === $suffix ) {
		$hook[2] = 'activation';
	}

	/**
	 * @since 1.1.0 (BuddyPress)
	 */
	return bp_nouveau_hook( $hook );
}

/**
 * Output the signup form for the requested section
 *
 * @since 1.0.0
 *
 * @param  string     $section The section of fields to get 'account_details' or 'blog_details'. Required.
 *                             Default: 'account_details'.
 * @return string              HTML Output.
 */
function bp_nouveau_signup_form( $section = 'account_details' ) {
	$fields = bp_nouveau_get_signup_fields( $section );

	if ( ! $fields ) {
		return;
	}

	foreach ( $fields as $name => $attributes ) {
		list( $label, $required, $value, $attribute_type, $type, $class ) = array_values( $attributes );

		if ( $required ) {
			$required = ' ' . _x( '(required)', 'signup required field', 'bp-nouveau' );
		}

		// Text fields are using strings, radios are using their inputs
		$label_output = '<label for="%1$s">%2$s</label>';
		$id           = $name;

		// Output the label for regular fields
		if ( 'radio' !== $type ) {
			printf( $label_output, esc_attr( $name ), esc_html( sprintf( $label, $required ) ) );

			if ( ! empty( $value ) && is_callable( $value ) ) {
				$value = call_user_func( $value );
			}

		// Handle the specific case of Site's privacy differently
		} elseif ( 'signup_blog_privacy_private' !== $name ) {
			?>
				<span class="label">
					<?php esc_html_e( 'I would like my site to appear in search engines, and in public listings around this network.', 'bp-nouveau' ); ?>
				</span>
			<?php
		}

		// Set the additional attributes
		if ( $attribute_type ) {
			$existing_attributes = array();

			if ( ! empty( $required ) ) {
				$existing_attributes = array( 'aria-required' => 'true' );

				/**
				 * The blog section is hidden, so let's avoid a browser warning
				 * and deal with the Blog section in Javascript.
				 */
				if ( $section !== 'blog_details' ) {
					$existing_attributes['required'] = 'required';
				}
			}

			$attribute_type = ' ' . bp_get_form_field_attributes( $attribute_type, $existing_attributes );
		}

		// Specific case for Site's privacy
		if ( 'signup_blog_privacy_public' === $name || 'signup_blog_privacy_private' === $name ) {
			$name           = 'signup_blog_privacy';
			$submitted      = bp_get_signup_blog_privacy_value();

			if ( ! $submitted ) {
				$submitted = 'public';
			}

			$attribute_type = ' ' . checked( $value, $submitted, false );
		}

		if ( ! empty( $class ) ) {
			// In case people are adding classes..
			$classes = explode( ' ', $class );
			$class = ' class="' . join( ' ', array_map( 'sanitize_html_class', $classes ) ) . '"';
		}

		// Do not fire the do_action to display errors for the private radio.
		if ( 'private' !== $value ) {
			/**
			 * Fires and displays any member registration field errors.
			 *
			 * @since 1.1.0 (BuddyPress)
			 */
			do_action( "bp_{$name}_errors" );
		}

		// Set the input.
		$field_output = sprintf( '<input type="%1$s" name="%2$s" id="%3$s"%4$s value="%5$s"%6$s/>',
			esc_attr( $type ),
			esc_attr( $name ),
			esc_attr( $id ),
			$class,
			esc_attr( $value ),
			$attribute_type
		);

		// Not a radio, let's output the field
		if ( 'radio' !== $type ) {
			if ( 'signup_blog_url' !== $name ) {
				print( $field_output );

			// If it's the signup blog url, it's specific to Multisite config.
			} elseif ( is_subdomain_install() ) {
				printf( '%1$s %2$s . %3$s',
					is_ssl() ? 'https://' : 'http://',
					$field_output,
					bp_signup_get_subdomain_base()
				);

			// Subfolders!
			} else {
				printf( '%1$s %2$s',
					home_url( '/' ),
					$field_output
				);
			}

		// It's a radio, let's output the field inside the label
		} else {
			printf( $label_output, esc_attr( $name ), $field_output . ' ' . esc_html( $label ) );
		}

		// Password strength is restricted to the signup_password field
		if ( 'signup_password' === $name ) : ?>
			<div id="pass-strength-result"></div>
		<?php endif ;
	}

	/**
	 * Fires and displays any extra member registration details fields.
	 *
	 * @since 1.9.0 (BuddyPress)
	 */
	do_action( "bp_{$section}_fields" );
}

/**
 * Output a submit button and the nonce for the requested action.
 *
 * @since 1.0.0
 *
 * @param  string $action The action to get the submit button for. Required.
 * @return string         HTML Output.
 */
function bp_nouveau_submit_button( $action = '' ) {
	$submit_data = bp_nouveau_get_submit_button( $action );

	if ( empty( $submit_data['attributes'] ) || empty( $submit_data['nonce'] ) ) {
		return;
	}

	if ( ! empty( $submit_data['before'] ) ) {
		do_action( $submit_data['before'] );
	}

	// Output the submit button.
	printf( '
		<div class="submit">
			<input type="submit"%s/>
		</div>',
		bp_get_form_field_attributes( 'submit', $submit_data['attributes'] )
	);

	// Output the nonce field
	wp_nonce_field( $submit_data['nonce'] );

	if ( ! empty( $submit_data['after'] ) ) {
		do_action( $submit_data['after'] );
	}
}
