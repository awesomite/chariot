<?php

namespace Awesomite\Chariot\Speedtest;

/**
 * @internal
 */
class Timer
{
    private $time;

    private $startedAt;

    private $minTime = null;

    private $maxTime = null;

    public function start()
    {
        $this->startedAt = microtime(true);
    }

    public function stop()
    {
        $time = microtime(true) - $this->startedAt;

        if (is_null($this->minTime)) {
            $this->minTime = $time;
            $this->maxTime = $time;
        } else {
            $this->minTime = min($this->minTime, $time);
            $this->maxTime = max($this->maxTime, $time);
        }
        $this->time += $time;
    }

    public function getMinTime(): float
    {
        return $this->minTime;
    }

    public function getMaxTime(): float
    {
        return $this->maxTime;
    }

    public function getTime(): float
    {
        return $this->time;
    }
}
