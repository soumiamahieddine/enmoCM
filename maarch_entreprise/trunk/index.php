<?php
/*
*   Copyright 2008-2015 Maarch
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
* @brief Maarch index page : every php page is loaded with this page
*
* @file
* @author  Claire Figueras  <dev@maarch.org>
* @author  Laurent Giovannoni <dev@maarch.org>
* @author  Loic Vinet  <dev@maarch.org>
* @date $date$
* @version $Revision$
* @ingroup apps
*/
include_once('../../core/class/class_functions.php');
include_once '../../core/class/class_db_pdo.php';
include_once '../../core/init.php';

if ($_SESSION['config']['usePHPIDS'] == 'true') {
    include 'apps/maarch_entreprise/phpids_control.php';
}

include 'apps/maarch_entreprise/tools/maarchIVS/MaarchIVS.php';
$started = MaarchIVS::start(__DIR__ . '/xml/ivs.xml', 'xml');
$valid = MaarchIVS::run('silent');
if (!$valid) {
    echo '<pre>';
    var_dump(MaarchIVS::debug());
    echo '</pre>';
} else {
    //echo "Request is valid";
}


if (isset($_SESSION['config']['corepath'])) {
    require_once 'core/class/class_functions.php';
    require_once 'core/class/class_db.php';
    require_once 'core/class/class_core_tools.php';
    $core = new core_tools();
    if (! isset($_SESSION['custom_override_id'])
        || empty($_SESSION['custom_override_id'])
    ) {
        $_SESSION['custom_override_id'] = $core->get_custom_id();
        if (! empty($_SESSION['custom_override_id'])) {
            $path = $_SESSION['config']['corepath'] . 'custom'
                  . DIRECTORY_SEPARATOR . $_SESSION['custom_override_id']
                  . DIRECTORY_SEPARATOR;
            set_include_path(
                $path . PATH_SEPARATOR . $_SESSION['config']['corepath']
            );
        }
    }
} else {
    require_once '../../core/class/class_functions.php';
    require_once '../../core/class/class_db.php';
    require_once '../../core/class/class_core_tools.php';
    $core = new core_tools();
    $_SESSION['custom_override_id'] = $core->get_custom_id();
    chdir('../..');
    if (! empty($_SESSION['custom_override_id'])) {
        $path = $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
              . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR;
        set_include_path(
            $path . PATH_SEPARATOR . $_SESSION['config']['corepath']
        );
    }
}

if (isset($_SESSION['user']['UserId']) && isset($_GET['page'])
    && ! empty($_SESSION['user']['UserId']) && $_GET['page'] <> 'login'
    && $_GET['page'] <> 'log' && $_GET['page'] <> 'logout'
) {
    $db = new dbquery();
    $db->connect();
    $key = md5(
        time() . '%' . $_SESSION['user']['FirstName'] . '%'
        . $_SESSION['user']['UserId'] . '%' . $_SESSION['user']['UserId']
        . '%' . date('dmYHmi') . '%'
    );

    $db->query(
        'update ' . $_SESSION['tablename']['users'] . " set cookie_key = '"
        . $key . "', cookie_date = ".$db->current_datetime()." where user_id = '"
        . $_SESSION['user']['UserId'] . "' and mail = '"
        . $_SESSION['user']['Mail'] . "'", 1
    );

    /*setcookie(
        $_SESSION['sessionName'], 'UserId=' . $_SESSION['user']['UserId'] . '&key=' . $key,
        time() + ($_SESSION['config']['cookietime'] * 1000),
        0, 0, $_SERVER["HTTPS"], 1
    );*/

}


// CV 31 oct 2014 : clean request
//var_dump($_REQUEST);
foreach ($_REQUEST as $name => $value) {
    //if (is_string($value) && strpos($value, "<") !== false) {
        //$value = preg_replace('/(<\/?script[^>]*>|<\w+[\s\n\r]*on[^>]*>|<\?php|<\?[\s|\n|\r])/i', "", $value);
        // $value = functions::xssafe($value);
        $_REQUEST[$name] = $value;
    //}
}
//var_dump($_REQUEST);
//exit;
if (! isset($_SESSION['user']['UserId']) && $_REQUEST['page'] <> 'login' && $_REQUEST['page'] <> 'log' ) {

    $_SESSION['HTTP_REFERER'] = Url::requestUri();
    if (trim($_SERVER['argv'][0]) <> '') {
        header('location: reopen.php?' . $_SERVER['argv'][0]);
    } else {
        header('location: reopen.php');
    }
    exit();
}

