<?php

/*!
 * ifsoft.co.uk v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class stream extends db_connect
{
    private $requestFrom = 0;

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM items WHERE removeAt = 0 AND category > 0");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getLikeMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM likes");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM items");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function count($language = 'en')
    {
        $count = 0;

        $stmt = $this->db->prepare("SELECT count(*) FROM items WHERE fromUserId <> (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(':fromUserId', $this->requestFrom, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $count = $stmt->fetchColumn();
        }

        return $count;
    }

    public function getFavoritesCount()
    {
        $count = 0;

        $stmt = $this->db->prepare("SELECT count(*) FROM likes WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(':fromUserId', $this->requestFrom, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $count = $stmt->fetchColumn();
        }

        return $count;
    }

    public function getFavorites($itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getLikeMaxId();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT itemId FROM likes WHERE removeAt = 0 AND id < (:itemId) AND fromUserId = (:fromUserId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':fromUserId', $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $items = new items($this->db);
                    $items->setRequestFrom($this->requestFrom);
                    $itemInfo = $items->info($row['itemId']);
                    unset($items);

                    array_push($result['items'], $itemInfo);

                    $result['itemId'] = $itemInfo['id'];

                    unset($itemInfo);
                }
            }
        }

        return $result;
    }

    public function get($itemId = 0, $language = 'en')
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxId();
            $itemId++;
        }

        $result = array("error" => false,
                         "error_code" => ERROR_SUCCESS,
                         "itemId" => $itemId,
                         "items" => array());

        $stmt = $this->db->prepare("SELECT id FROM items WHERE removeAt = 0 AND category > 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $item = new items($this->db);
                    $item->setRequestFrom($this->requestFrom);
                    $itemInfo = $item->info($row['id']);
                    unset($post);

                    array_push($result['items'], $itemInfo);

                    $result['itemId'] = $itemInfo['id'];

                    unset($itemInfo);
                }
            }
        }

        return $result;
    }

    public function getByCategory($categoryId, $itemId = 0, $language = 'en')
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxId();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "categoryId" => $categoryId,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT id FROM items WHERE removeAt = 0 AND category = (:category) AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $stmt->bindParam(':category', $categoryId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $item = new items($this->db);
                    $item->setRequestFrom($this->requestFrom);
                    $itemInfo = $item->info($row['id']);
                    unset($post);

                    array_push($result['items'], $itemInfo);

                    $result['itemId'] = $itemInfo['id'];

                    unset($itemInfo);
                }
            }
        }

        return $result;
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

