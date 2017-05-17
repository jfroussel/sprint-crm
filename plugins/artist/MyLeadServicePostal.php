<?php
namespace App\Artist;

use RedCat\Strategy\Di;
use RedCat\Artist\ArtistPlugin;
use FoxORM\MainDb;
use Redis;
use App\Api\LeadLetter;



class MyLeadServicePostal extends ArtistPlugin{

    protected $description = "Send letter on demand with Service-Postal";
    protected $args = [
        'step_id'=>'step id'
    ];
    protected $opts = [];
    protected $boolOpts = [];
    protected $apiletter;
    function __construct($name = null, Redis $redis, MainDb $db, Di $di){
        parent::__construct($name);
        $this->redis = $redis;
        $this->db = $db;
        $this->di = $di;
    }

    protected function exec(){

        $id = $this->input->getArgument('step_id');
        $step = $this->db['running_scenario_step'][$id];
        $template = $step->_one_template;
        $lead = $step->_one_running_scenario->_one_lead;
        $instanceUser = $step->_one_instance;

        $this->apiletter = $this->di->get(LeadLetter::class,[
            'db'=>$this->db,
            'instance_id'=>$step->instance_id,
        ]);

        $this->apiletter->sendLetter($lead->id,$step->type,$template->message,$template->id);



    }
}
