<?php

/*!
 * ifsoft.co.uk v1.0
 *
 * http://ifsoft.com.ua, http://ifsoft.co.uk
 * qascript@ifsoft.co.uk
 *
 * Copyright 2012-2016 Demyanchuk Dmitry (https://vk.com/dmitry.demyanchuk)
 */

class popular extends db_connect
{
    private $requestFrom = 0;

    public function __construct($dbo = NULL)
    {
        parent::__construct($dbo);
    }

    public function get($rating = 0, $category = 0)
    {
        if ($rating == 0) {

            $rating = 1000000;
        }

        $timeAt = 0;

        switch ($category) {

            // All time

            case 0: {

                $timeAt = time();

                $sql = "SELECT id FROM items WHERE createAt < (:timeAt) AND removeAt = 0 AND rating > 0 AND rating < (:rating) ORDER BY rating DESC LIMIT 50";

                break;
            }

            // Day

            case 1: {

                $timeAt = time() - (24 * 3600);

                $sql = "SELECT id FROM items WHERE createAt > (:timeAt) AND removeAt = 0 AND rating > 0 AND rating < (:rating) ORDER BY rating DESC LIMIT 50";

                break;
            }

            // Week

            case 2: {

                $timeAt = time() - (7 * 24 * 3600);

                $sql = "SELECT id FROM items WHERE createAt > (:timeAt) AND removeAt = 0 AND rating > 0 AND rating < (:rating) ORDER BY rating DESC LIMIT 50";

                break;
            }

            // Month

            case 3: {

                $timeAt = time() - (30 * 24 * 3600);

                $sql = "SELECT id FROM items WHERE createAt > (:timeAt) AND removeAt = 0 AND rating > 0 AND rating < (:rating) ORDER BY rating DESC LIMIT 50";

                break;
            }

            // All Time default

            default: {

                $timeAt = time();

                $sql = "SELECT id FROM items WHERE createAt < (:timeAt) AND removeAt = 0 AND rating > 0 AND rating < (:rating) ORDER BY rating DESC LIMIT 50";

                break;
            }
        }

        $result = array("error" => false,
                         "error_code" => ERROR_SUCCESS,
                         "rating" => $rating,
                         "category" => $category,
                         "items" => array());

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':timeAt', $timeAt, PDO::PARAM_INT);

        if ($stmt->execute()) {

            if ($stmt->rowCount() > 0) {

                while ($row = $stmt->fetch()) {

                    $item = new items($this->db);
                    $item->setRequestFrom($this->requestFrom);
                    $itemInfo = $item->info($row['id']);
                    unset($post);

                    array_push($result['items'], $itemInfo);

                    $result['rating'] = $itemInfo['rating'];

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

