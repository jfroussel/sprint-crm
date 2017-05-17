<?php
namespace App\Api;

use DateTime;
use App\AbstractController;
 
abstract class AbstractSms extends AbstractController{
    
    protected $user_id;
    protected $instance_id;
    protected $apiKey;
    protected $apiUrl;
    
    function __construct($db, $instance_id, $urlBaseHref, $apiUrl, $apiKey, $user_id = null){
        $this->db = $db;
        $this->user_id = $user_id;
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->instance_id = $instance_id;
        $this->urlBaseHref = $urlBaseHref;

    }
	
    protected function curlSender($url, $body, $header){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

    }

}
