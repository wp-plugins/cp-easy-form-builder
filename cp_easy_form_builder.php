<?php
/*
Plugin Name: CP Easy Form Builder
Plugin URI: http://wordpress.dwbooster.com/forms/cp-easy-form-builder
Description: This plugin allows you to easily insert forms into your website and get them via email.
Version: 1.01
Author: CodePeople.net
Author URI: http://codepeople.net
License: GPL
*/


/* initialization / install / uninstall functions */


define('CP_EASYFORM_TABLE_NAME_NO_PREFIX', "cp_easy_forms");
define('CP_EASYFORM_TABLE_NAME', $wpdb->prefix . CP_EASYFORM_TABLE_NAME_NO_PREFIX);


// CP Easy Form constants
     

define('CP_EASYFORM_DEFAULT_form_structure', '[[{"name":"email","index":0,"title":"Email","ftype":"femail","userhelp":"","csslayout":"","required":true,"predefined":"","size":"medium"},{"name":"subject","index":1,"title":"Subject","required":true,"ftype":"ftext","userhelp":"","csslayout":"","predefined":"","size":"medium"},{"name":"message","index":2,"size":"large","required":true,"title":"Message","ftype":"ftextarea","userhelp":"","csslayout":"","predefined":""}],[{"title":"Contact Form","description":"You can use the following form to contact us. <br />","formlayout":"top_aligned"}]]');

define('CP_EASYFORM_DEFAULT_fp_subject', 'Contact from the blog...');
define('CP_EASYFORM_DEFAULT_fp_inc_additional_info', 'true');
define('CP_EASYFORM_DEFAULT_fp_return_page', get_site_url());
define('CP_EASYFORM_DEFAULT_fp_message', "The following contact message has been sent:\n\n<%INFO%>\n\n");

define('CP_EASYFORM_DEFAULT_cu_enable_copy_to_user', 'false');
define('CP_EASYFORM_DEFAULT_cu_user_email_field', '');
define('CP_EASYFORM_DEFAULT_cu_subject', 'Confirmation: Message received...');
define('CP_EASYFORM_DEFAULT_cu_message', "Thank you for your message. We will reply you as soon as possible.\n\nThis is a copy of the data sent:\n\n<%INFO%>\n\nBest Regards.");

define('CP_EASYFORM_DEFAULT_vs_use_validation', 'true');

define('CP_EASYFORM_DEFAULT_vs_text_is_required', 'This field is required.');
define('CP_EASYFORM_DEFAULT_vs_text_is_email', 'Please enter a valid email address.');

define('CP_EASYFORM_DEFAULT_vs_text_datemmddyyyy', 'Please enter a valid date with this format(mm/dd/yyyy)');
define('CP_EASYFORM_DEFAULT_vs_text_dateddmmyyyy', 'Please enter a valid date with this format(dd/mm/yyyy)');
define('CP_EASYFORM_DEFAULT_vs_text_number', 'Please enter a valid number.');
define('CP_EASYFORM_DEFAULT_vs_text_digits', 'Please enter only digits.');
define('CP_EASYFORM_DEFAULT_vs_text_max', 'Please enter a value less than or equal to {0}.');
define('CP_EASYFORM_DEFAULT_vs_text_min', 'Please enter a value greater than or equal to {0}.');


define('CP_EASYFORM_DEFAULT_cv_enable_captcha', 'false');
define('CP_EASYFORM_DEFAULT_cv_width', '180');
define('CP_EASYFORM_DEFAULT_cv_height', '60');
define('CP_EASYFORM_DEFAULT_cv_chars', '5');
define('CP_EASYFORM_DEFAULT_cv_font', 'font-1.ttf');
define('CP_EASYFORM_DEFAULT_cv_min_font_size', '25');
define('CP_EASYFORM_DEFAULT_cv_max_font_size', '35');
define('CP_EASYFORM_DEFAULT_cv_noise', '200');
define('CP_EASYFORM_DEFAULT_cv_noise_length', '4');
define('CP_EASYFORM_DEFAULT_cv_background', 'ffffff');
define('CP_EASYFORM_DEFAULT_cv_border', '000000');
define('CP_EASYFORM_DEFAULT_cv_text_enter_valid_captcha', 'Please enter a valid captcha code.');
 
