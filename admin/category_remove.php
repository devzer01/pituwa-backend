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

    if (!admin::isSession()) {

        header("Location: /admin/login.php");
    }

    $admin = new admin($dbo);


    if (isset($_GET['id'])) {

        $itemId = isset($_GET['id']) ? $_GET['id'] : 0;
        $accessToken = isset($_GET['access_token']) ? $_GET['access_token'] : '';

        $itemId = helper::clearInt($itemId);

        if ($accessToken === admin::getAccessToken() && !APP_DEMO) {

            $category = new categories($dbo);
            $category->remove($itemId);
        }

        header("Location: /admin/categories.php");
        exit;

    } else {

        header("Location: /admin/main.php");
        exit;
    }
