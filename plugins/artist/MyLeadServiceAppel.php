<?php
namespace App\Artist;

use RedCat\Artist\ArtistPlugin;
use FoxORM\MainDb;
use Redis;
use App\Api\LeadAppel;
use RedCat\Strategy\Di;
use DateTime;

class MyLeadServiceAppel extends ArtistPlugin{

    protected $description = "Send agenda message";
    protected $args = [
        'step_id'=>'step id'
    ];
    protected $opts = [];
    protected $boolOpts = [];
    protected $apiappel;
    protected $db;
    protected $di;
    protected $redis;
    protected $runningDate;
    function __construct($name = null, Redis $redis, MainDb $db, Di $di){
        parent::__construct($name);
        $this->redis = $redis;
        $this->db = $db;
        $this->di = $di;
    }

    protected function exec(){
        $this->runningDate = new DateTime();
        $id = $this->input->getArgument('step_id');
        $step = $this->db['running_scenario_step'][$id];
        $lead = $step->_one_running_scenario->_one_lead;

        $this->apiappel = $this->di->get(LeadAppel::class,[
            'db'=> $this->db,
            'instance_id'=> $step->instance_id,
        ]);

        $message = 'Rappeler le débiteur <a href="#lead/update?id='.$lead->id.'">'.$lead->debit_first_name.' '.$lead->debit_last_name.'</a>';
        $date = $this->getMiddayNext()->format('Y-m-d');
        $this->apiappel->addToAgenda($lead->id, $date, $message);

    }
    protected function getMiddayNext(){
        $hour =	$this->runningDate->format('His');
        $hour = (int)ltrim($hour,'0');
        $midday = 120000;
        $dateTime = new DateTime($this->runningDate->format('Y-m-d'));
        if($hour>$midday){
            $dateTime->modify('+1 day');
        }
        return $dateTime;
    }
}
