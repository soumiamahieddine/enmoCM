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
	var tree = $(treeId);
	// If reload the tree, remove existing tree roots before creating new tree
   /* if(tree)
    {
       tree.remove();
    }*/
    div = tree.insert(new Element('div', {'id' : 'rootTree'}));
    // Get tree parameters from an ajax script (get_tree_info.php)
    new Ajax.Request(BASE_URL+'index.php?dir=indexing_searching&page=get_tree_info&display=true',{
        method: 'post',
        parameters: {
            tree_id: treeId,
            project: projectStr
        },
        onSuccess: function(response){
            eval('params='+response.responseText+';');
            console.log(params);
            Tree = new Maarch.treeview.Tree(treeId, params);
        },
        onComplete: function(response){
            if(more_params['onComplete_callback'])
            {
                eval(more_params['onComplete_callback']);
            }
        }
    });
}