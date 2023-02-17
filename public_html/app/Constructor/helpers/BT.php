<?php
namespace App\Constructor\helpers;

class BT extends BTBooster  {
	//This CB class is for alias of BTBooster class


    //alias of echoSelect2Mult
    public function ES2M($values, $table, $id, $name) {
        return BTBooster::echoSelect2Mult($values, $table, $id, $name);
    }
}
