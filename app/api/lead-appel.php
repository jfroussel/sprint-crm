<?php
namespace App\Api;

class LeadAppel extends AbstractAppel {
	
	use LeadTrait;
	
    function addToAgenda($lead_id, $date, $message){
        $agenda = [
            'lead_id'=>$lead_id,
            'category'=>'agenda',
            'type'=>'appel',
            'todo_date'=>$date,
            'message'=>$message,
            'instance_id' => $this->instance_id,
            'linked_by'=>'management',
        ];
        $this->db['agenda']->simpleEntity($agenda)->store();
    }
}
