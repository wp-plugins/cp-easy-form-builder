<?php

if ( !defined('CP_AUTH_INCLUDE') )
{
    echo 'Direct access not allowed.';
    exit;
}

?>
</p>
<link href="<?php echo plugins_url('css/stylepublic.css', __FILE__); ?>" type="text/css" rel="stylesheet" />
<link href="<?php echo plugins_url('css/cupertino/jquery-ui-1.8.20.custom.css', __FILE__); ?>" type="text/css" rel="stylesheet" />
<script type="text/javascript">
           $easyFormQuery = jQuery.noConflict();
           $easyFormQuery(document).ready(function() {
         	   var f = $easyFormQuery("#fbuilder").fbuilder({pub:true,messages: {
	                	required: '<?php echo str_replace("'","\\'",get_option('vs_text_is_required', CP_EASYFORM_DEFAULT_vs_text_is_required)); ?>',
	                	email: '<?php echo str_replace("'","\\'",get_option('vs_text_is_email', CP_EASYFORM_DEFAULT_vs_text_is_email)); ?>',
	                	datemmddyyyy: '<?php echo str_replace("'","\\'",get_option('vs_text_datemmddyyyy', CP_EASYFORM_DEFAULT_vs_text_datemmddyyyy)); ?>',
	                	dateddmmyyyy: '<?php echo str_replace("'","\\'",get_option('vs_text_dateddmmyyyy', CP_EASYFORM_DEFAULT_vs_text_dateddmmyyyy)); ?>',
	                	number: '<?php echo str_replace("'","\\'",get_option('vs_text_number', CP_EASYFORM_DEFAULT_vs_text_number)); ?>',
	                	digits: '<?php echo str_replace("'","\\'",get_option('vs_text_digits', CP_EASYFORM_DEFAULT_vs_text_digits)); ?>',
	                	max: '<?php echo str_replace("'","\\'",get_option('vs_text_max', CP_EASYFORM_DEFAULT_vs_text_max)); ?>',
	                	min: '<?php echo str_replace("'","\\'",get_option('vs_text_min', CP_EASYFORM_DEFAULT_vs_text_min)); ?>'
	                }});
               f.fBuild.loadData("form_structure");
                $easyFormQuery("#cp_easyform_pform").validate({
			        //ignore: "",
			        errorElement: "div",
			        errorPlacement: function(e, element) {
                        if (element.hasClass('group')){
                            element = element.parent().siblings(":last");
                            //element = element.siblings(":last");
                        }
                        //else
                        //    e.insertAfter(element);
                        offset = element.offset();
                        e.insertBefore(element)
                        e.addClass('message');  // add a class to the wrapper
                        e.css('position', 'absolute');
                        e.css('left',0 );
                        ////e.css('left', offset.left );//+ element.outerWidth()
                        //e.css('top', offset.top+element.outerHeight()+0);
                        e.css('top',element.outerHeight());
                    },
                    submitHandler: function(form) {
                        $easyFormQuery("#cp_easyform_subbtn").attr("disabled", "disabled");
                        $easyFormQuery("#cp_easyform_subbtn_animation").show();
                        $easyFormQuery.post('<?php echo cp_easyform_get_site_url(); ?>/', $easyFormQuery("#cp_easyform_pform").serialize(),  function(data) {
                            if (data == "captchafailed")
                            {
                                 $easyFormQuery("#cp_easyform_subbtn").removeAttr("disabled");
                                 $easyFormQuery("#cp_easyform_subbtn_animation").hide();
                                 $easyFormQuery("#hdcaptcha_error").html("<?php echo esc_attr(get_option('cv_text_enter_valid_captcha', CP_EASYFORM_DEFAULT_cv_text_enter_valid_captcha)); ?>");
                                 $easyFormQuery("#hdcaptcha_error").css('top',$easyFormQuery("#hdcaptcha").outerHeight());
                                 $easyFormQuery("#hdcaptcha_error").css("display","inline");
                                 $easyFormQuery("#captchaimg").attr('src', $easyFormQuery("#captchaimg").attr('src')+'&'+Date());
                            }
                            else
                                document.location.href='<?php echo get_option('fp_return_page', CP_EASYFORM_DEFAULT_fp_return_page); ?>';
                        });
                        return false;
                    }
                });
           });
</script>

<form name="cp_easyform_pform" id="cp_easyform_pform" action="<?php get_site_url(); ?>" method="post">
  <input type="hidden" name="cp_easyform_pform_process" value="1" />
<input type="hidden" name="form_structure" id="form_structure" size="180" value="<?php echo esc_attr(cp_easyform_cleanJSON(get_option('form_structure', CP_EASYFORM_DEFAULT_form_structure))); ?>" />
    <div id="fbuilder">
        <div id="formheader"></div>
        <div id="fieldlist"></div>
    </div>
  <br />
<?php if (get_option('cv_enable_captcha', CP_EASYFORM_DEFAULT_cv_enable_captcha) != 'false') { ?>
  Please enter the security code:<br />
  <img src="<?php echo plugins_url('/captcha/captcha.php?width='.get_option('cv_width', CP_EASYFORM_DEFAULT_cv_width).'&height='.get_option('cv_height', CP_EASYFORM_DEFAULT_cv_height).'&letter_count='.get_option('cv_chars', CP_EASYFORM_DEFAULT_cv_chars).'&min_size='.get_option('cv_min_font_size', CP_EASYFORM_DEFAULT_cv_min_font_size).'&max_size='.get_option('cv_max_font_size', CP_EASYFORM_DEFAULT_cv_max_font_size).'&noise='.get_option('cv_noise', CP_EASYFORM_DEFAULT_cv_noise).'&noiselength='.get_option('cv_noise_length', CP_EASYFORM_DEFAULT_cv_noise_length).'&bcolor='.get_option('cv_background', CP_EASYFORM_DEFAULT_cv_background).'&border='.get_option('cv_border', CP_EASYFORM_DEFAULT_cv_border).'&font='.get_option('cv_font', CP_EASYFORM_DEFAULT_cv_font), __FILE__); ?>"  id="captchaimg" alt="security code" border="0"  />
  <br />
  Security Code (lowercase letters):<br />
  <div class="dfield">
  <input type="text" size="20" name="hdcaptcha" id="hdcaptcha" value="" />
  <div class="error message" id="hdcaptcha_error" generated="true" style="display:none;position: absolute; left: 0px; top: 25px;"></div>
  </div>
  <br />
<?php } ?>
<input type="submit" class="submit" name="cp_easyform_subbtn" id="cp_easyform_subbtn" value="<?php _e("Submit"); ?>">
<div style="display:none" id="cp_easyform_subbtn_animation" style="background:#ffffff;width:18;height:18;padding:1px;">
 <img src="<?php echo plugins_url('/images/loading.gif', __FILE__); ?>" width="16" height="16" alt="loading" />
</div>
</form>