<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

	file_put_contents("/var/tmp/signup-post.txt", print_r($_POST, true));

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $gcm_regId = isset($_POST['gcm_regId']) ? $_POST['gcm_regId'] : '';
    $ios_fcm_regId = isset($_POST['ios_fcm_regId']) ? $_POST['ios_fcm_regId'] : '';

    $facebookId = isset($_POST['facebookId']) ? $_POST['facebookId'] : '';

    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $language = isset($_POST['language']) ? $_POST['language'] : '';
    $profilePicture = isset($_POST['profilePicture']) ? $_POST['profilePicture'] : '';
    $coverPicture = isset($_POST['coverPicture']) ? $_POST['coverPicture'] : '';

    $clientId = helper::clearInt($clientId);

    $facebookId = helper::clearText($facebookId);

    $gcm_regId = helper::clearText($gcm_regId);
    $username = helper::clearText($username);
    $fullname = helper::clearText($fullname);
    $password = helper::clearText($password);
    $email = helper::clearText($email);
    $language = helper::clearText($language);

    $facebookId = helper::escapeText($facebookId);
    $gcm_regId = helper::escapeText($gcm_regId);
    $username = helper::escapeText($username);
    $fullname = helper::escapeText($fullname);
    $password = helper::escapeText($password);
    $email = helper::escapeText($email);
    $language = helper::escapeText($language);

    $ios_fcm_regId = helper::clearText($ios_fcm_regId);
    $ios_fcm_regId = helper::escapeText($ios_fcm_regId);

    if ($clientId != CLIENT_ID) {

        api::printError(ERROR_UNKNOWN, "Error client Id.");
    }

    $result = array("error" => true);

    $account = new account($dbo);
    $result = $account->signup($username, $fullname, $password, $email);
        if ($profilePicture != "") {
            $params = [
                    'originPhotoUrl' => $profilePicture, 'normalPhotoUrl' => $profilePicture,
                   'bigPhotoUrl' => $profilePicture, 'lowPhotoUrl' => $profilePicture];
            $account->setPhoto($params);
        }
        if ($coverPicture != "") {
            $account->setCover(['originCoverUrl' => $coverPicture, 'normalCoverUrl' => $coverPicture]);
        }
    unset($account);

    if ($result['error'] === false) {

        $account = new account($dbo);
        $account->setState(ACCOUNT_STATE_ENABLED);
        $account->setLastActive();
        $result = $account->signin($username, $password);
        unset($account);

        if ($result['error'] === false) {

            $auth = new auth($dbo);
            $result = $auth->create($result['accountId'], $clientId);

            if ($result['error'] === false) {

                $account = new account($dbo, $result['accountId']);

                if (strlen($facebookId) != 0) {

                    $helper = new helper($dbo);

                    if ($helper->getUserIdByFacebook($facebookId) == 0) {

                        $account->setFacebookId($facebookId);
                    }

                } else {

                    $account->setFacebookId("");
                }

                if (strlen($gcm_regId) != 0) {

                    $account->setGCM_regId($gcm_regId);
                }

                if (strlen($ios_fcm_regId) != 0) {

                    $account->set_iOS_regId($ios_fcm_regId);
                }

                $result['account'] = array();

                array_push($result['account'], $account->get());
            }
        }
    }

	file_put_contents("/var/tmp/signup-json.txt", json_encode($result));
    echo json_encode($result);
    exit;
}
