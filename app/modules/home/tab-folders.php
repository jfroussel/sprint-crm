<?php

namespace App\Modules\Home;
use App\AbstractController;

class TabFolders extends AbstractController
{
	protected $needAuth = true;
    function load(){
		return [
			'note'=>$this->db['note'],
		];
	}
}
