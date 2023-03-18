<?php
//=============================================
// File.......: saxeniot_lib.php
// Date.......: 2023-03-18
// Author.....: Benny Saxen
// Description: 
//=============================================
$date         = date_create();
$sys_ts       = date_format($date, 'Y-m-d H:i:s');
$sys_date     = date_format($date, 'Y-m-d');

$repository = 'iot';
$endpoint   = 'http://simuino.com:7200';
$reference  = 'http://rdf.simuino.com';

$pfx_rdf       = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>';
$pfx_rdfs      = 'PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>';
$pfx_owl       = 'PREFIX owl: <http://www.w3.org/2002/07/owl#>';

$pfx_device    = 'PREFIX device: <http://rdf.simuino.com/device#>';
$pfx_map       = 'PREFIX device: <http://rdf.simuino.com/map#>';
$pfx_category  = 'PREFIX category: <http://rdf.simuino.com/category#>';
$pfx_home      = 'PREFIX home: <http://rdf.simuino.com/home#>';
$pfx_vehicle   = 'PREFIX vehicle: <http://rdf.simuino.com/vehicle#>';
$pfx_person    = 'PREFIX person: <http://rdf.simuino.com/person#>';
$pfx_work      = 'PREFIX work: <http://rdf.simuino.com/work#>';
$pfx_company   = 'PREFIX company: <http://rdf.simuino.com/company#>';
$pfx_pet       = 'PREFIX pet: <http://rdf.simuino.com/pet#>';

//=============================================
// Library
//=============================================
$values = array();
$count = 0;
//=============================================
// Define recursive function to extract nested values
function printValues($arr) {
//=============================================
    global $count;
    global $values;
    
    // Check input is an array
    if(!is_array($arr)){
        die("ERROR: Input is not an array");
    }
    
    /*
    Loop through array, if value is itself an array recursively call the
    function else add the value found to the output items array,
    and increment counter by 1 for each value found
    */
    foreach($arr as $key=>$value){
        if(is_array($value)){
            printValues($value);
        } else{
            $values[$count] = $value;
            //echo("$count $values[$count]\n");
            $count++;
        }
    }
    
    // Return total count and values found in array
    return array('total' => $count, 'values' => $values);
  }
//=============================================
function sparqlGetQuery($query)
//=============================================
{
    global $values,$count;
    global $endpoint,$repository;
    $sparql = 'void';
    //echo "$query<br>";
  
    $query = urlencode($query);
    $cmd = "curl -X GET --header 'Accept: application/sparql-results+json' '".$endpoint."/repositories/".$repository."?query=".$query."'";
    $cmd = $cmd." > temp.txt";
    system($cmd);
    $json = file_get_contents('temp.txt');
    $n = showFileContent("temp.txt");
   
    // // Decode JSON data to PHP associative array
    $arr = json_decode($json,true);


    return $arr;
}
//=============================================
function sparqlPostQuery($query)
//=============================================
{
    global $endpoint,$repository;
    //echo "$query";
    //echo "\n";

    $query = rawurlencode($query);
    //$query = urlencode($query);

    //$cmd = "curl -d '".$query."' -H 'Content-Type: application/sparql-query' -X POST '".$endpoint."/repositories/".$repository."'";

    $cmd = "curl -X POST --header 'Content-Type: application/rdf+xml' --header 'Accept: application/json' '".$endpoint."/repositories/".$repository."/statements?update=".$query."'";


    $cmd = $cmd." > temp.txt";
    //echo $cmd;
    system($cmd);

    $n = showFileContent("temp.txt");
  
    return;
}


//=============================================
function showFileContent($f_file)
//=============================================
{
    $nchar = 0;
    $file = fopen($f_file, "r");
    if ($file)
    {
        while(! feof($file))
        {
            $line = fgets($file);
            //echo $line;
            $nchar += strlen($line);
        }
        fclose($file);
    }
    else
    {
        echo ("Sparql Query Error");
    }
    //echo "$nchar";
    return $nchar;
}
//=============================================
function addTriple($pfx1,$pfx2,$pfx3,$sub,$pre,$obj)
//=============================================
{ 
    echo "Add triple: s=$sub p=$pre o=$obj<br>";
    if (strlen($sub)>0 && strlen($pre)>0 && strlen($obj)>0)
    {
        echo "Add Triple $sub,$pre,$obj<br>";
        
        $query ="$pfx1 $pfx2 $pfx3
        insert data  { 
            $sub $pre $obj . }";
        //echo $query;
        sparqlPostQuery($query);
    }
}

//=============================================
function removeTriple($pfx1,$pfx2,$pfx3,$sub,$pre,$obj)
//=============================================
{
    echo "Remove triple: s=$sub p=$pre o=$obj<br>";
    if (strlen($sub)>0 && strlen($pre)>0 && strlen($obj)>0)
    {
        echo "Remove Triple $sub,$pre,$obj<br>";
        
        $query ="$pfx1 $pfx2 $pfx3
        delete  { 
            $sub $pre $obj . }
        where { 
            $sub $pre $obj . }";
        //echo $query;
        sparqlPostQuery($query);
    }
}
//=============================================
// End of library
//=============================================