// end CP Easy Form constants


register_activation_hook(__FILE__,'cp_easyform_install'); 
register_deactivation_hook( __FILE__, 'cp_easyform_remove' );


function cp_easyform_install($networkwide)  {
	global $wpdb;
 
	if (function_exists('is_multisite') && is_multisite()) {
		// check if it is a network activation - if so, run the activation function for each blog id
		if ($networkwide) {
	                $old_blog = $wpdb->blogid;
			// Get all blog ids
			$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs"));
			foreach ($blogids as $blog_id) {
				switch_to_blog($blog_id);
				_cp_easyform_install();
			}
			switch_to_blog($old_blog);
			return;
		}	
	} 
	_cp_easyform_install();	
}

function _cp_easyform_install() {
    global $wpdb;
    
    /*
    $table_name = $wpdb->prefix . CP_EASYFORM_TABLE_NAME_NO_PREFIX;
      
    $sql = "......";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);    
    */
    
    add_option("cp_easyform_data", 'Default', '', 'yes'); // Creates new database field 
}

function cp_easyform_remove() {    
    delete_option('cp_easyform_data'); // Deletes the database field 
}


/* Filter for placing the maps into the contents */

add_filter('the_content','cp_easyform_filter_content');

function cp_easyform_filter_content($content) {
    
    if (strpos($content, "[CP_EASY_FORM_WILL_APPEAR_HERE]") !== false) 
    {        
        ob_start();
        define('CP_AUTH_INCLUDE', true);
        @include dirname( __FILE__ ) . '/cp_easyform_public_int.inc.php';
        $buffered_contents = ob_get_contents();
        ob_end_clean();

        $content = str_replace("[CP_EASY_FORM_WILL_APPEAR_HERE]", $buffered_contents, $content);
    }    
    return $content;
}


/* Code for the admin area */

if ( is_admin() ) {
    add_action('media_buttons', 'set_cp_easyform_insert_button', 100);
    add_action('admin_enqueue_scripts', 'set_cp_easyform_insert_adminScripts', 1);    
    add_action('admin_menu', 'cp_easyform_admin_menu');
    add_action('admin_init', 'cp_easyform_register_mysettings' );
    
    $plugin = plugin_basename(__FILE__);        
    add_filter("plugin_action_links_".$plugin, 'cp_easyform_customAdjustmentsLink');    
    add_filter("plugin_action_links_".$plugin, 'cp_easyform_settingsLink');
    add_filter("plugin_action_links_".$plugin, 'cp_easyform_helpLink');   

    function cp_easyform_register_mysettings() { // whitelist options
      
      register_setting( 'cp-easyform-group', 'form_structure' );
      
      register_setting( 'cp-easyform-group', 'fp_from_email' );
      register_setting( 'cp-easyform-group', 'fp_destination_emails' );
      register_setting( 'cp-easyform-group', 'fp_subject' );
      register_setting( 'cp-easyform-group', 'fp_inc_additional_info' );
      register_setting( 'cp-easyform-group', 'fp_return_page' );
      
      register_setting( 'cp-easyform-group', 'cu_enable_copy_to_user' );
      register_setting( 'cp-easyform-group', 'cu_user_email_field' );
      register_setting( 'cp-easyform-group', 'cu_subject' );
      register_setting( 'cp-easyform-group', 'cu_message' );      
      
      register_setting( 'cp-easyform-group', 'vs_use_validation' );
      register_setting( 'cp-easyform-group', 'vs_text_is_required' );
      register_setting( 'cp-easyform-group', 'vs_text_is_email' );
      
      register_setting( 'cp-easyform-group', 'vs_text_datemmddyyyy' );
      register_setting( 'cp-easyform-group', 'vs_text_dateddmmyyyy' );
      register_setting( 'cp-easyform-group', 'vs_text_number' );
      register_setting( 'cp-easyform-group', 'vs_text_digits' );
      register_setting( 'cp-easyform-group', 'vs_text_max' );
      register_setting( 'cp-easyform-group', 'vs_text_min' );
      
      
      register_setting( 'cp-easyform-group', 'cv_enable_captcha' );
      register_setting( 'cp-easyform-group', 'cv_width' );
      register_setting( 'cp-easyform-group', 'cv_height' );
      register_setting( 'cp-easyform-group', 'cv_chars' );
      register_setting( 'cp-easyform-group', 'cv_font' );
      register_setting( 'cp-easyform-group', 'cv_min_font_size' );
      register_setting( 'cp-easyform-group', 'cv_max_font_size' );
      register_setting( 'cp-easyform-group', 'cv_noise' );
      register_setting( 'cp-easyform-group', 'cv_noise_length' );
      register_setting( 'cp-easyform-group', 'cv_background' );
      register_setting( 'cp-easyform-group', 'cv_border' );
      register_setting( 'cp-easyform-group', 'cv_text_enter_valid_captcha' );     
      
      
    }

    function cp_easyform_admin_menu() {                
        add_options_page('CP Easy Form Builder Options', 'CP Easy Form Builder', 'manage_options', 'cp_easy_form_builder', 'cp_easyform_html_post_page' );
    }
} else { // if not admin
    add_action('wp_enqueue_scripts', 'set_cp_easyform_insert_publicScripts');
}

