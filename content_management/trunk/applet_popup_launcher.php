<?php
require_once 'core/class/class_core_tools.php';
$core_tools = new core_tools();
$core_tools->load_html();
$core_tools->load_header();
$core_tools->load_js();

?>

<body>
    <div id="container">
        <div id="content">
            <div class="error" id="divError" name="divError"></div>
            <script language="javascript">
                loadApplet('<?php 
                    echo $_SESSION['config']['coreurl'];
                    ?>modules/content_management/applet_launcher.php?objectType=attachment&objectId=<?php 
                    echo $_REQUEST['objectId'];
                    ?>&objectType=<?php
                    echo $_REQUEST['objectType'];
                    ?>&objectTable=<?php
                    echo $_REQUEST['objectTable'];
                    ?>&resMaster=<?php
                    echo $_REQUEST['resMaster'];
                    ?>');
            </script>
        </div>
    </div>
</body>
