<?php

namespace project\modules\input_variables;

class input_variables
{
    
    public $type = 'text';
    
    public function start( array $par=[] )
    {   
        $input_variables = json_decode($par['input_variables'], true); 
        $variable_data = isset($par['variable_data']) ? $par['variable_data'] : [];
        foreach($input_variables['variables'] as $variable){
            $variable_data[$variable['var']] = ['type'=>'text'];
            $val = $variable['val'];
            $tVal = '';
            $prepareVal = getStrBetween($val, '{', '}');
            if($prepareVal){
                $tVal = $par[$prepareVal];
                if(!$tVal)
                    $tVal = $GLOBALS[$prepareVal];
            }
            

            
            if(!$tVal)
                $tVal = $val;
            $GLOBALS[$variable['var']] = $tVal;
            $par[$variable['var']] = $tVal;
        }
        
        $par['variable_data'] = $variable_data;
        unset($par[$par['script_step']]);
        unset($par['input_variables']);
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'],$par);
        return false; 
    }
    

    
}