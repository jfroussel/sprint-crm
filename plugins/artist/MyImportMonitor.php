<?php
namespace App\Artist;

use RedCat\Artist\ArtistPlugin;
use RedCat\Framework\App;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\Helper;

use Redis;

class MyImportMonitor extends ArtistPlugin{
	
	protected $tickTime = 100000;
	
	protected $description = "Monitor for importations";
	protected $args = [
		
	];
	protected $boolOpts = [
	];
	protected $opts = [
		
	];
	protected $ns;
	protected $contexts = ['routine','user'];
	
	function __construct($name = null, Redis $redis){
		parent::__construct($name);
		$this->redis = $redis;
	}
	
	protected function exec(){
		$this->ns = 'my:tasks:running:import:';
		while(true){
			$this->refreshView();
			usleep($this->tickTime);
		}
	}
    
	protected function refreshView(){
		$i = 1;		
		$rows = [];
		$this->output->write("\033c");
		
		$errors = [];
		
		foreach($this->contexts as $context){
			$ns = $this->ns.$context.':progress';
			foreach($this->redis->hGetAll($ns) as $pid=>$json){
				$params = json_decode($json,true);
				if(isset($params['error'])){
					$errors[] = $params;
				}
				if(!isset($params['pid'])) continue;
				$rows[] = $this->makeRow($params);
			}
		}
		
		
		$table = new Table($this->output);
		$table
			->setHeaders(['stack','pid','user','table         ','state       ','current/total','progression               ','elapsed','remaining'])
			->setRows($rows)
		;
		$table->render();
        
        foreach($errors as $error){
			$this->output->writeln('error on file '.$error['file'].': '.$error['error']);
		}
	}
	protected function makeRow($params){		
		$current = $params['current'];
		$total = $params['total'];
		$start = $params['start'];
		$percent = $current*100/$total;
		
		if($total){
			$barWidth = 15;
			$barCharacter = '=';
			$progressChar = '>';
			$emptyBarChar = '-';
			$completeBars = floor($percent * $barWidth / 100);
			$emptyBar = $barWidth - $completeBars;
			$completeBarsStr = $completeBars > 0?str_repeat($barCharacter, $completeBars):'';
			$emptyBarStr = $emptyBar > 0?$progressChar.str_repeat($emptyBarChar, $emptyBar):'';
			$bar = '['.$completeBarsStr.$emptyBarStr.']';
		}
		else{
			$bar = '';
		}
		
		$row = [];
		$row[0] = $params['context'];
		$row[1] = $params['pid'];
		$row[2] = $params['user'];
		$row[3] = $params['table'];
		$row[4] = $params['state'];
		$row[5] = ($current?:'0').'/'.$total;
		$row[6] = $bar.' '.round($percent).'%';
		
		$elapsed = time()-$start;
		$estimated = $start&&$current&&$total ?  round((time() - $start) / $current * $total) : 0;
		
		$remaining = $estimated-$elapsed;
		
		$row[7] = Helper::formatTime($elapsed);
		$row[8] = Helper::formatTime($remaining);
		
		return $row;
	}
}
