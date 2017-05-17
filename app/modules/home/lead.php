<?php
namespace App\Modules\Home;
use App\AbstractController;
use DateTime;
use DateInterval;
use App\Model\Dso;
class Lead extends AbstractController{
	protected $needAuth = true;
   
    function load(){
		return [
			'leads' => $this->mainDb['lead']->where('user_id = ?',[$this->user->id]),
		];
	}
}
