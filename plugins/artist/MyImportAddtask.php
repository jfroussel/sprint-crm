<?php
namespace App\Artist;

use RedCat\Artist\ArtistPlugin;
use Redis;

class MyImportAddtask extends ArtistPlugin{
	
	protected $description = "Add a csv import task to the redis stack that will be handled by daemon";
	protected $args = [
		'user'=>'user id',
		'table'=>'table name',
		'context'=>'context',
	];
	protected $opts = [
	];
	protected $boolOpts = [
		
	];
	function __construct($name = null, Redis $redis){
		parent::__construct($name);
		$this->redis = $redis;
	}
	
	protected function exec(){
		$user = $this->input->getArgument('user');
		$table = $this->input->getArgument('table');
		$context = $this->input->getArgument('context');
		
		$noduplicate = true;
		
		if(!$context){
			$context = 'user';
		}
		$key = 'import:'.$context;
		$artist = $this->cwd.'artist';
		if($user&&$table){
			$task = [
				'key'=>$key,
				'cmd'=>"php $artist my:import:runtask $user $table $context",
				'uniq'=>"import:$user:$table",
				'metadata'=>['user'=>$user,'table'=>$table,'context'=>$context],
			];
			$json = json_encode($task);
			
			if($noduplicate){
				$waiting = $this->redis->lRange('my:tasks:waiting',0,-1);
				$count = count($waiting);
				for($i=0;$i<$count;$i++){
					if($waiting[$i]===$json){
						return;
					}
				}
			}
			
			$this->redis->rPush('my:tasks:waiting',$json);
		}
	}
}
