<?php
namespace App\Artist;

use RedCat\Artist\ArtistPlugin;
use FoxORM\MainDb;
use ForceUTF8\Encoding;
use PDOException;
use Redis;
use InvalidArgumentException;
use DateTime;


class MyScenarioRundebtor extends ArtistPlugin{
	
	protected $db;
	protected $pid;	
	protected $description = "Run a scenario for a debtor";
	protected $args = [
		'debtor'=>'debtor id',
	];
	protected $ns;
	protected $debtorId;
	protected $debtor;
	protected $runningDate;
	protected $runningMidday;
	function __construct($name = null, MainDb $db, Redis $redis, DateTime $datetime=null){
		parent::__construct($name);
		$this->db = $db;
		$this->redis = $redis;
		$this->pid = getmypid();
		$this->runningDate = $datetime?:new DateTime();
		$this->runningMidday = $this->getMidday();
	}
	
	protected function exec(){
		$this->db->debug();
		
		$this->ns = 'my:tasks:running:scenario:';
		
		$this->debtorId = $this->input->getArgument('debtor');
		$this->debtor = $this->db['debtor'][$this->debtorId];
		
		if(is_null($this->debtor)){
			throw new InvalidArgumentException('Invalid debtor id');
		}
		
		$this->progress = [
			'pid'=>$this->pid,
			'debtor'=>$this->debtorId,
			'state'=>'',
			'start'=>time(),
		];
		
		//if($this->db->tableExists('running_scenario')) $this->db->exec('DELETE FROM running_scenario');//for dev
		
		$r = $this->runScenario();
		if($r===false){
			trigger_error(print_r($this->progress,true));
			$this->progress('state','error');
		}
	}
	protected function progress($k,$v=null){
		$progress = $this->redis->hGet($this->ns.'progress',$this->pid);
		
		if($progress){
			$progress = json_decode($progress,true);
		}
		if(is_array($progress)){
			$this->progress = array_merge($progress,$this->progress);
		}
		
		if(is_array($k)){
			foreach($k as $key=>$v){
				$this->progress[$key] = $v;
			}
		}
		else{
			$this->progress[$k] = $v;
		}
		if((isset($this->progress['error'])&&$this->progress['error'])||$this->progress['state']=='error'){
			$this->progress['expire'] = time()+10;
			trigger_error($this->progress['error'].' '.print_r($this->progress,true));
		}
		$this->redis->hSet($this->ns.'progress',$this->pid,json_encode($this->progress));
	}
	
	protected function getMidday(){
		$hour =	$this->runningDate->format('His');
		$hour = (int)ltrim($hour,'0');
		$midday = 120000;
		$dateTime = new DateTime($this->runningDate->format('Y-m-d'));
		if($hour<$midday){
			$dateTime->modify('-1 day');
		}
		return $dateTime;
	}
	
