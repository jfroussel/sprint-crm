<?php
namespace App\Modules\Home;
use App\AbstractController;
use App\Model\Entity\Paperwork_Table;
use App\Model\LawCashing;
use DateTime;
use DateInterval;
use App\Model\Dso;


class TabHome extends AbstractController{
	protected $needAuth = true;
   
    function load(){


		//$paperwork = $this->di->get(Paperwork_Table::class);
        $debtor = $this->db['user'][$this->user->instance_id];
		$data = [
			'user' => $debtor,
		];
		//ddj($data);
		return $data;
	}





	


}
