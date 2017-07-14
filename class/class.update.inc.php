<?php

/*!
 * ifsoft.co.uk engine v1.1
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class update extends db_connect
{
    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);

    }

    function setCommentEmojiSupport()
    {
        $stmt = $this->db->prepare("ALTER TABLE comments charset = utf8mb4, MODIFY COLUMN comment VARCHAR(800) CHARACTER SET utf8mb4");
        $stmt->execute();
    }

    // For version 1.5

    function addColumnToUsersTable1()
    {
        $stmt = $this->db->prepare("ALTER TABLE users ADD ios_fcm_regid TEXT after gcm_regid");
        $stmt->execute();
    }
}
