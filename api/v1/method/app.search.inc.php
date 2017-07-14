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

    $query = isset($_POST['query']) ? $_POST['query'] : '';
    $itemId = isset($_POST['itemId']) ? $_POST['itemId'] : 0;

    $query = helper::clearText($query);
    $query = helper::escapeText($query);

    $itemId = helper::clearInt($itemId);

    $accountId = helper::clearInt($accountId);

    $result = array("error" => true,
                    "error_code" => ERROR_UNKNOWN);

    $search = new search($dbo);
    $search->setRequestFrom($accountId);

    $result = $search->itemsQuery($query, $itemId);

    echo json_encode($result);
    exit;
}
