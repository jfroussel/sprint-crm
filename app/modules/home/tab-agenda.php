<?php
namespace App\Modules\Home;
use App\AbstractController;

class TabAgenda extends AbstractController{
	protected $needAuth = true;
	function load(){

		 $data = [];
		 $types = [false, 'appel', 'courrier', 'email', 'alerte', 'fax', 'tache'];
		 
		 $isSuperRoot = $this->user->is_superroot;
		 $isInstanceRoot = $this->user->instance_id==$this->user->id;
		
		 //$this->db->debug();
		 
		 foreach($types as $type){
			$query = $this->db['agenda']
				->unSelect()
				->select('todo_date, COUNT(*) as nb')
				->groupBy('todo_date')
			;
			if($type){
				$query->where('type = ?', [$type]);
			}
			
			$query1 = clone $query;
			$query2 = clone $query;
			
			$query1
				->joinAdd('INNER JOIN management')
				->joinAdd('ON agenda.linked_by = ? AND agenda.debtor_id = management.debtor_id', ['management'])
			;
			
			if($isSuperRoot || $isInstanceRoot){
				$query1->joinAdd('AND ( management.user_id = ? OR management.id IS NULL )',[$this->user->id]);
			}
			else{
				$query1->joinAdd('AND management.user_id = ?',[ $this->user->id ]);
			}
			
			$query2
				->joinAdd('INNER JOIN agenda_user')
				->joinAdd('ON agenda.linked_by = ? AND agenda.id = agenda_user.agenda_id AND agenda_user.user_id = ?', ['agenda_user',$this->user->id])
			;
			
			$params = array_merge($query1->getParams(),$query2->getParams());
			
			$queryUnion = $query1->getQuery().' UNION '.$query2->getQuery();
			$all = $this->db->getAll($queryUnion,$params);
			 
			$countKey = $type?'count_'.$type:'total';
			 
			foreach($all as $k => $v){
			    $v = (object)$v;
				$data[$v->todo_date][$countKey] = $v->nb;
				$data[$v->todo_date]['start'] = $v->todo_date;
				$data[$v->todo_date]['end'] = $v->todo_date;
			}
			 
		}
		 
		return [
			'agenda'=>$data,
		];
	}
}
