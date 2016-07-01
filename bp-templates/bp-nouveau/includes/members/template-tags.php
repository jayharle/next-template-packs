<?php
/**
 * Members template tags
 *
 * @since 1.0.0
 *
 * @package BP Nouveau
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template tag to wrap all Legacy actions that was used
 * before the members directory content
 *
 * @since 1.0.0
 */
function bp_nouveau_before_members_directory_content() {
	/**
	 * Fires at the begining of the templates BP injected content.
	 *
	 * @since 2.3.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_members_page' );

	/**
	 * Fires before the display of the members.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_members' );

	/**
	 * Fires before the display of the members content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_members_content' );

	/**
	 * Fires before the display of the members list tabs.
	 *
	 * @since 1.8.0 (BuddyPress)
	 */
	do_action( 'bp_before_directory_members_tabs' );
}

/**
 * Template tag to wrap all Legacy actions that was used
 * after the members directory content
 *
 * @since 1.0.0
 */
function bp_nouveau_after_members_directory_content() {
	/**
	 * Fires and displays the members content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_directory_members_content' );

	/**
	 * Fires after the display of the members content.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_members_content' );

	/**
	 * Fires after the display of the members.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_members' );

	/**
	 * Fires at the bottom of the members directory template file.
	 *
	 * @since 1.5.0 (BuddyPress)
	 */
	do_action( 'bp_after_directory_members_page' );
}

/**
 * Output the action buttons for the displayed user profile
 *
 * @since 1.0.0
 */
function bp_nouveau_member_header_buttons() {
	$bp_nouveau = bp_nouveau();

	echo join( ' ', bp_nouveau_get_members_buttons() );

	/**
	 * On the member's header we need to reset the group button's global
	 * once displayed as the friends component will use the member's loop
	 */
	if ( ! empty( $bp_nouveau->members->member_buttons ) ) {
		unset( $bp_nouveau->members->member_buttons );
	}

	/**
	 * Fires in the member header actions section.
	 *
	 * @since 1.2.6 (BuddyPress)
	 */
	do_action( 'bp_member_header_actions' );
}

