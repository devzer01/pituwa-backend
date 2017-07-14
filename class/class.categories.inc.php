<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class categories extends db_connect
{
	private $requestFrom = 0;
    private $language = 'en';
    private $profileId = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    public function getAllCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM categories");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxIdItems()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM categories");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function add($title, $description, $imgUrl)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        if (strlen($title) == 0) {

            return $result;
        }

        if (strlen($description) == 0) {

            return $result;
        }

        if (strlen($imgUrl) == 0) {

            return $result;
        }

        $currentTime = time();
        $ip_addr = helper::ip_addr();
        $u_agent = helper::u_agent();

        $stmt = $this->db->prepare("INSERT INTO categories (title, description, imgUrl, createAt, ip_addr, u_agent) value (:title, :description, :imgUrl, :createAt, :ip_addr, :u_agent)");
        $stmt->bindParam(":title", $title, PDO::PARAM_STR);
        $stmt->bindParam(":description", $description, PDO::PARAM_STR);
        $stmt->bindParam(":imgUrl", $imgUrl, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS,
                            "itemId" => $this->db->lastInsertId(),
                            "item" => $this->info($this->db->lastInsertId()));
        }

        return $result;
    }

    public function remove($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $itemInfo = $this->info($itemId);

        if ($itemInfo['error'] === true) {

            return $result;
        }

        $currentTime = time();

        $stmt = $this->db->prepare("UPDATE categories SET removeAt = (:removeAt) WHERE id = (:itemId)");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $currentTime, PDO::PARAM_INT);

        if ($stmt->execute()) {

            //remove all comments to item

            $stmt3 = $this->db->prepare("SELECT id FROM items WHERE category = (:category)");
            $stmt3->bindParam(":category", $itemId, PDO::PARAM_INT);

            if ($stmt3->execute()) {

                while ($row = $stmt->fetch()) {

                    $item = new items($this->db);

                    $item->remove($row['id']);

                    unset($item);
                }
            }

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);

            $this->recalculate($itemId);
        }

        return $result;
    }

    public function edit($itemId, $title, $description, $imgUrl)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        if (strlen($title) == 0) {

            return $result;
        }

        if (strlen($description) == 0) {

            return $result;
        }

        if (strlen($imgUrl) == 0) {

            return $result;
        }


        $stmt = $this->db->prepare("UPDATE categories SET title = (:title), description = (:description), imgUrl = (:imgUrl) WHERE id = (:itemId)");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":title", $title, PDO::PARAM_STR);
        $stmt->bindParam(":description", $description, PDO::PARAM_STR);
        $stmt->bindParam(":imgUrl", $imgUrl, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);

        }

        return $result;
    }

    public function recalculate($categoryId) {

        $items_count = 0;

        $items_count = $this->getItemsCount($categoryId);

        $stmt = $this->db->prepare("UPDATE categories SET itemsCount = (:itemsCount) WHERE id = (:categoryId)");
        $stmt->bindParam(":itemsCount", $items_count, PDO::PARAM_INT);
        $stmt->bindParam(":categoryId", $categoryId, PDO::PARAM_INT);
        $stmt->execute();
    }

    private function getItemsCount($categoryId)
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM items WHERE category = (:category) AND removeAt = 0");
        $stmt->bindParam(":category", $categoryId, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function info($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = (:itemId) LIMIT 1");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "itemsCount" => $row['itemsCount'],
                                "title" => htmlspecialchars_decode(stripslashes($row['title'])),
                                "description" => htmlspecialchars_decode(stripslashes($row['description'])),
                                "imgUrl" => $row['imgUrl'],
                                "createAt" => $row['createAt'],
                                "date" => date("Y-m-d H:i:s", $row['createAt']),
                                "removeAt" => $row['removeAt']);
            }
        }

        return $result;
    }

    public function get($itemId = 0)
    {
        if ($itemId == 0) {

            $itemId = $this->getMaxIdItems();
            $itemId++;
        }

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT id FROM categories WHERE removeAt = 0 AND id < (:itemId) ORDER BY id DESC LIMIT 20");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $itemInfo = $this->info($row['id']);

                array_push($result['items'], $itemInfo);

                $result['itemId'] = $itemInfo['id'];

                unset($itemInfo);
            }
        }

        return $result;
    }

    public function getList()
    {
        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT id FROM categories WHERE removeAt = 0 ORDER BY id");

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $itemInfo = $this->info($row['id']);

                array_push($result['items'], $itemInfo);

                unset($itemInfo);
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

    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    public function getProfileId()
    {
        return $this->profileId;
    }
}
