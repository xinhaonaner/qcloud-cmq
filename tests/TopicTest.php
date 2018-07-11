<?php

namespace XuTL\QCloud\Cmq\Tests;

use XuTL\QCloud\Cmq\Client;
use XuTL\QCloud\Cmq\Exception\CMQException;
use XuTL\QCloud\Cmq\Requests\CreateTopicRequest;
use XuTL\QCloud\Cmq\Requests\SetTopicAttributeRequest;

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

        $this->endPoint = $ini_array["endpoint"] != '<endpoint>' ? $ini_array["endpoint"] : 'https://cmq-topic-bj.api.qcloud.com';
        $this->secretId = $ini_array["secretId"] != '<secretId>' ? $ini_array["secretId"] : getenv('SECRET_ID');
        $this->secretKey = $ini_array["secretKey"] != '<secretKey>' ? $ini_array["secretKey"] : getenv('SECRET_KEY');

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

    public function testTopicAttributes()
    {
        $topicName = "testTopicAttributes";
        $topic = $this->prepareTopic($topicName);

        try {
            $res = $topic->getAttribute();
            $this->assertTrue($res->isSucceed());
            $this->assertEquals($topicName, $res->getTopicName());
        } catch (CMQException $e) {
            $this->assertTrue(false, $e);
        }

        $maximumMessageSize = 10 * 1024;
        $request = new SetTopicAttributeRequest();
        $request->setTopicName($topicName);
        $request->setMaxMsgSize($maximumMessageSize);
        try {
            $res = $topic->setAttribute($request);
            $this->assertTrue($res->isSucceed());
        } catch (CMQException $e) {
            $this->assertTrue(false, $e);
        }

        try {
            $res = $topic->getAttribute();
            $this->assertTrue($res->isSucceed());
            $this->assertEquals($res->getMaxMsgSize(), $maximumMessageSize);
        } catch (CMQException $e) {
            $this->assertTrue(false, $e);
        }

        $this->client->deleteTopic($topicName);

        try {
            $res = $topic->getAttribute();
            $this->assertTrue(false, "Should throw TopicNotExistException");
        } catch (CMQException $e) {
            $this->assertEquals($e->getCode(), 4000);
        }

        try {
            $res = $topic->setAttribute($request);
            $this->assertTrue(False, "Should throw TopicNotExistException");
        } catch (CMQException $e) {
            $this->assertEquals($e->getCode(), 4000);
        }
    }
}
