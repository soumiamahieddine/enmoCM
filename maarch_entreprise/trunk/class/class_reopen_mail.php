<?php
/**
* Reopen Mail Class
*
* Contains all the specific functions to reopen mail
*
* @package  Maarch LetterBox 2.0
* @version 2.0
* @since 06/2007
* @license GPL
* @author  Claire Figueras  <dev@maarch.org>
*
*/

/**
* Class ReopenMail : Contains all the specific functions to reopen a mail
*
* @author  Claire Figueras  <dev@maarch.org>
* @license GPL
* @package Maarch LetterBox 2.0
* @version 2.0
*/

class ReopenMail extends dbquery
{

    /**
    * Redefinition of the LetterBox object constructor
    */
    function __construct()
    {
        parent::__construct();
    }

    /**
    * Checks the res_id
    *
    * @param string $mode add or up
    */
    public function reopen_mail_check()
    {
        if (!empty($_REQUEST['id']) && !empty($_REQUEST['ref_id'])) {
            $_SESSION['error'] = _ENTER_REF_ID_OR_GED_ID;
            $_SESSION['m_admin']['reopen_mail']['REF_ID'] = '';
            $_SESSION['m_admin']['reopen_mail']['ID'] = '';
            return false;
        }
        if (empty($_REQUEST['id']) && empty($_REQUEST['ref_id'])) {
            $_SESSION['error'] = _REF_ID . ', ' . _GED_ID . ' ' . _IS_EMPTY;
        } else {
            if (!empty($_REQUEST['ref_id'])) {
                $_SESSION['m_admin']['reopen_mail']['REF_ID'] = $_REQUEST['ref_id'];
            } elseif (!empty($_REQUEST['id'])) {
                $_SESSION['m_admin']['reopen_mail']['ID'] = $this->wash(
                    $_REQUEST['id'], 'num',  _GED_ID . ' ');
            }
        }
    }

    /**
    * Update databse
    *
    */
    public function update_db()
    {
        // add ou modify users in the database
        $this->reopen_mail_check();
        if (! empty($_SESSION['error'])) {
            header(
                'location: ' . $_SESSION['config']['businessappurl']
                . 'index.php?page=reopen_mail&id='
                . $_SESSION['m_admin']['reopen_mail']['ID']
                . '&ref_id=' . $_SESSION['m_admin']['reopen_mail']['REF_ID']
                . '&admin=reopen_mail'
            );
            exit();
        } else {
            require_once 'core/class/class_security.php';
            $sec = new security();
            $ind_coll = $sec->get_ind_collection('letterbox_coll');
            $table = $_SESSION['collections'][$ind_coll]['table'];
            $this->connect();
            if (!empty($_SESSION['m_admin']['reopen_mail']['REF_ID'])) {
                $this->query(
                    "select res_id, alt_identifier, status from res_view_letterbox where alt_identifier = '" 
                        . $_SESSION['m_admin']['reopen_mail']['REF_ID'] . "'"
                );
                $errorMsg = _REF_ID . ' ' . _UNKNOWN;
            } elseif (!empty($_SESSION['m_admin']['reopen_mail']['ID'])) {
                $this->query(
                    'select res_id, alt_identifier, status from res_view_letterbox where res_id = ' 
                        . $_SESSION['m_admin']['reopen_mail']['ID'] 
                );
                $errorMsg = _GED_ID . ' ' . _UNKNOWN;
            }
            
            if ($this->nb_result() == 0) {
                $_SESSION['error'] = $errorMsg;
                header(
                    'location: ' . $_SESSION['config']['businessappurl']
                    . 'index.php?page=reopen_mail&id='
                    . $_SESSION['m_admin']['reopen_mail']['ID']
                    . '&admin=reopen_mail'
                );
                exit();
            } else {
                $resultRes = $this->fetch_object();

                if ($resultRes->status <> "END" && $resultRes->status <> "CLO" && $resultRes->status <> "CLOS") {
                    $_SESSION['error'] = _DOC_NOT_CLOSED;
                    header(
                        'location: ' . $_SESSION['config']['businessappurl']
                        . 'index.php?page=reopen_mail&id='
                        . $_SESSION['m_admin']['reopen_mail']['ID']
                        . '&admin=reopen_mail'
                    );
                    exit();
                }
            }
            
            $this->query(
                'update ' . $table . " set status = 'COU' where res_id = "
                . $resultRes->res_id
            );
            
            $historyMsg = _REOPEN_THIS_MAIL . ' : ';
            if ($resultRes->alt_identifier <> '') {
                $historyMsg .= $resultRes->alt_identifier . ' (' . $resultRes->res_id . ')';
            } else {
                $historyMsg .= $resultRes->res_id;
            }
            
            if ($_SESSION['history']['resup'] == true) {
                require_once 'core/class/class_history.php';
                $hist = new history();
                $hist->add(
                    $table, $_SESSION['m_admin']['reopen_mail']['ID'], 'UP','resup',
                    $historyMsg,
                    $_SESSION['config']['databasetype'], 'apps'
                );
            }

            $_SESSION['error'] = $historyMsg;
            
            unset($_SESSION['m_admin']);
            header(
                'location: ' . $_SESSION['config']['businessappurl']
                . 'index.php?page=admin'
            );
            exit();
        }
    }

    /**
    * Form to reopen a mail
    *
    */
    public function formreopenmail()
    {
        ?>
        <h1><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=default_status_big.gif" alt="" border="0" /> <?php echo _REOPEN_MAIL;?></h1>

        <div id="inner_content" class="clearfix" align="center">
        <br /><br />
        <p ><?php echo _MAIL_SENTENCE2 . '<br />' . _MAIL_SENTENCE3 . '<br />' . _MAIL_SENTENCE4 . ' ' . _BASKETS . ' ' . _MAIL_SENTENCE5;?> </p>
          <br/>
          <p ><img src="<?php echo $_SESSION['config']['businessappurl'];?>static.php?filename=separateur_1.jpg" width="90%" height="1" alt="" /></p>
          <form name="form1" method="post" action="<?php echo $_SESSION['config']['businessappurl']."index.php?display=true&admin=reopen_mail&page=reopen_mail_db";?>" >
          <p>
            <?php echo _ENTER_REF_ID;?> : 
                <input type="text" name="ref_id" id="ref_id" value="<?php if(isset($_SESSION['m_admin']['reopen_mail']['REF_ID'])){ echo $_SESSION['m_admin']['reopen_mail']['REF_ID'];}?>" />
            <?php echo _ENTER_DOC_ID;?> :  
                <input type="text" name="id" id="id" value="<?php if(isset($_SESSION['m_admin']['reopen_mail']['ID'])){ echo $_SESSION['m_admin']['reopen_mail']['ID'];}?>" />
          </p>
             <br/>

           <p >(<?php echo _TO_KNOW_ID;?>) </p>

            <br/>
            <p class="buttons">
                    <input type="submit" name="Submit" value="<?php echo _VALIDATE;?>" class="button"/>
                    <input type="button" name="close" value="<?php echo _CANCEL;?>" onclick="javascript:window.location.href='<?php echo $_SESSION['config']['businessappurl'];?>index.php?page=admin';" class="button"/>
                </p>

          </form>
        </div>
    <?php
    }
}