if (isset($_REQUEST['display'])) {
     $core->insert_page();
     exit();
}

//var_dump($_SESSION['info']);exit;

if (isset($_GET['show'])) {
    $show = $_GET['show'];
} else {
    $show = 'true';
}

$core->start_page_stat();
$core->configPosition();
if (isset($_SESSION['HTTP_REFERER'])) {
    $url = $_SESSION['HTTP_REFERER'];
    unset($_SESSION['HTTP_REFERER']);
    header('location: '.$url);
}
$core->load_lang();
$core->load_html();
$core->load_header();
$time = $core->get_session_time_expire();

?>
<body style="background: #f2f2f2;" onload="session_expirate(<?php echo $time;?>, '<?php 
    echo $_SESSION['config']['businessappurl'];
    ?>index.php?display=true&page=logout&logout=true');" id="maarch_body">

<?php
$path = $_SESSION['config']['corepath'] . 'custom' . DIRECTORY_SEPARATOR
      . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . 'maarch_entreprise'. DIRECTORY_SEPARATOR . 'template'. DIRECTORY_SEPARATOR . 'header.html';

if (file_exists($path)) {
    include_once('custom' . DIRECTORY_SEPARATOR
      . $_SESSION['custom_override_id'] . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . 'maarch_entreprise'. DIRECTORY_SEPARATOR . 'template'. DIRECTORY_SEPARATOR . 'header.html');
} else {
    include_once('apps' . DIRECTORY_SEPARATOR . 'maarch_entreprise'. DIRECTORY_SEPARATOR . 'template'. DIRECTORY_SEPARATOR . 'header.html');
}
?>

    <div id="container">
        <div id="content">
            <div class="error" id="main_error" onclick="this.hide();"></div>
            <?php
            if(isset($_SESSION['error'])) {
                ?>
                <div class="error" id="main_error_popup" onclick="this.hide();">
                    <?php
                    echo functions::xssafe($_SESSION['error']);
                    ?>
                </div>
                <?php
            }

            if(isset($_SESSION['info'])) {
                ?>
                <div class="info" id="main_info" onclick="this.hide();">
                    <?php
                    echo functions::xssafe($_SESSION['info']);
                    ?>
                </div>
                <?php
            }
            ?>

            <?php
            if(isset($_SESSION['error']) && $_SESSION['error'] <> '') {
                ?>
                <script>
                    var main_error = $('main_error_popup');
                    if (main_error != null) {
                        main_error.style.display = 'table-cell';
                        Element.hide.delay(10, 'main_error_popup');
                    }
                </script>
                <?php
            }

            if(isset($_SESSION['info']) && $_SESSION['info'] <> '') {
                ?>
                <script>
                    var main_info = $('main_info');
                    if (main_info != null) {
                        main_info.style.display = 'table-cell';
                        Element.hide.delay(10, 'main_info');
                    }
                </script>
                <?php
            }

            if ($core->is_module_loaded('basket')
                && isset($_SESSION['abs_user_status'])
                && $_SESSION['abs_user_status'] == true) {
                include
                    'modules' . DIRECTORY_SEPARATOR . 'basket'
                    . DIRECTORY_SEPARATOR . 'advert_missing.php';
            } else {
              $core->insert_page();
            }
            ?>
        </div>
        <p id="footer">
            <?php
            if (isset($_SESSION['config']['showfooter'])
                && $_SESSION['config']['showfooter'] == 'true'
            ) {
                $core->load_footer();
            }
            ?>
        </p>
        <?php
        $_SESSION['error'] = '';
        $_SESSION['info'] = '';
        $core->view_debug();
        ?>
    </div>

<script type="text/javascript">//HideMenu('menunav');</script>
</body>
</html>
