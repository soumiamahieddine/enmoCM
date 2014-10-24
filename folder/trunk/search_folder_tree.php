<?php
/**
* File : search_customer.php
*
* Advanced search form
*
* @package  Maarch Framework 3.0
* @version 2.1
* @since 10/2005
* @license GPL
* @author Loïc Vinet  <dev@maarch.org>
* @author Claire Figueras  <dev@maarch.org>
*/

require_once
    "apps" . DIRECTORY_SEPARATOR . $_SESSION['config']['app_id']
    . DIRECTORY_SEPARATOR . "class" . DIRECTORY_SEPARATOR 
    . "class_business_app_tools.php";
    
$appTools   = new business_app_tools();
$core       = new core_tools();

$core->test_user();
$core->load_lang();
$core->test_service('view_folder_tree', 'folder');
$_SESSION['indexation'] = false;

$_SESSION['category_id_session'] = '';

//Definition de la collection
$_SESSION['collection_id_choice'] = $_SESSION['user']['collections'][0];
if ($_SESSION['user']['collections'][1] == 'letterbox_coll') {
    $_SESSION['collection_id_choice'] = 'letterbox_coll';
}
/****************Management of the location bar  ************/
$init = false;
if (isset($_REQUEST['reinit']) && $_REQUEST['reinit'] == 'true') {
    $init = true;
}
$level = '';
if (isset($_REQUEST['level']) && ($_REQUEST['level'] == 2
    || $_REQUEST['level'] == 3 || $_REQUEST['level'] == 4
    || $_REQUEST['level'] == 1)
) {
    $level = $_REQUEST['level'];
}
$pagePath = $_SESSION['config']['businessappurl']
           . 'index.php?page=search_folder_tree&module=folder';
$pageLabel = _VIEW_FOLDER_TREE;
$pageId = 'search_folder_tree';
$core->manage_location_bar(
    $pagePath, $pageLabel, $pageId, $init, $level
);
/***********************************************************/

//$core->show_array($_REQUEST);
$_SESSION['origin'] = "search_folder_tree";
?>
<script type="text/javascript" >
    BASE_URL = "<?php echo $_SESSION['config']['businessappurl'] ?>";
</script>
<h1><img src="<?php
echo $_SESSION['config']['businessappurl'] . "static.php?filename=search_proj_off.gif";
?>" alt="" /> <?php  echo _VIEW_FOLDER_TREE; ?></h1>
<div id="inner_content" align="center">
    <div class="block">
		<form method="post" name="form_search_folder" id="form_search_folder" action="#">
        <table width="100%" border="0">
            <tr>
                <td align="right"><label for="folder"><?php
            echo _FOLDER;
            ?> :</label></td>
                            <td class="indexing_field">
                                <input type="text" name="folder" id="folder" size="45" onKeyPress="if(event.keyCode == 13) submitForm();" />
                                <div id="show_folder" class="autocomplete"></div>
                            </td>
                            <!-- <td align="right"><label for="subfolder"><?php  echo _SUBFOLDER;?> :</label></td>
                            <td>
                                <input type="text" name="subfolder" id="subfolder" size="45" onKeyPress="if(event.keyCode == 13) submitForm();" />
                                <div id="show_subfolder" class="autocomplete"></div>
                            </td>-->
                            <td>
                                <input id="tree_send" type="button" value="<?php
            echo _SEARCH;
            ?>" onclick="javascript:submitForm();" class="button">
                </td>
				<td width="50%">&nbsp;</td>
            </tr>
        </table>
		</form>
    </div>
    <div class="clearsearch">
        <br>
        <a href="javascript://" onClick="window.top.location.href='<?php
                echo $_SESSION['config']['businessappurl'];
                ?>index.php?page=search_folder_tree&module=folder&erase=true';"><img src="<?php
                echo $_SESSION['config']['businessappurl']."static.php?filename=reset.gif";
                ?>" alt="" height="15px" width="15px" /><?php  echo _NEW_SEARCH; ?></a>
    </div>
    <!-- Display the layout of search_folder_tree -->
    <table width="100%" height="100%" cellspacing="5" style="border:1px solid #999999;">
        <tr>
            <td width="55%" height="720px" style="vertical-align: top; text-align: left;border-right:1px solid #999999;">
                <div id="loading" style="display:none;"><img src="<?php echo $_SESSION['config']['businessappurl']; ?>static.php?filename=loading.gif" alt="loading..." width="24px" height="24px" /></div>
                <div id="myTree">&nbsp;</div>
            </td>
            <td width="45%" style="vertical-align: top;">
                <div id="docView"><p align="center"><img src="<?php echo $_SESSION['config']['businessappurl']
                    .'static.php?filename=bg_home_home.gif'; 
                    ?>"  width="400px" alt="Maarch" /></p></div>
            </td>
        </tr>
    </table>