function cp_easyform_settingsLink($links) {
    $settings_link = '<a href="options-general.php?page=cp_easy_form_builder">'.__('Settings').'</a>'; 
	array_unshift($links, $settings_link);     
	return $links;
}

function cp_easyform_helpLink($links) {
    $help_link = '<a href="http://wordpress.dwbooster.com/forms/cp-easy-form-builder">'.__('Help').'</a>'; 
	array_unshift($links, $help_link);     
	return $links;
}

function cp_easyform_customAdjustmentsLink($links) {
    $customAdjustments_link = '<a href="http://wordpress.dwbooster.com/contact-us">'.__('Request custom changes').'</a>'; 
	array_unshift($links, $customAdjustments_link);     
	return $links;
}

function cp_easyform_html_post_page() {
    @include_once dirname( __FILE__ ) . '/cp_easyform_admin_int.php';
}

function set_cp_easyform_insert_button() {
    print '<a href="javascript:cp_easyform_insertForm();" title="'.__('Insert CP Easy Form').'"><img hspace="5" src="'.plugins_url('/images/cp_form.gif', __FILE__).'" alt="'.__('Insert CP Easy Form').'" /></a>';    
} 

function set_cp_easyform_insert_adminScripts($hook) { 
         
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-sortable' );
    wp_enqueue_script( 'jquery-ui-tabs' );
    wp_enqueue_script( 'jquery-ui-droppable' );
    wp_enqueue_script( 'jquery-ui-button' );
    wp_enqueue_script( 'query-stringify', plugins_url('/js/jQuery.stringify.js', __FILE__) );
    wp_enqueue_script( 'cp_easyform_buikder_script', plugins_url('/js/fbuilder.jquery.js', __FILE__) );
    
    if( 'post.php' != $hook  && 'post-new.php' != $hook )
        return;
    wp_enqueue_script( 'cp_easyform_script', plugins_url('/cp_easyform_scripts.js', __FILE__) );        
}

function set_cp_easyform_insert_publicScripts($hook) {           
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'jquery-ui-tabs' );
    wp_enqueue_script( 'jquery-ui-button' );    
    wp_enqueue_script( 'jquery-ui-core' );
    wp_enqueue_script( 'jquery-ui-datepicker' );
    wp_enqueue_script( 'query-stringify', plugins_url('/js/jQuery.stringify.js', __FILE__) );
    wp_enqueue_script( 'cp_easyform_builder_script', plugins_url('/js/fbuilder.jquery.js', __FILE__) );
    wp_enqueue_script( 'cp_easyform_validate_script', plugins_url('/js/jquery.validate.js', __FILE__) );
}

