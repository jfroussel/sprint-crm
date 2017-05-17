<?php
namespace App\Artist;


use RedCat\Artist\ArtistPlugin;
use FoxORM\MainDb;
use Redis;

class MyLeadRoutine extends ArtistPlugin{
	
	protected $description = "Routine to add all night lead scenario tasks";
	protected $args = [];
	protected $opts = [];
	protected $boolOpts = [];
	function __construct($name = null, Redis $redis, MainDb $db){
		parent::__construct($name);
		$this->redis = $redis;
		$this->db = $db;

	}

	protected function exec(){
	    //$this->db->debug();
		foreach($this->db['lead']->where('startscenario = 1') as $debtor){
			$id = $debtor->id;
			$artist = $this->cwd.'artist';
			$task = [
				'key'=>'scenariolead',
				'cmd'=>"php $artist my:scenario:runlead $id",
				'uniq'=>"scenario:lead:$id",
			];
			$json = json_encode($task);
			$this->redis->rPush('my:tasks:waiting',$json);


		}
	}
}
