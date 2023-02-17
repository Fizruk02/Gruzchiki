<?php

namespace project\modules\output_data_to_the_log;

class output_data_to_the_log
{
    
    public function start( array $par=[] )
    {   
        qwe($par,'logmodule');
        set_pos($par['step'], $par);
        the_distribution_module($par['script_source'],$par);
        
        return true;
    }
    

    
}