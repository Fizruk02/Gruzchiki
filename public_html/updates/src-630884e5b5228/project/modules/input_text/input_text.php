<?php
namespace project\modules\input_text;

class input_text
{

    public $type = 'text';

    public function start(array $par = [])
    {
        if (!$par = echo_message_from_par($par)) return false;
        set_pos($par['step'], $par);

        return true;
    }

    public function listener(array $par = [])
    {
        global $original, $obj, $chat_id, $username, $message_id, $text_message, $user_settings;

        $text = $text_message;

        if ($pars_num = $obj['message']['contact']['phone_number']) $text = $pars_num;

        $l_text = trim(mb_strtolower($text, 'utf-8'));

        if ($text == '' || is_array(json_decode($text, true))) return tgMess(DIALTEXT(inputTextSendOnlyTheText)); # Пришлите только текст
        $settings = json_decode($par['input_text'], true);

        $skip_commands = preg_split("/\\r\\n?|\\n/", $settings['skip_commands']);
        $skip_commands = array_values($skip_commands); # убирает пустые строки
        if (array_search($text_message, $skip_commands) !== false)
        {
            methods()->delete_this_inline_keyboard();
            $par[$par['script_step']] = '';
            unset($par['input_text']);
            set_pos($par['step'], $par);
            the_distribution_module($par['script_source'], $par);
            return;
        }

        switch ($settings['type'])
        {
            case 'number':
                $num = str_replace(',', '.', $text);
                if (!is_numeric($num))
                {
                    tgMess(DIALTEXT('inputTextWeNeedToSendANumber')); # надо прислать число
                    return;
                }

                $num = (float)$num;
                $range_from = (float)$settings['range_from'];
                $range_to = (float)$settings['range_to'];

                if ($range_to > 0 && $range_to > $range_from)
                {
                    if ($num < $range_from || $num > $range_to)
                    {
                        $tempMess = DIALTEXT('inputTextTheNumberMustBeInTheRangeFromto');
                        $tempMess = str_replace('{range_from}', $range_from, $tempMess);
                        $tempMess = str_replace('{range_to}', $range_to, $tempMess);
                        tgMess($tempMess); # число должно быть в диапазоне от {range_from} до {range_to}"
                        return;
                    }
                }

            break;

            case 'integer':
                $num = $text;
                if (strval($num) !== strval(intval($num)))
                {
                    tgMess(DIALTEXT('inputTextWeNeedToSendAnInteger')); # надо прислать целое число
                    return;
                }

                $num = (float)$num;
                $range_from = (float)$settings['range_from'];
                $range_to = (float)$settings['range_to'];

                if ($range_to > 0 && $range_to > $range_from)
                {
                    if ($num < $range_from || $num > $range_to)
                    {
                        $tempMess = DIALTEXT('inputTextTheNumberMustBeInTheRangeFromto');
                        $tempMess = str_replace('{range_from}', $range_from, $tempMess);
                        $tempMess = str_replace('{range_to}', $range_to, $tempMess);
                        tgMess($tempMess); # число должно быть в диапазоне от {range_from} до {range_to}"
                        return;
                    }
                }

            break;

            case 'text':
                if (($limit = (float)$settings['limit_chars']) && ($len = mb_strlen($text)) > $limit)
                {
                    tgMess('Длина текста не должна превышать ' . text()->num_word($limit, ['знак', 'знака', 'знаков']) . ' (у вас ' . $len . ')');
                    return;
                }
            break;

            case 'mask':
                $mask = $settings['mask'];
                if (!preg_match($mask, $text))
                {
                    return tgmess('Не верный формат');
                }
            break;

        }

        $par[$par['script_step']] = $text;

        if (!intermediate_function($par)) return;
        unset($par['input_text']);
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'], $par);
        return;
    }
}