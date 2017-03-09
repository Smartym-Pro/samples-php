<?php

namespace QueueBundle\Message;

use BinSoul\Net\Mqtt\Message;

abstract class BaseMessage implements Message
{
    /** @var string */
    protected $topic;
    /** @var string */
    protected $payload;
    /** @var bool */
    protected $isRetained;
    /** @var bool */
    protected $isDuplicate;
    /** @var int */
    protected $qosLevel;

    /**
     * @return string
     */
    abstract protected function getBaseTopic();

    /**
     * Constructs an instance of this class.
     *
     * @param string $topicSuffix
     * @param string $payload
     * @param int $qosLevel
     * @param bool $retain
     * @param bool $isDuplicate
     */
    public function __construct($topicSuffix = '', $payload = '', $qosLevel = 1, $retain = true, $isDuplicate = false)
    {
        $this->topic = $this->getTopicWithSuffix($topicSuffix);
        $this->payload = $payload;
        $this->isRetained = $retain;
        $this->qosLevel = $qosLevel;
        $this->isDuplicate = $isDuplicate;
    }

    public function getTopic()
    {
        return $this->topic;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function getQosLevel()
    {
        return $this->qosLevel;
    }

    public function isDuplicate()
    {
        return $this->isDuplicate;
    }

    public function isRetained()
    {
        return $this->isRetained;
    }

    public function withTopic($topicSuffix)
    {
        $result = clone $this;
        $result->topic = $result->getTopicWithSuffix($topicSuffix);

        return $result;
    }

    public function withPayload($payload)
    {
        $result = clone $this;
        $result->payload = $payload;

        return $result;
    }

    public function withQosLevel($level)
    {
        $result = clone $this;
        $result->qosLevel = $level;

        return $result;
    }

    public function retain()
    {
        $result = clone $this;
        $result->isRetained = true;

        return $result;
    }

    public function release()
    {
        $result = clone $this;
        $result->isRetained = false;

        return $result;
    }

    public function duplicate()
    {
        $result = clone $this;
        $result->isDuplicate = true;

        return $result;
    }

    public function original()
    {
        $result = clone $this;
        $result->isDuplicate = false;

        return $result;
    }

    /**
     * @param string $suffix
     * @return string
     */
    protected function getTopicWithSuffix($suffix)
    {
        return $this->getBaseTopic() . $suffix;
    }
}
