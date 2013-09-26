<?php

if ( !is_admin() ) 
{
    echo 'Direct access not allowed.';
    exit;
}

global $wpdb;
$message = "";

if (isset($_GET['u']) && $_GET['u'] != '')
{
    $wpdb->query('UPDATE `'.$wpdb->prefix.CP_EASYFORM_FORMS_TABLE.'` SET form_name="'.esc_sql($_GET["name"]).'" WHERE id='.$_GET['u']);           
    $message = "Item updated";        
}
else if (isset($_GET['ac']) && $_GET['ac'] == 'st')
{   
    update_option( 'CP_EFB_LOAD_SCRIPTS', ($_GET["scr"]=="1"?"0":"1") );   
    if ($_GET["chs"] != '')
    {
        $target_charset = $_GET["chs"];
        $tables = array( $wpdb->prefix.CP_EASYFORM_FORMS_TABLE );                
        foreach ($tables as $tab)
        {  
            $myrows = $wpdb->get_results( "DESCRIBE {$tab}" );                                                                                 
            foreach ($myrows as $item)
	        {
	            $name = $item->Field;
		        $type = $item->Type;
		        if (preg_match("/^varchar\((\d+)\)$/i", $type, $mat) || !strcasecmp($type, "CHAR") || !strcasecmp($type, "TEXT") || !strcasecmp($type, "MEDIUMTEXT"))
		        {
	                $wpdb->query("ALTER TABLE {$tab} CHANGE {$name} {$name} {$type} COLLATE {$target_charset}");	            
	            }
	        }
        }
    }
    $message = "Troubleshoot settings updated";
}


if ($message) echo "<div id='setting-error-settings_updated' class='updated settings-error'><p><strong>".$message."</strong></p></div>";

?>
<div class="wrap">
<h2>CP Easy Form Builder</h2>

<script type="text/javascript">
 
 function cp_updateItem(id)
 {
    var calname = document.getElementById("calname_"+id).value;    
    document.location = 'options-general.php?page=cp_easy_form_builder&u='+id+'&r='+Math.random()+'&name='+encodeURIComponent(calname);    
 }
 
 function cp_manageSettings(id)
 {
    document.location = 'options-general.php?page=cp_easy_form_builder&cal='+id+'&r='+Math.random();
 }
   
 function cp_updateConfig()
 {
    if (confirm('Are you sure that you want to update these settings?'))
    {        
        var scr = document.getElementById("ccscriptload").value;    
        var chs = document.getElementById("cccharsets").value;    
        document.location = 'options-general.php?page=cp_easy_form_builder&ac=st&scr='+scr+'&chs='+chs+'&r='+Math.random();
    }    
 }
 
</script>


<div id="normal-sortables" class="meta-box-sortables">


 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Form List / Items List</span></h3>
  <div class="inside">
  
  
  <table cellspacing="10"> 
   <tr>
    <th align="left">ID</th><th align="left">Form Name</th><th align="left">&nbsp; &nbsp; Options</th><th align="left">Shorttag for Pages and Posts</th>
   </tr> 
<?php  

  $myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix.CP_EASYFORM_FORMS_TABLE );                                                                     
  foreach ($myrows as $item)         
  {
?>
   <tr> 
    <td nowrap><?php echo $item->id; ?></td>
    <td nowrap><input type="text" name="calname_<?php echo $item->id; ?>" id="calname_<?php echo $item->id; ?>" value="<?php echo esc_attr($item->form_name); ?>" /></td>          
    
    <td nowrap>&nbsp; &nbsp; 
                             <input type="button" name="calupdate_<?php echo $item->id; ?>" value="Update" onclick="cp_updateItem(<?php echo $item->id; ?>);" /> &nbsp; 
                             <input type="button" name="calmanage_<?php echo $item->id; ?>" value="Manage Settings" onclick="cp_manageSettings(<?php echo $item->id; ?>);" /> 
                             
    </td>
    <td nowrap>[CP_EASY_FORM_WILL_APPEAR_HERE id="<?php echo $item->id; ?>"]</td>          
   </tr>
<?php  
   } 
?>   
     
  </table> 
    
    
   
  </div>    
 </div> 
 

 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>New Form</span></h3>
  <div class="inside"> 
   
    This version supports one form. <a href="http://wordpress.dwbooster.com/forms/cp-easy-form-builder">Click for other versions</a>.

  </div>    
 </div>


 <div id="metabox_basic_settings" class="postbox" >
  <h3 class='hndle' style="padding:5px;"><span>Troubleshoot Area</span></h3>
  <div class="inside"> 
    <p><strong>Important!</strong>: Use this area <strong>only</strong> if you are experiencing conflicts with third party plugins, with the theme scripts or with the character encoding.</p>
    <form name="updatesettings">
      Script load method:<br />
       <select id="ccscriptload" name="ccscriptload">
        <option value="0" <?php if (get_option('CP_EFB_LOAD_SCRIPTS',"1") == "1") echo 'selected'; ?>>Classic (Recommended)</option>
        <option value="1" <?php if (get_option('CP_EFB_LOAD_SCRIPTS',"1") != "1") echo 'selected'; ?>>Direct</option>
       </select><br />
       <em>* Change the script load method if the form doesn't appear in the public website.</em>
      
      <br /><br />
      Character encoding:<br />
       <select id="cccharsets" name="cccharsets">
        <option value="">Keep current charset (Recommended)</option>
        <option value="utf8_general_ci">UTF-8 (try this first)</option>
        <option value="latin1_swedish_ci">latin1_swedish_ci</option>
       </select><br />
       <em>* Update the charset if you are getting problems displaying special/non-latin characters. After updated you need to edit the special characters again.</em>
       <br />
       <input type="button" onclick="cp_updateConfig();" name="gobtn" value="UPDATE" />
      <br /><br />      
    </form>

  </div>    
 </div> 


  
</div> 


[<a href="http://wordpress.dwbooster.com/contact-us" target="_blank">Request Custom Modifications</a>] | [<a href="http://wordpress.dwbooster.com/calendars/cp-easy-form-builder" target="_blank">Help</a>]
</form>
</div>














