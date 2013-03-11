<?php  
$theme = "maarch";
$show_maarch_list = false;
if($_SESSION['mode_editor'])
{
    //$theme = trim($_SESSION['mode_editor']);
    $show_maarch_list = true;
}
?>
<!-- tinyMCE -->
<script  type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'].'tools/';?>tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
    tinyMCE.init({
        theme : "<?php  echo $theme;?>",
        mode : "exact",
        elements : "body_from_html",
        language : "fr",
        // save_callback : "customSave",
        //apply_source_formatting : true,
        //convert_newlines_to_brs : true,
        //preformatted : true,
        convert_fonts_to_spans : false,
        verify_html : true,
        //content_css : "example_advanced.css",
        extended_valid_elements : "a[href|target|name]",
        plugins : "table,insertdatetime,fullscreen,paste,searchreplace,preview,print,directionality,layer",
        theme_<?php  echo $theme;?>_buttons1_add : "fontselect,fontsizeselect",
        theme_<?php  echo $theme;?>_buttons2_add_before : "cut,copy,paste,pastetext,pasteword,separator,search,replace,separator",
        theme_<?php  echo $theme;?>_buttons2_add : "separator,insertdate,inserttime,preview,separator,forecolor,backcolor",
        theme_<?php  echo $theme;?>_buttons3_add_before : "tablecontrols,separator",
        theme_<?php  echo $theme;?>_buttons3_add : "separator,print,separator,ltr,rtl,separator,fullscreen,separator,insertlayer,moveforward,movebackward,absolut",
        theme_<?php  echo $theme;?>_toolbar_align : "left",
        theme_advanced_toolbar_location : "top",
        //invalid_elements : "a",
        theme_<?php  echo $theme;?>_styles : "Header 1=header1;Header 2=header2;Header 3=header3;Table Row=tableRow1" // Theme specific setting CSS classes
        <?php  
        if($show_maarch_list)
        {
            //echo ', mapping_file : "'.$_SESSION['config']['coreurl'].'modules/templates/xml/mapping_file.xml"';
            if(file_exists($_SESSION['config']['corepath'].'custom'.DIRECTORY_SEPARATOR.$_SESSION['custom_override_id'].DIRECTORY_SEPARATOR."modules".DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR."xml".DIRECTORY_SEPARATOR."mapping_file.xml"))
            {
                echo ', mapping_file : "'.$_SESSION['config']['coreurl'].'custom/'.$_SESSION['custom_override_id'].'/modules/templates/xml/mapping_file.xml"';
            }
            else
            {
                echo ', mapping_file : "'.$_SESSION['config']['coreurl'].'modules/templates/xml/mapping_file.xml"';
            }
        }
        ?>
    });
    
    function getBodyConten() {
        var body =  tinyMCE.get('body_from_html').getContent();
        // alert(body);
        return body;
    }

    // Custom save callback, gets called when the contents is to be submitted
    function customSave(id, content) {
        alert(id + "=" + content);
    }
</script>
<!-- /tinyMCE -->