	protected function getCurrentRunningScenario(){
		$running_scenario = $this->db['running_scenario'];
		if($running_scenario->exists()){
			$scenario = $running_scenario
				->where('debtor_id = ?',[$this->debtorId])
				->where('running_type = ?',['current'])
				->limit(1)
				->getRow()
			;
			return $scenario;
		}
	}
	protected function getPreviousRunningScenario(){
		$running_scenario = $this->db['running_scenario'];
		if($running_scenario->exists()){
			return $running_scenario
				->where('debtor_id = ?',[$this->debtorId])
				->where('running_type = ?',['previous'])
				->limit(1)
				->getRow()
			;
		}
	}
	protected function getScenarioChosen(){
		$override = $this->debtor->scenario_id;
		if($override){
			$scenario = $this->debtor->_one_scenario;
		}
		else{
			$scenario = $this->getScenarioByDebtorScore();
		}
		return $scenario;
	}
	protected function getScenarioByDebtorScore(){
		$letter = $this->debtor->letter;

		$scenario = $this->db['scenario']
			->where('instance_id = ?',[$this->debtor->instance_id])
			->where('letter = ?',[$letter])
			->where('active = 1')
			->getRow();
		if(!$scenario){
			throw new InvalidArgumentException("Unable to find scenario with letter $letter");
		}
		return $scenario;
	}
	protected function getNewOrResumeRunningScenario($oldestPaperwork){
		$running_scenario = $this->db['running_scenario'];
		if($running_scenario->exists()){
			$scenario = $running_scenario
				->where('debtor_id = ?',[$this->debtorId])
				->where('running_type = ?',['suspended'])
				->where('paperwork_id = ?',[$oldestPaperwork->id])
				->limit(1)
				->getRow()
			;
			if($scenario){
				$this->renewExpectedRunDay($scenario);
				return $scenario;
			}
		}
		return $this->getNewRunningScenario();
	}
	protected function renewExpectedRunDay($runningScenario){
		
		$runnedDay = new DateTime($runningScenario->suspended_day);
		$diff =  $runnedDay->diff($this->runningMidday);
		$diffDays = (int)$diff->days;
		if(!$diffDays) return;
		
		$running_scenario->runned_day = $this->runningMidday->format('Y-m-d');
		$running_scenario->store();
		
		foreach($running_scenario->_many_running_scenario_step->where('runned_day IS NULL') as $step){
			$expectedRunDay = new DateTime($step->expected_run_day);
			$expectedRunDay->modify('+'.$diffDays.' day');
			$running_scenario->expected_run_day = $expectedRunDay->format('Y-m-d');
			$running_scenario->store();
		}
		
	}
	protected function getNewRunningScenario(){
		$running_scenario = $this->db['running_scenario'];
		$scenario = $this->getScenarioChosen();
		
		$steps = $scenario->many('scenario_step');
		if(!count($steps)){
			throw new InvalidArgumentException('No steps founds in that scenario');
		}
		$step = $steps
			->orderBy('id')
			->sort('ASC')
			->limit(1)
			->getRow();
		
		$running_scenario_step = [];
		foreach($scenario->many('scenario_step') as $row){
			$runningScenarioStep = $this->db['running_scenario_step']->newEntity($row);
			$running_scenario_step[] = $runningScenarioStep;
		}
		
		
		$rd = clone $this->runningMidday;
		
		$previous = $this->getPreviousStep();
		if($previous){
			$length = (int)$previous->length;
			$expectedRunDay = new DateTime($previous->expected_run_day);
			$diff =  $expectedRunDay->diff($this->runningMidday);
			$diffDays = (int)$diff->days;
			$remaining = $length - $diffDays;
			if($remaining>0){
				$rd->modify('+'.$remaining.' day');
			}
		}
		
		$stepRd = clone $rd;
		foreach($running_scenario_step as $row){
			$row->expected_run_day = $stepRd->format('Y-m-d');
			if((int)$row->length!=0){
				$stepRd->modify('+'.$row->length.' day');
			}
		}
		
		$oldestPaperwork = $this->getOldestPaperwork();
		$oldestPaperwork->state = 'standard';
		$oldestPaperwork->store();
		
		$running = $running_scenario->newEntity($scenario);
		$running->import([
			'create_day'=>$this->runningMidday->format('Y-m-d'),
			'exepected_run_day'=>$rd->format('Y-m-d'),
			'running_type'=>'current',
			'step'=>0,
			'current_step_length'=>$step->length,
			'_one_debtor'=>$this->debtor,
			'_one_paperwork'=>$oldestPaperwork,
			'_one_scenario'=>$scenario,
			'_many_running_scenario_step_x_'=>$running_scenario_step,
		]);
		
		$running_scenario[] = $running;
		return $running;
	}
	protected function getOldestPaperwork(){
		return $this->db['paperwork']
			
			->where('debtor_primary = ?',[$this->debtor->primary])
			
			->where('state IS NULL OR state = ?',['standard'])
			
			->where('lettrage IS NULL')
			->where('type_ecriture = ?',['FACT'])
			
			->where('date_echeance IS NOT NULL')
			->orderBy('date_echeance')
			->sort('ASC')
			->limit(1)
			
			->getRow();
	}
	
	protected function getConcernedPaperworks($scenario){
		
		$scenarioStart = clone $this->runningMidday;
		$modify = $scenario->start_day;
		if($modify){
			if(substr($modify,0,1)!='-'){
				$modify = '+'.substr($modify,1);
			}
			$modify .= ' day';
			$scenarioStart->modify($modify);
		}
		
		$today = $scenarioStart->format('Y-m-d');
		
		return $this->db['paperwork']
			->where('date_echeance >= ?',[$today])
			->orderBy('date_echeance')
			->sort('ASC')
			->where('debtor_primary = ?',[$this->debtor->primary])
			->where('state IS NULL OR state = ?',['standard'])
			->getAll()
		;
	}
	
