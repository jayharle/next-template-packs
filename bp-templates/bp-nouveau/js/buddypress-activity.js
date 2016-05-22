/* global bp, BP_Next, ajaxurl */
window.bp = window.bp || {};

( function( exports, $ ) {

	// Bail if not set
	if ( typeof BP_Next === 'undefined' ) {
		return;
	}

	bp.Next = bp.Next || {};

	/**
	 * [Activity description]
	 * @type {Object}
	 */
	bp.Next.Activity = {
		/**
		 * [start description]
		 * @return {[type]} [description]
		 */
		start: function() {
			this.setupGlobals();

			// Listen to events ("Add hooks!")
			this.addListeners();
		},

		/**
		 * [setupGlobals description]
		 * @return {[type]} [description]
		 */
		setupGlobals: function() {
			// Init just posted activities
			this.just_posted    = [];

			// Init current page
			this.current_page   = 1;

			// Init mentions count
			this.mentions_count = Number( $( bp.Next.objectNavParent + ' [data-bp-scope="mentions"]' ).find( 'a span' ).html() ) || 0;

			// HeartBeat Globals
			this.heartbeat_data = {
				newest         : '',
				highlights     : {},
				last_recorded  : 0,
				first_recorded : 0,
				document_title : $( document ).prop( 'title' )
			};
		},

		/**
		 * [addListeners description]
		 */
		addListeners: function() {
			// HeartBeat listeners
			$( '#buddypress' ).on( 'bp_heartbeat_send', this.heartbeatSend.bind( this ) );
			$( '#buddypress' ).on( 'bp_heartbeat_tick', this.heartbeatTick.bind( this ) );

			// Inject Activities
			$( '#buddypress [data-bp-list="activity"]' ).on( 'click', 'li.load-newest, li.load-more', this.injectActivities.bind( this ) );

			// Hightlight new activities & clean up the stream
			$( '#buddypress' ).on( 'bp_ajax_request', '[data-bp-list="activity"]', this.scopeLoaded.bind( this ) );

			// Activity comments effect
			$( '#buddypress [data-bp-list="activity"]' ).on( 'bp_ajax_append', this.hideComments );
			$( '#buddypress [data-bp-list="activity"]' ).on( 'click', '.show-all', this.showComments );

			// Activity actions
			$( '#buddypress [data-bp-list="activity"]' ).on( 'click', '.activity-item', bp.Next, this.activityActions );
			$( document ).keydown( this.closeCommentForm );
		},

		/**
		 * [heartbeatSend description]
		 * @param  {[type]} event [description]
		 * @param  {[type]} data  [description]
		 * @return {[type]}       [description]
		 */
		heartbeatSend: function( event, data ) {
			this.heartbeat_data.first_recorded = $( '#buddypress [data-bp-list] [data-bp-activity-id] time' ).first().data( 'bp-timestamp' ) || 0;

			if ( 0 === this.heartbeat_data.last_recorded || this.heartbeat_data.first_recorded > this.heartbeat_data.last_recorded ) {
				this.heartbeat_data.last_recorded = this.heartbeat_data.first_recorded;
			}

			data.bp_activity_last_recorded = this.heartbeat_data.last_recorded;

			if ( $( '#buddypress .dir-search input[type=search]' ).length ) {
				data.bp_activity_last_recorded_search_terms = $( '#buddypress .dir-search input[type=search]' ).val();
			}

			$.extend( data, { bp_heartbeat: bp.Next.getStorage( 'bp-activity' ) } );

			// Update all displayed time
			$.each( $( '#buddypress time' ), function( t, time ) {
				if ( $( time ).data( 'bp-timestamp' ) ) {
					$( time ).html( bp.Next.updateTimeSince( Number( $( time ).data( 'bp-timestamp' ) ) ) );
				}
			} );
		},

		/**
		 * [heartbeatTick description]
		 * @param  {[type]} event          [description]
		 * @param  {[type]} data           [description]
		 * @return {[type]}                [description]
		 */
		heartbeatTick: function( event, data ) {
			var newest_activities_count, newest_activities, objects = bp.Next.objects,
				scope = bp.Next.getStorage( 'bp-activity', 'scope' ), self = this;

			// Only proceed if we have newest activities
			if ( undefined === data || ! data.bp_activity_newest_activities ) {
				return;
			}

			this.heartbeat_data.newest = $.trim( data.bp_activity_newest_activities.activities ) + this.heartbeat_data.newest;
			this.heartbeat_data.last_recorded  = Number( data.bp_activity_newest_activities.last_recorded );

			// Parse activities
			newest_activities = $( this.heartbeat_data.newest ).filter( '.activity-item' );

			// Count them
			newest_activities_count = Number( newest_activities.length );

			/**
			 * It's not a regular object but we need it!
			 * so let's add it temporarly..
			 */
			objects.push( 'mentions' );

			/**
			 * On the All Members tab, we need to know what these activities are about
			 * in order to update all the other tabs dynamic span
			 */
			if ( 'all' === scope ) {

				$.each( newest_activities, function( a, activity ) {
					var activity = $( activity );

					$.each( objects, function( o, object ) {
						if ( -1 !== $.inArray( 'bp-my-' + object, activity.get( 0 ).classList ) ) {
							if ( undefined === self.heartbeat_data.highlights[ object ] ) {
								self.heartbeat_data.highlights[ object ] = [ activity.data( 'bp-activity-id' ) ];
							} else if ( -1 === $.inArray( activity.data( 'bp-activity-id' ), self.heartbeat_data.highlights[ object ] ) ) {
								self.heartbeat_data.highlights[ object ].push( activity.data( 'bp-activity-id' ) );
							}
						}
					} );
				} );

				// Remove the specific classes to count highligthts
				var regexp = new RegExp( 'bp-my-(' + objects.join( '|' ) + ')', 'g' );
				this.heartbeat_data.newest = this.heartbeat_data.newest.replace( regexp, '' );

				/**
				 * Deal with the 'All Members' dynamic span from here as HeartBeat is working even when
				 * the user is not logged in
				 */
				 $( bp.Next.objectNavParent + ' [data-bp-scope="all"]' ).find( 'a span' ).html( newest_activities_count );

			// Set all activities to be highlighted for the current scope
			} else {
				// Init the array of highlighted activities
				this.heartbeat_data.highlights[ scope ] = [];

				$.each( newest_activities, function( a, activity ) {
					self.heartbeat_data.highlights[ scope ].push( $( activity ).data( 'bp-activity-id' ) );
				} );
			}

			$.each( objects, function( o, object ) {
				if ( undefined !== self.heartbeat_data.highlights[ object ] && self.heartbeat_data.highlights[ object ].length ) {
					var count = 0;

					if ( 'mentions' === object ) {
						count = self.mentions_count;
					}

					$( bp.Next.objectNavParent + ' [data-bp-scope="' + object + '"]' ).find( 'a span' ).html( Number( self.heartbeat_data.highlights[ object ].length ) + count );
				}
			} );

			/**
			 * Let's remove the mentions from objects!
			 */
			objects.pop();

			// Add an information about the number of newest activities inside the document's title
			$( document ).prop( 'title', '(' + newest_activities_count + ') ' + this.heartbeat_data.document_title );

			// Update the Load Newest li if it already exists.
			if ( $( '#buddypress [data-bp-list="activity"] li' ).first().hasClass( 'load-newest' ) ) {
				newest_link = $( '#buddypress [data-bp-list="activity"] .load-newest a' ).html();
				$( '#buddypress [data-bp-list="activity"] .load-newest a' ).html( newest_link.replace( /([0-9]+)/, newest_activities_count ) );

			// Otherwise add it
			} else {
				$( '#buddypress [data-bp-list="activity"]' ).prepend( '<li class="load-newest"><a href="#newest">' + BP_Next.newest + ' (' + newest_activities_count + ')</a></li>' );
			}

			/**
			 * Finally trigger a pending event containing the activity heartbeat data
			 */
			$( '#buddypress [data-bp-list="activity"]' ).trigger( 'bp_heartbeat_pending', this.heartbeat_data );
		},

		/**
		 * [injectQuery description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		injectActivities: function( event ) {
			var store = bp.Next.getStorage( 'bp-activity' ),
				scope = store.scope || null, filter = store.filter || null;

			// Load newest activities
			if ( $( event.currentTarget ).hasClass( 'load-newest' ) ) {
				// Stop event propagation
				event.preventDefault();

				$( event.currentTarget ).remove();

				/**
				 * If a plugin is updating the recorded_date of an activity
				 * it will be loaded as a new one. We need to look in the
				 * stream and eventually remove similar ids to avoid "double".
				 */
				var activities = $.parseHTML( this.heartbeat_data.newest );

				$.each( activities, function( a, activity ){
					if( 'LI' === activity.nodeName && $( activity ).hasClass( 'just-posted' ) ) {
						if( $( '#' + $( activity ).prop( 'id' ) ).length ) {
							$( '#' + $( activity ).prop( 'id' ) ).remove();
						}
					}
				} );

				// Now the stream is cleaned, prepend newest
				$( event.delegateTarget ).prepend( this.heartbeat_data.newest ).trigger( 'bp_heartbeat_prepend', this.heartbeat_data );

				// Reset the newest activities now they're displayed
				this.heartbeat_data.newest = '';

				// Reset the All members tab dynamic span id it's the current one
				if ( 'all' === scope ) {
					$( bp.Next.objectNavParent + ' [data-bp-scope="all"]' ).find( 'a span' ).html( '' );
				}

				// Specific to mentions
				if ( 'mentions' === scope ) {
					// Now mentions are displayed, remove the user_metas
					bp.Next.ajax( { action: 'activity_clear_new_mentions' }, 'activity' );
					this.mentions_count = 0;
				}

				// Activities are now displayed, clear the newest count for the scope
				$( bp.Next.objectNavParent + ' [data-bp-scope="' + scope + '"]' ).find( 'a span' ).html( '' );

				// Activities are now displayed, clear the highlighted activities for the scope
				if ( undefined !== this.heartbeat_data.highlights[ scope ] ) {
					this.heartbeat_data.highlights[ scope ] = [];
				}

				// Remove highlighted for the current scope
				setTimeout( function () {
					$( event.delegateTarget ).find( '[data-bp-activity-id]' ).removeClass( 'newest_' + scope + '_activity' );
				}, 3000 );

				// Reset the document title
				$( document ).prop( 'title', this.heartbeat_data.document_title );

			// Load more activities
			} else if ( $( event.currentTarget ).hasClass( 'load-more' ) ) {
				var next_page = ( Number( this.current_page ) * 1 ) + 1, self = this, search_terms = '';

				// Stop event propagation
				event.preventDefault();

				$( event.currentTarget ).find( 'a' ).first().addClass( 'loading' );

				// reset the just posted
				this.just_posted = [];

				// Now set it
				$( event.delegateTarget ).children( '.just-posted' ).each( function() {
					self.just_posted.push( $( this ).data( 'bp-activity-id' ) );
				} );

				if ( $( '#buddypress .dir-search input[type=search]' ).length ) {
					search_terms = $( '#buddypress .dir-search input[type=search]' ).val();
				}

				bp.Next.objectRequest( {
					object              : 'activity',
					scope               : scope,
					filter              : filter,
					search_terms        : search_terms,
					page                : next_page,
					method              : 'append',
					exclude_just_posted : this.just_posted.join( ',' )
				} ).done( function( response ) {
					if ( true === response.success ) {
						$( event.currentTarget ).remove();

						// Update the current page
						self.current_page = next_page;
					}
				} );
			}
		},

		/**
		 * [truncateComments description]
		 * @param  {[type]} event [description]
		 * @param  {[type]} data  [description]
		 * @return {[type]}       [description]
		 */
		hideComments: function( event ) {
			var comments = $( event.target ).find( '.activity-comments' ),
				activity_item, comment_items, comment_count;

			if ( ! comments.length ) {
				return;
			}

			comments.each( function( c, comment ) {
				comment_items = $( comment ).children( 'ul' ).find( 'li' );

				if ( ! comment_items.length ) {
					return;
				}

				// Get the activity id
				activity_item = $( comment ).closest( '.activity-item' );

				// Get the comment count
				comment_count = $( '#acomment-comment-' + activity_item.data( 'bp-activity-id' ) + ' span.comment-count' ).html() || ' ';

				// Keep first 5 root comments
				comment_items.each( function( i, item ) {
					if ( i < comment_items.length - 5 ) {
						$( item ).addClass( 'hidden' );
						$( item ).toggle();

						// Prepend a link to display all
						if ( ! i ) {
							$( item ).before( '<li class="show-all"><a href="#' + activity_item.prop( 'id' ) + '/show-all/" title="' + BP_Next.show_all_comments + '">' + BP_Next.show_x_comments.replace( '%d', comment_count ) + '</a></li>' );
						}
					}
				} );
			} );
		},

		/**
		 * [showComments description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		showComments: function( event ) {
			// Stop event propagation
			event.preventDefault();

			$( event.target ).addClass( 'loading' );

			setTimeout( function() {
				$( event.target ).closest( 'ul' ).find( 'li' ).fadeIn( 200, function() {
					$( event.target ).remove();
				} );
			}, 600 );
		},

		/**
		 * [scopeLoaded description]
		 * @param  {[type]} event [description]
		 * @param  {[type]} data  [description]
		 * @return {[type]}       [description]
		 */
		scopeLoaded: function ( event, data ) {
			// Make sure to only keep 5 root comments
			this.hideComments( event );

			// Mentions are specific
			if ( 'mentions' === data.scope && undefined !== data.response.new_mentions ) {
				$.each( data.response.new_mentions, function( i, id ) {
					$( '#buddypress #activity-stream' ).find( '[data-bp-activity-id="' + id + '"]' ).addClass( 'newest_mentions_activity' );
				} );

				// Reset mentions count
				this.mentions_count = 0;
			} else if ( undefined !== this.heartbeat_data.highlights[data.scope] && this.heartbeat_data.highlights[data.scope].length ) {
				$.each( this.heartbeat_data.highlights[data.scope], function( i, id ) {
					if ( $( '#buddypress #activity-stream' ).find( '[data-bp-activity-id="' + id + '"]' ).length ) {
						$( '#buddypress #activity-stream' ).find( '[data-bp-activity-id="' + id + '"]' ).addClass( 'newest_' + data.scope + '_activity' );
					}
				} );
			}

			// Reset the newest activities now they're displayed
			this.heartbeat_data.newest = '';
			$( bp.Next.objectNavParent + ' [data-bp-scope="all"]' ).find( 'a span' ).html( '' );

			// Activities are now loaded, clear the highlighted activities for the scope
			if ( undefined !== this.heartbeat_data.highlights[ data.scope ] ) {
				this.heartbeat_data.highlights[ data.scope ] = [];
			}

			// Reset the document title
			$( document ).prop( 'title', this.heartbeat_data.document_title );

			setTimeout( function () {
				$( '#buddypress #activity-stream .activity-item' ).removeClass( 'newest_' + data.scope +'_activity' );
			}, 3000 );
		},

		/**
		 * [activityActions description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		activityActions: function( event ) {
			var parent = event.data, target = $( event.target ), activity_item = $( event.currentTarget ),
				activity_id = activity_item.data( 'bp-activity-id' ), stream = $( event.delegateTarget );

			// Favoriting
			if ( target.hasClass( 'fav') || target.hasClass('unfav') ) {
				var type = target.hasClass( 'fav' ) ? 'fav' : 'unfav';

				// Stop event propagation
				event.preventDefault();

				target.addClass( 'loading' );

				parent.ajax( { action: 'activity_mark_' + type, 'id': activity_id }, 'activity' ).done( function( response ) {
					target.removeClass( 'loading' );

					if ( false === response.success ) {
						return;
					} else {
						target.fadeOut( 200, function() {
							if ( $( this ).find( 'span' ).first().length ) {
								$( this ).find( 'span' ).first().html( response.data.content );
							} else {
								$( this ).html( response.data.content );
							}
							$( this ).prop( 'title', response.data.content );
							$( this ).fadeIn( 200 );
						} );
					}

					if ( 'fav' === type ) {
						if ( undefined !== response.data.directory_tab ) {
							if ( ! $( parent.objectNavParent + ' [data-bp-scope="favorites"]' ).length ) {
								$( parent.objectNavParent + ' [data-bp-scope="all"]' ).after( response.data.directory_tab );
							}
						}

						target.removeClass( 'fav' );
						target.addClass( 'unfav' );

					} else if ( 'unfav' === type ) {
						// If on user's profile or on the favorites directory tab, remove the entry
						if ( ! $( parent.objectNavParent + ' [data-bp-scope="favorites"]' ).length || $( parent.objectNavParent + ' [data-bp-scope="favorites"]' ).hasClass( 'selected' )  ) {
							activity_item.remove();
						}

						if ( undefined !== response.data.no_favorite ) {
							// Remove the tab when on activity directory but not on the favorites tabs
							if ( $( parent.objectNavParent + ' [data-bp-scope="all"]' ).length && $( parent.objectNavParent + ' [data-bp-scope="all"]' ).hasClass( 'selected' ) ) {
								$( parent.objectNavParent + ' [data-bp-scope="favorites"]' ).remove();

							// In all the other cases, append a message to the empty stream
							} else {
								stream.append( response.data.no_favorite );
							}
						}

						target.removeClass( 'unfav' );
						target.addClass( 'fav' );
					}
				} );
			}

			// Deleting
			if ( target.hasClass( 'delete-activity' ) ) {
				// Stop event propagation
				event.preventDefault();

				target.addClass( 'loading' );

				parent.ajax( {
					action     : 'delete_activity',
					'id'       : activity_id,
					'_wpnonce' : parent.getLinkParams( target.prop( 'href' ), '_wpnonce' )
				}, 'activity' ).done( function( response ) {
					target.removeClass( 'loading' );

					if ( false === response.success ) {
						activity_item.prepend( response.data.feedback );
						activity_item.find( '.feedback' ).hide().fadeIn( 300 );
					} else {
						activity_item.slideUp( 300 );

						// reset vars to get newest activities
						if ( activity_item.find( 'time' ).first().data( 'bp-timestamp' ) === parent.Activity.heartbeat_data.last_recorded ) {
							parent.Activity.heartbeat_data.newest        = '';
							parent.Activity.heartbeat_data.last_recorded  = 0;
						}
					}
				} );
			}

			// Reading more
			if ( target.closest( 'span' ).hasClass( 'activity-read-more' ) ) {
				var content = target.closest( 'div' ), readMore = target.closest( 'span' ), item_id;

				if ( $( content ).hasClass( 'activity-inner' ) ) {
					item_id = activity_id;
				} else if ( $( content ).hasClass( 'acomment-content' ) ) {
					item_id = target.closest( 'li' ).data( 'bp-activity-comment-id' );
				}

				if ( ! item_id ) {
					return event;
				}

				// Stop event propagation
				event.preventDefault();

				$( readMore ).addClass( 'loading' );

				parent.ajax( {
					action     : 'get_single_activity_content',
					'id'       : item_id,
				}, 'activity' ).done( function( response ) {
					$( readMore ).removeClass( 'loading' );

					if ( content.parent().find( '.feedback' ).length ) {
						content.parent().find( '.feedback' ).remove();
					}

					if ( false === response.success ) {
						content.after( response.data.feedback );
						content.parent().find( '.feedback' ).hide().fadeIn( 300 );
					} else {
						$( content ).slideUp( 300 ).html( response.data.contents ).slideDown( 300 );
					}
				} );
			}

			// Displaying the comment form
			if ( target.hasClass( 'acomment-reply' ) || target.parent().hasClass( 'acomment-reply' ) ) {
				var comment_link = target, item_id = activity_id, form = $( '#ac-form-' + activity_id );

				// Stop event propagation
				event.preventDefault();

				// If the comment count span inside the link is clicked
				if ( target.parent().hasClass( 'acomment-reply' ) ) {
					comment_link = target.parent();
				}

				if ( target.closest( 'li' ).data( 'bp-activity-comment-id' ) ) {
					item_id = target.closest( 'li' ).data( 'bp-activity-comment-id' );
				}

				// ?? hide and display none..
				//form.css( 'display', 'none' );
				form.removeClass( 'root' );
				$('.ac-form').hide();

				/* Remove any error messages */
				$.each( form.children( 'div' ), function( e, err ) {
					if ( $( err ).hasClass( 'error' ) ) {
						$( err ).remove();
					}
				} );

				// It's an activity we're commenting
				if ( item_id === activity_id ) {
					$( '[data-bp-activity-id="' + item_id + '"] .activity-comments' ).append( form );
					form.addClass( 'root' );

				// It's a comment we're replying to
				} else {
					$( '[data-bp-activity-comment-id="' + item_id + '"]' ).append( form );
				}

				form.slideDown( 200 );

				$.scrollTo( form, 500, {
					offset:-100,
					easing:'swing'
				} );

				$( '#ac-form-' + activity_id + ' textarea' ).focus();
			}

			// Removing the form
			if ( target.hasClass( 'ac-reply-cancel' ) ) {
				$( target ).closest( '.ac-form' ).slideUp( 200 );

				// Stop event propagation
				event.preventDefault();
			}

			// Submitting comments and replies
			if ( 'ac_form_submit' === target.prop( 'name' ) ) {
				var form = target.closest( 'form' ), item_id = activity_id, comment_content,
					comment_data, comment_count;

				// Stop event propagation
				event.preventDefault();

				if ( target.closest( 'li' ).data( 'bp-activity-comment-id' ) ) {
					item_id    = target.closest( 'li' ).data( 'bp-activity-comment-id' );
				}

				comment_content = $( form ).find( 'textarea' ).first();

				target.addClass( 'loading' ).prop( 'disabled', true );
				comment_content.addClass( 'loading' ).prop( 'disabled', true );

				comment_data = {
					action:                          'new_activity_comment',
					'_wpnonce_new_activity_comment': $( '#_wpnonce_new_activity_comment' ).val(),
					'comment_id':                    item_id,
					'form_id':                       activity_id,
					'content':                       comment_content.val()
				};

				// Akismet
				if ( $( '#_bp_as_nonce_' + item_id ).val() ) {
					comment_data['_bp_as_nonce_' + item_id] = $( '#_bp_as_nonce_' + item_id ).val();
				}

				parent.ajax( comment_data, 'activity' ).done( function( response ) {
					target.removeClass( 'loading' );
					comment_content.removeClass( 'loading' );

					if ( false === response.success ) {
						form.append( $( response.data.feedback ).hide().fadeIn( 200 ) );
					} else {
						var activity_comments = form.parent();
						var the_comment = $.trim( response.data.contents );

						form.fadeOut( 200, function() {
							if ( 0 === activity_comments.children( 'ul' ).length ) {
								if ( activity_comments.hasClass( 'activity-comments' ) ) {
									activity_comments.prepend( '<ul></ul>' );
								} else {
									activity_comments.append( '<ul></ul>' );
								}
							}

							activity_comments.children( 'ul' ).append( $( the_comment ).hide().fadeIn( 200 ) );
							$( form ).find( 'textarea' ).first().val( '' );

							activity_comments.parent().addClass( 'has-comments' );
						} );

						// why, as it's already done a few lines ahead ???
						//jq( '#' + form.attr('id') + ' textarea').val('');

						// Set the new count
						comment_count = Number( $( activity_item ).find( 'a span.comment-count' ).html() || 0 ) + 1;

						// Increase the "Reply (X)" button count
						$( activity_item ).find( 'a span.comment-count' ).html( comment_count );

						// Increment the 'Show all x comments' string, if present
						show_all_a = $( activity_item ).find( '.show-all a' );
						if ( show_all_a ) {
							show_all_a.html( BP_Next.show_x_comments.replace( '%d', comment_count ) );
						}
					}

					target.prop( 'disabled', false );
					comment_content.prop( 'disabled', false );
				} );
			}
		},

		/**
		 * [closeCommentForm description]
		 * @param  {[type]} event [description]
		 * @return {[type]}       [description]
		 */
		closeCommentForm: function( event ) {
			var event = event || window.event, element, keyCode;

			if ( event.target ) {
				element = event.target;
			} else if ( event.srcElement) {
				element = event.srcElement;
			}

			if ( element.nodeType === 3 ) {
				element = element.parentNode;
			}

			if ( event.ctrlKey === true || event.altKey === true || event.metaKey === true ) {
				return event;
			}

			keyCode = ( event.keyCode) ? event.keyCode : event.which;

			if ( 27 === keyCode ) {
				if ( element.tagName === 'TEXTAREA' ) {
					if ( $( element ).hasClass( 'ac-input' ) ) {
						$( element ).closest( 'form' ).slideUp( 200 );
					}
				}
			}
		}
	}

	// Launch BP Next Activity
	bp.Next.Activity.start();

} )( bp, jQuery );