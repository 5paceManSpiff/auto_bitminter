<?php

/**
 * PHP version 5
 *
 * requires the cURL php library and simple_html_dom.php
 *
 * @author Aaron Landis <alspore@gmail.com>
 * @version 1.0
 * @package PlaceLocalInclude
 * @subpackage bitminter_library.php
 */

require_once('simple_html_dom.php');

class BitminterManager{
	private $cookie_path = 'cookie.txt';
	private $username = null;
	private $password = null;

	function addWorker($name, $pass){

		//action='...'
		$bitminter_workerpage = 'https://bitminter.com/members/workers';
		$bitminter_add_workerpage = 'https://bitminter.com/ajax_request/';

		//attempts to open worker page for scraping
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $bitminter_workerpage);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_path);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_path);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		$workerpage = curl_exec($curl);

		//scrapes 'token'
		$workerpage_html = str_get_html($workerpage);
		$post_fields = '';
		$workerpage_inputs = 0;

		foreach($workerpage_html->find('input') as $element){
			$workerpage_inputs++;
			if($workerpage_inputs == 1){
				$post_fields .= $element->name.'='.$name.'&';
			}elseif($workerpage_inputs == 2){
				$post_fields .= $element->name.'='.$pass.'&';
			}else{
				$post_fields .= $element->name.'='.$element->value.'&';
			}
		}

		foreach($workerpage_html->find('form') as $element){
			$bitminter_add_workerpage .= $element->id.'-00/';
		}

		curl_setopt($curl, CURLOPT_URL, $bitminter_add_workerpage);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_path);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_path);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		$workertable = curl_exec($curl);
		curl_close($curl);

		return $workertable;
	}

	function setCookiePath($cp){
		$this->cookie_path = $cp;
	}

	function setUsername($u){
		$this->username = $u;
	}

	function setPassword($p){
		$this->password = $p;
	}

	function setUserPass($u, $p){
		$this->username = $u;
		$this->password = $p;
	}

	function generateCookie(){

		//user info for myopenid
		$openid_identifier = 'http://'.$this->username.'.pip.verisignlabs.com';

		//action='...'
		$bitminter_login = 'https://bitminter.com/openid/login';
		$verisignlabs_server_login = 'http://pip.verisignlabs.com/server';
		$verisignlabs_login = 'https://pip.verisignlabs.com/login_user.do';


		if(file_exists($this->cookie_path)){
			unlink($this->cookie_path);
		}

		//post data for first_request
		$bitminter_loginpage_post_fields = 'openid_identifier='.$openid_identifier.'&openid_username='.$this->username;

		//attempts to post login form to bitminter
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $bitminter_login);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $bitminter_loginpage_post_fields);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_path);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_path);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
		$bitminter_loginpage = curl_exec($curl);

		//gets openid info from bitminter
		$bitminter_loginpage_html = str_get_html($bitminter_loginpage); //sets dom object from string
		$step2_post_fields = '';
		$first = true;
		foreach($bitminter_loginpage_html->find('input') as $element) { //sets openid post data scraped from bitminter
			if($first) { $step2_post_fields .= $element->name.'='.$element->value; $first = false; }
			else { $step2_post_fields .= '&'.$element->name.'='.$element->value; }
		}

		//attempts to get redirected from verisignlabs openid server
		curl_setopt($curl, CURLOPT_URL, $verisignlabs_server_login);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $step2_post_fields);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_path);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_path);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		$step2 = curl_exec($curl);

		//gets form data for verisignlabs login
		$step2_html = str_get_html($step2);
		$step3_post_fields = '';
		$first = true;

		//formats post data for login form
		foreach($step2_html->find('input') as $element){
			if($element->name == 'username'){
				$step3_post_fields .= $element->name.'='.$this->username;
			}elseif($element->name == 'password'){
				$step3_post_fields .= '&'.$element->name.'='.$this->password;
			}
		}

		//attempts to post login form
		curl_setopt($curl, CURLOPT_URL, $verisignlabs_login);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $step3_post_fields);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookie_path);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookie_path);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		$step3 = curl_exec($curl);
		curl_close($curl);

		$return_value = false;
		if(strlen($step3) > 0) { $return_value = true; }
		return $return_value;
	}
}
?>