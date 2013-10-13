<?php 
/**
* User settings for BadgeOS Analytics
*/
//Hook Options Menu Initialization
add_action('admin_init', 'BOSAplugin_admin_init');
//hook the function that adds our settings page to the admin menu
add_action( 'admin_menu', 'badgeos_analytics_menu' );
//Initialize Plugin
function BOSAplugin_admin_init(){
//Register BOSA_plugin_options (ID = "BOSA_plugin_options") setting in options.php, validate before updating
register_setting( 'BOSA_plugin_options', 'BOSA_plugin_options', 'BOSA_plugin_options_validate');
// Initialize the BadgeOS Analytics Settings (ID = "BOSA_Settings) section of BOSA_plugin_options for BadgeOS Analytics, declare output function 'B
add_settings_section('BOSA_Settings', 'BadgeOS Analytics Settings', 'BOSA_Settings_Text', 'BadgeOS Analytics');
//Initialize the Google Universal Analytics Account (ID =  "BOSA_UA_Account") field inside of the "BOSA_Settings" section as part of BadgeOS Analytics
add_settings_field('BOSA_UA_Account', 'Google Universal Analytics Account', 'BOSA_UAField_Text', 'BadgeOS Analytics', 'BOSA_Settings');
//Initialize the Universal Analytics Tracking Code (ID =  "BOSA_Track_Snippit") field inside of the "BOSA_Settings" section as part of BadgeOS Analytics
add_settings_field('BOSA_Track_Snippit', 'Universal Analytics Tracking Code', 'BOSA_Track_Snippit_Text', 'BadgeOS Analytics', 'BOSA_Settings');
//Initialize theIgnore Admin in UA Toggle (ID =  "BOSA_Ignore_Admin") field inside of the "BOSA_Settings" section as part of BadgeOS Analytics
add_settings_field('BOSA_Ignore_Admin', 'Ignore Admin in UA Toggle', 'BOSA_Ignore_Admin_Text', 'BadgeOS Analytics', 'BOSA_Settings');
}
//Add BadgeOS Analytics Settings menu to Wordpress admin menu for administrators, create the page "BadgeOSAnalyticSettings.php"
function badgeos_analytics_menu() {
	add_options_page( 'BadgeOS Analytics Settings', 'BadgeOS Analytics Settings', 'administrator', 'BadgeOSAnalyticSettings.php', 'BadgeOS_Analytics_Settings' );
}
//Text echoed for the BadgeOS Analytics Settings section
function BOSA_Settings_Text() {
 echo "This page sets the primary options for BadgeOS.</br>If you don't already have a Universal Analytics account (different from a Google Analytics account), sign up for one here: <a href='http://www.google-analytics.com' target='_blank'>www.google-analytics.com</a>. The account is free and only requires basic knowledge of your website and a Google Account.";
}
//Display option selection area for the Google Universal Analytics Account field
function BOSA_UAField_Text() {
	//retrieve current BOSA_plugin_options settings set by user, if any
	$options = get_option('BOSA_plugin_options');
	//instructions
	echo "<h3>Universal Analytics</h3><p><b>Required Field.</b>Please input your Universal Analytics Property ID (UA-XXXXXXXX-Y): ";
	//input (with current outputs already displayed)
	echo "<input id='BOSA_UA_Account' name='BOSA_plugin_options[BOSA_UA_Account]' size='15' type='text' value='{$options['BOSA_UA_Account']}' />";
}
//Display option selection area for the Universal Analytics Tracking Code field
function BOSA_Track_Snippit_Text()
{
	//retrieve current BOSA_plugin_options settings set by user, if any
	$options = get_option('BOSA_plugin_options');
	//Change quotes to be HTML output friendly 
	$tracksnippit_clean = htmlspecialchars($options['BOSA_Track_Snippit'], ENT_QUOTES);
	//instructions
	echo "<h3>Universal Analytics Code</h3><p><b>Optional.</b>Use this area to insert your Universal Analytics tracking code found in the administrator settings of Google Analytics. Alternatively, you may also place your Google Tag Manager code in this area. This plugin will function without Universal Analytics tracking code, but you will not receive pageview information in Universal Analytics.</p></br>";
	//input (with current outputs already displayed)
	echo "<textarea rows='10' cols='30' id='BOSA_Track_Snippit' name='BOSA_plugin_options[BOSA_Track_Snippit]'>{$tracksnippit_clean}</textarea>";
}
function BOSA_Ignore_Admin_Text()
{
	//retrieve current BOSA_plugin_options settings set by user, if any
	$options = get_option('BOSA_plugin_options');
	$checked = $options['BOSA_Ignore_Admin'];
	//Set Plugin Default if option is not set
	if($checked != 'ignore' && $checked != 'include')
	{
		$checked == 'ignore';
	}
	//Set active radio button
	if ($checked == 'ignore')
	{
		$ignore = "checked='checked'";
	}
	else
	{
		$include = "checked='checked'";
	}
	//instructions
	echo "<h3>Track Administrators</h3><p>This option toggles whether or not administrators will be tracked in terms of pageviews. This setting does not impact tracking of administrators when they trigger learning events.</p>";
	//input (with current outputs already displayed)
	echo "<form><input id='BOSA_Ignore_Admin' type='radio' name='BOSA_plugin_options[BOSA_Ignore_Admin]' value='ignore' {$ignore} >Ignore<br><input id='BOSA_Ignore_Admin' type='radio' name='BOSA_plugin_options[BOSA_Ignore_Admin]' value='include' {$include} >Include</form>";
}
// validate our fields before updating options.php
function BOSA_plugin_options_validate($input) {
//check for valid UA format (UA-XXXXXXXX-Y) note: there has to be at least 1 Y digits
if(preg_match("/UA-[a-zA-Z0-9]{8}-[0-9]+/",$input['BOSA_UA_Account'])) {
	//set output variable to our input values
	$output = $input;
	//check if Universal Analytics Tracking Code has a value
	if($output['BOSA_Track_Snippit'])
	{
		//Replace any &quote with actual quotation marks
		$cleantracksnippit = str_replace('"','&quot;',$input['BOSA_Track_Snippit']); 
		$cleantracksnippit = str_replace('"','&quot;',$cleantracksnippit);
		//Use tidy to validate HTML structure
		$originalsnip = $cleantracksnippit;
		$tidy = tidy_parse_string($originalsnip);
		$tidy->cleanRepair();
		if ($tidy)
		{
			//Valid structure - change value of output to cleaned snippit and return settings
			$output['BOSA_Track_Snippit'] = $cleantracksnippit;
			return $output;
		}
		else
		{
		//Invalid tracking code: set error
		add_settings_error( 'BOSA_plugin_options', $cleantracksnippit, 'The tracking code HTML does not appear to be valid and my damage your theme. Please double check it.', 'error' );
		//return original values, discard changes
return get_option('BOSA_plugin_options');
		}
	}
	else {
	//Universal Analytics Tracking Code has a no value: return new values
	return $output;
	}
}
//Check if user entered valid Google Analytics (non-UA) account and throw error
else if(preg_match("/GA-[a-zA-Z0-9]{8}-[0-9]+/",$input['BOSA_UA_Account']))
{
//User inputted GA account instead of UA, throw error
add_settings_error( 'BOSA_plugin_options', $input['BOSA_UA_Account'], 'The Measurement Protocol used by this plugin requires an instance of Universal Analytics. Standard Google Analytics code will not work.', 'error' );
//return original values, discard changes
return get_option('BOSA_plugin_options');
}
//At this point, user has failed to enter a valid UA/GA account, throw error
else {
add_settings_error( 'BOSA_plugin_options', $input['BOSA_UA_Account'], 'Please provide a valid UA account identifier.', 'error' );
return get_option('BOSA_plugin_options');
}
}
//This function echoes the option sections and fields for BadgeOS Analytics Settings. badgeos_analytics_menu() calls this function using add_options_page
function BadgeOS_Analytics_Settings() {
	?>
    <div class="wrap">
    <h2>BadgeOS Analytics Settings</h2>
    <form method="post" action="options.php">
    <?php settings_fields( 'BOSA_plugin_options' ); ?>
    <?php do_settings_sections( 'BadgeOS Analytics' ); ?>
    <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
    </form>
    </div>
    <?php
}
//Add tracking snippit code into header
function insert_BOSA_snippit_wphead() {
	//Query options.php for BOSA_plugin_options settings
  $BOSA_Options = get_option('BOSA_plugin_options');
  $ShowAdmin = $BOSA_Options['BOSA_Ignore_Admin'];
  get_currentuserinfo();
  global $user_level;
  $show = true;
  if ($user_level > 9)
  	{
  	if ($ShowAdmin == 'ignore')
 	 {
	  $show = false;
  	  }
  	}
  //Check if Universal Analytics Tracking Code was set and echo it inside of the "Badge_OS_Tracking_Snippit" div
  if($BOSA_Options['BOSA_Track_Snippit'] && $show)
  {
	  echo "<div id='Badge_OS_Tracking_Snippit'>";
	  echo $BOSA_Options['BOSA_Track_Snippit'];
	  echo "</div>";
  }
}
//hook the insert_BOSA_snippit_wphead function to the wp_head function.
add_action( 'wp_head', 'insert_BOSA_snippit_wphead' );
//Prompt admin to set UA Property ID
add_action('admin_notices', 'example_admin_notice');
function example_admin_notice() {
	$bosa_options = get_option('BOSA_plugin_options');
        /* Check that the user hasn't already clicked to ignore the message */
	if (!isset($bosa_options['BOSA_UA_Account'] ) ) {
        echo '<div class="update"><p>';
        echo 'Univeral Analytics Property ID is not set. Please <a href="options-general.php?page=BadgeOSAnalyticSettings.php">set your UA property ID in BadgeOS Analytics</a>.';
        echo "</p></div>";
	}
}
?>