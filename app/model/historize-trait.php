<?php
namespace App\Model;
trait HistorizeTrait{
    function historize(){
        $history = $this->db['history'];
        $action = $history->entity([
            'category'=>$this->category,
            '_one_'.$this->_type=>$this->id,
            'table'=>$this->_type,
            'message'=>$this->message,
            '_one_user'=>$this->_one_user,
            'debtor_primary'=>$this->debtor_primary,
            'debtor_id'=>$this->debtor_id,
            'job_primary' =>$this->job_primary,
            'job_url' => $this->job_url,
            'type' => $this->type,
            'timer' =>$this->timer,
        ]);

        if($this->instance_id){
			$action['_one_instance'] = [
				'_type'=>'user',
				'id'=>$this->instance_id,
			];
		}

        $history[] = $action;
    }
}
