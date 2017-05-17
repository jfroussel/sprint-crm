<?php
namespace App\Modules\Debtors;

use App\AbstractController;

class TabScenario extends AbstractController{
	
	protected $needAuth = true;
	
	function load($id){
		return [
			'scenarioStep'=>$this->db['debtor']->getCurrentScenarioStep($id),
			'scenarioList'=>$this->db['scenario'],
			'scenarioIsRunning'=>$this->isRunning($id),
		];
	}
	
	function updateUseInterval($debtor_id,$use_interval){
		$this->db['debtor']->simpleEntity([
			'id'=>$debtor_id,
			'use_interval'=>$use_interval,
		])->store();
	}
	
	function scenarioOverride($id,$scenario_id){
		$this->db['debtor'][$id] = [ 'scenario_id' => $scenario_id ];
		return true;
	}

    function stopRunning($id){
		$this->db->exec('DELETE FROM running_scenario WHERE debtor_id = ?',[$id]);
		return true;
	}
    
    function isRunning($id){
		
		$scenario = $this->getRunning($id);
		if(!$scenario) return false;
		
		$step = $scenario->_many_running_scenario_step
			->orderBy('id')
			->sort('ASC')
			->limit(1)
			->offset($scenario->step)
			->unSelect()
			->select('id')
			->getCell();
		if(!$step) return false;
		
		return true;
    }
    protected function getRunning($id){
		$running_scenario = $this->db['running_scenario'];
		if(!$running_scenario->exists()) return false;
		
		return $running_scenario
			->where('debtor_id = ?',[$id])
			->where('running_type = ?',['current'])
			->limit(1)
			->getRow()
		;
	}
	
	

}
