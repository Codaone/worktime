<?php
/**
 @example <xmp style="whitespace:pre-wrap"><div class="mylist">
		<div class="mylistHeader">
			<table>
				<tbody>
					<tr>
						<td class="headerTitle">Kohdelutttelo 2012</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="mylistContent">
			<table class="mylistGrid">
				<thead>
					<tr>
						<th>Osoite</th>
						<th>Numero</th>
						<th>Neliöt</th>
						<th>Osoite</th>
						<th>Numero</th>
					</tr>
				</thead>
				<tbody class="datarow">
					<tr>
						<td>Keskuskatu 1 B</td>
						<td>Helsinki</td>
						<td>105 m2</td>
						<td>Keskuskatu 1 B</td>
						<td>Helsinki</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td>00100</td>
						<td></td>
						<td>314 m2</td>
						<td>00100</td>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="mylistHeaderShader"></div>
	</div>
	</xmp>
*/
Class myList{
	/**
	 * Table content list must be array
	 * @example 
	 * array(
	 * 	[0] => <xmp><tr><td>Test</td><td>1</td></tr></xmp>
	 * )
	 * @var array
	 */
	public $listContent 	= array();
	public $headerTitle 	= "";
	
	private $listHeaders 	= array();
	private $listFooters 	= array();
	private $noRowsMessage 	= "No rows found";	
	private $listRowClass 	= "datarow";
	private $id			 	= "";
	private $mainstr		= "";
	private $columns		= FALSE;
	
	private $footerSpan 	= 0;
	private $headerSpan 	= 0;
	private $colLine 		= array();
	private $sortable 		= FALSE;
	private $script			= "";
	
	/**
	 * Generates id for table
	 */
	public function generateId(){
		$this->id = "mylist_".substr(md5(rand(1000, 9999)),0,20);
	}
	private function genParams(array $params){
		$str = "";
		foreach ($params as $name=>$value) $str .= ' '.$name.'="'.HTMLENTS($value).'"';
		return $str;
	}
	
	/** Public functions **/
	
	public function __construct($id = FALSE){
		$this->reset();	
		if(!$id) $this->generateId();
		else $this->id = $id;
	}
	/**
	 * Returns the id given to mylist table
	 */
	public function getId() {
		return $this->id;
	}
	public function sortable($bool = TRUE){
		$this->sortable = (bool) $bool;
	}
	/**
	 * Number of columns used in table
	 * @param int $number
	 */
	public function setColumns($number){
		if(is_numeric($number)) $this->columns = $number;
	}
	public function setMainProps(array $props){
		$this->mainstr = $this->genParams($props);
	}
	/**
	 * If no columns given or empty string/array given to addRow show this message
	 * @param string $str
	 */
	public function setNoRowsMessage($str){
		$this->noRowsMessage = $str;
	}
	/**
	 * Resets all data to default values
	 */
	public function reset(){
		$this->headerTitle 	 = "";
		$this->listHeaders 	 = array();
		$this->listFooters 	 = array();
		$this->listContent 	 = array();
		$this->listRowClass  = "datarow";
		$this->columns 		 = 1;
		$this->noRowsMessage = "No rows found";
		$this->colLine 		 = array();
		$this->mainstr		 = "";
		$this->footerSpan 	 = 0;
		$this->headerSpan	 = 0;
		$this->script 		 = "";
		$this->sortable 	 = false;
		$this->generateId();
	}
	/**
	 * Sets the title for mylist
	 * @param string $text
	 */
	public function setTitle($text){
		$this->headerTitle = $text;
	}
	/**
	 * Sets sortability for mylist
	 * @param bool $val
	 */
	public function setSortable($val = true){
		$this->sortable = $val ? true : false;
	}
	/**
	 * Sets script to include in mylist
	 * @param string $text
	 */
	public function setScript($script){
		$this->script = $script;
	}
	/**
	 * Row style classname
	 * @param string $listRowClass
	 */
	public function setRowStyle($listRowClass = "datarow"){
		$this->listRowClass = $listRowClass;
	}
	/**
	 * addCols
	 * @param string $str
	 * @param array $props
	 */
	public function addCol($str, $props = array(), $rowProps = array()){
		if($this->columns === FALSE){
			$this->columns = $this->headerSpan;
		}
		$c = $this->columns;
		$colstr = "";
		foreach ($props as $name=>$value) $colstr .= ' '.$name.'="'.HTMLENTS($value).'"';
		$this->colLine[] = "<td$colstr>$str</td>";
		if(count($this->colLine) == $c){
			$rowstr = "";
			foreach ($rowProps as $name=>$value) $rowstr .= ' '.$name.'="'.HTMLENTS($value).'"';
			$this->listContent[] = "<tr$rowstr>".implode("",$this->colLine)."</tr>";
			$this->colLine = array();
		}
	}
	/**
	 * Adds a data row to mylist
	 * @example colProps: array( [class] => label, [style] => width:50%; )
	 * @param string/array $data
	 * @param array $colProps
	 * @param array $rowProps
	 */
	public function addRow($data, $colProps = array(), $rowProps = array()){
		if(is_array($data) && is_array(reset($data))){
			foreach ($data as $row){
				$this->addRow($row, $colProps, $rowProps);
			}
			return TRUE;
		}
		if(is_array($data)){
			foreach ($data as $i=>$row){
				$this->addCol($row, $colProps, $rowProps);
			}
		} elseif(is_string($data)) {
			$this->addCol($data, $colProps, $rowProps);
		}
	}
	/**
	 * Adds table footer values
	 * @param string $text text/htm
	 * @param int $span [optional] colspan default is 1 rest is autofilled
	 * @param array $params [optional] if needed attributes to td 
	 * @example for $params: 
	 * array( [style] => 'color:red' )
	 * <xmp><td style="color:red">Väärin</td></xmp>
	 */
	public function addFooterCol($text, $span = 1, $params = array()){
		if(isset($params["colspan"])) unset($params["colspan"]);
		$this->listFooters[] = array("text" => $text, "span" => $span, "params" => $params);
		$this->footerSpan += $span;
	}
	public function addFooters(array $footers){
		foreach ($footers as $f){
			if(is_array($f)){
				call_user_func_array(array($this, "addFooterCol"), $f);
			} else {
				$this->addHeaderCol($f);
			}
		}
	}
	/**
	 * Adds table header names
	 * @param string $text text/htm
	 * @param int $span [optional] colspan default is 1 rest is autofilled
	 * @param array $params [optional] if needed attributes to td 
	 * @example for $params: 
	 * array( [style] => 'color:red' )
	 * <xmp><td style="color:red">Väärin</td></xmp>
	 */
	public function addHeaderCol($text, $span = 1, $params = array()){
		if(isset($params["colspan"])) unset($params["colspan"]);
		$this->listHeaders[] = array("text" => $text, "span" => $span, "params" => $params);
		$this->headerSpan += $span;
	}
	public function addHeaders(array $headers){
		foreach($headers as $h){
			if(is_array($h)){
				call_user_func_array(array($this, "addHeaderCol"), $h);
			} else {
				$this->addHeaderCol($h);
			}
		}
	}
	private function processHeaders(){
		if(count($this->listHeaders)>0){
			if($this->headerSpan < $this->columns){
				$num = $this->columns - $this->headerSpan;
				$this->addHeaderCol("&nbsp;", $num);
			}
			$temp = array();
			foreach ($this->listHeaders as $h){
				extract($h);
				if(!$this->sortable){
					if(isset($params["style"])){
						$params["style"] .= "cursor:default;";
					} else {
						$params["style"] = "cursor:default;";
					}
				}
				$str = $this->genParams($params);
				$temp[] = "<th $str colspan='$span'>$text</th>";
			}
			$this->listHeaders = $temp;
		}
	}
	private function processFooters(){
		if(count($this->listFooters)>0){
			if($this->footerSpan < $this->columns){
				$num = $this->columns - $this->footerSpan;
				$this->addFooterCol("&nbsp;", $num);
			}
			$temp = array();
			foreach ($this->listFooters as $h){
				extract($h);
				if(!$this->sortable){
					if(isset($params["style"])){
						$params["style"] .= "cursor:default;";
					} else {
						$params["style"] = "cursor:default;";
					}
				}
				$str = $this->genParams($params);
				$temp[] = "<td $str colspan='$span'>$text</td>";
			}
			$this->listFooters = $temp;
		}
	}
	/**
	 * Outputs the html mylist
	 */
	public function out(){
		$this->processHeaders();
		$this->processFooters();

		$table = '<div class="mylist" id="'.$this->id.'" '.$this->mainstr.'>';
		$header = empty($this->headerTitle) ? FALSE : $this->headerTitle;
		if($header){
			$table .= '<div class="mylistHeader"><table><tbody><tr>
							<td class="headerTitle">'.$header.'</td>
						</tr></tbody></table></div>';
		}
		
		$table .= '<div class="mylistContent"><table class="mylistGrid table table-striped table-condensed table-hover">';
		$listHeader = count($this->listHeaders)==0 ? FALSE : $this->listHeaders;
		if($listHeader){
			$table .= '<thead style="cursor:normal;"><tr>'.implode('',$listHeader).'</tr></thead>';
		}
		if(count($this->listContent)==0) $this->listContent[] = "<tr><td colspan='$this->columns'>$this->noRowsMessage</td></tr>";
		$table .= '<tbody class="'.$this->listRowClass.'">'.implode('',$this->listContent).'</tbody>';
		$listFooter = count($this->listFooters)==0 ? FALSE : $this->listFooters;
		if($listFooter){
			$table .= '<tfoot><tr>'.implode('',$listFooter).'</tr></tfoot>';
		}
		$table .= '</table></div><div class="mylistHeaderShader"></div></div>';
		if($listHeader && $this->sortable){
			// Mootools sortable
			$table .= '<script type="text/javascript" execute="1">(function(){ new HtmlTable($("'.$this->id.'").getElement(".mylistGrid"),{sortable:1,sortIndex:null}); })()</script>';
		}
		if($this->script != "")
		{
			$table .= $this->script;
		}
		return $table;
	}
}

function HTMLENTS($str) {
	return htmlentities($str, ENT_QUOTES|ENT_IGNORE, "utf-8");
}