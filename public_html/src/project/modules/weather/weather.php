<?php
namespace project\modules\weather;

class weather
{
    public function start(array $par = [])
    {
        if (!$par = echo_message_from_par($par)) return false;
        set_pos($par['step'], $par);

        $id_region = json_decode($par['weather'], true)['region'];
        $data = $this->getWeather($id_region);
        $par['weather_city'] = $data['city'];
        $par['weather_type'] = $data['type'];
        $par['weather_temperature'] = $data['temperature'];

        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'],$par);
        return true;
    }

    private function getStrBetween($string, $from, $to)
    {
        if (!strpos(' ' . $string, $from)) return '';
        $prepared = substr($string, stripos($string, $from) + strlen($from));
        $returned = substr($prepared, 0, (stripos($prepared, $to) - strlen($prepared) + strlen($to) - strlen($to)));
        return $returned;
    }

    private function getWeather($id_region)
    {
        $url='http://export.yandex.ru/bar/reginfo.xml?region='.$id_region.'.xml';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        curl_close($ch);

        $data = [
            'city' => (getStrBetween($html, '<title>', '</title>')),
            'type' => (getStrBetween($html, '<weather_type>', '</weather_type>')),
            'temperature' => (explode('>', getStrBetween($html, '<temperature', '</temperature>'))[1])
        ];
        return $data;
    }
}