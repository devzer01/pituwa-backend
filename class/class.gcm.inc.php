<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class gcm extends db_connect
{
    private $accountId = 0;
    private $url = "https://android.googleapis.com/gcm/send";
    private $ids = array();
    private $data = array();

    public function __construct($dbo = NULL, $accountId = 0)
    {
        parent::__construct($dbo);

        $this->accountId = $accountId;

        if ($this->accountId != 0) {

            $account = new account($this->db, $this->accountId);

            $deviceId = $account->getGCM_regId();

            if (strlen($deviceId) != 0) {

                $this->addDeviceId($deviceId);
            }

            $ios_deviceId = $account->get_iOS_regId();

            if (strlen($ios_deviceId) != 0) {

                $this->addDeviceId($ios_deviceId);
            }
        }
    }

    public function setIds($ids)
    {
        $this->ids = $ids;
    }

    public function getIds()
    {
        return $this->ids;
    }

    public function clearIds()
    {
        $this->ids = array();
    }

    public function sendToAll()
    {
        $laps = ceil(count($this->ids) / 1000);

        if ($laps == 1) {

            $this->send();
        }

        $mod = count($this->ids) % 1000;

        $marker = 0;

        $delivered = 0;
        $status = 0;

        while ($laps > 0) {

            $fcm_ids = array();

            if ($laps == 1) {

                $n = $marker + $mod;

            } else {

                $n = $marker + 1000;
            }

            for ($i = $marker; $i < $n; $i++) {

                $fcm_ids[] = $this->ids[$i];
            }

            $marker = $marker + 1000;

            // Send

            $delivered = $delivered + $this->send_to($fcm_ids);

            $laps--;
        }

        if ($delivered > 0) {

            $status = 1;
        }

        $this->addToHistory($this->data['msg'], $this->data['type'], $status, $delivered);
    }

    public function send_to($fcm_ids)
    {
        $result = array("error" => true,
            "description" => "regId not found");

        if (empty($fcm_ids)) {

            return $result;
        }

        $post = array(
            'registration_ids'   => $fcm_ids,
            'content_available'  => true,
            'data'               => $this->data,
        );

        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));

        $result = curl_exec($ch);

        if (curl_errno($ch)) {

            $result = array("error" => true,
                "failure" => 1,
                "description" => curl_error($ch));
        }

        curl_close($ch);

        $obj = json_decode($result, true);

        return $obj['success'];
    }

    public function send()
    {
        $result = array("error" => true,
                        "description" => "regId not found");

        if (empty($this->ids)) {

            return $result;
        }

        $post = array(
            'registration_ids'  => $this->ids,
            'data'              => $this->data,
        );

        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        
        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_URL, $this->url);
        curl_setopt( $ch, CURLOPT_POST, true);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($post));

        $result = curl_exec($ch);

        if (curl_errno($ch)) {

            $result = array("error" => true,
                            "failure" => 1,
                            "description" => curl_error($ch));
        }

        curl_close($ch);

        $obj = json_decode($result, true);

        $status = 0;

        if ($obj['success'] != 0) {

            $status = 1;
        }

        $this->addToHistory($this->data['msg'], $this->data['type'], $status, $obj['success']);

        return $result;
    }

    private function addToHistory($msg, $msgType, $status, $success)
    {
        if ($msgType == GCM_NOTIFY_SYSTEM || $msgType == GCM_NOTIFY_CUSTOM || $msgType == GCM_NOTIFY_PERSONAL) {

            $currentTime = time();

            $stmt = $this->db->prepare("INSERT INTO gcm_history (msg, msgType, accountId, status, success, createAt) value (:msg, :msgType, :accountId, :status, :success, :createAt)");
            $stmt->bindParam(":msg", $msg, PDO::PARAM_STR);
            $stmt->bindParam(":msgType", $msgType, PDO::PARAM_INT);
            $stmt->bindParam(":accountId", $this->accountId, PDO::PARAM_INT);
            $stmt->bindParam(":status", $status, PDO::PARAM_INT);
            $stmt->bindParam(":success", $success, PDO::PARAM_INT);
            $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    public function forAll()
    {
        // add android devices

        $stmt = $this->db->prepare("SELECT gcm_regid FROM users WHERE gcm_regid <> ''");
        $stmt->execute();

        while ($row = $stmt->fetch()) {

            $this->addDeviceId($row['gcm_regid']);
        }

        // add iOS devices

        $stmt2 = $this->db->prepare("SELECT ios_fcm_regid FROM users WHERE ios_fcm_regid <> ''");
        $stmt2->execute();

        while ($row = $stmt2->fetch()) {

            $this->addDeviceId($row['ios_fcm_regid']);
        }
    }

    public function addDeviceId($id)
    {
        $this->ids[] = $id;
    }

    public function setData($msgType, $msg, $id = 0)
    {
        $this->data = array("type" => $msgType,
                            "msg" => $msg,
                            "id" => $id,
                            "accountId" => $this->accountId);
    }

    public function getData()
    {
        return $this->data;
    }

    public function clearData()
    {
        $this->data = array();
    }

    public function addGCM_regId($gcm_regid)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $currentTime = time();

        $u_agent = helper::u_agent();
        $ip_addr = helper::ip_addr();

        $stmt = $this->db->prepare("INSERT INTO gcm_reg_ids (gcm_regid, createAt, u_agent, ip_addr) value (:gcm_regid, :createAt, :u_agent, :ip_addr)");
        $stmt->bindParam(":gcm_regid", $gcm_regid, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function searchGCM_regId($gcm_regid)
    {
        $stmt = $this->db->prepare("SELECT id FROM gcm_reg_ids WHERE gcm_regid = (:gcm_regid) LIMIT 1");
        $stmt->bindParam(":gcm_regid", $gcm_regid, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['id'];
        }

        return 0;
    }
}