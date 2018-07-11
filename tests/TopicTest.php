<?php

namespace XuTL\QCloud\Cmq\Tests;

use XuTL\QCloud\Cmq\Client;
use XuTL\QCloud\Cmq\Exception\CMQException;
use XuTL\QCloud\Cmq\Requests\CreateTopicRequest;

class TopicTest extends \PHPUnit\Framework\TestCase
{
    private $secretId;
    private $secretKey;
    private $endPoint;

    /**
     * @var Client
     */
    private $client;

    private $topicToDelete;

    public function setUp()
    {
        $ini_array = parse_ini_file(__DIR__ . "/cmq.ini");
        $this->endPoint = $ini_array["endpoint"];
        $this->secretId = $ini_array["secretId"];
        $this->secretKey = $ini_array["secretKey"];
        $this->topicToDelete = [];
        $this->client = new Client($this->endPoint, $this->secretId, $this->secretKey);
    }

    public function tearDown()
    {
        foreach ($this->topicToDelete as $topicName) {
            try {
                $this->client->deleteTopic($topicName);
            } catch (\Exception $e) {
            }
        }
    }

    private function prepareTopic($topicName)
    {
        $request = new CreateTopicRequest($topicName);
        $this->topicToDelete[] = $topicName;
        try {
            $res = $this->client->createTopic($request);
            $this->assertTrue($res->isSucceed());
        } catch (CMQException $e) {
            $this->assertTrue(false, $e);
        }

        return $this->client->getTopicRef($topicName);
    }
}