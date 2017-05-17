<?php
namespace App\Artist;

use RedCat\Artist\ArtistPlugin;
use FoxORM\MainDb;
use Redis;
use App\Api\DebtorMail;
use RedCat\Strategy\Di;


class MyDebtorServiceEmail extends ArtistPlugin{

    protected $description = "Send email on demand with Service-Postal";
    protected $args = [
        'step_id'=>'step id'
    ];
    protected $opts = [];
    protected $boolOpts = [];
    protected $apimail;
    protected $db;
    protected $di;
    protected $redis;
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
        $debtor = $step->_one_running_scenario->_one_debtor;
        $instanceUser = $step->_one_instance;

        $this->apimail = $this->di->get(DebtorMail::class,[
            'db'=> $this->db,
            'instance_id'=> $step->instance_id,
            'user_id'=> null,
            'urlBaseHref'=> $this->di['urlBaseHref'],
        ]);

        $contact_id = $this->db['contact']
            ->unSelect()
            ->select('id')
            ->where('principal = 1 AND debtor_primary = ?',[$debtor->primary])
            ->getCell();

        $data = [];

        $data['contact_id'] = $contact_id;
        $data['template_id'] = $template->id;
        $data['message'] = $template->message;
        $data['debtor_id'] = $debtor->id;
        $data['expediteur'] = 'info@mycreance.com';

        $this->apimail->sendEmail($data);



    }
}
