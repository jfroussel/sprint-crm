<?php
namespace App\Artist;

use RedCat\Artist\ArtistPlugin;
use FoxORM\MainDb;
use ForceUTF8\Encoding;
use PDOException;
use Redis;
use InvalidArgumentException;
use DateTime;


class MyScenarioRunlead extends ArtistPlugin{

	protected $db;
	protected $pid;
	protected $description = "Run a scenario for a lead";
	protected $args = [
		'lead'=>'lead id',
	];
	protected $ns;
	protected $leadId;
	protected $lead;
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

		$this->ns = 'my:tasks:running:scenariolead:';

		$this->leadId = $this->input->getArgument('lead');
		$this->lead = $this->db['lead'][$this->leadId];

		if(is_null($this->lead)){
			throw new InvalidArgumentException('Invalid lead id');
		}

		$this->progress = [
			'pid'=>$this->pid,
			'lead'=>$this->leadId,
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
		if($running_scenario->exists()&&$running_scenario->columnExists('lead_id')){
			$scenario = $running_scenario
				->where('lead_id = ?',[$this->leadId])
				->where('running_type = ?',['current'])
				->limit(1)
				->getRow()
			;
			return $scenario;
		}
	}
	protected function getScenarioChosen(){
		$superrootInstanceId = $this->db['user']->unSelect()->select('id')->where('is_superroot = 1')->getCell();
		$scenario = $this->db['scenario']
			->where('instance_id = ?',[$superrootInstanceId])
			->where('active = 1')
			->where('category = ?',[$this->lead->category])
			->where('type = ?',[$this->lead->type])
			->getRow();
		if(!$scenario){
			throw new InvalidArgumentException("Unable to find scenario for lead category '{$this->lead->category}' and type = '{$this->lead->type}'");
		}
		return $scenario;
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
	protected function getNewRunningScenario($scenario){
		
		$running_scenario = $this->db['running_scenario'];

		$steps = $scenario->many('scenario_step');
		if(!count($steps)){
			throw new InvalidArgumentException('No steps founds in that scenario');
		}
		
		$step = $steps
			->getClone()
			->orderBy('id')
			->sort('ASC')
			->limit(1)
			->getRow();

		$running_scenario_step = [];
		
		$rd = clone $this->runningMidday;
		$stepRd = clone $rd;
		foreach($steps as $row){
			$runningScenarioStep = $this->db['running_scenario_step']->newEntity($row);
			$runningScenarioStep->expected_run_day = $stepRd->format('Y-m-d');
			if((int)$runningScenarioStep->length!=0){
				$stepRd->modify('+'.$row->length.' day');
			}
			$running_scenario_step[] = $runningScenarioStep;
		}

		$running = $running_scenario->newEntity($scenario);
		$running->import([
			'create_day'=>$this->runningMidday->format('Y-m-d'),
			'exepected_run_day'=>$rd->format('Y-m-d'),
			'running_type'=>'current',
			'step'=>0,
			'current_step_length'=>$step->length,
			'lead_id'=>$this->lead->id,
			'scenario_id'=>$scenario->id,
			'_xmany_running_scenario_step'=>$running_scenario_step,
		]);

		$running_scenario[] = $running;
		return $running;
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

	protected function runScenario(){

		$db = $this->db;
		
		$lead = $this->lead;
		
		$currentScenario = $this->getCurrentRunningScenario();
		if($currentScenario){
			$scenario = $currentScenario;
		}
		else{
			$scenario = $this->getScenarioChosen();
		}

		if($currentScenario&&$lead->status=='completed'){
			$currentScenario->delete();
			return;
		}

		if(!$currentScenario){
			$currentScenario = $this->getNewRunningScenario($scenario);
		}

		$currentStep = $this->getScenarioStep($currentScenario);
		
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
		
		$nbstep = $this->db['running_scenario_step']
			->unSelect()
			->select('COUNT(*)')
			->where('running_scenario_id = ?',[$currentScenario->id])
			->getCell();
		
		if( ((int)$currentScenario->step) >= $nbstep ){ //scenario last step
			$lead->status = 'completed';
			$lead->store();
			$currentScenario->delete();
		}
		
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
				$this->runCmd('my:lead:service:postal',['step_id'=>$step->id]);
			break;
            case 'sms':
                $this->runCmd('my:lead:service:sms', ['step_id'=>$step->id]);
            break;
            case 'email':
				$this->runCmd('my:lead:service:email', ['step_id'=>$step->id]);
            break;
            case 'tel':
				$this->runCmd('my:lead:service:appel', ['step_id'=>$step->id]);
            break;
		}
		
	}
}
