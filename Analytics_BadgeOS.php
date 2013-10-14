<?php
/**
 * Plugin Name: BadgeOS Analytics
 * Plugin URI: https://github.com/TylerShadick/Analytics_BadgeOS
 * Description: Creates Events for BadgeOS Submissions, Nominations and Achievement Unlocks.
 * Version: 0.2
 * Author: Tyler Shadick
 * Author URI: http://www.tylershadick.com
 * License: GPL2
 */
// add the admin settings and such
//Include BadgeOS Options page file
include( plugin_dir_path( __FILE__ ) . 'BOSA_options.php');
//Returns an anonymous ID or GA CID (also anonymous) for tracking
function get_GA_ID()
{
//If user has a Google Analytics cookie, return their CID
if (isset($_COOKIE['_ga']))
		{
			list($version,$domainDepth, $cid1, $cid2) = split('[\.]', $_COOKIE['_ga'],4);
			$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1.'.'.$cid2);
			return $contents['cid'];
		}
		else
		{
			return gaGenUUID();
		}
}
//Generates a unique identifier (random) and returns it
		function gaGenUUID() {
  return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    // 32 bits for "time_low"
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

    // 16 bits for "time_mid"
    mt_rand( 0, 0xffff ),

    // 16 bits for "time_hi_and_version",
    // four most significant bits holds version number 4
    mt_rand( 0, 0x0fff ) | 0x4000,

    // 16 bits, 8 bits for "clk_seq_hi_res",
    // 8 bits for "clk_seq_low",
    // two most significant bits holds zero and one for variant DCE1.1
    mt_rand( 0, 0x3fff ) | 0x8000,

    // 48 bits for "node"
    mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
  );
}
//Adapted from another programmer - fires a POST to google analytics using Measurement Protocol
// See https://developers.google.com/analytics/devguides/collection/protocol/v1/devguide
function gaFireHit( $data = null ) {
  if ( $data ) {
    $getString = 'https://ssl.google-analytics.com/collect';
    $getString .= '?payload_data&';
    $getString .= http_build_query($data);
    $result = wp_remote_get( $getString );

    return $result;
  }
  return false;
}
//Populates the data array before firing
function gaBuildHit( $method = null, $info = null ) {
	$bosa_options = get_option('BOSA_plugin_options');
	$tid_set = (isset($bosa_options['BOSA_UA_Account']));
  if ( $method && $info && $tid_set) {

  // Standard params
  $v = 1;
  $tid = $bosa_options['BOSA_UA_Account']; // Put your own Analytics ID in here
  $cid = get_GA_ID();
//if a submission is saved...
  if ($method === 'submission') {

    // Send Submission Hit
    $data = array(
      'v' => $v,
      'tid' => $tid,
      'cid' => $cid,
      't' => 'event',
	  'ec' => 'learning_event',
      'ea' => 'submission',
      'el' => $info['title'],
	  'ev' => '1'
    );
	gaFireHit($data);
  }
 //if a nomination is saved...
	else if ($method === 'nomination') {

    // Send PageView hit
    $data = array(
      'v' => $v,
      'tid' => $tid,
      'cid' => $cid,
      't' => 'event',
	  'ec' => 'learning_event',
      'ea' => 'nomination',
      'el' => $info['title'],
	  'ev' => '1'
    );
	gaFireHit($data);
	}
//if ANY achievement is unlocked
		else if ($method === 'award') {
    // Send PageView hit
    $data = array(
      'v' => $v,
      'tid' => $tid,
      'cid' => $cid,
      't' => 'event',
	  'ec' => 'learning_event',
      'ea' => $info['award_type'],
      'el' => $info['award_name'],
	  'ev' => '1'
    );
    gaFireHit($data);
  } 
 }
}
//get the slug of the current page (for nominations and submissions)
function the_slug() {
    $post_data = get_post($post->ID, ARRAY_A);
    $slug = $post_data['post_name'];
    return $slug; 
}
//Submission
 function track_submission_to_GA()
	{
		$pagename = the_slug();
		$data = array(
  'title' => $pagename
);
//move to building a submission hit with this data
gaBuildHit( 'submission', $data);
}
//Nominiation
function track_nomination_to_GA()
{
	$pagename = the_slug();
		$data = array(
  		'title' => $pagename
		);
	//move to building a nomination hit with this data
	gaBuildHit( 'nomination', $data);
}
//Award
function track_award_to_GA($userid, $achievement_id)
{
		$pagename = the_slug();
		$name = "";
		$type = "award";
		if(isset($achievement_id))
			{
				$name = urlencode(get_the_title($achievement_id));
				$type = urlencode(get_post_type( $achievement_id ));
			}
		$data = array(
  			'title' => $pagename,
  			'award_name' => $name,
			'award_type' => $type
			);
		//move to building an award hit with this data
		gaBuildHit( 'award', $data);
}
//Add the action hooks
	add_action('badgeos_save_submission', 'track_submission_to_GA');
	add_action('badgeos_save_nomination', 'track_nomination_to_GA');
	add_action('badgeos_award_achievement','track_award_to_GA', 10, 2);
?>