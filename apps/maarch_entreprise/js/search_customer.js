$(document).observe("maarch:tree:branchselect", function(evt){
    // First clean the script result div
	var div_to_clean = $('docView');
	div_to_clean.innerHTML = '';

    // get the id of the selected branch
    var id_branch = evt.findElement().id;
    // A branch id is like prefix_[main div]_[tree id]_[id from db]
    // Node id MUST NOT contain _
    // get the id
    var branchId = id_branch.split('::').pop();

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

function addCustomClasses()
{
    var _old_insertBranch = Maarch.treeview.Tree.prototype._insertBranch;
    Maarch.treeview.Tree.addMethods({

        _insertBranch : function(branch, parent_branch_id){

            var new_branch = _old_insertBranch.bind(this)(branch, parent_branch_id);
            new_branch.addClassName(branch.classes.join(" "));
            if(branch.open == true)
            {
                this.expand(new_branch);
                new_branch.removeClassName('mt_leaf');
            }
            return new_branch;
        }
    });

}


// Activate ToolTips displays and Ajax loading
Maarch.cssPath = "./css/";
Maarch.require('treeview', function(){
    addCustomClasses();
    Maarch.treeview.activateToolTip();

});
