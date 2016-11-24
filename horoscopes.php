<?php
/*
Plugin Name: Horoscopes
Plugin URI: http://yourdomain.com/
Description: A wordpress plugin to add horoscopes
Version: 1.0
Author: David Klugmann
Author URI: http://www.myastrologycharts.com
License: GPL
*/

/*
    Adds the shortcode so using the shortcode [horoscopes] in the text displays the horoscopes
*/

add_shortcode("horoscopes", "horoscopes");

/*
    Called when the Plugin is Activated
*/

function activation_horoscopesplugin () 
{

    $apikey = uniqid();
    $initialnumbermonths = 1;
    delete_option('horoscopes_options');
    delete_option('generatedapikey');
    add_option('generatedapikey',$apikey);
    delete_option('initialnumbermonths');
    add_option('initialnumbermonths',$initialnumbermonths);
    $horoscopesclientpath = 'http://www.myastrologycharts.com/';
    $horoscopesclientinsert = 'horoscopesclientinsert.php';
    $params = sprintf('<horoscopesRequest apikey="%s" numbermonths="%d"></horoscopesRequest>',$apikey,$initialnumbermonths);
    $url = sprintf ("%s%s?requestxml=%s",$horoscopesclientpath,$horoscopesclientinsert,urlencode($params));
    $returnxmlstring = loadXML($url);
    $filename = './debugactivationhoroscopes.txt';
    if (!$handle = fopen($filename, 'w'))
    {
        print "Cannot open file ($filename)";
        exit;
    }
    fprintf ($handle,"url is %s\n",$url);
}
register_activation_hook( __FILE__, 'activation_horoscopesplugin');

/*
    Called when the Plugin is Deactivated
*/

function deactivation_horoscopesplugin ()
{
    $apikey = get_option('generatedapikey');
    $filename = './debugdeactivationhoroscopes.txt';
    if (!$handle = fopen($filename, 'w'))
    {
        print "Cannot open file ($filename)";
        exit;
    }
    fprintf ($handle,"apikey is %s\n",$apikey);
    $horoscopesclientpath = 'http://www.myastrologycharts.com/';
    $horoscopesclientdelete = 'horoscopesclientdelete.php';
    $params = sprintf('<horoscopesRequest apikey="%s"></horoscopesRequest>',$apikey);
    $url = sprintf ("%s%s?requestxml=%s",$horoscopesclientpath,$horoscopesclientdelete,urlencode($params));
    $returnxmlstring = loadXML($url);
}
register_deactivation_hook( __FILE__, 'deactivation_horoscopesplugin');

/*
    Addis the submenu to the Wordpress Dashboard
*/

function horoscopes_admin_menu_setup()
{
    add_submenu_page
    (
        'options-general.php',
        'Horoscopes Settings',
        'Horoscopes',
        'manage_options',
        'horoscopes',
        'horoscopes_admin_page_screen'
    );
}
add_action('admin_menu', 'horoscopes_admin_menu_setup');

/*
    Display the Horoscopes Admin Page
*/

function horoscopes_admin_page_screen()
{
    global $submenu;
    $page_data = array();
    foreach($submenu['options-general.php'] as $i => $menu_item) 
    {
        if($submenu['options-general.php'][$i][2] == 'horoscopes')
            $page_data = $submenu['options-general.php'][$i];
    }
?>
    <div class="wrap">
    <?php screen_icon();?>
<?
    $options = get_option('horoscopes_options');
    if ($options)
    {
        $websitename = (isset($options['websitename_template'])) ? $options['websitename_template'] : '';
        $apikey = (isset($options['apikey_template'])) ? $options['apikey_template'] : '';
        $contactemail = (isset($options['contactemail_template'])) ? $options['contactemail_template'] : '';
        $numbermonths  = (isset($options['numbermonths_template'])) ? $options['numbermonths_template'] : '';
        $horoscopesclientpath = 'http://www.myastrologycharts.com/';
        $horoscopesclientupdate = 'horoscopesclientupdate.php';
        $params = sprintf('<astroRequest apikey="%s" websitename="%s" email="%s" numbermonths="%d"></astroRequest>',$apikey,$websitename,$contactemail,$numbermonths);
        $url = sprintf ("%s%s?requestxml=%s",$horoscopesclientpath,$horoscopesclientupdate,urlencode($params));
        $returnxmlstring = loadXML($url);
    }
?>
    <h2><?php echo $page_data[3];?></h2>
    <form id="horoscopes_options" action="options.php" method="post">
<?php
    settings_fields('horoscopes_options');
    do_settings_sections('horoscopes'); 
    submit_button('Save options', 'primary', 'horoscopes_options_submit');
?>
    </form>
    </div>
<?php
}

