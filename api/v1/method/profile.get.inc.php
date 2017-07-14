<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

include_once($_SERVER['DOCUMENT_ROOT']."/core/init.inc.php");
include_once($_SERVER['DOCUMENT_ROOT']."/config/api.inc.php");

if (!empty($_POST)) {

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $profileId = isset($_POST['profileId']) ? $_POST['profileId'] : 0;

    $profileId = helper::clearInt($profileId);

    $accountId = helper::clearInt($accountId);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);


    $profile = new profile($dbo, $profileId);
    $profile->setRequestFrom($accountId);

    if ($accountId != 0) {

        $account = new account($dbo, $accountId);
        $account->setLastActive();
    }

    $result = $profile->get();

    echo json_encode($result);
    exit;
}