function bp_nouveau_members_loop_buttons() {
	if ( empty( $GLOBALS['members_template'] ) ) {
		return;
	}

	$type   = 'loop';
	$action = 'bp_directory_members_actions';

	// specific case for group members
	if ( bp_is_active( 'groups' ) && bp_is_group_members() ) {
		$type   = 'group_member';
		$action = 'bp_group_members_list_item_action';
	}

	echo join( ' ', bp_nouveau_get_members_buttons( 'loop' ) );

	/**
	 * Fires inside the members action HTML markup to display actions.
	 *
	 * @since 1.1.0 (BuddyPress)
	 */
	do_action( $action );
}

	/**
	 * Get the action buttons for the displayed user profile
	 *
	 * @since 1.0.0
	 */
	function bp_nouveau_get_members_buttons( $type = 'profile' ) {
		// Not really sure why BP Legacy needed to do this...
		if ( 'profile' === $type && is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			return;
		}

		$buttons = array();

		if ( 'loop' === $type ) {
			$user_id = bp_get_member_user_id();
		} elseif ( 'group_member' === $type ) {
			$user_id = bp_get_group_member_id();
		} else {
			$user_id = bp_displayed_user_id();
		}

		if ( ! $user_id ) {
			return $buttons;
		}

		if ( bp_is_active( 'friends' ) ) {
			/**
			 * This filter workaround is waiting for a core adaptation
			 * so that we can directly get the friends button arguments
			 * instead of the button.
			 * @see https://buddypress.trac.wordpress.org/ticket/7126
			 */
			add_filter( 'bp_get_add_friend_button', 'bp_nouveau_members_catch_button_args', 100, 1 );

			bp_get_add_friend_button( $user_id );

			remove_filter( 'bp_get_add_friend_button', 'bp_nouveau_members_catch_button_args', 100, 1 );

			if ( ! empty( bp_nouveau()->members->button_args ) ) {
				$buttons['member_friendship'] = wp_parse_args( array(
					'id'       => 'member_friendship',
					'position' => 5,
				), bp_nouveau()->members->button_args );

				unset( bp_nouveau()->members->button_args );
			}
		}

		// Only add The public and private messages when not in a loop
		if ( 'profile' === $type ) {
			if ( bp_is_active( 'activity' ) && bp_activity_do_mentions() ) {
				/**
				 * This filter workaround is waiting for a core adaptation
				 * so that we can directly get the public message button arguments
				 * instead of the button.
				 * @see https://buddypress.trac.wordpress.org/ticket/7126
				 */
				add_filter( 'bp_get_send_public_message_button', 'bp_nouveau_members_catch_button_args', 100, 1 );

				bp_get_send_public_message_button();

				remove_filter( 'bp_get_send_public_message_button', 'bp_nouveau_members_catch_button_args', 100, 1 );

				if ( ! empty( bp_nouveau()->members->button_args ) ) {
					$buttons['public_message'] = wp_parse_args( array(
						'position' => 15,
					), bp_nouveau()->members->button_args );

					unset( bp_nouveau()->members->button_args );
				}
			}

			if ( bp_is_active( 'messages' ) ) {
				/**
				 * This filter workaround is waiting for a core adaptation
				 * so that we can directly get the private messages button arguments
				 * instead of the button.
				 * @see https://buddypress.trac.wordpress.org/ticket/7126
				 */
				add_filter( 'bp_get_send_message_button_args', 'bp_nouveau_members_catch_button_args', 100, 1 );

				bp_get_send_message_button();

				remove_filter( 'bp_get_send_message_button_args', 'bp_nouveau_members_catch_button_args', 100, 1 );

				if ( ! empty( bp_nouveau()->members->button_args ) ) {
					$buttons['private_message'] = wp_parse_args( array(
						'position'  => 25,
						'link_href' => esc_url( trailingslashit( bp_loggedin_user_domain() . bp_get_messages_slug() ) . '#compose?r=' . bp_core_get_username( $user_id ) ),
					), bp_nouveau()->members->button_args );

					unset( bp_nouveau()->members->button_args );
				}
			}
		}

		/**
		 * Filter here to add your buttons, use the position argument to choose where to insert it.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $buttons The list of buttons.
		 * @param int    $user_id The displayed user ID.
		 * @param string $type    Whether we're displaying a members loop or a user's page
		 */
		$buttons_group = apply_filters( 'bp_nouveau_get_members_buttons', $buttons, $user_id, $type );

		if ( empty( $buttons_group ) ) {
			return $buttons;
		}

		// It's the first entry of the loop, so build the Group and sort it
		if ( ! isset( bp_nouveau()->members->member_buttons ) || false === is_a( bp_nouveau()->members->member_buttons, 'BP_Buttons_Group' ) ) {
			$sort = true;
			bp_nouveau()->members->member_buttons = new BP_Buttons_Group( $buttons_group );

		// It's not the first entry, the order is set, we simply need to update the Buttons Group
		} else {
			$sort = false;
			bp_nouveau()->members->member_buttons->update( $buttons_group );
		}

		$return = bp_nouveau()->members->member_buttons->get( $sort );

		if ( ! $return ) {
			return array();
		}

		/**
		 * Leave a chance to adjust the $return
		 *
		 * @since 1.0.0
		 *
		 * @param array  $return  The list of buttons ordered.
		 * @param int    $user_id The displayed user ID.
		 * @param string $type    Whether we're displaying a members loop or a user's page
		 */
		do_action_ref_array( 'bp_nouveau_return_members_buttons', array( &$return, $user_id, $type ) );

		return $return;
	}

/**
 * Does the member has meta.
 *
 * @since  1.0.0
 *
 * @return bool True if the member has meta. False otherwise.
 */
function bp_nouveau_member_has_meta() {
	return (bool) bp_nouveau_get_member_meta();
}

/**
 * Display the member meta.
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_member_meta() {
	$meta = bp_nouveau_get_member_meta();

	echo join( "\n", $meta );
}

	/**
	 * Get the member meta.
	 *
	 * @since  1.0.0
	 *
	 * @return array The member meta.
	 */
	function bp_nouveau_get_member_meta() {
		$meta     = array();
		$is_loop  = false;

		if ( ! empty( $GLOBALS['members_template']->member ) ) {
			$member = $GLOBALS['members_template']->member;
			$is_loop = true;
		} else {
			$member = bp_get_displayed_user();
		}

		if ( empty( $member->id ) ) {
			return $meta;
		}

		if ( empty( $member->template_meta ) ) {
			// It's a single user's header
			if ( ! $is_loop ) {
				$meta['last_activity'] = sprintf( '<span class="activity">%s</span>', bp_get_last_activity( bp_displayed_user_id() ) );

			// We're in the members loop
			} else {
				$meta = array(
					'last_activity' => sprintf( '<span class="activity">%s</span>', bp_get_member_last_active() ),
				);
			}

			// Make sure to include hooked meta.
			$extra_meta = bp_nouveau_get_hooked_member_meta();

			if ( $extra_meta ) {
				$meta['extra'] = $extra_meta;
			}

			/**
			 * Filter here to add/remove Member meta.
			 *
			 * @since  1.0.0
			 *
			 * @param array  $meta     The list of meta to output.
			 * @param object $member   The member object
			 * @param bool   $is_loop  True if in the members loop. False otherwise.
			 */
			$member->template_meta = apply_filters( 'bp_nouveau_get_member_meta', $meta, $member, $is_loop );
		}

		return $member->template_meta;
	}

