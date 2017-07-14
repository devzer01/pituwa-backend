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

    $clientId = isset($_POST['clientId']) ? $_POST['clientId'] : 0;

    $accountId = isset($_POST['accountId']) ? $_POST['accountId'] : 0;
    $accessToken = isset($_POST['accessToken']) ? $_POST['accessToken'] : '';

    $rating = isset($_POST['rating']) ? $_POST['rating'] : 0;
    $category = isset($_POST['category']) ? $_POST['category'] : 0;

    $clientId = helper::clearInt($clientId);
    $accountId = helper::clearInt($accountId);

    $rating = helper::clearInt($rating);
    $category = helper::clearInt($category);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $popular = new popular($dbo);
    $popular->setRequestFrom($accountId);

    $result = $popular->get($rating, $category);

    echo json_encode($result);
    exit;
}
