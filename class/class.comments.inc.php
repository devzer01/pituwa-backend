<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class comments extends db_connect
{

	private $requestFrom = 0;
    private $language = 'en';

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function allCommentsCount()
    {
        $stmt = $this->db->prepare("SELECT max(id) FROM comments");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM comments WHERE removeAt = 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function count($itemId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM comments WHERE itemId = (:itemId) AND removeAt = 0");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function create($itemId, $text, $notifyId = 0, $replyToUserId = 0)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        if (strlen($text) == 0) {

            return $result;
        }

        $items = new items($this->db);

        $itemInfo = $items->info($itemId);

        $currentTime = time();
        $ip_addr = helper::ip_addr();
        $u_agent = helper::u_agent();

        $stmt = $this->db->prepare("INSERT INTO comments (fromUserId, replyToUserId, itemId, comment, createAt, notifyId, ip_addr, u_agent) value (:fromUserId, :replyToUserId, :itemId, :comment, :createAt, :notifyId, :ip_addr, :u_agent)");
        $stmt->bindParam(":fromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":replyToUserId", $replyToUserId, PDO::PARAM_INT);
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":comment", $text, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":notifyId", $notifyId, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "commentId" => $this->db->lastInsertId(),
                            "comment" => $this->info($this->db->lastInsertId()));

            $account = new account($this->db, $this->requestFrom);
            $account->setLastActive();
            unset($account);

            if ($replyToUserId != $this->requestFrom && $replyToUserId != 0) {

                $account = new account($this->db, $replyToUserId);

                if ($account->getAllowCommentReplyGCM() == 1) {

                    $gcm = new gcm($this->db, $replyToUserId);
                    $gcm->setData(GCM_NOTIFY_COMMENT_REPLY, "You have a new reply to comment.", $itemId);
                    $gcm->send();
                }

                $notify = new notify($this->db);
                $notifyId = $notify->createNotify($replyToUserId, $this->requestFrom, NOTIFY_TYPE_COMMENT_REPLY, $itemInfo['id']);
                unset($notify);

                unset($account);
            }

            $account = new account($this->db, $this->requestFrom);
            $account->updateCounters();
            unset($account);

            $items->recalculate($itemId);
        }

        unset($items);

        return $result;
    }

    private function setNotifyId($commentId, $notifyId)
    {
        $stmt = $this->db->prepare("UPDATE comments SET notifyId = (:notifyId) WHERE id = (:commentId)");
        $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);
        $stmt->bindParam(":notifyId", $notifyId, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function remove($commentId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $commentInfo = $this->info($commentId);

        if ($commentInfo['error'] === true) {

            return $result;
        }

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE comments SET removeAt = (:removeAt) WHERE id = (:commentId)");
        $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $notify = new notify($this->db);
            $notify->remove($commentInfo['notifyId']);
            unset($notify);

            $item = new items($this->db);
            $item->recalculate($commentInfo['itemId']);
            unset($item);

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function removeAll($itemId) {

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE comments SET removeAt = (:removeAt) WHERE itemId = (:itemId)");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);
    }

    public function info($commentId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM comments WHERE id = (:commentId) LIMIT 1");
        $stmt->bindParam(":commentId", $commentId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $time = new language($this->db, $this->language);

                $profile = new profile($this->db, $row['fromUserId']);
                $fromUserId = $profile->get();
                unset($profile);

                $replyToUserId = $row['replyToUserId'];
                $replyToUserUsername = "";
                $replyToFullname = "";

                if ($replyToUserId != 0) {

                    $profile = new profile($this->db, $row['replyToUserId']);
                    $replyToUser = $profile->get();
                    unset($profile);

                    $replyToUserUsername = $replyToUser['username'];
                    $replyToFullname = $replyToUser['fullname'];
                }

                $lowPhotoUrl = "";

                if (strlen($fromUserId['lowPhotoUrl']) != 0) {

                    $lowPhotoUrl = $fromUserId['lowPhotoUrl'];
                }

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "comment" => htmlspecialchars_decode(stripslashes($row['comment'])),
                                "fromUserId" => $row['fromUserId'],
                                "fromUserState" => $fromUserId['state'],
                                "fromUserUsername" => $fromUserId['username'],
                                "fromUserFullname" => $fromUserId['fullname'],
                                "fromUserPhotoUrl" => $lowPhotoUrl,
                                "replyToUserId" => $replyToUserId,
                                "replyToUserUsername" => $replyToUserUsername,
                                "replyToFullname" => $replyToFullname,
                                "itemId" => $row['itemId'],
                                "createAt" => $row['createAt'],
                                "notifyId" => $row['notifyId'],
                                "timeAgo" => $time->timeAgo($row['createAt']));
            }
        }

        return $result;
    }

    public function get($itemId, $commentId = 0)
    {
        if ($commentId == 0) {

            $commentId = $this->allCommentsCount() + 1;
        }

        $comments = array("error" => false,
                          "error_code" => ERROR_SUCCESS,
                          "commentId" => $commentId,
                          "itemId" => $itemId,
                          "comments" => array());

        $stmt = $this->db->prepare("SELECT id FROM comments WHERE itemId = (:itemId) AND id < (:commentId) AND removeAt = 0 ORDER BY id DESC LIMIT 70");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $stmt->bindParam(':commentId', $commentId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $commentInfo = $this->info($row['id']);

                array_push($comments['comments'], $commentInfo);

                $comments['commentId'] = $commentInfo['id'];

                unset($commentInfo);
            }
        }

        return $comments;
    }

    public function stream($itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->allCommentsCount();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT id FROM comments WHERE removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $info = $this->info($row['id']);

                    array_push($result['items'], $info);

                    $result['itemId'] = $info['id'];

                    unset($info);
                }
            }
        }

        return $result;
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setRequestFrom($requestFrom)
    {
        $this->requestFrom = $requestFrom;
    }

    public function getRequestFrom()
    {
        return $this->requestFrom;
    }
}
