<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief MaarchParapheur Controller
 * @author dev@maarch.org
 */


class MaarchParapheurController
{
    public static function getModal($config)
    {
        $initializeDatas = MaarchParapheurController::getInitializeDatas($config);
        if (!empty($initializeDatas['error'])) {
            return ['error' => $initializeDatas['error']];
        }
        $html .= '<label for="processingUser">' . _USER_MAARCH_PARAPHEUR . '</label><select name="processingUser" id="processingUser">';
        if (!empty($initializeDatas['users'])) {
            foreach ($initializeDatas['users'] as $value) {
                $html .= '<option value="';
                $html .= $value['id'];
                $html .= '">';
                $html .= $value['firstname'] . ' ' . $value['lastname'];
                $html .= '</option>';
            }
        }
        $html .= '</select><br /><br /><br /><br />';
        $html .= '<input type="radio" name="objectSent" id="objectSentNote" value="mail" checked="checked" /><label for="objectSentNote" style="float: none;display: unset;">' . _MAIL_NOTE . '</label><br/>';
        $html .= '<input type="radio" name="objectSent" id="objectSentSign" value="attachment" /><label for="objectSentSign" style="float: none;display: unset;">' . _ATTACHMENT_SIGNATURE .'</label><br /><br />';

        return $html;
    }

    public static function getInitializeDatas($config)
    {
        $rawResponse['users'] = \ExternalSignatoryBook\controllers\MaarchParapheurController::getUsers(['config' => $config]);
        if (!empty($rawResponse['users']['error'])) {
            return ['error' => $rawResponse['users']['error']];
        }
        return $rawResponse;
    }
}
