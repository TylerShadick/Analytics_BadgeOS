<?php
/**
 * Plugin Name: BadgeOS Analytics
 * Plugin URI: http://www.tylershadick.com
 * Description: Creates Events for BadgeOS Submissions and Badge Awarding (Requires Universal Analytics)
 * Version: 0.1
 * Author: Tyler Shadick
 * Author URI: http://www.tylershadick.com
 * License: GPL2
 */
function get_GA_ID()
{
if (isset($_COOKIE['_ga']))
		{
			list($version,$domainDepth, $cid1, $cid2) = split('[\.]', $_COOKIE['_ga'],4);
			$contents = array('version' => $version, 'domainDepth' => $domainDepth, 'cid' => $cid1.'.'.$cid2);
			return $contents['cid'];
		}
		else
		{
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
}
}
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
function gaBuildHit( $method = null, $info = null ) {
  if ( $method && $info) {

  // Standard params
  $v = 1;
  $tid = "UA-43688811-1"; // Put your own Analytics ID in here
  $cid = get_GA_ID();

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
		else if ($method === 'award') {
    // Send PageView hit
    $data = array(
      'v' => $v,
      'tid' => $tid,
      'cid' => $cid,
      't' => 'event',
	  'ec' => 'learning_event',
      'ea' => 'award',
      'el' => $info['award_name'],
	  'ev' => '1'
    );
    gaFireHit($data);

  } // end pageview method
 }
}
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
gaBuildHit( 'submission', $data);
}
//Nominiation
function track_nomination_to_GA()
{
	$pagename = the_slug();
		$data = array(
  		'title' => $pagename
		);
	gaBuildHit( 'nomination', $data);
}
//Award
function track_award_to_GA()
{
		$pagename = the_slug();
		global $achievement_object;
		$name = "";
		if(isset($achievement_object))
			{
				$achievementID = $achievement_object -> ID;
				$name = urlencode(get_the_title($achievementID));
			}
		$data = array(
  			'title' => $pagename,
  			'award_name' => $name
			);
		gaBuildHit( 'award', $data);
}
	add_action('badgeos_save_submission', 'track_submission_to_GA');
	add_action('badgeos_save_nomination', 'track_nomination_to_GA');
	add_action('badgeos_unlock_badges','track_award_to_GA');
?>