	protected function restartScenario($currentScenario){
		$this->db->exec('DELETE FROM running_scenario WHERE debtor_id = ? AND running_type = ?',[$this->debtorId,'previous']);
		$currentScenario->running_type = 'previous';
		$currentScenario->store();
		return $this->runScenario();
	}
	
	protected function checkTodayPaperworkInScenario($paperwork,$scenario){
		$realStartTime = clone $this->runningMidday;
		$termTime = new DateTime($paperwork->date_echeance);
		//$termTime = new DateTime("2016-11-26"); //dev
		$modify = $scenario->start_day;
		if($modify){
			if(substr($modify,0,1)=='-'){
				$modify = '+'.substr($modify,1);
			}
			else{
				$modify = '-'.$modify;
			}
			$modify .= ' day';
			$realStartTime->modify($modify);
		}
		return $realStartTime->getTimestamp() >= $termTime->getTimestamp();
	}
	
	
	protected function getScenarioStep($scenario){
		if($this->db->tableExists('running_scenario_step')){
			return $scenario->_many_running_scenario_step
				->orderBy('id')
				->sort('ASC')
				->limit(1)
				->offset($scenario->step)
				->getRow();
		}
	}
	protected function getPreviousStep(){
		$scenario = $this->getPreviousRunningScenario();
		if($scenario){
			return $this->getScenarioStep($scenario);
		}
	}
	
	protected function runScenario(){
		
		$db = $this->db;
		
		$currentScenario = $this->getCurrentRunningScenario();
		$oldestPaperwork = $this->getOldestPaperwork();
		if($currentScenario){
			$scenario = $currentScenario;
			$currentPaperwork = $currentScenario->_one_paperwork;
		}
		else{
			$scenario = $this->getScenarioChosen();
			$currentPaperwork = $oldestPaperwork;
		}
		
		if(!$currentPaperwork||!$this->checkTodayPaperworkInScenario($currentPaperwork,$scenario)) return;
		
		if($currentScenario){			
			
			if($currentPaperwork->state!='standard'){
				$currentScenario->running_type = 'suspended';
				$currentScenario->suspended_day = $this->runningMidday->format('Y-m-d');
				$currentScenario->store();
				return $this->runScenario();
			}
			
			if($currentScenario->_one_paperwork->id!=$oldestPaperwork->id || $currentPaperwork->payed){
				return $this->restartScenario($currentScenario);
			}
		}
		
		if(!$currentScenario){
			$currentScenario = $this->getNewOrResumeRunningScenario($oldestPaperwork);
		}
		
		
		if($currentScenario->step==0&&$this->debtor->use_interval){
			$term = new DateTime($currentPaperwork->date_echeance);
			$diff = $term->diff($this->runningMidday);
			$diffDays = (int)$diff->days;
			
			$i = 0;
			$length = 0;
			foreach($currentScenario->_many_running_scenario_step as $step){
				$length += (int)$step->length;
				if($length>=$diffDays){
					break;
				}
				$i++;
			}
			$currentScenario->step = $i;
		}
		
		$currentStep = $this->getScenarioStep($currentScenario);
		
		if(!$currentStep) return; //scenarion ended
		
		$expectedRunDay = new DateTime($currentStep->expected_run_day);
		
		if(!($expectedRunDay <= $this->runningMidday)) return;
		
		
		$this->makeAction($currentStep);
		
		if($currentScenario->step==0){
			$currentScenario->runned_day = $this->runningMidday->format('Y-m-d');
		}
		
		$currentScenario->step++;
		$currentScenario->store();
		
		$currentStep->runned_day = $this->runningMidday;
		$currentStep->store();
		
		$this->progress('finish',true);
		return true;
	}
	protected function makeAction($step){
		
		switch($step->type){
			case 'LETTRE_VERTE':
            case 'LETTRE_ECOPLI':
            case 'LETTRE_PRIORITAIRE':
            case 'LETTRE_RECOMMANDEE_AVEC_AR':
            case 'LETTRE_RECOMMANDEE':
				$this->runCmd('my:debtor:service:postal',['step_id'=>$step->id]);
			break;
            case 'sms':
                $this->runCmd('my:debtor:service:sms:debtor', ['step_id'=>$step->id]);
            break;
            case 'email':
				$this->runCmd('my:debtor:service:email:debtor', ['step_id'=>$step->id]);
            break;
            case 'tel':
				$this->runCmd('my:debtor:service:appel:debtor', ['step_id'=>$step->id]);
            break;
		}
	}
}
