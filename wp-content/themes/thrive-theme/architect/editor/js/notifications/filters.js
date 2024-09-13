module.exports = {
	/**
	 * Include the custom components
	 *
	 * @param  TVE
	 * @return {*}
	 */
	'tcb.includes': TVE => {
		TVE.Views.Components = {...TVE.Views.Components, ...( require( './components/_includes' ) )};

		return TVE;
	},

	/**
	 * Remove classes and attributes that are not necessary
	 *
	 * @param  $content
	 * @return {*}
	 */
	'tcb_filter_html_before_save': $content => {
		const $notificationWrapper = $content.find( TVE.identifier( 'notification' ) );

		if ( $notificationWrapper.attr( 'data-timer' ) < 0 ) {
			$notificationWrapper.attr( 'data-timer', 3000 );
		}

		$content.find( '.notification-edit-mode' ).removeClass( 'notification-edit-mode' );
		$content.find( '.tve_no_icons' ).removeClass( 'tve_no_drag tve_no_icons' );

		return $content;
	},

	/**
	 * Update the selected notification template
	 *
	 * @param  data
	 * @return {*}
	 */
	'tcb_save_post_data_after': data => {
		if ( TVE.CONST.post.post_type === 'tve_notifications' ) {
			TVE.$.ajax( {
				url: ajaxurl,
				type: 'post',
				data: {
					action: 'notification_update_template',
					post_id: data.post_id,
				}
			} );
		}

		return data;
	},

	/**
	 * Do not allow elements to be dropped elsewhere than inside the Notification element
	 *
	 * @param  elements
	 * @return {*}
	 */
	'only_inner_drop': elements => {
		elements += ',.notifications-content';

		return elements;
	},

	/* Do not allow elements to be inserted outside the notification element */
	'tve.drag.position.insert': ( dir, $newElement, $target ) => {
		if ( $target.is( '.notifications-content' ) ) {
			dir = 'mid';
		}

		return dir;
	},

	/* Insert new elements inside the corresponding notification */
	'tve.insert.near.target': $target => {
		if ( $target.is( TVE.identifier( 'notification' ) ) ) {
			$target = $target.find( `.notifications-content.notification-${$target.attr( 'data-state' )}` );
		}

		return $target;
	},

	/* Allow custom refocus after exiting the Edit Mode */
	'tve.edit.mode.refocus': () => {
		return false;
	},

	/* Add prefix in order to successfully override the default style */
	'tcb_head_css_prefix': ( prefix, element ) => {
		/* Check if element is part of the notification & is not one of the following components */
		if ( element.parents( '.notifications-content-wrapper' ).length > 0 && ! element.is( '.notifications-content,.thrv-notification_message,.notifications-content-wrapper' ) ) {
			const state = TVE.FLAGS.notification_state || TVE.inner_$( TVE.identifier( 'notification' ) ).attr( 'data-state' );
			prefix = `.notification-${state} `;
		}

		return prefix;
	},

	/**
	 * Add the local default notification template to the list of cloud templates
	 *
	 * @param  data
	 * @return {*}
	 */
	'tcb.cloud_templates.notification': data => {
		const defaultNotificationTemplate = {
			/* set the ID as negative to mark this as a local template */
			id: '-1',
			name: 'Default Notifications',
			local: true,
			thumb: `${TVE.CONST.plugin_url}editor/css/images/notification_template_default.jpg`,
			thumb_size: {w: 655, h: 326},
			v: 1
		};

		data.unshift( defaultNotificationTemplate );

		return data;
	},
	/**
	 * Instead of rendering the default notification template from the cloud, render it from localize
	 *
	 * @param  tpl
	 * @param  id
	 * @return {{v: number, head_css: string, name: string, custom_css: string, id: number, type: string, content: *}|boolean}
	 */
	'tcb.cloud_template.notification': ( tpl, id ) => {
		/* local templates have negative IDs */
		if ( id < 0 ) {
			return {
				content: `${TVE.tpl( 'elements/notification' )()}`,
				custom_css: '',
				head_css: '',
				id: '-1',
				name: 'Default Notifications',
				type: 'notification',
				v: 1
			};
		}

		return false;
	},
};
