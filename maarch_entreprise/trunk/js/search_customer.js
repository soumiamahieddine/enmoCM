$(document).observe("maarch:tree:branchselect", function(evt){
    // First clean the script result div
	var div_to_clean = $('docView');
	div_to_clean.innerHTML = '';

    // get the id of the selected branch
    var id_branch = evt.findElement().id;
    // A branch id is like prefix_[main div]_[tree id]_[id from db]
    // Node id MUST NOT contain _
    // get the id
    var branchId = id_branch.split('_').pop();

    var tmp_branch = $(id_branch);
    var levelBranch = tmp_branch.ancestors().filter(function(el){
                        return el.match("ul");
                    }).size();
    // Display page in docView only if level is the last (res_id)
    if (levelBranch == 5) {
    	var url_script = BASE_URL+'index.php?dir=indexing_searching&page=little_details_invoices&display=true&value='+branchId;
    	updateContent(url_script, 'docView');
    }
});


// Activate ToolTips displays and Ajax loading
Maarch.cssPath = "./css/";
Maarch.require('treeview', function(){
    Maarch.treeview.activateToolTip();

});

/**
 * Init tree data
 *
 * @param treeId string Tree identifier
 * @param force_load bool Force loading tree even if already present in dom (false by default)
 * @param onComplete_callback string Callback to execute after the completion of the ajax request
 */
// Init tree data
function tree_init(treeId, projectStr, more_params)
{
	if($('myTree'))
    {
		$('myTree').childElements().each(function(item){
			item.remove();
		});
    }
    // Get tree parameters from an ajax script (get_tree_info.php)
    new Ajax.Request(BASE_URL+'index.php?dir=indexing_searching&page=get_tree_info&display=true',{
        method: 'post',
        parameters: {
            tree_id: treeId,
            project: projectStr
        },
        onSuccess: function(response){
            eval('params='+response.responseText+';');
            //console.log(params);
            Tree = new Maarch.treeview.Tree(treeId, params);
        },
        onComplete: function(response){
         /*   if(more_params['onComplete_callback'])
            {
                eval(more_params['onComplete_callback']);
            }*/
        }
    });
}