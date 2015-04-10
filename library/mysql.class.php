<?php
class Database {
	public $showErrors = true;
	var $server   = ""; //database server
	var $user     = ""; //database login name
	var $pass     = ""; //database login password
	var $database = ""; //database name
	var $pre      = ""; //table prefix



	#######################
	//internal info
	var $record = array();
	var $rows	= ""; //Table rows
	var $error 	= "";
	var $errno 	= 0;

	//table name affected by SQL query
	var $field_table= "";

	//number of rows affected by SQL query
	var $affected_rows = 0;

	var $link_id = 0;
	var $query_id = 0;


	#-#############################################
	# desc: constructor
	function Database($server, $user, $pass, $database, $pre=''){
		$this->server	= $server;
		$this->user		= $user;
		$this->pass		= $pass;
		$this->database	= $database;
		$this->pre		= $pre;
	}#-#constructor()


	#-#############################################
	# desc: connect and select database using vars above
	# Param: $new_link can force connect() to open a new link, even if mysql_connect() was called before with the same parameters
	function connect($new_link=false) {
		$this->link_id=@mysql_connect($this->server,$this->user,$this->pass,$new_link);

		if (!$this->link_id) {//open failed
			$this->oops("Could not connect to server: <b>$this->server</b>.");
			}

		if(!@mysql_select_db($this->database, $this->link_id)) {//no database
			$this->oops("Could not open database: <b>$this->database</b>.");
			}

		// unset the data so it can't be dumped
		$this->server='';
		$this->user='';
		$this->pass='';
		$this->database='';
	}#-#connect()

	function rows($table) {
		$sql = "SELECT * FROM $table";
		$search= mysql_query($sql, $this->link_id);
		$rows = mysql_num_rows($search);
		return $rows;
	}
		

	#-#############################################
	# desc: close the connection
	function close() {
		if(!mysql_close($this->link_id)){
			$this->oops("Connection close failed.");
		}
	}#-#close()


	#-#############################################
	# Desc: escapes characters to be mysql ready
	# Param: string
	# returns: string
	function escape($string) {
		if(get_magic_quotes_gpc()) $string = stripslashes($string);
		return mysql_real_escape_string($string);
	}#-#escape()


	#-#############################################
	# Desc: executes SQL query to an open connection
	# Param: (MySQL query) to execute
	# returns: (query_id) for fetching results etc
	function query($sql) {
		// do query
		$this->query_id = @mysql_query($sql, $this->link_id);

		if (!$this->query_id) {
			$this->oops("<b>MySQL Query fail:</b> $sql");
		}
		
		$this->affected_rows = @mysql_affected_rows();

		return $this->query_id;
	}#-#query()


	#-#############################################
	# desc: fetches and returns results one line at a time
	# param: query_id for mysql run. if none specified, last used
	# return: (array) fetched record(s)
	function fetch_array($query_id=-1) {
		// retrieve row
		if ($query_id!=-1) {
			$this->query_id=$query_id;
		}

		if (isset($this->query_id)) {
			$this->record = @mysql_fetch_assoc($this->query_id);
		}else{
			$this->oops("Invalid query_id: <b>$this->query_id</b>. Records could not be fetched.");
		}

		// unescape records
		if($this->record){
			$this->record=array_map("stripslashes", $this->record);
			//foreach($this->record as $key=>$val) {
			//	$this->record[$key]=stripslashes($val);
			//}
		}
		return $this->record;
	}#-#fetch_array()


	#-#############################################
	# desc: returns all the results (not one row)
	# param: (MySQL query) the query to run on server
	# returns: assoc array of ALL fetched results
	function get($sql) {
		$query_id = $this->query($sql);
		$out = array();

		while ($row = $this->fetch_array($query_id, $sql)){
			$out[] = $row;
		}

		$this->free_result($query_id);
		return $out;
	}#-#fetch_all_array()


