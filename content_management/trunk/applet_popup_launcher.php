<?php
require_once 'core/class/class_core_tools.php';
$core_tools = new core_tools();
$core_tools->load_html();
$core_tools->load_header();
$core_tools->load_js();

if (
    file_exists(
        $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
        . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'modules'
        . DIRECTORY_SEPARATOR . 'content_management' . DIRECTORY_SEPARATOR . 'applet_launcher.php'
    )
) {
    $path = 'custom/'. $_SESSION['custom_override_id'] .'/modules/content_management/applet_launcher.php';
} else {
    $path = 'modules/content_management/applet_launcher.php';
}

?>
<style type="text/css">html{overflow:hidden}</style>
<body style="height:179px;width:231px;">
    <div id="container">
        <div id="content">
            <div class="error" id="divError" name="divError"></div>
            <script language="javascript">
                loadApplet('<?php 
                    echo $_SESSION['config']['coreurl'] .''.$path;
                    ?>?objectType=attachment&objectId=<?php 
                    echo $_REQUEST['objectId'];
                    ?>&objectType=<?php
                    echo $_REQUEST['objectType'];
                    ?>&objectTable=<?php
                    echo $_REQUEST['objectTable'];
                    ?>&resMaster=<?php
                    echo $_REQUEST['resMaster'];
                    ?>&custom_override_id=<?php 
                    echo $_SESSION['custom_override_id'];
                    ?>');
            </script>
        </div>
    </div>
</body>
