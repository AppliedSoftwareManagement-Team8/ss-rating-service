<?php
require_once "UserRatingPublisher.php";
/**
 * User: Samuil
 * Date: 04-12-2015
 * Time: 11:40 PM
 */
class RatingsDAO
{
    const MONGO_HOST = "mongodb://localhost:27017";
    const DATABASE_NAME = "ss-rating";
    const COLLECTION_NAME = "ratings";

    private static $collection;
    private static $connection;

    public function __construct()
    {
    }

    private function connect()
    {
        if (!isset(self::$collection)) {
            self::$connection = new MongoClient(self::MONGO_HOST);
            $db = self::$connection->selectDb(self::DATABASE_NAME);
            self::$collection = $db->selectCollection(self::COLLECTION_NAME);
        }
        return true;
    }

    public static function getAll()
    {
        self::connect();
        $ratings = self::$collection->find();
        $result = array();
        foreach ($ratings as $rating) {
            $result[] = $rating;
        }
        self::closeConnection();
        return $result;
    }

    public static function getAllByRecipient($id)
    {
        self::connect();
        $criteria = array('recipient_id' => $id);
        $ratings = self::$collection->find($criteria);
        $result = array();
        foreach ($ratings as $rating) {
            $result[] = $rating;
        }
        self::closeConnection();
        return $result;
    }

    public static function getAllByPublisher($id)
    {
        self::connect();
        $criteria = array('publisher_id' => $id);
        $ratings = self::$collection->find($criteria);
        $result = array();
        foreach ($ratings as $rating) {
            $result[] = $rating;
        }
        self::closeConnection();
        return $result;
    }

    public static function getOneByPublisher($id)
    {
        self::connect();
        $criteria = array('publisher_id' => $id);
        $result = self::$collection->findOne($criteria);
        self::closeConnection();
        return $result;
    }

    public static function getOneByRecipient($id)
    {
        self::connect();
        $criteria = array('recipient_id' => $id);
        $result = self::$collection->findOne($criteria);
        self::closeConnection();
        return $result;
    }

    public static function create($doc)
    {
        self::connect();
        $data = json_decode($doc);
        $result = self::$collection->insert($data);
        self::calcAverage($data['recipient_id']);
        self::closeConnection();
        return array('success' => 'created');
    }

    public static function delete($id)
    {
        self::connect();
        $criteria = array('_id' => new MongoId($id));
        self::$collection->remove(
            $criteria,
            array(
                'safe' => true
            )
        );
        self::closeConnection();
        return array('success' => 'deleted');
    }

    public static function calcAverage($recipient_id) {
        $criteria = array('recipient_id' => $recipient_id);
        $ratings = self::$collection->find($criteria, array(
            'recipient_id' => 1
        ));
        $result = 0;
        foreach ($ratings as $rating) {
            $result += $rating;
        }
        $average = $result/count($ratings);
        $userRating = array( 'userID' => $recipient_id, 'rating' => $average);
        UserRatingPublisher::publishUserRating($userRating);
    }

    private function closeConnection()
    {
        if (self::$connection != null) {
            self::$connection->close();
        }
    }
}