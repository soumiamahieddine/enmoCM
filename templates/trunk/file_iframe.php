<?php

$db = new dbquery();
$core = new core_tools();
$core->load_lang();
$_SESSION['template_content'] = '';
if (isset($_REQUEST['template_content']) && !empty($_REQUEST['template_content'])) {
    $_SESSION['template_content'] = stripslashes($_REQUEST['template_content']);
} else {
    if (isset($_REQUEST['model_id']) && !empty($_REQUEST['model_id'])) {
        $model = new dbquery();
        $model->connect();
        $model->query("SELECT content from ".$_SESSION['tablename']['temp_templates']."  WHERE  id = ".$_REQUEST['model_id']." ");
        //$model->show();
        $res_model = $model->fetch_object();
        $_SESSION['template_content'] = stripslashes($res_model->content);
        $_SESSION['upfile']['format'] = 'maarch';
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $_SESSION['config']['lang'] ?>" lang="<?php echo $_SESSION['config']['lang'] ?>">
<head>
    <title><?php echo $_SESSION['config']['applicationname']; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="<?php echo $_SESSION['config']['lang'] ?>" />
    <link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['config']['css']; ?>" media="screen" />
    <!--[if lt IE 7.0]>  <link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['config']['css_IE']; ?>" media="screen" />  <![endif]-->
    <!--[if gte IE 7.0]>  <link rel="stylesheet" type="text/css" href="<?php echo $_SESSION['config']['css_IE7']; ?>" media="screen" />  <![endif]-->
    <script type="text/javascript" src="js/functions.js"></script>
    <?
    $_SESSION['mode_editor'] = false;
    include("modules" . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . "load_editor.php");
    ?>
</head>
<body>
    <form name="frmtemplate" id="frmtemplate" method="post">
        <textarea name="template_content" id="template_content" style="width:98%" rows="40">
            <?php echo $_SESSION['template_content'];?>
        </textarea>
        <p>
            <input type="submit" class="button" name="valid" id="valid" value="<?php echo _VALID_TEXT;?>" />
        </p>
    </form>
</body>
</html>