</div>
<script type="text/javascript">

    initList('folder', 'show_folder', '<?php
        echo $_SESSION['config']['businessappurl'];
        ?>index.php?display=true&module=folder&page=autocomplete_folders&mode=folder', 
        'Input', '2');
    function submitForm()
    {
        var folder = $('folder').value;
        if($('myTree'))
        {
            $('myTree').innerHTML="";
        }
        // Get tree parameters from an ajax script (get_tree_info.php)
        new Ajax.Request(BASE_URL+'index.php?display=true&module=folder&page=get_tree_info&display=true',{
            method: 'post',
            parameters: {
                tree_id: 'myTree',
                project: folder
            },
            onSuccess: function(response){
                eval('params='+response.responseText+';');
                //console.log(params);
            },
            onLoading: function(answer) {
                //show loading
                $('loading').style.display='block';
            }, 
            onComplete: function(response){
                $('loading').style.display='none';
                $('myTree').innerHTML=response.responseText;
                 // alert(response.responseText);

             /*   if(more_params['onComplete_callback'])
                {
                    eval(more_params['onComplete_callback']);
                }*/
            }
        });
    }
    function get_folders(folders_system_id)
    {

            if($(''+folders_system_id+'_img').hasClassName('mt_fopened')){
                new Ajax.Request(BASE_URL+'index.php?page=ajax_get_folder&module=folder&display=true',
                {  
                    method:'post',
                    parameters: {folders_system_id: folders_system_id,
                                FOLDER_TREE_RESET: true
                                },
                    onSuccess: function(answer){
                        folders = JSON.parse(answer.responseText);

                        for (var i = 0; i <= folders.length; i++) {
                            level=folders[i].folder_level*10;
                            //console.log(folders[i]);
                            $(''+folders_system_id).innerHTML ='<span onclick="get_folder_docs('+folders[i].folders_system_id+')">'+folders[i].nom_folder+' <b>('+folders[i].nb_subfolder+' sous-dossier(s), '+folders[i].nb_doc+' document(s))</b></span>';
                            $(''+folders_system_id+"_img").addClassName('mt_fclosed');
                            $(''+folders_system_id+"_img").removeClassName('mt_fopened');
                            $(''+folders_system_id+"_img").src = BASE_URL+'static.php?filename=folder.gif';
                        };
                        
                    },
                    onFailure: function(){
                        $(''+folders_system_id).innerHTML += '<div class="error">_SERVER_ERROR</div>';
                       }
                });

            }else{
                new Ajax.Request(BASE_URL+'index.php?page=ajax_get_folder&module=folder&display=true',
                {  
                    method:'post',
                    parameters: {folders_system_id: folders_system_id,
                                FOLDER_TREE: true
                                },
                    onSuccess: function(answer){
                        folders = JSON.parse(answer.responseText);

                        for (var i = 0; i <= folders.length; i++) {
                            //console.log(answer.responseText);
                            level=folders[i].folder_level*10;
                            //if(i!=0){
                                var style='style="margin-left:'+level+'px;"';
                            //}
                            //alert('ok');
                            $(''+folders_system_id).innerHTML +="<br/>";
                            $(''+folders_system_id).innerHTML +='<span '+style+' onclick="get_folders('+folders[i].folders_system_id+')" class="folder"><img src=\"'+BASE_URL+'static.php?filename=folder.gif\" class=\"mt_fclosed\" alt=\"\" id=\"'+folders[i].folders_system_id+'_img\" ></span><li class="folder" id="'+folders[i].folders_system_id+'"><span onclick="get_folder_docs('+folders[i].folders_system_id+')">'+folders[i].nom_folder+' <b>('+folders[i].nb_subfolder+' sous-dossier(s), '+folders[i].nb_doc+' document(s))</b></span></li>';

                            $(''+folders_system_id+"_img").addClassName('mt_fopened');
                            $(''+folders_system_id+"_img").removeClassName('mt_fclosed');
                            $(''+folders_system_id+"_img").src = BASE_URL+'static.php?filename=folderopen.gif';
                        };
                        
                    },
                    onFailure: function(){
                        $(''+folders_system_id).innerHTML += '<div class="error">_SERVER_ERROR</div>';
                       }
                });
        }

        }

        function get_autofolders(folder_level)
        {

            if($(''+folder_level+'_img').hasClassName('mt_fopened')){
                $('autofolders').innerHTML = "Plan de classement dynamique";
                $(''+folder_level+"_img").addClassName('mt_fclosed');
                $(''+folder_level+"_img").removeClassName('mt_fopened');
                $(''+folder_level+"_img").src = BASE_URL+'static.php?filename=folder.gif';

            }else{
                new Ajax.Request(BASE_URL+'index.php?page=ajax_get_folder&module=folder&display=true',
                {  
                    method:'post',
                    parameters: {folder_level: folder_level,
                                AUTOFOLDERS_TREE: true
                                },
                    onSuccess: function(answer){
                        autofolders = JSON.parse(answer.responseText);

                        for (var i = 0; i <= autofolders.length; i++) {
                            //console.log(autofolders[i].desc[0]);
                            //if(i!=0){
                                var style='style="margin-left:20px;"';
                            //}
                            //alert('ok');
                            $('autofolders').innerHTML +="<br/>";
                            $('autofolders').innerHTML +='<span '+style+' onclick="get_autofolder_folders(\''+autofolders[i].id[0]+'\','+autofolders[i].tree_level+',\''+autofolders[i].id[0]+'\',\''+autofolders[i].id[0]+'\',\''+autofolders[i].desc[0]+'\')" class="folder"><img src=\"'+BASE_URL+'static.php?filename=folder.gif\" class=\"mt_fclosed\" alt=\"\" id=\"'+autofolders[i].id[0]+'_img\" ></span><li class="folder" id="'+autofolders[i].id[0]+'">'+autofolders[i].desc[0]+'</li>';

                            $(''+folder_level+"_img").addClassName('mt_fopened');
                            $(''+folder_level+"_img").removeClassName('mt_fclosed');
                            $(''+folder_level+"_img").src = BASE_URL+'static.php?filename=folderopen.gif';
                        };
                        
                    },
                    onFailure: function(){
                        $(''+folders_system_id).innerHTML += '<div class="error">_SERVER_ERROR</div>';
                       }
                });
        }

        }

        function get_autofolder_folders(id, folder_level,id_folder,path_folder,desc_tree)
        {
            if($(''+id_folder+'_img').hasClassName('mt_fopened')){
                $(''+id_folder).innerHTML = desc_tree;
                $(''+id_folder+"_img").addClassName('mt_fclosed');
                $(''+id_folder+"_img").removeClassName('mt_fopened');
                $(''+id_folder+"_img").src = BASE_URL+'static.php?filename=folder.gif';

            }else{
                new Ajax.Request(BASE_URL+'index.php?page=ajax_get_folder&module=folder&display=true',
                {  
                    method:'post',
                    parameters: {folder_level: folder_level,
                                id : id,
                                path_folder : path_folder,
                                id_folder : id_folder,
                                AUTOFOLDER_FOLDERS: true
                                },
                    onSuccess: function(answer){
                        folders = JSON.parse(answer.responseText);
                        $(''+id_folder).innerHTML += '<ul>';
                        for (var i = 0; i <= folders.length; i++) {
                            //console.log(autofolders[i].desc[0]);
                            if(folders[i].folder_level==2){
                                var style='style="margin-left:40px;"';
                            }else{
                                var folder_level=folders[i].folder_level+1;
                                level=folder_level*7;
                                var style='style="margin-left:'+level+'px;"';
                            }
                            var new_path_folder=path_folder+';'+folders[i].libelle;
                            if(folders[i].check_docs==true){
                                var onclick='onclick="get_autofolder_folder_docs(\''+id+'\','+folders[i].folder_level+',\''+folders[i].libelle+'\',\''+new_path_folder+'\',\''+folders[i].libelle+'\')"';
                            }else{
                                var onclick='onclick="get_autofolder_folders(\''+id+'\','+folders[i].folder_level+',\''+folders[i].libelle+'\',\''+new_path_folder+'\',\''+folders[i].libelle+'\')"';
                            }
                            $(''+id_folder).innerHTML +="<br/>";
                            $(''+id_folder).innerHTML +='<span '+onclick+' class="folder" '+style+'><img src=\"'+BASE_URL+'static.php?filename=folder.gif\" class=\"mt_fclosed\" alt=\"\" id=\"'+folders[i].libelle+'_img\" ></span><li '+style+' class="folder" id="'+folders[i].libelle+'">'+folders[i].libelle+'</li>';

                            $(''+id_folder+"_img").addClassName('mt_fopened');
                            $(''+id_folder+"_img").removeClassName('mt_fclosed');
                            $(''+id_folder+"_img").src = BASE_URL+'static.php?filename=folderopen.gif';
                        };
                        //$(''+id_folder).innerHTML += '</ul>';
                        
                    },
                    onFailure: function(){
                        $(''+folders_system_id).innerHTML += '<div class="error">_SERVER_ERROR</div>';
                       }
                });
        }

        }

        function get_autofolder_folder_docs(id, folder_level,id_folder,path_folder)
        {
            if($(''+id_folder+'_img').hasClassName('mt_fopened')){
                $(''+id_folder).innerHTML = id_folder;
                $(''+id_folder+"_img").addClassName('mt_fclosed');
                $(''+id_folder+"_img").removeClassName('mt_fopened');
                $(''+id_folder+"_img").src = BASE_URL+'static.php?filename=folder.gif';

            }else{

                new Ajax.Request(BASE_URL+'index.php?page=ajax_get_folder&module=folder&display=true',
                {  
                    method:'post',
                    parameters: {folder_level: folder_level,
                                id : id,
                                id_folder : id_folder,
                                path_folder : path_folder,
                                AUTOFOLDER_FOLDER_DOCS: true
                                },
                    onSuccess: function(answer){
                        docs = JSON.parse(answer.responseText);
                        for (var i = 0; i <= docs.length; i++) {
                            //console.log(autofolders[i].desc[0]);
                            var style='style="margin-left:35px;"';
                            $(''+id_folder).innerHTML +="<br/>";
                            $(''+id_folder).innerHTML +='<ul class="doc" style="margin-top:10px;margin-left:10px;'+style+'"><li><b>'+docs[i].doctypes_first_level_label+'</b></li><li style="'+style+'"><b>'+docs[i].doctypes_second_level_label+'</b></li><span style="position: relative;top: 4px;'+style+'"" class="doc"><img src=\"'+BASE_URL+'static.php?filename=page.gif\" alt=\"\" id=\"'+docs[i].res_id+'_img_doc\" ></span><a onclick=\'updateContent("index.php?dir=indexing_searching&page=little_details_invoices&display=true&value='+docs[i].res_id+'", "docView");\'>('+docs[i].res_id+') '+docs[i].type_label+' - '+docs[i].subject+'</ul>';

                            $(''+id_folder+"_img").addClassName('mt_fopened');
                            $(''+id_folder+"_img").removeClassName('mt_fclosed');
                            $(''+id_folder+"_img").src = BASE_URL+'static.php?filename=folderopen.gif';
                        };
                        
                    },
                    onFailure: function(){
                        $(''+folders_system_id).innerHTML += '<div class="error">_SERVER_ERROR</div>';
                       }
                });
        }

        }

        function get_folder_docs(folders_system_id)
        {
            if($(''+folders_system_id+'_img').hasClassName('mt_fopened')){
                new Ajax.Request(BASE_URL+'index.php?page=ajax_get_folder&module=folder&display=true',
                {  
                    method:'post',
                    parameters: {folders_system_id: folders_system_id,
                                FOLDER_TREE_RESET: true
                                },
                    onSuccess: function(answer){
                        folders = JSON.parse(answer.responseText);

                        for (var i = 0; i <= folders.length; i++) {
                            level=folders[i].folder_level*10;
                            //console.log(folders[i]);
                            $(''+folders_system_id).innerHTML ='<span onclick="get_folder_docs('+folders[i].folders_system_id+')">'+folders[i].nom_folder+' <b>('+folders[i].nb_subfolder+' sous-dossier(s), '+folders[i].nb_doc+' document(s))</b></span>';
                            $(''+folders_system_id+"_img").addClassName('mt_fclosed');
                            $(''+folders_system_id+"_img").removeClassName('mt_fopened');
                            $(''+folders_system_id+"_img").src = BASE_URL+'static.php?filename=folder.gif';
                        };
                        
                    },
                    onFailure: function(){
                        $(''+folders_system_id).innerHTML += '<div class="error">_SERVER_ERROR</div>';
                       }
                });

            }else{
                new Ajax.Request(BASE_URL+'index.php?page=ajax_get_folder&module=folder&display=true',
                {  
                    method:'post',
                    parameters: {folders_system_id: folders_system_id,
                                FOLDER_TREE_DOCS: true
                                },
                    onSuccess: function(answer){
                        docs = JSON.parse(answer.responseText);

                        for (var i = 0; i <= docs.length; i++) {
                            //console.log(docs);
                            level=docs[i].folder_level*10;
                            //if(i!=0){
                                var style='margin-left:'+level+'px;';
                            //}
                            //alert('ok');
                            $(''+folders_system_id).innerHTML +="<br/>";
                            $(''+folders_system_id).innerHTML +='<ul class="doc" style="margin-top:10px;'+style+'"><li><b>'+docs[i].doctypes_first_level_label+'</b></li><li style="'+style+'"><b>'+docs[i].doctypes_second_level_label+'</b></li><span style="position: relative;top: 4px;'+style+'"" class="doc"><img src=\"'+BASE_URL+'static.php?filename=page.gif\" alt=\"\" id=\"'+docs[i].res_id+'_img_doc\" ></span><a onclick=\'updateContent("index.php?dir=indexing_searching&page=little_details_invoices&display=true&value='+docs[i].res_id+'", "docView");\'>('+docs[i].res_id+') '+docs[i].type_label+' - '+docs[i].subject+'</ul>';

                            $(''+folders_system_id+"_img").addClassName('mt_fopened');
                            $(''+folders_system_id+"_img").removeClassName('mt_fclosed');
                            $(''+folders_system_id+"_img").src = BASE_URL+'static.php?filename=folderopen.gif';
                        };
                        
                    },
                    onFailure: function(){
                        $(''+folders_system_id).innerHTML += '<div class="error">_SERVER_ERROR</div>';
                       }
                });
        }

        }
</script>
<script type="text/javascript" src="<?php
echo $_SESSION['config']['businessappurl'] . 'tools/'
?>MaarchJS/dist/maarch.js"></script>
<script type="text/javascript" src="<?php
echo $_SESSION['config']['businessappurl'] . 'js/'
?>search_customer.js"></script>