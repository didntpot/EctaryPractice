<?php


namespace practice\manager;


class TimeManager
{

    public static function timestampToTime(int $date1)
    {
        $diff = abs($date1 - time());
        $retour = array();

        $tmp = $diff;
        $retour['second'] = $tmp % 60;
        $tmp = floor(($tmp - $retour['second']) / 60);
        $retour['minute'] = $tmp % 60;
        $tmp = floor(($tmp - $retour['minute']) / 60);
        $retour['hour'] = $tmp % 24;
        $tmp = floor(($tmp - $retour['hour']) / 24);
        $retour['day'] = $tmp;
        return $retour;
    }

    public static function timestampDiffToTime($date1, $date2)
    {
        $diff = abs($date1 - $date2);
        $retour = array();

        $tmp = $diff;
        $retour['second'] = $tmp % 60;
        $tmp = floor(($tmp - $retour['second']) / 60);
        $retour['minute'] = $tmp % 60;
        $tmp = floor(($tmp - $retour['minute']) / 60);
        $retour['hour'] = $tmp % 24;
        $tmp = floor(($tmp - $retour['hour']) / 24);
        $retour['day'] = $tmp;
        return $retour["day"] . "d " . $retour["hour"] . "h " . $retour["minute"] . "m " . $retour["second"] . "s";
    }

    public static function secondsToTime($seconds_time)
    {
        if ($seconds_time < 24 * 60 * 60) {
            return gmdate('i:s', $seconds_time);
        } else {
            $hours = floor($seconds_time / 3600);
            $minutes = floor(($seconds_time - $hours * 3600) / 60);
            $seconds = floor($seconds_time - ($hours * 3600) - ($minutes * 60));
            return "$minutes:$seconds";
        }
    }

}