<?php
namespace App\Api;

use App\AbstractController;

abstract class AbstractAppel extends AbstractController {
	
	protected $user_id;
    protected $instance_id;
	
	function __construct($db, $instance_id){
        $this->db = $db;
        $this->instance_id = $instance_id;

    }

}
