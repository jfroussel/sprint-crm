<?php
namespace App;

use App\Model\Db;
use App\Route\User;
use App\Route\Route;
use RedCat\Route\Request;
use RedCat\Strategy\Di;
use FoxORM\MainDb;

class AbstractController{
	protected $di;
	protected $db;
	protected $user;
	protected $request;	
	protected $route;
	protected $needAuth;
	protected $mainDb;
	function __construct(Di $di,Db $db ,Request $request, User $user, Route $route, MainDb $mainDb){
        $this->di = $di;
        $this->db = $db;
        $this->mainDb = $mainDb;
        $this->request = $request;
        $this->user = $user;
        $this->route = $route;
    }
	
	function checkAuth(){
		if($this->needAuth&&!$this->user->id){
			$this->route->redirect('auth/login');
		}
	}
}
