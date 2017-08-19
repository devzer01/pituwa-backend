<?php

/*!
 * ifsoft.co.uk engine v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2017 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class account extends db_connect
{

    private $id = 0;

    public function __construct($dbo = NULL, $accountId = 0)
    {

        parent::__construct($dbo);

        $this->setId($accountId);
    }

    public function signup($username, $fullname, $password, $email, $language = '')
    {

        $result = array("error" => true);

        $helper = new helper($this->db);

        if (!helper::isCorrectLogin($username)) {

            $result = array("error" => true,
                            "error_code" => ERROR_UNKNOWN,
                            "error_type" => 0,
                            "error_description" => "Incorrect login");

            return $result;
        }

        if ($helper->isLoginExists($username)) {

            $result = array("error" => true,
                            "error_code" => ERROR_LOGIN_TAKEN,
                            "error_type" => 0,
                            "error_description" => "Login already taken");

            return $result;
        }

        if (empty($fullname)) {

            $result = array("error" => true,
                            "error_code" => ERROR_UNKNOWN,
                            "error_type" => 3,
                            "error_description" => "Empty user full name");

            return $result;
        }

        if (!helper::isCorrectPassword($password)) {

            $result = array("error" => true,
                            "error_code" => ERROR_UNKNOWN,
                            "error_type" => 1,
                            "error_description" => "Incorrect password");

            return $result;
        }

        if (!helper::isCorrectEmail($email)) {

            $result = array("error" => true,
                            "error_code" => ERROR_UNKNOWN,
                            "error_type" => 2,
                            "error_description" => "Wrong email");

            return $result;
        }

        if ($helper->isEmailExists($email)) {

            $result = array("error" => true,
                            "error_code" => ERROR_EMAIL_TAKEN,
                            "error_type" => 2,
                            "error_description" => "User with this email is already registered");

            return $result;
        }

        $salt = helper::generateSalt(3);
        $passw_hash = md5(md5($password).$salt);
        $currentTime = time();

        $ip_addr = helper::ip_addr();

        $accountState = ACCOUNT_STATE_ENABLED;

        $stmt = $this->db->prepare("INSERT INTO users (state, login, fullname, passw, email, salt, regtime, ip_addr) value (:state, :username, :fullname, :password, :email, :salt, :createAt, :ip_addr)");
        $stmt->bindParam(":state", $accountState, PDO::PARAM_INT);
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);
        $stmt->bindParam(":password", $passw_hash, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":salt", $salt, PDO::PARAM_STR);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $this->setId($this->db->lastInsertId());

            $settings = new settings($this->db);
            $this->setAdmob($settings->getIntValue("admob"));
            unset($settings);

            $this->setLanguage("en");

            $result = array("error" => false,
                            'accountId' => $this->id,
                            'username' => $username,
                            'password' => $password,
                            'error_code' => ERROR_SUCCESS,
                            'error_description' => 'SignUp Success!');

            return $result;
        }

        return $result;
    }

    public function signin($username, $password)
    {
        $access_data = array('error' => true);

        $username = helper::clearText($username);
        $password = helper::clearText($password);

        $stmt = $this->db->prepare("SELECT salt FROM users WHERE login = (:username) LIMIT 1");
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();
            $passw_hash = md5(md5($password).$row['salt']);

            $stmt2 = $this->db->prepare("SELECT id, state FROM users WHERE login = (:username) AND passw = (:password) LIMIT 1");
            $stmt2->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt2->bindParam(":password", $passw_hash, PDO::PARAM_STR);
            $stmt2->execute();

            if ($stmt2->rowCount() > 0) {

                $row2 = $stmt2->fetch();

                $access_data = array("error" => false,
                                     "error_code" => ERROR_SUCCESS,
                                     "accountId" => $row2['id']);
            }
        }

        return $access_data;
    }

    public function logout($accountId, $accessToken)
    {
        $auth = new auth($this->db);
        $auth->remove($accountId, $accessToken);
    }

    public function setPassword($password, $newPassword)
    {
        $result = array('error' => true,
                        'error_code' => ERROR_UNKNOWN);

        if (!helper::isCorrectPassword($password)) {

            return $result;
        }

        if (!helper::isCorrectPassword($newPassword)) {

            return $result;
        }

        $stmt = $this->db->prepare("SELECT salt FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();
            $passw_hash = md5(md5($password).$row['salt']);

            $stmt2 = $this->db->prepare("SELECT id FROM users WHERE id = (:accountId) AND passw = (:password) LIMIT 1");
            $stmt2->bindParam(":accountId", $this->id, PDO::PARAM_INT);
            $stmt2->bindParam(":password", $passw_hash, PDO::PARAM_STR);
            $stmt2->execute();

            if ($stmt2->rowCount() > 0) {

                $this->newPassword($newPassword);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS);
            }
        }

        return $result;
    }

    public function newPassword($password)
    {
        $newSalt = helper::generateSalt(3);
        $newHash = md5(md5($password).$newSalt);

        $stmt = $this->db->prepare("UPDATE users SET passw = (:newHash), salt = (:newSalt) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":newHash", $newHash, PDO::PARAM_STR);
        $stmt->bindParam(":newSalt", $newSalt, PDO::PARAM_STR);
        $stmt->execute();
    }

    public function setSex($sex)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET sex = (:sex) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":sex", $sex, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getSex()
    {
        $stmt = $this->db->prepare("SELECT sex FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['sex'];
        }

        return 0;
    }

    public function setBirth($year, $month, $day)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET bYear = (:bYear), bMonth = (:bMonth), bDay = (:bDay) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":bYear", $year, PDO::PARAM_INT);
        $stmt->bindParam(":bMonth", $month, PDO::PARAM_INT);
        $stmt->bindParam(":bDay", $day, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function setAdmob($admob)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET admob = (:mode) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":mode", $admob, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getAdmob()
    {
        $stmt = $this->db->prepare("SELECT admob FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['admob'];
        }

        return 0;
    }

    public function setGhost($ghost)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET ghost = (:ghost) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":ghost", $ghost, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getGhost()
    {
        $stmt = $this->db->prepare("SELECT ghost FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['ghost'];
        }

        return 0;
    }

    public function setFacebookId($fb_id)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET fb_id = (:fb_id) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":fb_id", $fb_id, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getFacebookId()
    {
        $stmt = $this->db->prepare("SELECT fb_id FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['fb_id'];
        }

        return 0;
    }

    public function setFacebookPage($fb_page)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET fb_page = (:fb_page) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":fb_page", $fb_page, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getFacebookPage()
    {
        $stmt = $this->db->prepare("SELECT fb_page FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['fb_page'];
        }

        return '';
    }

    public function setInstagramPage($instagram_page)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET my_page = (:my_page) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":my_page", $instagram_page, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getInstagramPage()
    {
        $stmt = $this->db->prepare("SELECT my_page FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['my_page'];
        }

        return '';
    }

    public function setEmail($email)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $helper = new helper($this->db);

        if (!helper::isCorrectEmail($email)) {

            return $result;
        }

        if ($helper->isEmailExists($email)) {

            return $result;
        }

        $stmt = $this->db->prepare("UPDATE users SET email = (:email) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getEmail()
    {
        $stmt = $this->db->prepare("SELECT email FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['email'];
        }

        return '';
    }

    public function setUsername($username)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $helper = new helper($this->db);

        if (!helper::isCorrectLogin($username)) {

            return $result;
        }

        if ($helper->isLoginExists($username)) {

            return $result;
        }

        $stmt = $this->db->prepare("UPDATE users SET login = (:login) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":login", $username, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getUsername()
    {
        $stmt = $this->db->prepare("SELECT login FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['login'];
        }

        return '';
    }

    public function setLocation($location)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET country = (:country) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":country", $location, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getLocation()
    {
        $stmt = $this->db->prepare("SELECT country FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['country'];
        }

        return '';
    }

    public function setGeoLocation($lat, $lng)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET lat = (:lat), lng = (:lng) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":lat", $lat, PDO::PARAM_STR);
        $stmt->bindParam(":lng", $lng, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getGeoLocation()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT lat, lng FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS,
                            'lat' => $row['lat'],
                            'lng' => $row['lng']);
        }

        return $result;
    }

    public function setStatus($status)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET status = (:status) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":status", $status, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getStatus()
    {
        $stmt = $this->db->prepare("SELECT status FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['status'];
        }

        return '';
    }

    public function restorePointCreate($email, $clientId)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $restorePointInfo = $this->restorePointInfo();

        if ($restorePointInfo['error'] === false) {

            return $restorePointInfo;
        }

        $currentTime = time();	// Current time

        $u_agent = helper::u_agent();
        $ip_addr = helper::ip_addr();

        $hash = md5(uniqid(rand(), true));

        $stmt = $this->db->prepare("INSERT INTO restore_data (accountId, hash, email, clientId, createAt, u_agent, ip_addr) value (:accountId, :hash, :email, :clientId, :createAt, :u_agent, :ip_addr)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":hash", $hash, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":clientId", $clientId, PDO::PARAM_INT);
        $stmt->bindParam(":createAt", $currentTime, PDO::PARAM_INT);
        $stmt->bindParam(":u_agent", $u_agent, PDO::PARAM_STR);
        $stmt->bindParam(":ip_addr", $ip_addr, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS,
                            'accountId' => $this->id,
                            'hash' => $hash,
                            'email' => $email);
        }

        return $result;
    }

    public function restorePointInfo()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("SELECT * FROM restore_data WHERE accountId = (:accountId) AND removeAt = 0 LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS,
                            'accountId' => $row['accountId'],
                            'hash' => $row['hash'],
                            'email' => $row['email']);
        }

        return $result;
    }

    public function restorePointRemove()
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $removeAt = time();

        $stmt = $this->db->prepare("UPDATE restore_data SET removeAt = (:removeAt) WHERE accountId = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":removeAt", $removeAt, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function set_iOS_regId($ios_fcm_regid)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET ios_fcm_regid = (:ios_fcm_regid) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":ios_fcm_regid", $ios_fcm_regid, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function get_iOS_regId()
    {
        $stmt = $this->db->prepare("SELECT ios_fcm_regid FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['ios_fcm_regid'];
        }

        return 0;
    }

    public function setGCM_regId($gcm_regid)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET gcm_regid = (:gcm_regid) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":gcm_regid", $gcm_regid, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getGCM_regId()
    {
        $stmt = $this->db->prepare("SELECT gcm_regid FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['gcm_regid'];
        }

        return 0;
    }

    public function deactivation($password)
    {

        $result = array('error' => true,
                        'error_code' => ERROR_UNKNOWN);

        if (!helper::isCorrectPassword($password)) {

            return $result;
        }

        $stmt = $this->db->prepare("SELECT salt FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {

            $row = $stmt->fetch();
            $passw_hash = md5(md5($password) . $row['salt']);

            $stmt2 = $this->db->prepare("SELECT id FROM users WHERE id = (:accountId) AND passw = (:password) LIMIT 1");
            $stmt2->bindParam(":accountId", $this->id, PDO::PARAM_INT);
            $stmt2->bindParam(":password", $passw_hash, PDO::PARAM_STR);
            $stmt2->execute();

            if ($stmt2->rowCount() > 0) {

                $this->setState(ACCOUNT_STATE_DISABLED);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS);
            }
        }

        return $result;
    }

    public function setLanguage($language)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET language = (:language) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":language", $language, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getLanguage()
    {
        $stmt = $this->db->prepare("SELECT language FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['language'];
        }

        return 'en';
    }

    public function setVerify($verify)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET verify = (:verify) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":verify", $verify, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function setFullname($fullname)
    {
        if (strlen($fullname) == 0) {

            return;
        }

        $stmt = $this->db->prepare("UPDATE users SET fullname = (:fullname) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);

        $stmt->execute();
    }

    public function setBalance($balance)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET balance = (:balance) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":balance", $balance, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getBalance()
    {
        $stmt = $this->db->prepare("SELECT balance FROM users WHERE id = (:accountId) LIMIT 1");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['balance'];
        }

        return 0;
    }

    public function setAllowMessages($allowMessages)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowMessages = (:allowMessages) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowMessages", $allowMessages, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowMessages()
    {
        $stmt = $this->db->prepare("SELECT allowMessages FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowMessages'];
        }

        return 0;
    }

    public function setAllowComments($allowComments)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowComments = (:allowComments) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowComments", $allowComments, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowComments()
    {
        $stmt = $this->db->prepare("SELECT allowComments FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowComments'];
        }

        return 0;
    }

    public function setAllowPosts($allowPosts)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowPosts = (:allowPosts) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowPosts", $allowPosts, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowPosts()
    {
        $stmt = $this->db->prepare("SELECT allowPosts FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowPosts'];
        }

        return 0;
    }

    public function setAllowLikesGCM($allowLikesGCM)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowLikesGCM = (:allowLikesGCM) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowLikesGCM", $allowLikesGCM, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowLikesGCM()
    {
        $stmt = $this->db->prepare("SELECT allowLikesGCM FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowLikesGCM'];
        }

        return 0;
    }

    public function setAllowCommentsGCM($allowCommentsGCM)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowCommentsGCM = (:allowCommentsGCM) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowCommentsGCM", $allowCommentsGCM, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowCommentsGCM()
    {
        $stmt = $this->db->prepare("SELECT allowCommentsGCM FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowCommentsGCM'];
        }

        return 0;
    }

    public function setAllowFollowersGCM($allowFollowersGCM)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowFollowersGCM = (:allowFollowersGCM) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowFollowersGCM", $allowFollowersGCM, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowFollowersGCM()
    {
        $stmt = $this->db->prepare("SELECT allowFollowersGCM FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowFollowersGCM'];
        }

        return 0;
    }

    public function setAllowMessagesGCM($allowMessagesGCM)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowMessagesGCM = (:allowMessagesGCM) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowMessagesGCM", $allowMessagesGCM, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowMessagesGCM()
    {
        $stmt = $this->db->prepare("SELECT allowMessagesGCM FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowMessagesGCM'];
        }

        return 0;
    }

    public function setAllowCommentReplyGCM($allowCommentReplyGCM)
    {
        $stmt = $this->db->prepare("UPDATE users SET allowCommentReplyGCM = (:allowCommentReplyGCM) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":allowCommentReplyGCM", $allowCommentReplyGCM, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getAllowCommentReplyGCM()
    {
        $stmt = $this->db->prepare("SELECT allowCommentReplyGCM FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['allowCommentReplyGCM'];
        }

        return 0;
    }

    public function setState($accountState)
    {

        $stmt = $this->db->prepare("UPDATE users SET state = (:accountState) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":accountState", $accountState, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getState()
    {
        $stmt = $this->db->prepare("SELECT state FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $row = $stmt->fetch();

            return $row['state'];
        }

        return 0;
    }

    public function setLastActive()
    {
        $time = time();

        $stmt = $this->db->prepare("UPDATE users SET last_authorize = (:last_authorize) WHERE id = (:id)");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":last_authorize", $time, PDO::PARAM_INT);
        $stmt->execute();
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

                $notifications_count = 0;

                // Get notifications count

                $notifications = new notify($this->db);
                $notifications->setRequestFrom($this->id);

                $notifications_count = $notifications->getNewCount($row['last_notify_view']);

                unset($notifications);

                $profile = new profile($this->db, $row['id']);

                $online = false;

                $current_time = time();

                if ($row['last_authorize'] != 0 && $row['last_authorize'] > ($current_time - 15 * 60)) {

                    $online = true;
                }

                $time = new language($this->db);

                $result = array("error" => false,
                                "error_code" => ERROR_SUCCESS,
                                "id" => $row['id'],
                                "gcm_regid" => $row['gcm_regid'],
                                "ios_fcm_regid" => $row['ios_fcm_regid'],
                                "admob" => $row['admob'],
                                "gcm" => $row['gcm'],
                                "fb_id" => $row['fb_id'],
                                "state" => $row['state'],
                                "regtime" => $row['regtime'],
                                "ip_addr" => $row['ip_addr'],
                                "username" => $row['login'],
                                "fullname" => stripcslashes($row['fullname']),
                                "location" => stripcslashes($row['country']),
                                "status" => stripcslashes($row['status']),
                                "fb_page" => stripcslashes($row['fb_page']),
                                "instagram_page" => stripcslashes($row['my_page']),
                                "email" => $row['email'],
                                "sex" => $row['sex'],
                                "year" => $row['bYear'],
                                "month" => $row['bMonth'],
                                "day" => $row['bDay'],
                                "lat" => $row['lat'],
                                "lng" => $row['lng'],
                                "language" => $row['language'],
                                "lowPhotoUrl" => $row['lowPhotoUrl'],
                                "normalPhotoUrl" => $row['normalPhotoUrl'],
                                "bigPhotoUrl" => $row['normalPhotoUrl'],
                                "coverUrl" => $row['normalCoverUrl'],
                                "originCoverUrl" => $row['originCoverUrl'],
                                "allowCommentReplyGCM" => $row['allowCommentReplyGCM'],
                                "lastAuthorize" => $row['last_authorize'],
                                "lastAuthorizeDate" => date("Y-m-d H:i:s", $row['last_authorize']),
                                "lastAuthorizeTimeAgo" => $time->timeAgo($row['last_authorize']),
                                "online" => $online,
                                "itemsCount" => $row['items_count'],
                                "commentsCount" => $row['comments_count'],
                                "likesCount" => $row['likes_count'],
                                "notificationsCount" => $notifications_count);

                unset($profile);
                unset($time);
            }
        }

        return $result;
    }

    public function updateCounters()
    {
        $itemsCount = $this->getItemsCount();
        $likesCount = $this->getLikesCount();
        $commentsCount = $this->getCommentsCount();

        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET items_count = (:items_count), comments_count = (:comments_count), likes_count = (:likes_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":items_count", $itemsCount, PDO::PARAM_INT);
        $stmt->bindParam(":comments_count", $commentsCount, PDO::PARAM_INT);
        $stmt->bindParam(":likes_count", $likesCount, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function setCommentsCount($commentsCount)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET comments_count = (:comments_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":comments_count", $commentsCount, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getCommentsCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM comments WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function setItemsCount($itemsCount)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET items_count = (:items_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":items_count", $itemsCount, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getItemsCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM items WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    public function setLikesCount($likesCount)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET likes_count = (:likes_count) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":likes_count", $likesCount, PDO::PARAM_INT);

        if ($stmt->execute()) {

            $result = array('error' => false,
                            'error_code' => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getLikesCount()
    {
        $stmt = $this->db->prepare("SELECT count(*) FROM likes WHERE fromUserId = (:fromUserId) AND removeAt = 0");
        $stmt->bindParam(":fromUserId", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return $number_of_rows = $stmt->fetchColumn();
    }

    public function edit($fullname)
    {
        $result = array("error" => true);

        $stmt = $this->db->prepare("UPDATE users SET fullname = (:fullname) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false);
        }

        return $result;
    }

    public function setPhoto($array_data)
    {
        $stmt = $this->db->prepare("UPDATE users 
            SET originPhotoUrl = :p1, 
                normalPhotoUrl = :p2, 
                bigPhotoUrl = :p3, 
                lowPhotoUrl = :p4 
                WHERE id = :id");
        $stmt->execute([':p1' => $array_data['originPhotoUrl'],
            ':p2' => $array_data['originPhotoUrl'],
            ':p3' => $array_data['originPhotoUrl'],
            ':p4' => $array_data['originPhotoUrl'], ':id' => $this->id]);
    }

    public function setCover($array_data)
    {
        $stmt = $this->db->prepare("UPDATE users SET originCoverUrl = (:originCoverUrl), normalCoverUrl = (:normalCoverUrl) WHERE id = (:account_id)");
        $stmt->bindParam(":account_id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":originCoverUrl", $array_data['originCoverUrl'], PDO::PARAM_STR);
        $stmt->bindParam(":normalCoverUrl", $array_data['normalCoverUrl'], PDO::PARAM_STR);

        $stmt->execute();
    }

    public function setCoverPosition($position)
    {
        $stmt = $this->db->prepare("UPDATE users SET coverPosition = (:coverPosition) WHERE id = (:account_id)");
        $stmt->bindParam(":account_id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":coverPosition", $position, PDO::PARAM_STR);

        $stmt->execute();
    }

    public function setBackgroundUrl($url)
    {
        $result = array("error" => true,
                        "error_code" => ERROR_UNKNOWN);

        $stmt = $this->db->prepare("UPDATE users SET originCoverUrl = (:originCoverUrl), normalCoverUrl = (:normalCoverUrl) WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":originCoverUrl", $url, PDO::PARAM_STR);
        $stmt->bindParam(":normalCoverUrl", $url, PDO::PARAM_STR);

        if ($stmt->execute()) {

            $result = array("error" => false,
                            "error_code" => ERROR_SUCCESS);
        }

        return $result;
    }

    public function getBackgroundUrl()
    {
        $stmt = $this->db->prepare("SELECT originCoverUrl FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                return $row['originCoverUrl'];
            }
        }

        return '';
    }

    public function getAccessLevel($user_id)
    {
        $stmt = $this->db->prepare("SELECT access_level FROM users WHERE id = (:id) LIMIT 1");
        $stmt->bindParam(":id", $user_id, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                $row = $stmt->fetch();

                return $row['access_level'];
            }
        }

        return 0;
    }

    public function setAccessLevel($access_level)
    {
        $stmt = $this->db->prepare("UPDATE users SET access_level = (:access_level) WHERE id = (:accountId)");
        $stmt->bindParam(":accountId", $this->id, PDO::PARAM_INT);
        $stmt->bindParam(":access_level", $access_level, PDO::PARAM_INT);

        $stmt->execute();
    }

    public function setId($accountId)
    {
        $this->id = $accountId;
    }

    public function getId()
    {
        return $this->id;
    }
}

