<?php
namespace App\Modules\Scenarios;

use App\AbstractController;
use App\Model\Lead;

class Update  extends AbstractController{
	
	protected $needAuth = true;
	
	function load($id){
		$scenario = $this->db['scenario'][$id];
		$scenarioArray = $scenario->getArray();
		$scenarioStepArray = [];
		$categories = [
		    'li'    => Lead::LOYER_IMPAYE,
            'ci'    => Lead::CHEQUE_IMPAYE,
            'cc'    => Lead::CREANCE_COMMERCIALE,
            'rc'    => Lead::REACTIVATION_CREANCE,
        ];


		foreach($scenario->_many_scenario_step as $scenarioStep){
			$scenarioStepArray[] = $scenarioStep;
		}
		return [
			'scenario'					         =>	 $scenarioArray,
			'scenario_step'				         =>	 $scenarioStepArray,
			'templatesList'				         =>	 $this->db['template'],
            'is_superroot'                       =>  $this->user->is_superroot,
            'templatesLoyerImpaye'               =>  $this->db['template']->where('category = ?', [$categories['li']]),
            'templatesChequeImpaye'              =>  $this->db['template']->where('category = ?', [$categories['ci']]),
            'templatesCreanceCommerciale'        =>  $this->db['template']->where('category = ?', [$categories['cc']]),
            'templatesReactivationCreance'       =>  $this->db['template']->where('category = ?', [$categories['rc']]),
		];

	}
	
	function store($data,$add=[],$remove=[]){
		if(!isset($data['id'])) return;
		$id = $data['id'];
		$scenario_step = $this->db['scenario_step'];
		$scenario = $this->db['scenario'];
		$row = $scenario->simpleEntity($data);
		foreach($add as $k=>$scenario_step_row){
			$scenario_step_row['scenario_id'] = $id;
			$scenario_step->simpleEntity($scenario_step_row)->store();
		}
		foreach($remove as $scenarioStepId){
			unset($scenario_step[$scenarioStepId]);
		}
		$row->store();
		return $this->load($id);
	}

}