function cp_easyform_get_site_url()
{
    $url = parse_url(get_site_url());
    $url = rtrim($url["path"],"/");
    if ($url == '')
        $url = 'http://'.$_SERVER["HTTP_HOST"];
    return $url;
}

function cp_easyform_cleanJSON($str)
{
    $str = str_replace('&qquot;','"',$str);
    $str = str_replace('	',' ',$str);
    return $str;
}

/* hook for checking posted data for the admin area */

add_action( 'init', 'cp_easy_form_check_posted_data', 11 );

function cp_easy_form_check_posted_data() {
	
    global $wpdb;
    
	if ( 'POST' != $_SERVER['REQUEST_METHOD'] || ! isset( $_POST['cp_easyform_pform_process'] ) )		
		return;  
    
    
    // get form info
    //---------------------------
       
    @include_once dirname( __FILE__ ) . '/JSON.inc.php';
    $json = new Services_JSON();
    $form_data = $json->decode(cp_easyform_cleanJSON(get_option('form_structure', CP_EASYFORM_DEFAULT_form_structure)));         
    $fields = array();
    foreach ($form_data[0] as $item)
        $fields[$item->name] = $item->title;
    
    // grab posted data
    //---------------------------
    
    $buffer = "";
    foreach ($_POST as $item => $value)
        if (isset($fields[$item]))
            $buffer .= $fields[$item] . ": ". $value . "\n\n";
        else if (isset($fields[str_replace("_"," ",$item)]))
            $buffer .= $fields[str_replace("_"," ",$item)] . ": ". $value . "\n\n";
    $buffer_A = $buffer;
    if ('true' == get_option('fp_inc_additional_info', CP_EASYFORM_DEFAULT_fp_inc_additional_info))
    {
        $buffer .="ADDITIONAL INFORMATION\n"
              ."*********************************\n"              
              ."IP: ".$_SERVER['REMOTE_ADDR']."\n"
              ."Referer: ".$_SERVER["HTTP_REFERER"]."\n"
              ."Server Time:  ".date("Y-m-d H:i:s")."\n"              
              ."User Agent: ".$_SERVER['HTTP_USER_AGENT']."\n";
              
    }
 
    // 1- Send email
    //---------------------------
    $message = str_replace('<%INFO%>',$buffer,get_option('fp_message', CP_EASYFORM_DEFAULT_fp_message));
    $subject = get_option('fp_subject', CP_EASYFORM_DEFAULT_fp_subject);
    $from = get_option('fp_from_email', CP_EASYFORM_DEFAULT_fp_from_email);    
    $to = explode(",",get_option('fp_destination_emails', CP_EASYFORM_DEFAULT_fp_destination_emails));

    foreach ($to as $item)
        if (trim($item) != '')
        {
            wp_mail(trim($item), $subject, $message,
                "From: \"$from\" <".$from.">\r\n".
                "Content-Type: text/plain; charset=utf-8\n".
                "X-Mailer: PHP/" . phpversion()); 
        }
    
    echo 'ok';
    exit;      
} 


// WIDGET CODE BELOW
// ***********************************************************************
 
class CP_EasyForm_Widget extends WP_Widget
{
  function CP_EasyForm_Widget()
  {
    $widget_ops = array('classname' => 'CP_EasyForm_Widget', 'description' => 'Displays a form' );
    $this->WP_Widget('CP_EasyForm_Widget', 'CP Easy Form Builder', $widget_ops);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
    $title = $instance['title'];
    ?><p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p><?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
 
    if (!empty($title))
      echo $before_title . $title . $after_title;;
 
    // WIDGET CODE GOES HERE
    define('CP_AUTH_INCLUDE', true);
    @include_once dirname( __FILE__ ) . '/cp_easyform_public_int.inc.php';    
 
    echo $after_widget;
  }
 
}

add_action( 'widgets_init', create_function('', 'return register_widget("CP_EasyForm_Widget");') );

?>