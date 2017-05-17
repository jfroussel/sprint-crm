<?php
namespace App\Api;

class DebtorAppel extends AbstractAppel {
	
    function addToAgenda($debtor_id, $date, $contact_id, $message){
        $agenda = [
            'debtor_id'=>$debtor_id,
            'category'=>'agenda',
            'type'=>'appel',
            'todo_date'=>$date,
            'contact_id'=>$contact_id,
            'message'=>$message,
//            '_one_instance'=>['_type'=>'user','id'=>$this->instance_id],
            'instance_id' => $this->instance_id,
            'linked_by'=>'management',
        ];
        $this->db['agenda']->simpleEntity($agenda)->store();
    }

}