/*
    Add the setting link on the Plugins page pointing to the Admin Page
*/

function plugin_add_settings_link( $links )
{
    $settings_link = '<a href="options-general.php?page=horoscopes">' . __( 'Settings' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );

function horoscopes_settings_init()
{

    register_setting
    (
        'horoscopes_options',
        'horoscopes_options',
        'horoscopes_options_validate'
    );

    /*
       Add the Settings Section
    */

    add_settings_section
    (
        'horoscopes_settings',
        '', 
        'horoscopes_headertext',
        'horoscopes'
    );

    /*
        Add the various fields to be displayed on the screem
    */

    add_settings_field
    (
        'horoscopes_websitename_template',
        'Website URL', 
        'horoscopes_websitename_field',
        'horoscopes',
        'horoscopes_settings'
    );

    add_settings_field
    (
        'horoscopes_contactemail_template',
        'Contact Email', 
        'horoscopes_contactemail_field',
        'horoscopes',
        'horoscopes_settings'
    );

    add_settings_field
    (
        'horoscopes_numbermonths_template',
        'Number of Months Displayed', 
        'horoscopes_numbermonths_field',
        'horoscopes',
        'horoscopes_settings'
    );

    add_settings_field
    (
        'horoscopes_apikey_template',
        'Apikey', 
        'horoscopes_apikey_field',
        'horoscopes',
        'horoscopes_settings'
    );
}
add_action('admin_init', 'horoscopes_settings_init');

/* 
    Validates Input. Not currently Used.
*/

function horoscopes_options_validate($input)
{
    return $input;
}

function horoscopes_headertext()
{
    echo "<p>Please make sure the Website URL and Contact Email are set before using the plugin</p>";
}

function horoscopes_websitename_field()
{
    $options = get_option('horoscopes_options');
    $websitename = (isset($options['websitename_template'])) ? $options['websitename_template'] : '';
    $websitename = esc_textarea($websitename); //sanitise output
?>
    <input id="websitename_template" name="horoscopes_options[websitename_template]" value="<?php echo$websitename?>">
<?
}

function horoscopes_contactemail_field()
{
    $options = get_option('horoscopes_options');
    $contactemail = (isset($options['contactemail_template'])) ? $options['contactemail_template'] : '';
    $contactemail = esc_textarea($contactemail); //sanitise output
?>
    <input id="contactemail_template" name="horoscopes_options[contactemail_template]" value="<?php echo$contactemail?>">
<?
}

function horoscopes_numbermonths_field()
{
    $options = get_option('horoscopes_options');
    $numbermonths  = (isset($options['numbermonths_template'])) ? $options['numbermonths_template'] : '';
    $numbermonths = esc_textarea($numbermonths); //sanitise output

    /*
        If Number Months is not set make it the initial value on activation
    */

    if (!$numbermonths)
        $numbermonths = get_option('initialnumbermonths');
?>
    <input id="numbermonths_template" name="horoscopes_options[numbermonths_template]" value="<?php echo$numbermonths?>">
<?php
}

function horoscopes_apikey_field()
{
    $options = get_option('horoscopes_options');
    $apikey = (isset($options['apikey_template'])) ? $options['apikey_template'] : '';
    $apikey = esc_textarea($apikey); //sanitise output
    /*
        If APikey is not set make it the generated API
    */
    if (!$apikey)
       $apikey = get_option('generatedapikey');
?>
    <input readonly="readonly" id="apikey_template" name="horoscopes_options[apikey_template]" value="<?php echo$apikey?>">
<?php
}

function horoscopes()
{
    $options = get_option('horoscopes_options');
    $websitename = (isset($options['websitename_template'])) ? $options['websitename_template'] : '';
    $contactemail = (isset($options['contactemail_template'])) ? $options['contactemail_template'] : '';
    $numbermonths = (isset($options['numbermonths_template'])) ? $options['numbermonths_template'] : '';
    $apikey = (isset($options['apikey_template'])) ? $options['apikey_template'] : '';
/*
    if (!$websitename || !$contactemail)
    {
        printf ("<p>Please setup both the Website Name & Contact Email</p>");
        printf ("You can do this via the Plugins/Horoscopes Settings Link</p>");
        return;
    }
*/
/*
    $filename = './debughoroscopes.txt';
    if (!$handle = fopen($filename, 'w'))
    {
        print "Cannot open file ($filename)";
        exit;
    }
*/
    $enginepath = "http://www.myastrologycharts.com/";
    $enginename = "horoscopesservice.php";
    $params = sprintf('<astroRequest responseFormat="xml">
        <reports numbermonths="%d">
        </reports>
        <auth siteId="iPhone" apiKey="%s"/>
      </astroRequest>',$numbermonths,$apikey);

    $url = sprintf ("%s%s?requestxml=%s",$enginepath,$enginename,urlencode($params));
    $returnxmlstring = loadXML($url);
    $Interpretations = $returnxmlstring->Interpretations;
    foreach ($Interpretations->Interpretation as $Interpretation)
    {
#        fprintf ($handle,"Processing %s %s\n", $Interpretation["month"],$Interpretation["year"]);
        $retstring = sprintf("%s<p style=\"font-size:120%%; font-weight:bold\">Horoscopes %s %s</p>",$retstring,$Interpretation["month"],$Interpretation["year"]);
        foreach ($Interpretation->Intro as $Intro)
        {
            $retstring = sprintf("%s<p style=\"font-weight:bold\">Introduction</p>",$retstring);
            $Intro->content = str_ireplace("<![CDATA[","",$Intro->content );
            $Intro->content = str_ireplace("]]>","",$Intro->content );
            $retstring = sprintf("%s<p style=\"text-align:justify\">%s</p",$retstring,$Intro->content);
        }
        foreach ($Interpretation->SunSigns->Sign as $Sign)
        {
            $retstring = sprintf("%s<p style=\"font-weight:bold\">%s (%s)</p>",$retstring,$Sign["name"], $Sign["dates"]);
            $Sign->content = str_ireplace("<![CDATA[","",$Sign->content );
            $Sign->content = str_ireplace("]]>","",$Sign->content );
            $retstring = sprintf("%s<p style=\"text-align:justify\">%s</p>",$retstring,$Sign->content);
        }          
    }
    $returnxmlstring->Footer->content = str_ireplace("<![CDATA[","",$returnxmlstring->Footer->content);
    $returnxmlstring->Footer->content = str_ireplace("]]>","",$returnxmlstring->Footer->content);
    if ($returnxmlstring->Footer->content != '')
        $retstring = sprintf("%s%s",$retstring,$returnxmlstring->Footer->content);

    $retstring = sprintf("%s<span style=\"color:black\">&copy %s <a style=\"color:black; text-decoration:none\" href=\"http://www.seeingwithstars.net\">Seeingwithstars</a> & <a style=\"color:black; text-decoration:none\" href=\"http://www.myastrologycharts.com\">Myastrologycharts</a></span>",$retstring,date("Y"));
    return ($retstring);
}

function loadXML($url)
{
    if (ini_get('allow_url_fopen') == true)
    {
         return load_xml_fopen($url);
    } 
    else if (function_exists('curl_init'))
    {
         return load_xml_curl($url);
    }
    else
    {
         throw new Exception("Can't load data.");
    }
}
 
function load_xml_fopen($url)
{
    return simplexml_load_file($url);
}
 
function load_xml_curl($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return simplexml_load_string($result);
}

function loadContents($url)
{
    if (ini_get('allow_url_fopen') == true)
    {
         return load_contents_get($url);
    }
    else if (function_exists('curl_init'))
    {
         return load_contents_curl($url);
    }
    else
    {
         throw new Exception("Can't load data.");
    }
}
 
function load_contents_get($url)
{
    return file_get_contents($url);
}
 
function load_contents_curl($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($curl);
    curl_close($curl);
    return $result;
}
?>