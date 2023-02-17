<?php

namespace project\modules\category_group;

class category_group
{
    
    public function start( array $par=[] )
    {   
        $settings = json_decode($par['category_group'], true);
        
        $settings['type']='category';
        $settings['source_category']=$settings['id_cat'];
        
        $text = '≡ Categories';
        if($messId = $par['script_messages'][0]['message'])
           $text = DIALTEXT($messId);
           
        $text = text()->variables($text, $par);
        
        $settings['title'] = $text;
        
        $par['variable_data'][$par['script_step']]=$settings;
        
        

        
        
        $par['id_category']=$settings['id_cat'];
        
        set_pos('category_group', $par);
        
        categories()->_category($par);
        return true;
    }
    
    public function listener(){
        global $text_message;
        $l_text = trim(mb_strtolower($text_message));
        
        if ($l_text == 'пропустить') {
            $last_par[$last_par['script_step']] = '';
            $last_par['market_items_skip'] = '1';
            set_pos($last_par['step'], $last_par);
            if (!intermediate_function($last_par)) return;
            the_distribution_module($last_par['script_source'], $last_par);
            return;
        }
    }
    
}