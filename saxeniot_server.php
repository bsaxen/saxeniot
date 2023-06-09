<?php
//=============================================
// File.......: saxeniot_server.php
// Date.......: 2023-03-24
// Author.....: Benny Saxen
// Description: 
//=============================================
#include("saxeniot_lib.php");
//=============================================
$date         = date_create();
$sys_ts       = date_format($date, 'Y-m-d H:i:s');
$sys_date     = date_format($date, 'Y-m-d');


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
function logging($id,$par,$value,$ts,$day,$label)
//=============================================
{
 
  $file = "log-$label-p$par-$id-$day.saxeniot";
  $line = "$ts $value\n";
  file_put_contents($file, $line, FILE_APPEND | LOCK_EX);

  return $file;
}
//=============================================
function currentValue($id,$par,$value,$ts,$day,$label)
//=============================================
{
  
  $file = "current-$label-p$par.saxeniot";
  $line = "$value\n";
  file_put_contents($file, $line);

  return $file;
}
//=============================================
function todayValue($id,$par,$value,$ts,$day,$label)
//=============================================
{
  
  $file_today = "today-$label-p$par.saxeniot";
  $file_sum = "sum-$label-p$par-$day.saxeniot";
  $handle = fopen($file_sum, "r");
  if ($handle) {
    while (($line = fgets($handle)) !== false) {
        sscanf($line,'%d',$x);
    }
    fclose($handle);
    file_put_contents($file_today, $x);
  }

  return $file;
}
//=============================================
function sumValue($id,$par,$value,$ts,$day,$label)
//=============================================
{
  $file = "sum-$label-p$par-$day.saxeniot";
  $handle = fopen($file, "r");
  if ($handle) {
    while (($line = fgets($handle)) !== false) {
        sscanf($line,'%d',$x);
    }
    fclose($handle);
    $new = $x + $value;
    file_put_contents($file, $new);
  }
  else 
  {
    file_put_contents($file, 0);
  }

  return $file;
}
//=============================================
//  Handle requests from clients
//=============================================
if (isset($_GET['id']))
{

  $id = $_GET['id'];
  $label = $_GET['label'];
  $category = $_GET['category'];
  $counter = $_GET['counter'];
  $period = $_GET['period'];
  $tot = $_GET['tot'];
 
  
  for ($ii = 1;$ii <= $tot;$ii++)
  {
      $var = 'p'.$ii;
      $temp = $_GET[$var];
  
      $ref = logging($id,$ii,$temp,$sys_ts, $sys_date,$label);
      $ref = currentValue($id,$ii,$temp,$sys_ts, $sys_date,$label);
      $ref = sumValue($id,$ii,$temp,$sys_ts, $sys_date,$label);
      //$ref = todayValue($id,$ii,$temp,$sys_ts, $sys_date,$label);
  }
  

} 
else
  echo "SAXENIOT Server ok\n";

//=============================================
// End of File
//=============================================
echo("OK\n");
?>
