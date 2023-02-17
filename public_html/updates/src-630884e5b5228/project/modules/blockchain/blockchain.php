<?php

namespace project\modules\blockchain;

class blockchain
{
    public function start( array $par=[] )
    {   
        
        $settings = json_decode($par['blockchain'], true);
        foreach($settings as &$tSetting){
            $tSetting = text()->variables($tSetting, $par);
            $tSetting = text()->shortcodes($tSetting, $par);
        }
        
        $script = $settings['name'];
        $step = $settings['block'];
        if($id = $settings['id'])
            if( !$script = singleQuery('SELECT techname FROM `constructors` WHERE techname = :id OR id = :id OR name = :id', [':id'=> $id])['techname'] )
            return tgMess('Сценарий "'.$id.'" не найден');
        if($step&&($sd=singleQuery('SELECT item id FROM `s_steps` WHERE item = :id OR id = :id OR name = :id', [':id'=> $step])))
            $step=$sd['id']; else $step=false;
        
        $previousBlockchainList = $par['previousBlockchainList'];
        if(!$previousBlockchainList) $previousBlockchainList = [];
        
        $previousBlockchain= [];    
        $previousBlockchain['script_source']=$par['script_source'];
        $previousBlockchain['script_step']=$par['script_step'];
        $previousBlockchain['id_step']=$par['id_step'];
        array_push($previousBlockchainList, $previousBlockchain);
        $par['previousBlockchainList'] = $previousBlockchainList;
        go_to($script,$step?:false,$par);
        //the_distribution_module($script, $par);
        return;
    }
}