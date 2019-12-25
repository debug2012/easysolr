<?php
/**
 * Author: huanw2010@gmail.com
 * Date: 2018/7/18 15:07
 */

namespace terry\solr;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class EasyLogger extends AbstractLogger
{
    private $level;
    private $levelMap = [
        LogLevel::EMERGENCY => 8,
        LogLevel::ALERT => 7,
        LogLevel::CRITICAL => 6,
        LogLevel::ERROR => 5,
        LogLevel::WARNING => 4,
        LogLevel::NOTICE => 3,
        LogLevel::INFO => 2,
        LogLevel::DEBUG => 1,
    ];

    public function __construct($level)
    {
        $this->level = $level;
    }

    public function log($level, $message, array $context = array())
    {
        if ($this->levelMap[$level] >= $this->levelMap[$this->level]) {
            printf("\n[%s][%s]%s\n", $level, date("Y-m-d H:i:s"), $message);
        }
    }

}