<?php
namespace App\Modules\Lead;

use App\AbstractController;

class All extends AbstractController{
	protected $needAuth = true;


    function load(){
        return [

        ];
    }

    function store($data){
		if(!isset($data['id'])) return;
		return $this->mainDb['lead']->simpleEntity($data)->store();
	}


}
