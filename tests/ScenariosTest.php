<?php
/**
 * Created by PhpStorm.
 * User: jeff
 * Date: 03/01/17
 * Time: 16:06
 */

namespace MyApp\tests;


use MyApp\Scenarios;


class ScenariosTest extends FixturesTestCase {


    protected $data;


    public function setUp(){
        $this->data = $this->getGlobalRedcat();

    }

   public function testScenario(){

        //$this->scenario = new Scenarios($this->db, $this->route, $this->di, $this->request, $this->user);
        $this->data->get(Scenarios::class, ['toto' => 'titi']);
       //var_dump($this->data);

   }

}
