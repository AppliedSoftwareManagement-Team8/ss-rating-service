<?php
require_once '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * User: Samuil
 * Date: 04-12-2015
 * Time: 12:52 PM
 */
class UserRatingPublisher
{
    private static $connection;
    private static $channel;

    const RABBIT_MQ_HOST = "127.0.0.1";
    const RABBIT_MQ_PORT = 5672;
    const RABBIT_MQ_USER = "guest";
    const RABBIT_MQ_PASSWORD = "guest";
    const RABBIT_MQ_QUEUE = "ss-user-rating";

    public function __construct()
    {
    }

    private function connect()
    {
        self::$connection = new AMQPStreamConnection(
            self::RABBIT_MQ_HOST,
            self::RABBIT_MQ_PORT,
            self::RABBIT_MQ_USER,
            self::RABBIT_MQ_PASSWORD);
        self::$channel = self::$connection->channel();
        self::$channel->queue_declare(self::RABBIT_MQ_QUEUE, false, false, false, false);
    }

    public static function publishUserRating($data)
    {
        self::connect();
        $msg = new AMQPMessage(json_decode($data), array('delivery_mode' => 2));
        self::$channel->basic_publish($msg, '', self::RABBIT_MQ_QUEUE);
        self::closeConnection();
    }

    private function closeConnection()
    {
        if (self::$channel != null && self::$connection != null) {
            self::$channel->close();
            self::$connection->close();
        }
    }
}