<?php
namespace App\Modules\Lead;

use App\AbstractController;

class Update extends AbstractController{
	protected $needAuth = true;
	function load($id){
	    //ddj($id);
        $management = $this->getManagement($id);
	    if($this->user->is_superroot){
            return [
                'lead'          =>  $this->mainDb['lead']->where('id = ?', [$id])->getRow(),
                'usersList'		=>	$this->db['user'],
                'user_id' => $management?$management->user_id:null,
            ];
        }
	}

    function updateManagement($id,$user_id=null){
        if(!$id) return;
        $management = $this->db['management'];
        $row = $this->getManagement($id);
        if(!$row){
            $row = $management->entity([
                'lead_id'=>$id,
            ]);
        }
        $row->user_id = $user_id;
        $row->store();
        return true;
    }

    protected function getManagement($id){
        $management = $this->db['management'];
        if($management->exists()){
            return $management->where('lead_id = ?',[$id])->limit(1)->getRow();
        }
    }



//    function store($data){
//        return $this->db['lead']->simpleEntity($data)->store();
//    }
}
