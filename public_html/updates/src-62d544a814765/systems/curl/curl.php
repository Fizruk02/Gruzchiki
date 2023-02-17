<?php
namespace systems\curl;

class curl
{
    public function post($url, $par=[], $add=[ /* 'header=> [] */ ]) {
        global $curl;

        curl_setopt_array(
            $curl,
            [
                CURLOPT_URL => $url,
                CURLOPT_POST => TRUE,
                CURLOPT_RETURNTRANSFER => TRUE,
                CURLOPT_POSTFIELDS => is_array($par)?http_build_query($par):$par,
            ]
        );

        if($add&&isset($add['header'])){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $add['header']);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        }

        $res = curl_exec($curl);
        curl_reset($curl);
        return $res;
    }

    function get($url, $par=[], $add=[ /* 'header=> [] */ ]) {
        $query = '';

        if(count($par))
            $query = '?'.http_build_query($par);

        $ch = curl_init($url.$query);

        curl_setopt_array(
            $ch,
            [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HEADER => false,
            ]
        );

        if($add&&isset($add['header'])){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $add['header']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }

        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

    public function put($url, $par=[], $add=[ /* 'header=> [] */ ]) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if($add['header']){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $add['header']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        }
        $res = curl_exec($ch);
        curl_reset($ch);
        return $res;
    }

}