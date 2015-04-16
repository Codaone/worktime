<?php
class User {
	// Config values
	public $userTable = "users";

	// Public variables
	public $error = "";
	
	// User variables
	private $user_id;
	private $username;
	private $group;
	private $name;
	private $lastlogin;
	
	// Other variables
	/** @var  $db Database */
	private $db;
	private $userData;
	
	public function __construct($db) {
		session_start();
		$this->db = $db;
		if(!isset($_SESSION['auth'])){
			$_SESSION['auth'] = 0;
		}	
	}

	// Public methods
	public function getUserId() {
		return $this->user_id;
	}
	public function getUsername() {
		return $this->username;
	}
	public function getGroup() {
		return $this->group;
	}
	public function getName() {
		return $this->name;
	}
	public function getLastLogin() {
		return $this->lastlogin;
	}	
	public function getIp() {
		return getenv("REMOTE_ADDR");
	}
	public function validateEmail($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	public function login($username, $password) {
		$this->username = $username;
		if(empty($username) || empty($password)) {
			$this->error = "Username or password is invalid";
			return false;
		}	
		else {
			$db = $this->db;
			$password = $this->passwordHash($password,$username);
			$data = $db->first("SELECT * FROM `".$this->userTable."` WHERE `user`='".$db->escape($username)."' AND `password`='".$db->escape($password)."' LIMIT 1");
			if(count($data)>0 && !empty($data)){
				if($data['active']==0){
					$this->error = "User is not active";
					return false; 
				}
				$this->user_id 		= $data['id'];
				$this->username 	= $data['user'];
				$this->group 		= $data['group'];
				$this->name 		= $data['name'];
				$this->lastlogin 	= $data['last_login'];
				unset($data['password']);
				$this->userData = $data;
				$db->update($this->userTable, array("last_login"=>date("Y-m-d h:i:s"), "last_ip" => $this->getIp()), "`id`=".$data['id']);
				$this->setSession();
				return true;
			} else {
				$this->error = "Username or password is invalid";
				return false;
			}
		}
	}
	public function logout(){
		$_SESSION['auth'] = 0;
		$_SESSION['access'] = "";
		$_SESSION['username'] = "";
		session_unset();
		session_destroy();
		return true;
	}
	public function isLoggedIn() {
		if( isset($_SESSION['access']) && 
			isset($_SESSION['username']) &&
			$_SESSION['access'] == md5($_SESSION['username'].session_id()) &&
			$_SESSION['auth'] == 1) {
			$this->setUserData();
			return true;
		}
		else {
			return false;
		}
	}
	public function saveNewUser($values = array(),$andLogin = false){
		$db = $this->db;
		$temp = $values["password"];
		$values["password"] = $this->passwordHash($values["password"],$values["username"]);
		$accepted = array();
		$columns = $db->columns($this->userTable);
		foreach($values as $col=>$value){
			if(in_array($col,$columns)) $accepted[$col] = $value;
		}
		$id = $db->insert($this->userTable,$accepted);
		if($id===false) return false;
		if($andLogin) return $this->login($values["username"],$values["password"]);
		return true;
	}
	// not done
	function activate(){
		if (empty($this->userID)) $this->error('No user is loaded', __LINE__);
		if ( $this->is_active()) $this->error('Allready active account', __LINE__);
		$res = $this->query("UPDATE `{$this->dbTable}` SET {$this->tbFields['active']} = 1 
			WHERE `{$this->tbFields['userID']}` = '".$this->escape($this->userID)."' LIMIT 1");
		if (@mysql_affected_rows() == 1){
			$this->userData[$this->tbFields['active']] = true;
			return true;
		}
		return false;
	}

	private function setUserData(){
		$db = $this->db;
		$data = $db->first("SELECT * FROM `".$this->userTable."` WHERE `user`='".$db->escape($_SESSION['username'] )."' AND `id`='".$db->escape($_SESSION['user_id'])."' LIMIT 1");
		if(count($data)>0 && !empty($data)){
			if($data['active']==0){
				return false;
			}
			$this->user_id 		= $data['id'];
			$this->username 	= $data['user'];
			$this->group 		= $data['group'];
			$this->name 		= $data['name'];
			$this->lastlogin 	= $data['last_login'];
			unset($data['password']);
			return true;
		}
		return false;
	}
	
	// Private methods
	private function setSession() {	
		session_regenerate_id();
		$_SESSION['auth'] = 1;
		$_SESSION['access'] = md5($this->username.session_id());
		$_SESSION['username'] = $this->username;
		$_SESSION['user_id'] = $this->user_id;
	}

	private function passwordHash($password,$username){
		//"salt:password:username"
		return sha1($username.sha1($password));
	}
}
?>