<?php
namespace App\Modules\Debtors;

use App\AbstractController;
use DateTime;

class Update extends AbstractController{
	protected $needAuth = true;
	
	function load($id){	
		$data = $this->db['debtor'][$id]->getArray();
		$data += [
			'contact'=>$this->db['contact']->where('debtor_primary = ? AND principal = ?',[$this->db['debtor']->getPrimary($id),'1'])->limit(1)->getRow(),
            'management' => $this->getManager($id),
            'user' =>$this->db['user'][$this->user->id],
		];
        //ddj($data);
		return $data;
	}

	function getManager($id){
	    $manager = $this->db['management']->where('debtor_id = ?', [$id]);
	    $managerId = '';
        foreach($manager as $k=>$v){
            $managerId= $v['user_id'];
        }
        $managerInfo = [];
        $user = $this->db['user']->where('id = ?', [$managerId]);
        foreach($user as $k=>$v){
            $managerInfo['last_name'] = $v['last_name'];
            $managerInfo['first_name'] = $v['first_name'];
        }
        return $managerInfo;
    }
}