	#-#############################################
	# desc: frees the resultset
	# param: query_id for mysql run. if none specified, last used
	function free_result($query_id=-1) {
		if ($query_id!=-1) {
			$this->query_id=$query_id;
		}
		if(!@mysql_free_result($this->query_id)) {
			$this->oops("Result ID: <b>$this->query_id</b> could not be freed.");
		}
	}#-#free_result()


	#-#############################################
	# desc: does a query, fetches the first row only, frees resultset
	# param: (MySQL query) the query to run on server
	# returns: array of fetched results
	function first($query_string) {
		$query_id = $this->query($query_string);
		$out = $this->fetch_array($query_id);
		$this->free_result($query_id);
		return $out;
	}#-#query_first()


	#-#############################################
	# desc: does an update query with an array
	# param: table (no prefix), assoc array with data (doesn't need escaped), where condition
	# returns: (query_id) for fetching results etc
	function update($table, $data, $where='1=1') {
		$q="UPDATE `".$this->pre.$table."` SET ";

		foreach($data as $key=>$val) {
			if(strtolower($val)=='null') $q.= "`$key` = NULL, ";
			elseif(strtolower($val)=='now()') $q.= "`$key` = NOW(), ";
			else $q.= "`$key`='".$this->escape($val)."', ";
		}

		$q = rtrim($q, ', ') . ' WHERE '.$where.';';

		return $this->query($q);
	}#-#query_update()


	#-#############################################
	# desc: does an insert query with an array
	# param: table (no prefix), assoc array with data
	# returns: id of inserted record, false if error
	function insert($table, $data) {
		$q="INSERT INTO `".$this->pre.$table."` ";
		$v=''; $n='';

		foreach($data as $key=>$val) {
			$n.="`$key`, ";
			if(strtolower($val)=='null') $v.="NULL, ";
			elseif(strtolower($val)=='now()') $v.="NOW(), ";
			else $v.= "'".$this->escape($val)."', ";
		}

		$q .= "(". rtrim($n, ', ') .") VALUES (". rtrim($v, ', ') .");";

		if($this->query($q)){
			//$this->free_result();
			return mysql_insert_id();
		}
		else return false;

	}#-#query_insert()

	function columns($table){
		$sql = "SHOW COLUMNS FROM `".$this->escape($table)."`";
		$data = $this->get($sql);
		$fields = array();
		foreach($data as $col){
			$fields[] = $col['field'];
		}
		return $fields;
	}

	#-#############################################
	# desc: throw an error message
	# param: [optional] any custom error to display
	function oops($msg='') {
		if($this->link_id>0){
			$this->error=mysql_error($this->link_id);
			$this->errno=mysql_errno($this->link_id);
		}
		else{
			$this->error=mysql_error();
			$this->errno=mysql_errno();
		}
		?>
			<table align="center" border="1" cellspacing="0" style="background:white;color:black;width:500px;margin:15px">
			<tr><th colspan=2>Database Error</th></tr>
			<tr><td align="right" valign="top">Message:</td><td><?php echo $msg; ?></td></tr>
			<?php if(strlen($this->error)>0) echo '<tr><td align="right" valign="top" nowrap>MySQL Error:</td><td>'.$this->error.'</td></tr>'; ?>
			<tr><td align="right">Date:</td><td><?php echo date("l, F j, Y \a\\t g:i:s A"); ?></td></tr>
			<tr><td align="right">Script:</td><td><a href="<?php echo @$_SERVER['REQUEST_URI']; ?>"><?php echo @$_SERVER['REQUEST_URI']; ?></a></td></tr>
			<?php if(strlen(@$_SERVER['HTTP_REFERER'])>0) echo '<tr><td align="right">Referer:</td><td><a href="'.@$_SERVER['HTTP_REFERER'].'">'.@$_SERVER['HTTP_REFERER'].'</a></td></tr>'; ?>
			</table>
		<?php
		die();
	}#-#oops()
}
?>