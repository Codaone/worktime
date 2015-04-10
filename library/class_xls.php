<?php

/**
 * Simple excel generating from PHP5
 * 
 * This is one of my utility-classes.
 * 
 * The MIT License
 * 
 * Copyright (c) 2007 Oliver Schwarz
 * 
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 * @package Utilities
 * @author Oliver Schwarz <oliver.schwarz@gmail.com>
 * @version 1.0
 */

/**
 * Generating excel documents on-the-fly from PHP5
 * 
 * Uses the excel XML-specification to generate a native
 * XML document, readable/processable by excel.
 * 
 * @package Utilities
 * @subpackage Excel
 * @author Oliver Schwarz <oliver.schwarz@vaicon.de>
 * @version 1.0
 *
  * @todo Add error handling (array corruption etc.)
 * @todo Write a wrapper method to do everything on-the-fly
 */
class Excel_XML
{

    /**
     * Header of excel document (prepended to the rows)
     * 
     * Copied from the excel xml-specs.
     * 
     * @access private
     * @var string
     */
    private $header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?\>
<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
 xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
 xmlns:html=\"http://www.w3.org/TR/REC-html40\">";
 

    /**
     * Footer of excel document (appended to the rows)
     * 
     * Copied from the excel xml-specs.
     * 
     * @access private
     * @var string
     */
    private $footer = "</Workbook>";

    /**
     * Document lines (rows in an array)
     * 
     * @access private
     * @var array
     */
    private $lines = array ();

    /**
     * Worksheet title
     *
     * Contains the title of a single worksheet
     *
     * @access private 
     * @var string
     */
    private $worksheet_title = "Table1";

    /**
     * Add a single row to the $document string
     * 
     * @access private
     * @param array 1-dimensional array
     * @todo Row-creation should be done by $this->addArray
     */
    private function addRow ($array)
    {
		global $p;

        // initialize all cells for this row
        $cells = "";

        // foreach key -> write value into cells
       if($p==0)
	   {
		   	foreach ($array as $k => $v):

            $cells .= "<Cell ss:StyleID=\"s21\"><Data ss:Type=\"String\">" . utf8_encode($v) . "</Data></Cell> \n "; 

        	endforeach;
			$p++;
		}
		else
		{
			$eka=0;
			foreach ($array as $k => $v):
			
			if(is_numeric($v) && $eka!=0)$cells .= "<Cell ss:StyleID=\"s23\"><Data ss:Type=\"Number\">" . utf8_encode($v) . "</Data></Cell> \n ";
            else $cells .= "<Cell ss:StyleID=\"s23\"><Data ss:Type=\"String\">" . utf8_encode($v) . "</Data></Cell> \n "; 
			$eka++;
        	endforeach;
		}

        // transform $cells content into one row
        $this->lines[] = "<Row> \n " . $cells . "</Row> \n ";

    }

    /**
     * Add an array to the document
     * 
     * This should be the only method needed to generate an excel
     * document.
     * 
     * @access public
     * @param array 2-dimensional array
     * @todo Can be transfered to __construct() later on
     */
    public function addArray ($array)
    {

        // run through the array and add them into rows
        foreach ($array as $k => $v):
            $this->addRow ($v);
        endforeach;

    }

    /**
     * Set the worksheet title
     * 
     * Checks the string for not allowed characters (:\/?*),
     * cuts it to maximum 31 characters and set the title. Damn
     * why are not-allowed chars nowhere to be found? Windows
     * help's no help...
     *
     * @access public
     * @param string $title Designed title
     */
    public function setWorksheetTitle ($title)
    {

        // strip out special chars first
        $title = preg_replace ("/[\\\|:|\/|\?|\*|\[|\]]/", "", $title);

        // now cut it to the allowed length
        $title = substr ($title, 0, 31);

        // set title
        $this->worksheet_title = $title;

    }

    /**
     * Generate the excel file
     * 
     * Finally generates the excel file and uses the header() function
     * to deliver it to the browser.
     * 
     * @access public
     * @param string $filename Name of excel file to generate (...xls)
     */
	 
function generateXML ($filename)
    {

        // deliver header (as recommended in php manual)
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: inline; filename=\"" . $filename . ".xls\"");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		// force download dialog
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");

        // print out document to the browser
        // need to use stripslashes for the damn ">"
        echo stripslashes ($this->header);
		echo "<Styles>
		<Style ss:ID=\"s21\"><Font x:Family=\"Swiss\" ss:Bold=\"1\"/>
		<Borders>
			<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			<Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			<Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			<Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   </Borders>
		</Style>
		<Style ss:ID=\"s23\">
		   <Borders>
			<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			<Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			<Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			<Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   </Borders>
		  </Style>
		</Styles>\n";
        echo "\n<Worksheet ss:Name=\"" . $this->worksheet_title . "\">\n<Table>\n";
		echo "<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"20\"/>\n";
        echo "<Column ss:Index=\"2\" ss:AutoFitWidth=\"0\" ss:Width=\"80\"/>\n";
		echo "<Column ss:Index=\"3\" ss:AutoFitWidth=\"0\" ss:Width=\"37\"/>\n";
		echo "<Column ss:Index=\"4\" ss:AutoFitWidth=\"0\" ss:Width=\"42\"/>\n";
		echo "<Column ss:Index=\"5\" ss:AutoFitWidth=\"0\" ss:Width=\"52\"/>\n";
        echo implode ("\n", $this->lines);
        echo "</Table>\n</Worksheet>\n";
        echo $this->footer;

    }

function generateXML_to_file ($filename)
    {
		$i=0;
	$filename="old/".$filename.".xls";
	$temp=$filename;
	while(file_exists($filename))
	{
		$i++;
		$ext = explode(".", $temp, 2);
		$filename=$ext[0]."_".$i.".xls";
	}
	$file = fopen($filename, "w+");
	if($file==false) die ("unable to create file");

	
        $header='<?xml version="1.0"?>
	<?mso-application progid="Excel.Sheet"?>
	<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 	xmlns:o="urn:schemas-microsoft-com:office:office"
 	xmlns:x="urn:schemas-microsoft-com:office:excel"
 	xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 	xmlns:html="http://www.w3.org/TR/REC-html40">';


        // need to use stripslashes for the damn ">"
		$content1="<Styles><Style ss:ID=\"s21\"><Font x:Family=\"Swiss\" ss:Bold=\"1\"/></Style></Styles>\n
        \n<Worksheet ss:Name=\"" . $this->worksheet_title . "\">\n<Table>\n
		<Column ss:Index=\"1\" ss:AutoFitWidth=\"0\" ss:Width=\"20\"/>\n
        <Column ss:Index=\"2\" ss:AutoFitWidth=\"0\" ss:Width=\"150\"/>\n";
        $content2=implode ("\n", $this->lines);
        $content3="</Table>\n</Worksheet>\n";
        $content4=$this->footer;
	
	$content=$header.$content1.$content2.$content3.$content4;
	fwrite($file, $content)or die("<h2>SHIT!2</h2>");
	fclose($file);

    }

}

?>