<?php
/**
* File : add_users_entities.php
*
* Form to add an entity to a user, pop up page
*
* @package  Maarch Framework 3.0
* @version 1
* @since 03/2009
* @license GPL
* @author  Cédric Ndoumba  <dev@maarch.org>
*/
try{
    require_once("modules/entities/class/EntityControler.php");
} catch (Exception $e){
    echo $e->getMessage();
}
core_tools::load_lang();
//core_tools::test_admin('manage_entities', 'entities');

require_once('modules'.DIRECTORY_SEPARATOR.'entities'.DIRECTORY_SEPARATOR.'class'.DIRECTORY_SEPARATOR.'class_manage_entities.php');
$ent = new entity();
$entity_ctrl = new EntityControler();
//$except = array();
/* If you want that a user can not belong to an entity and one of this entity subentity, decomment these lines
for($i = 0; $i < count($_SESSION['m_admin']['entity']['entities']); $i++)
{
    $except[] = $_SESSION['m_admin']['entity']['entities'][$i]['ENTITY_ID'];
}
*/


$entities = array();
//$entities = $entity_ctrl->getAllEntities(); // To do : recup l'arborescence des entités

if($_SESSION['user']['UserId'] == 'superadmin')
{

    $entities = $entity_ctrl->getAllEntities();
}
else
{
    $entities = $entity_ctrl->getEntitiesUser($_SESSION['user']['primaryentity']['id']);
	if(empty($entities)){
		echo "Vous ne pouvez plus associer d'entit&eacute;s &agrave; cet utilisateur!";	

	}else{
    		$entities = $entity_ctrl->sortentities($entities);
	}

}
/*
if($_SESSION['user']['UserId'] == 'superadmin')
{

    $entities = $ent->getShortEntityTree($entities,'all', '', $except);
}
else
{
    $entities = $ent->getShortEntityTree($entities,$_SESSION['user']['entities'],  '' , $except);
}*/
function in_session_array($entity_id)
{
    for($i=0; $i<count($_SESSION['m_admin']['entity']['entities']);$i++)
    {
        if(trim($entity_id) == trim($_SESSION['m_admin']['entity']['entities'][$i]['ENTITY_ID']))
            return true;
    }
    return false;
}
{

$tab2 = array();
if ( count($_SESSION['m_admin']['entity']['entities']) > 0 )
{
    for($i=0; $i < count($_SESSION['m_admin']['entity']['entities']); $i++)
    {
        array_push($tab2, array('ID'=> $_SESSION['m_admin']['entity']['entities'][$i]['ENTITY_ID'], 'LABEL' => $_SESSION['m_admin']['entity']['entities'][$i]['LABEL']));
    }
}

$res = $entities;
for($j=0; $j < count($entities); $j++)
{
    for($k=0; $k < count($tab2); $k++)
    {
        if($entities[$j]->__get('entity_id') ==  $tab2[$k]['ID'])
        {
            unset($res[$j]);
            break;
        }
    }
}
$res = array_values($res);
unset($tab2);
unset($entities);

}
?>
<div class="popup_content">
<h2 class="tit"><?php  echo USER_ADD_ENTITY;?></h2>
<form name="chooseEntity" id="chooseEntity" method="get" action="#" class="forms">
<p>
    <label for="entity_id"> <?php  echo _CHOOSE_ENTITY;?> : </label>
    <select name="entity_id" id="entity_id" size="10">
    <?php
   
        /*for($i=0; $i<count($entities);$i++)
        {
            $short_label = $entities[$i]->__get('short_label');
            $entity_id = $entities[$i]->__get('entity_id');

                //if(in_session_array($entity_id))
                //{
                //$i++;
                //    $short_label = $entities[$i]->__get('short_label');
                //    $entity_id = $entities[$i]->__get('entity_id');
                //}
            ?>
                <option value="<?php  echo $entity_id;?>" ><?php  if(isset($short_label) && !empty($short_label)){ echo $short_label;}else{echo $entities[$i]->__get('entity_label');}?></option><?php

        }*/
		
		for($j=0; $j<count($res); $j++)
		{
			$desc = $res[$j]->__get('short_label');
			if(isset($res[$j]) && !empty($desc))
			{
		?>
			<option value="<?php  echo $res[$j]->__get('entity_id') ?>"><?php   echo $res[$j]->__get('short_label'); ?></option>
		<?php
			}
		}
		
    ?>
    </select>
</p>
<br/>
<p>
    <label for="role"><?php  echo _ROLE;?> : </label>
    <input type="text"  name="role" id="role" />
</p>
<br/>
<p class="buttons">
    <input type="button" name="Submit" value="<?php  echo _VALIDATE;?>" class="button" onclick="checkUserEntity('chooseEntity', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=check_user_entities';?>', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=manage_user_entities';?>', '<?php echo $_SESSION['config']['businessappurl'].'index.php?display=true&module=entities&page=users_entities_form';?>');"  />
    <input type="button" name="cancel" value="<?php  echo _CANCEL;?>" class="button"  onclick="destroyModal('add_user_entities');"/>
</p>

</form>
</div>
