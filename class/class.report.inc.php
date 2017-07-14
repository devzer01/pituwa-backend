<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class report extends db_connect
{

	private $requestFrom = 0;

	public function __construct($dbo = NULL)
    {
		parent::__construct($dbo);
	}

    private function getMaxItemReportId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM items_abuse_reports");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    private function getMaxProfilesReportId()
    {
        $stmt = $this->db->prepare("SELECT MAX(id) FROM profile_abuse_reports");
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function removeItemReports($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("DELETE FROM items_abuse_reports WHERE abuseToItemId = (:itemId)");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function removeAllItemsReports()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("DELETE FROM items_abuse_reports");
        $stmt->bindParam(":postId", $postId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function item($itemId, $abuseId)
    {
        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS);

        $create_at = time();
        $ip_addr = helper::ip_addr();

        $stmt = $this->db->prepare("INSERT INTO items_abuse_reports (abuseFromUserId, abuseToItemId, abuseId, createAt, ip_addr) value (:abuseFromUserId, :abuseToItemId, :abuseId, :createAt, :ip_addr)");
        $stmt->bindParam(":abuseFromUserId", $this->requestFrom, PDO::PARAM_INT);
        $stmt->bindParam(":abuseToItemId", $itemId, PDO::PARAM_INT);
        $stmt->bindParam(":abuseId", $abuseId, PDO::PARAM_INT);
        $stmt->bindParam(":createAt", $create_at, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        $stmt->execute();

        return $result;
    }

    public function getProfilesReports($limit = 40)
    {
        $itemId = $this->getMaxProfilesReportId();
        $itemId++;

        $result = array("error" => false,
                        "error_code" => ERROR_SUCCESS,
                        "itemId" => $itemId,
                        "items" => array());

        $stmt = $this->db->prepare("SELECT id FROM profile_abuse_reports WHERE id < (:itemId) AND removeAt = 0 ORDER BY id DESC LIMIT :limit");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);

        if ($stmt->execute()) {

            while ($row = $stmt->fetch()) {

                $info = $this->profileReportInfo($row['id']);

                array_push($result['items'], $info);

                $result['itemId'] = $info['id'];

                unset($reportInfo);
            }
        }

        return $result;
    }

    public function profileReportInfo($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM profile_abuse_reports WHERE id = (:itemId) LIMIT 1");
        $stmt->bindParam(":itemId", $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "abuseFromUserId" => $row['abuseFromUserId'],
                                "abuseToUserId" => $row['abuseToUserId'],
                                "abuseId" => $row['abuseId'],
                                "date" => date("Y-m-d H:i:s", $row['createAt']));
            }
        }

        return $result;
    }

    public function removeProfileReports($itemId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("DELETE FROM profile_abuse_reports WHERE abuseToUserId = (:itemId)");
        $stmt->bindParam(':itemId', $itemId, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function removeAllProfilesReports()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("DELETE FROM profile_abuse_reports");

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
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

