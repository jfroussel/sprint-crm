<?php
namespace App\Modules\Plugins;
use FoxORM\Exception\Exception;
use RedCat\Route\Request;
use App\Model\Db;
use InvalidArgumentException;
use App\AbstractController;
class Datatables extends AbstractController{
	protected $needAuth = true;
	function __invoke(){
        $tableName = $this->request->table;
        $db = $this->db;
        switch($this->user->type){
            case 'lead':
               $db = $this->mainDb;
                break;
        }
		if($this->user->is_superroot){
		    switch($tableName){
                case 'lead':
                $db = $this->mainDb;
                break;
            }
        }

		$table = clone $db[$tableName];
		
		if(!$table->exists()){
			return false;
		}
		
		//$db->debug();
        switch($this->user->type){
            case 'lead':
                switch($tableName){
                    case 'lead':
                    case 'lead_invoice':
                        $table->where('user_id = ?', [$this->user->id]);
                        break;
                    default:
                        throw new InvalidArgumentException('Access to this table is not allowed for lead');
                        break;
                }
                break;
        }
		
		$where = [];
		foreach($this->request as $k=>$v){
			$x = explode('_',$k);
			if(count($x)>1){
				$action = array_shift($x);
				$column = implode('_',$x);
				$this->resolveAlias($tableName,$column,$v);
				if($table->columnExists($column)){
					switch($action){
						case 'whereNot':
						case 'ne':
						case 'neq':
						case 'notEqual':
							$where[$column]['neq'][] = $v;
						break;
						case 'where':
						case 'e':
						case 'eq':
						case 'equal':
							$where[$column]['eq'][] = $v;
						break;
						case 'like':
						case 'likeBoth':
							$where[$column]['like'][] = $v;
						break;
						case 'l':
						case 'left':
						case 'likeLeft':
							$where[$column]['left'][] = $v;
						break;
						case 'r':
						case 'right':
						case 'likeRight':
							$where[$column]['right'][] = $v;
						break;
					}
				}
			}
		}
		
		$table->openWhereAnd();
		foreach($where as $column=>$a){
			$table->openWhereOr();
			foreach($a as $type=>$a2){
				foreach($a2 as $v){
					if(!(is_object($v)||is_array($v))){
						$v = [$v];
					}
					foreach($v as $val){
						switch($type){
							case 'neq':
								if(!$val){
									$table->where($table->esc($column).' IS NOT NULL');
								}
								else{
									$table->where($table->esc($column).' != ?',[$val]);
								}
							break;
							case 'eq':
								if(!$val){
									$table->where($table->esc($column).' IS NULL');
								}
								else{
									$table->where($table->esc($column).' = ?',[$val]);
								}
							break;
							case 'like':
								$table->likeBoth($table->esc($column), $val);
							break;
							case 'left':
								$table->likeLeft($table->esc($column), $val);
							break;
							case 'right':
								$table->likeRight($table->esc($column), $val);
							break;
						}
					}
				}
			}
			$table->closeWhere();
		}
		$table->closeWhere();
		
		$data = [];
		
		$searchable = [];
		$orderable = [];
		$columns = [];
		$existingColumns = [];
		$emptyColumns = [];
		foreach($this->request->columns as $col){
			$column = $col->data;
			
			if($table->columnExists($column)){
				$existingColumns[] = $column;
			}
			else{
				$emptyColumns[] = $column;
			}
			
			$columns[] = $column;
			if($col->searchable){
				$searchable[] = $column;
			}
			if($col->orderable){
				$orderable[] = $column;
			}
		}
		
		$table->unSelect();
		foreach($existingColumns as $column){
			$table->selectMain($table->esc($column));
		}
		
		if($this->request->order){
			foreach($this->request->order as $order){
				$column = $columns[$order->column];
				if(!$table->columnExists($column)) continue;
				$table->orderByMain($column);
				$table->sort($order->dir);
			}
		}
		
		
		$data['recordsTotal'] = count($table);
		$search = $this->request->search->value;
		if($search){
			$table->openWhereOr();
			foreach($searchable as $column){
				if(!$table->columnExists($column)) continue;
				$table->likeBoth($table->esc($column),$search);
			}
			$table->closeWhere();
		}
		
		$table->limit($this->request->length);
		$table->offset($this->request->start);
		
		$data['draw'] = $this->request->draw;
		try{
			$data['recordsFiltered'] = (int)$table->getClone()->unLimit()->count();
			
			//$data['data'] = $table->getArray();
			$result = [];
			foreach($table->getAll() as $row){
				$result[] = $row->getDynamicData();
			}
			$data['data'] = $result;
			
			if(!empty($emptyColumns)){
				foreach($data['data'] as &$row){
					foreach($emptyColumns as $col){
						if(!isset($row[$col])){
							$row[$col] = '';
						}
					}
				}
			}
		}
		catch(Exception $e){
			$data['error'] = $e->getMessage();
		}
		
		return $data;
	}
	
	protected function resolveAlias($tableName,&$column,&$v){
		if($tableName=='paperwork'&&$column=='debtor_id'){
			$column = 'debtor_primary';
			$v = $this->db['debtor'][$v]->primary;
		}
        if($tableName=='contact'&&$column=='debtor_id'){
            $column = 'debtor_primary';
            $v = $this->db['debtor'][$v]->primary;
        }
		if($tableName=='debtor'&&$column=='paperwork_id'){
			$column = 'primary';
			$v = $this->db['paperwork'][$v]->debtor_primary;
		}
	}
}
