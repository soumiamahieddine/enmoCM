<?php
/*
*    Copyright 2014 Maarch
*
*  This file is part of Maarch Framework.
*
*   Maarch Framework is free software: you can redistribute it and/or modify
*   it under the terms of the GNU General Public License as published by
*   the Free Software Foundation, either version 3 of the License, or
*   (at your option) any later version.
*
*   Maarch Framework is distributed in the hope that it will be useful,
*   but WITHOUT ANY WARRANTY; without even the implied warranty of
*   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*   GNU General Public License for more details.
*
*   You should have received a copy of the GNU General Public License
*    along with Maarch Framework.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
* @brief Show the tree
*
* @file
* @author <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup admin
*/

require_once("core".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_request.php");
require_once("apps".DIRECTORY_SEPARATOR."maarch_entreprise".DIRECTORY_SEPARATOR."class".DIRECTORY_SEPARATOR."class_contacts_v2.php");

$core_tools = new core_tools();
$core_tools->load_lang();
$func = new functions();
$db = new dbquery();
$db->connect();
$db1 = new dbquery();
$db1->connect();
$db2 = new dbquery();
$db2->connect();
$db3 = new dbquery();
$db3->connect();

$core_tools->load_html();
$core_tools->load_header('', true, false);
$f_level = array();

?>
<body>
<?php

?>
<script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>js/prototype.js"></script>
<script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>js/scriptaculous.js"></script>
<script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>Tree.js"></script>
<?php
$search_customer_results = array();
$f_level = array();

$query="select id, label from  ".$_SESSION['tablename']['contact_types']." order by label";

$db1->query($query);
$contactv2 = new contacts_v2();
while($res1 = $db1->fetch_object())
{
    $s_level = array();
    $db2->query("select contact_id, society, society_short, lastname, firstname, is_corporate_person from ".$_SESSION['tablename']['contacts_v2']." where contact_type = ".$res1->id." group by contact_id, society, society_short, lastname, firstname, is_corporate_person ");
    while($res2 = $db2->fetch_object())
    {
        $doctypes = array();
        $db3->query("select id, contact_purpose_id, lastname, firstname, address_num, address_street, address_town, address_postal_code from ".$_SESSION['tablename']['contact_addresses']." where contact_id = ".$res2->contact_id." order by lastname, firstname, address_num");
        while($res3 = $db3->fetch_object())
        {
            $results = array();
            $address = '';
            $address = '('.$contactv2->get_label_contact($res3->contact_purpose_id, $_SESSION['tablename']['contact_purposes']).') ';
            if ($res3->lastname <> '' || $res3->firstname <> ''){
                $address .= strtoupper($func->show_string($res3->lastname, true)) . ' ' . $func->show_string($res3->firstname, true) . ' : ';
            }
            $address .= $func->show_string($res3->address_num, true) . ' ' . $func->show_string($res3->address_street, true) . ' ' . $func->show_string($res3->address_postal_code, true) . ' ' . $func->show_string($res3->address_town, true);
            array_push($doctypes, array('type_id' => $res3->id, 'description' => $address, "results" => $results));
        }
        $contact = '';
        if($res2->is_corporate_person == 'Y'){
            $contact = ucfirst($func->show_string($res2->society, true));
            if ($res2->society_short <> '') {
               $contact .= ' ('.$res2->society_short.')';
            }
        } else {
            $contact = strtoupper($func->show_string($res2->lastname, true)) . ' ' . $func->show_string($res2->firstname, true);
            if ($res2->society <> '') {
               $contact .= ' ('.$res2->society.')';
            }
        }
        array_push($s_level, array('contact_id' => $res2->contact_id, 'contact_label' => $contact, 'doctypes' => $doctypes));
    }
    array_push($f_level, array('contact_type_id' => $res1->id, 'contact_type_label' => ucfirst($func->show_string($res1->label, true)), 'second_level' => $s_level));
}

array_push($search_customer_results, array('contact' => _VIEW_TREE_CONTACTS . ' ' . _TREE_INFO, 'content' => $f_level));

//$core_tools->show_array($search_customer_results);
?>
<script type="text/javascript">

    var BASE_URL = '<?php echo $_SESSION['config']['businessappurl'];?>';
    function funcOpen(branch, response) {
        // Ici tu peux traiter le retour et retourner true si
        // tu veux ins�rer les enfants, false si tu veux pas
        //MyClick(branch);
        return true;
    }

    function myClick(branch) {
        //window.top.frames['view'].location.href='<?php echo $_SESSION['urltomodules']."indexing_searching/view_type_folder.php?id="; ?>'+branch.getId());;
        //window.top.frames['view'].location.href='<?php echo $_SESSION['urltomodules']."indexing_searching/view_type_folder.php?id="; ?>'+branch.getId());
        //window.top.frames['view'].location.href='<?php echo $_SESSION['config']['businessappurl'];?>indexing_searching/little_details_invoices.php?id='+branch.getId();
        //alert(branch.getId());
        //branch.setText('<b>'+branch.getText()+'</b>');
    }

    function MyOpen(branch)
    {
        if(branch.struct.script != '' && branch.struct.script != 'default')
        {
            var parents = [];
            parents = branch.getParents();
            var str = '';
            for(var i=0; i < (parents.length -1) ;i++)
            {
                str = str + '&parent_id[]=' + parents[i].getId();
            }
            var str_children  = '';
            var children = branch.getChildren();
            for(var i=0; i < (children.length -1) ;i++)
            {
                str_children = str_children + '&children_id[]=' + children[i].getId();
            }
        }
        return true;
    }

    function MyClose(branch)
    {
        var parents = branch.getParents();
        var branch_id = branch.getId();
        if(current_branch_id != null)
        {
            var branch2 = tree.getBranchById(current_branch_id);
            if(current_branch_id == branch_id )
            {
                current_branch_id = branch.getNextOpenedBranch;
            }
            else if(branch2 && branch2.isChild(branch_id))
            {
                current_branch_id = branch.getNextOpenedBranch;
            }
        }
        branch.collapse();
        branch.openIt(false);
    }

    function MyBeforeOpen(branch, opened)
    {
        if(opened == true)
        {
            MyClose(branch);
        }
        else
        {
            current_branch_id = branch.getId();
            MyOpen(branch);
            return true;
        }
    }

    function myMouseOver (branch)
    {
        document.body.style.cursor='pointer';
    }

    function myMouseOut (branch)
    {
        document.body.style.cursor='auto';
    }

    var tree = null;
    var current_branch_id = null;

    function TafelTreeInit ()
    {
        var struct = [
        <?php

            for($i=0;$i<count($search_customer_results);$i++)
            {
                    ?>
                    {
                        'id':'<?php  echo addslashes($search_customer_results[$i]['contact']);?>',
                        'txt':'<b><?php  echo addslashes($search_customer_results[$i]['contact']);?></b>',
                        'items':[
                                    <?php
                                    for($j=0;$j<count($search_customer_results[$i]['content']);$j++)
                                    {
                                        ?>
                                        {
                                            'id':'<?php  echo addslashes($search_customer_results[$i]['content'][$j]['contact_type_id']);?>',
                                            'txt':'<a onmouseover="this.style.cursor=\'pointer\';" onclick="window.open(\'<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=contact_types_up&id=<?php echo $search_customer_results[$i]['content'][$j]['contact_type_id'];?>&fromContactTree\');"><?php  echo addslashes($search_customer_results[$i]['content'][$j]['contact_type_label']);?></a>',
                                            'items':[
                                                        <?php
                                                        for($k=0;$k<count($search_customer_results[$i]['content'][$j]['second_level']);$k++)
                                                        {
                                                            ?>
                                                            {
                                                                'id':'<?php  echo addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['contact_id']);?>',
                                                                'txt':'<a onmouseover="this.style.cursor=\'pointer\';" onclick="window.open(\'<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=contacts_v2_up&id=<?php echo $search_customer_results[$i]['content'][$j]['second_level'][$k]['contact_id'];?>&fromContactTree\');"><?php  echo addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['contact_label']);?></a>',
                                                                'items':[
                                                                            <?php
                                                                            for($l=0;$l<count($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes']);$l++)
                                                                            {
                                                                                ?>
                                                                                {
                                                                                    <?php
                                                                                    ?>
                                                                                    'txt':'<span style="font-style:italic;"><small><small><?php  echo addslashes($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes'][$l]['description']);?></small></small></span>',
                                                                                    'img':'empty.gif'
                                                                                }
                                                                                <?php
                                                                                if($l <> count($search_customer_results[$i]['content'][$j]['second_level'][$k]['doctypes']) - 1)
                                                                                echo ',';
                                                                            } ?>
                                                                        ]
                                                            }
                                                            <?php
                                                            if($k <> count($search_customer_results[$i]['content'][$j]['second_level']) - 1)
                                                            echo ',';
                                                        }
                                                        ?>
                                                    ]
                                        }
                                        <?php
                                        if($j <> count($search_customer_results[$i]['content']) - 1)
                                            echo ',';
                                    }
                                    ?>
                                ]
                    }
                    <?php
                    if ($i <> count($search_customer_results) - 1)
                        echo ',';
                }

                        ?>
                    ];
        tree = new TafelTree('trees_div', struct, {
            'generate' : true,
            'imgBase' : '<?php  echo $_SESSION['config']['businessappurl'].'tools/tafelTree/';?>imgs/',
            'defaultImg' : 'folder.gif',
            //'defaultImg' : 'page.gif',
            'defaultImgOpen' : 'folderopen.gif',
            'defaultImgClose' : 'folder.gif',
            'onOpenPopulate' : [funcOpen, 'get_tree_children.php?IdTree=<?php  echo $_SESSION['doctypes_chosen_tree'];?>']
        });

        //open all branches
        tree.expend();
    };
</script>
<div id="trees_div"></div>
<?php

$core_tools->load_js();
?>
</body>
</html>
