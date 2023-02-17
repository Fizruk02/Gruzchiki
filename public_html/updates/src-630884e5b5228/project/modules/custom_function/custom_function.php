<?

namespace project\modules\custom_function;

class custom_function
{
    public function start( array $par=[] )
    {
        $settings = json_decode($par['custom_function'], true);
        if($name = $settings['name']){
            unset($par['custom_function']);
            if ($f = get_custom_function($name)){
                $localPar=[];
                foreach($settings['localPar'] as $lp) $localPar[$lp['vr']]=text()->variables($lp['vl'], $par);
                $f['funcName']($par,$localPar);
            }

            else
                tgMess('Ошибка при подключении файла с функцией "'.$f['funcName'].'"');
        }
        else
            tgMess('функция не передана');

        return true;
    }

}

