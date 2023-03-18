<?php
//=============================================
// File.......: saxeniot_server.php
// Date.......: 2023-03-18
// Author.....: Benny Saxen
// Description: 
//=============================================
include("lib.php");
//=============================================
//=============================================
function actLog($id,$msg,$ts,$day)
//=============================================
{
  $file = "actuator-log-_-$day-_-$id.graphiot";
  $line = "$ts $msg\n";
  file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
  return $file;
}
//=============================================
function store($id,$par,$value,$ts,$day)
//=============================================
{
  $file = "store-_-p$par-$id-_-$day.graphiot";
  $line = "$ts $value\n";
  file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
  return $file;
}
//=============================================
//=============================================
if (isset($_GET['id']))
{

  $id = $_GET['id'];
  $label = $_GET['label'];
  $category = $_GET['category'];
  $counter = $_GET['counter'];
  $period = $_GET['period'];
  $tot = $_GET['tot'];
 
  //----------------------------------------------------
  $query ="$pfx_device  
             ask { 
                 device:$id ?p ?o . 
             } ";
  $arr = sparqlGetQuery($query);   
  
  $res = $arr["boolean"];
  $conv_res = $res ? 'true' : 'false';  
  //----------------------------------------------------

  // Create device in KG
  if ($conv_res == 'false')
  {
    $log = "actuator-log-$sys_date-$id.graphiot";
    $query ="$pfx_device $pfx_rdf $pfx_rdfs $pfx_owl $pfx_category
      insert data  { 
        device:$id rdf:type owl:Device .
        device:$id device:hasActuator device:actuator-$id .
        device:$id rdfs:label '$label' .
        device:$id device:hasId '$id' . 
        device:$id device:hasCategory category:$category .
        device:$id device:hasPeriod $period .
        device:$id device:hasCounter $counter .
        device:$id device:hasTimestamp '$sys_ts' .
        device:$id device:hasActuatorLog '$reference/$log' .
      ";
      for ($ii = 1;$ii <= $tot;$ii++)
      {
        $var = 'p'.$ii;
        $temp = $_GET[$var];
        $ref = store($id,$ii,$temp,$sys_ts, $sys_date);
        $query = $query.
        "device:$id device:hasParameter device:$var-$id .
         device:$var-$id rdf:type owl:Parameter .
         device:$var-$id device:hasTimestamp '$sys_ts' .
         device:$var-$id device:hasValue $temp . 
         device:$var-$id device:hasReference '$reference/$ref' . " ;
      }
      $query = $query.'}';

      //echo $query;

      sparqlPostQuery($query);

  }
  else // Update parameters value
  {

    $query = " $pfx_device 
     DELETE {
      device:$id device:hasCounter ?co .
      device:$id device:hasTimestamp ?ts .
      ";
      for ($ii = 1;$ii <= $tot;$ii++)
      {
        $var = 'p'.$ii;
        $temp = $_GET[$var];
        $query = $query.
        "
        device:$var-$id device:hasTimestamp ?o1$var .
        device:$var-$id device:hasValue ?o2$var . " ;
      }
      $query = $query. "} 
      INSERT { 
        device:$id device:hasCounter $counter .
        device:$id device:hasTimestamp '$sys_ts' .
        ";
      for ($ii = 1;$ii <= $tot;$ii++)
      {
          $var = 'p'.$ii;
          $temp = $_GET[$var];
          $ref = store($id,$ii,$temp,$sys_ts, $sys_date);
          $query = $query.
          "device:$var-$id device:hasTimestamp '$sys_ts' .
           device:$var-$id device:hasValue $temp . ";
      }
      $query = $query."} 
      WHERE {
        device:$id device:hasCounter ?co .
        device:$id device:hasTimestamp ?ts .
      ";
      for ($ii = 1;$ii <= $tot;$ii++)
      {
          $var = 'p'.$ii;
          $temp = $_GET[$var];
          $query = $query.
          "device:$var-$id device:hasTimestamp ?o1$var .
           device:$var-$id device:hasValue ?o2$var . " ;
      }
      $query = $query. "}"; 
    
      //echo $query;
      sparqlPostQuery($query);

      // Check if actuator has any messages
      $query ="$pfx_device  
      select ?message where { 
        device:$id device:hasActuator ?actuator . 
        ?actuator device:hasMessage ?message .
      } ";
      $arr = sparqlGetQuery($query);
      $ii = 0;
      $msg = $arr["results"]["bindings"][0]["message"]["value"];
      while (strlen($msg) > 0 )
      { 
        $ans = "#$msg*";
        echo $ans;
        actLog($id,$ans,$sys_ts, $sys_date);       
        $ii++;  
        $msg = $arr["results"]["bindings"][$ii]["message"]["value"];
      }
      // Delete message from graph
      $query ="$pfx_device 
      delete  { 
        ?actuator device:hasMessage ?message .
       }
      where { 
        device:$id device:hasActuator ?actuator .
        ?actuator device:hasMessage ?message .
      } ";
      
      //echo $query;
      sparqlPostQuery($query);

  }

} 
else
  echo "Server ok";

//=============================================
// End of File
//=============================================

?>
