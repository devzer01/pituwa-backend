<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class profile extends db_connect
{

    private $id = 0;
    private $requestFrom = 0;

    public function __construct($dbo = NULL, $profileId)
    {

        parent::__construct($dbo);

        $this->setId($profileId);
    }

    public function lastIndex()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM users");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn() + 1;
    }

    public function get()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_ACCOUNT_ID);

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $online = false;

                $current_time = time();

                if ($row['last_authorize'] != 0 && $row['last_authorize'] > ($current_time - 15 * 60)) {

                    $online = true;
                }

                $time = new language($this->db);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "ios_fcm_regid" => $row['ios_fcm_regid'],
                                "state" => $row['state'],
                                "sex" => $row['sex'],
                                "year" => $row['bYear'],
                                "month" => $row['bMonth'],
                                "day" => $row['bDay'],
                                "username" => $row['login'],
                                "fullname" => htmlspecialchars_decode(stripslashes($row['fullname'])),
                                "location" => stripcslashes($row['country']),
                                "status" => stripcslashes($row['status']),
                                "fb_page" => stripcslashes($row['fb_page']),
                                "instagram_page" => stripcslashes($row['my_page']),
                                "my_page" => stripcslashes($row['my_page']),
                                "verify" => $row['verify'],
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "lowPhotoUrl" => $row['lowPhotoUrl'],
                                "bigPhotoUrl" => $row['bigPhotoUrl'],
                                "normalPhotoUrl" => $row['normalPhotoUrl'],
                                "normalCoverUrl" => $row['normalCoverUrl'],
                                "originCoverUrl" => $row['originCoverUrl'],
                                "coverPosition" => $row['coverPosition'],
                                "itemsCount" => $row['items_count'],
                                "likesCount" => $row['likes_count'],
                                "commentsCount" => $row['comments_count'],
                                "lastAuthorize" => $row['last_authorize'],
                                "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                                "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                                "online" => $online);
            }
        }

        return $result;
    }

    public function reportAbuse($abuseId)
    {
        $result = array("error" => true);

        $create_at = time();
        $ip_addr = helper::ip_addr();

        $stmt = $this->db->prepare("INSERT INTO profile_abuse_reports (abuseFromUserId, abuseToUserId, abuseId, createAt, ip_addr) value (:abuseFromUserId, :abuseToUserId, :abuseId, :createAt, :ip_addr)");
        $stmt->bindParam(":abuseFromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":abuseToUserId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":abuseId", $abuseId, PDO::PARAM_INT);
        $stmt->bindParam(":createAt", $create_at, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false);
        };

        return $result;
    }

    public function getItemsCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM items WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function getLikesCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM likes WHERE toUserId = (:toUserId) AND removeAt = 0");
        $stmt->bindParam(":toUserId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function setLastNotifyView()
    {
        $time = time();

        $stmt = $this->db->prepare("UPDATE users SET last_notify_view = (:last_notify_view) WHERE id = (:id)");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":last_notify_view", $time, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getLastNotifyView()
    {
        $stmt = $this->db->prepare("SELECT last_notify_view FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                return $row['last_notify_view'];
            }
        }

        $time = time();

        return $time;
    }

    public function setId($profileId)
    {
        $this->id = $profileId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setRequestFrom($requestFrom)
    {
        $this->requestFrom = $requestFrom;
    }

    public function getRequestFrom()
    {
        return $this->requestFrom;
    }

    public function getState()
    {
        $stmt = $this->db->prepare("SELECT state FROM users WHERE id = (:profileId) LIMIT 1");
        $stmt->bindParam(":profileId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row['state'];
    }

    public function getFullname()
    {
        $stmt = $this->db->prepare("SELECT login, fullname FROM users WHERE id = (:profileId) LIMIT 1");
        $stmt->bindParam(":profileId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        $fullname = stripslashes($row['fullname']);

        if (strlen($fullname) < 1) {

            $fullname = $row['login'];
        }

        return $fullname;
    }

    public function getUsername()
    {
        $stmt = $this->db->prepare("SELECT login FROM users WHERE id = (:profileId) LIMIT 1");
        $stmt->bindParam(":profileId", $this->id , PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();

        return $row['login'];
    }
}

