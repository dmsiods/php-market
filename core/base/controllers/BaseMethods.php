<?php

namespace core\base\controllers;

trait BaseMethods
{
    protected function clearStr($str)
    {
        if (is_array($str)) {
            foreach ($str as $key => $item) {
                $str[$key] = trim(strip_tags($item));
            }
        } else {
            $str = trim(strip_tags($str));
        }
        return $str;
    }

    protected function clearNum($num)
    {
        return $num * 1;  // приведение строки в числа (int, float)
    }

    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) and $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    protected function redirect($http = false, $code = false)
    {
        if ($code) {
            $codes = [
                '301' => 'HTTP/1.1 301 Move Permanently'
            ];

            if (isset($codes[$code])) {
                header($codes[$code]);
            }
        }

        if ($http) {
            $redirect = $http;
        } else {
            $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : PATH;
        }

        header("Location: $redirect");
    }

    protected function writeLog($message, $file = 'log.txt', $event = 'Fault')
    {
        $dateTime = new \DateTime();

        $str = $event . ': ' . $dateTime->format('d-m-Y G:i:s') . ' - ' . $message . "\r\n";

        file_put_contents('log/' . $file, $str, FILE_APPEND);
    }
}
