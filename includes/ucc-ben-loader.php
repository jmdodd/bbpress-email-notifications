<?php


if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'UCC_bbPress_Email_Notifications' ) ) {
class UCC_bbPress_Email_Notifications {
	public static $instance;
	public static $version;
	public static $headers;
	public static $unsubscribe;
	
	public function __construct() {
		self::$instance = $this;
		add_action( 'bbp_init', array( $this, 'init' ), 11 );
		$this->version = '2012042603';
		$this->headers = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=ISO-8859-1\r\n";
	}

	public function init() {
		// If BuddyPress, use Profile > Settings > Notifications.
		if ( defined( 'BP_VERSION' ) && did_action( 'bp_core_loaded' ) ) { 
			add_action( 'bp_notification_settings', array( $this, 'bp_notification_settings' ), 99 );

			$this->unsubscribe = __( '<br /><br />You can manage your email subscriptions in your Member Profile > Settings > Notifications.', 'bbpress-email-notifications' );

			// Make a better unsubscribe page for BuddyPress users of bbPress.
			// add_action( 'bp_subscription_settings' );
		} else { // Otherwise use WordPress user profile.
			add_action( 'edit_user_profile', array( &$this, 'wp_notification_settings' ) );
			add_action( 'show_user_profile', array( &$this, 'wp_notification_settings' ) );
			add_action( 'personal_options_update', array( &$this, 'wp_notification_settings_update' ) );
			add_action( 'edit_user_profile_update', array( &$this, 'wp_notification_settings_update' ) );

			$this->unsubscribe = __( '<br /><br />You can manage your email subscriptions in your User Profile.', 'bbpress-email-notifications' );
		}

		// bbPress compatability.
		add_action( 'bbp_get_topic_subscribers', array( $this, 'notify_subscriptions' ) );
		add_action( 'bbp_merge_topic', array( $this, 'notify_on_merge' ), 10, 2 );
		add_action( 'bbp_pre_split_topic', array( $this, 'notify_on_split' ), 10, 3 );
	}

	// Add form to Member Profile > Settings > Notifications in BuddyPress.
	public function bp_notification_settings() {
		global $bp;

		if ( ! $subscriptions = get_user_meta( $bp->displayed_user->id, 'notification_bbpress_subscriptions', true ) )
			$subscriptions = 'yes';

		if ( ! $notify_on_merge = get_user_meta( $bp->displayed_user->id, 'notification_bbpress_merge', true ) )
			$notify_on_merge = 'no';

		if ( ! $notify_on_split = get_user_meta( $bp->displayed_user->id, 'notification_bbpress_split', true ) )
			$notify_on_split = 'no';
		
		?>

		<table class="notification-settings" id="bbpress-notification-settings">
			<thead>
				<tr>
					<th class="icon"></th>
					<th class="title"><?php _e( 'Forums', 'bbpress-email-notifications' ) ?></th>
					<th class="yes"><?php _e( 'Yes', 'bbpress-email-notifications' ) ?></th>
					<th class="no"><?php _e( 'No', 'bbpress-email-notifications' )?></th>
				</tr>
			</thead>
	
			<tbody>
				<tr id="subscriptions">
					<td></td>
					<td><?php _e( 'A topic you have subscribed to receives a new reply<br /><em>(This overrides receipt of subscription emails)</em>', 'bbpress-email-notifications' ) ?></td>
					<td class="yes"><input type="radio" name="notifications[notification_bbpress_subscriptions]" value="yes" <?php checked( $subscriptions, 'yes', true ) ?>/></td>
					<td class="no"><input type="radio" name="notifications[notification_bbpress_subscriptions]" value="no" <?php checked( $subscriptions, 'no', true ) ?>/></td>
				</tr>	
				<tr id="topic-merge">
					<td></td>
					<td><?php _e( 'A topic that you posted is merged into an existing topic', 'bbpress-email-notifications' ) ?></td>
					<td class="yes"><input type="radio" name="notifications[notification_bbpress_merge]" value="yes" <?php checked( $notify_on_merge, 'yes', true ) ?>/></td>
					<td class="no"><input type="radio" name="notifications[notification_bbpress_merge]" value="no" <?php checked( $notify_on_merge, 'no', true ) ?>/></td>
				</tr>
				<tr id="topic-split">
					<td></td>
					<td><?php _e( 'A reply that you posted is split into a new topic', 'bbpress-email-notifications' ) ?></td>
					<td class="yes"><input type="radio" name="notifications[notification_bbpress_split]" value="yes" <?php checked( $notify_on_split, 'yes', true ) ?>/></td>
					<td class="no"><input type="radio" name="notifications[notification_bbpress_split]" value="no" <?php checked( $notify_on_split, 'no', true ) ?>/></td>
				</tr>

				<?php do_action( 'ucc_ben_buddypress_notification_settings' ); ?>
			</tbody>
		</table>

		<?php
	}

	// Add form to user profile in WordPress.
	public function wp_notification_settings( $user ) {
		if ( ! $subscriptions = get_user_meta( $user->ID, 'notification_bbpress_subscriptions', true ) )
			$subscriptions = 'yes';

		if ( ! $notify_on_merge = get_user_meta( $user->ID, 'notification_bbpress_merge', true ) )
			$notify_on_merge = 'no';

		if ( ! $notify_on_split = get_user_meta( $user->ID, 'notification_bbpress_split', true ) )
			$notify_on_split = 'no';

		?>

		<h3><?php _e( 'Forum Notifications', 'bbpress-email-notifications' ) ?></h3>

		<table>
			<thead>
				<tr>
					<th class="icon"></th>
					<th class="title"><?php _e( 'Notify me via email when:', 'bbpress-email-notifications' ) ?></th>
					<th class="yes"><?php _e( 'Yes', 'bbpress-email-notifications' ) ?></th>
					<th class="no"><?php _e( 'No', 'bbpress-email-notifications' )?></th>
				</tr>
			</thead>

			<tbody>
				<tr id="subscriptions">
					<td></td>
					<td><?php _e( 'A topic you have subscribed to receives a new reply<br /><em>(This overrides receipt of subscription emails)</em>', 'bbpress-email-notifications' ) ?></td>
					<td class="yes"><input type="radio" name="notification_bbpress_subscriptions" value="yes" <?php checked( $subscriptions, 'yes', true ) ?>/></td>
					<td class="no"><input type="radio" name="notification_bbpress_subscriptions" value="no" <?php checked( $subscriptions, 'no', true ) ?>/></td>
				</tr>
				<tr id="topic-merge">
					<td></td>
					<td><?php _e( 'A topic that you posted is merged into an existing topic', 'bbpress-email-notifications' ) ?></td>
					<td class="yes"><input type="radio" name="notification_bbpress_merge" value="yes" <?php checked( $notify_on_merge, 'yes', true ) ?>/></td>
					<td class="no"><input type="radio" name="notification_bbpress_merge" value="no" <?php checked( $notify_on_merge, 'no', true ) ?>/></td>
				</tr>
				<tr id="topic-split">
					<td></td>
					<td><?php _e( 'A reply that you posted is split into a new topic', 'bbpress-email-notifications' ) ?></td>
					<td class="yes"><input type="radio" name="notification_bbpress_split" value="yes" <?php checked( $notify_on_split, 'yes', true ) ?>/></td>
					<td class="no"><input type="radio" name="notification_bbpress_split" value="no" <?php checked( $notify_on_split, 'no', true ) ?>/></td>
				</tr>

				<?php do_action( 'ucc_ben_wordpress_notification_settings' ); ?>
			</tbody>
		</table>

		<?php

	}

	// Handle user profile data in WordPress.
	public function wp_notification_settings_update( $user ) {
		if ( ! current_user_can( 'edit_user', $user ) )
			return false;

		if ( isset( $_POST['notification_bbpress_subscriptions'] ) ) {
			$notification_bbpress_subscriptions = ( 'yes' == $_POST['notification_bbpress_subscriptions'] ) ? 'yes' : 'no'; 
			update_user_meta( $user, 'notification_bbpress_subscriptions', $notification_bbpress_subscriptions );
		} 

		if ( isset( $_POST['notification_bbpress_merge'] ) ) {
			$notification_bbpress_merge = ( 'yes' == $_POST['notification_bbpress_merge'] ) ? 'yes' : 'no';
			update_user_meta( $user, 'notification_bbpress_merge', $notification_bbpress_merge );
		}

		if ( isset( $_POST['notification_bbpress_split'] ) ) {
			$notification_bbpress_split = ( 'yes' == $_POST['notification_bbpress_split'] ) ? 'yes' : 'no';
			update_user_meta( $user, 'notification_bbpress_split', $notification_bbpress_split );
		}
	}

	// Remove users who don't want to receive subscribed thread emails from the array: "vacation mode".
	public function notify_subscriptions( $user_ids ) {
		$cleaned_user_ids = array();
		foreach ( (array) $user_ids as $user_id ) {
			$notify_me = get_user_meta( $user_id, 'notification_bbpress_subscriptions', true );
			if ( ( $notify_me == 'yes' ) || ! $notify_me ) 
				$cleaned_user_ids[] = $user_id;
		}
		return $cleaned_user_ids;
	}

	// Notify topic author when the topic is merged as a reply into an existing thread.
	public function notify_on_merge( $destination_topic_id, $source_topic_id ) {
		$author_id = bbp_get_topic_author_id( $source_topic_id );
		if ( 'yes' == get_user_meta( $author_id, 'notification_bbpress_merge', true ) ) {
			$to = bbp_get_topic_author_email( $source_topic_id );
			$to = apply_filters( 'ucc_ben_notify_on_merge_to', $to, $destination_topic_id, $source_topic_id );

			$name = get_bloginfo( 'name' );
			$title = bbp_get_topic_title( $source_topic_id );
			$subject = sprintf( __( '[%1$s] %2$s Merge Notification', 'bbpress-email-notifications' ), $name, $title );
			$subject = apply_filters( 'ucc_ben_notify_on_merge_subject', $subject, $destination_topic_id, $source_topic_id );

			$destination_url = bbp_get_topic_permalink( $destination_topic_id );
			$destination_title = bbp_get_topic_title( $destination_topic_id );
			$link = '<a href="' . esc_url( $destination_url ) . '">"' . $destination_title . '"</a>';
			$message = sprintf( __( 'Your topic %1$s has been merged into topic %2$s.', 'bbpress-email-notifications' ), $title, $link ) . $this->unsubscribe;
			$message = apply_filters( 'ucc_ben_notify_on_merge_message', $message, $destination_topic_id, $source_topic_id );

			$headers = apply_filters( 'ucc_ben_notify_on_merge_headers', $this->headers );
		
			wp_mail( $to, $subject, $message, $headers );
		}
	}

	// Notify reply author when the reply is split to a new/existing topic.
	public function notify_on_split( $from_reply_id, $source_topic_id, $destination_topic_id ) {
		$author_id = bbp_get_reply_author_id( $from_reply_id );
		if ( 'yes' ==  get_user_meta( $author_id, 'notification_bbpress_split', true ) ) {
			$to = bbp_get_reply_author_email( $from_reply_id );
			$to = apply_filters( 'ucc_ben_notify_on_split_to', $to, $from_reply_id, $source_topic_id, $destination_topic_id );

			$name = get_bloginfo( 'name' );
			$title = bbp_get_topic_title( $source_topic_id );
			$subject = sprintf( __( '[%1$s] %2$s Split Notification', 'bbpress-email-notifications' ), $name, $title );
			$subject = apply_filters( 'ucc_ben_notify_on_split_subject', $subject, $from_reply_id, $source_topic_id, $destination_topic_id );

			$destination_url = bbp_get_topic_permalink( $destination_topic_id );
			$destination_title = bbp_get_topic_title( $destination_topic_id );
			$link = '<a href="' . esc_url( $destination_url ) . '">"' . $destination_title . '"</a>';
			$message = sprintf( __( 'Your reply in topic %1$s has been split to topic %2$s.', 'bbpress-email-notifications' ), $title, $link ) . $this->unsubscribe;
			$message = apply_filters( 'ucc_ben_notify_on_split_message', $message, $from_reply_id, $source_topic_id, $destination_topic_id );

			$headers = apply_filters( 'ucc_ben_notify_on_split_headers', $this->headers );

			wp_mail( $to, $subject, $message, $headers );
		}
	}
} }


new UCC_bbPress_Email_Notifications;