/**
 * Load the appropriate content for the single member pages
 *
 * @since  1.0.0
 *
 * @return string HTML Output.
 */
function bp_nouveau_member_template_part() {
	/**
	 * Fires before the display of member body content.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_before_member_body' );

	if ( bp_is_user_front() ) {
		bp_displayed_user_front_template_part();
	} else {
		$template = 'plugins';

		if ( bp_is_user_activity() ) {
			$template = 'activity';
		} elseif ( bp_is_user_blogs() ) {
			$template = 'blogs';
		} elseif ( bp_is_user_friends() ) {
			$template = 'friends';
		} elseif ( bp_is_user_groups() ) {
			$template = 'groups';
		} elseif ( bp_is_user_messages() ) {
			$template = 'messages';
		} elseif ( bp_is_user_profile() ) {
			$template = 'profile';
		} elseif ( bp_is_user_notifications() ) {
			$template = 'notifications';
		} elseif ( bp_is_user_settings() ) {
			$template = 'settings';
		}

		bp_nouveau_member_get_template_part( $template );
	}

	/**
	 * Fires after the display of member body content.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_after_member_body' );
}

/**
 * Use the appropriate Member header and enjoy a template hierarchy
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_member_header_template_part() {
	$template = 'member-header';

	if ( bp_displayed_user_use_cover_image_header() ) {
		$template = 'cover-image-header';
	}

	/**
	 * Fires before the display of a member's header.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_before_member_header' );

	// Get the template part for the header
	bp_nouveau_member_get_template_part( $template );

	/**
	 * Fires after the display of a member's header.
	 *
	 * @since 1.2.0 (BuddyPress)
	 */
	do_action( 'bp_after_member_header' );

	// Display template notices if any
	bp_nouveau_template_notices();
}

/**
 * Get a link to set the Member's default front page and directly
 * reach the Customizer section where it's possible to do it.
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_members_get_customizer_option_link() {
	return bp_nouveau_get_customizer_link( array(
		'object'    => 'user',
		'autofocus' => 'bp_nouveau_user_front_page',
		'text'      => esc_html__( 'Members default front page', 'bp-nouveau' ),
	) );
}

/**
 * Get a link to set the Member's front page widgets and directly
 * reach the Customizer section where it's possible to do it.
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_members_get_customizer_widgets_link() {
	return bp_nouveau_get_customizer_link( array(
		'object'    => 'user',
		'autofocus' => 'sidebar-widgets-sidebar-buddypress-members',
		'text'      => esc_html__( '(BuddyPress) Widgets', 'bp-nouveau' ),
	) );
}

/**
 * Display the Edit profile link (temporary)
 * @todo  replace with Ajax featur
 *
 * @since  1.0.0
 *
 * @return string HTML Output
 */
function bp_nouveau_member_description_edit_link() {
	echo bp_nouveau_member_get_description_edit_link();
}

	/**
	 * Get the Edit profile link (temporary)
	 * @todo  replace with Ajax featur
	 *
	 * @since  1.0.0
	 *
	 * @return string HTML Output
	 */
	function bp_nouveau_member_get_description_edit_link() {
		// Disable the filter
		remove_filter( 'edit_profile_url', 'bp_members_edit_profile_url', 10, 3 );

		if ( is_multisite() && ! current_user_can( 'read' ) ) {
			$link = get_dashboard_url( bp_displayed_user_id(), 'profile.php' );
		} else {
			$link = get_edit_profile_url( bp_displayed_user_id() );
		}

		// Restore the filter
		add_filter( 'edit_profile_url', 'bp_members_edit_profile_url', 10, 3 );

		$link .= '#description';

		return sprintf( '<a href="%1$s">%2$s</a>', esc_url( $link ), esc_html__( 'Edit your bio', 'bp-nouveau' ) );
	}
