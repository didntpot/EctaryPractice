<?php


namespace practice\manager;


class LogsManager
{
    public static bool $logs = false;

    /**
     * @return bool
     */
    public static function isLogs(): bool
    {
        return self::$logs;
    }

    /**
     * @param bool $logs
     */
    public static function setLogs(bool $logs): void
    {
        self::$logs = $logs;
    }
}