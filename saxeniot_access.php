<?php
//=============================================
// File.......: saxeniot_access.php
// Date.......: 2023-03-24
// Author.....: Benny Saxen
// Description: 
//=============================================

//=============================================
$date         = date_create();
$sys_ts       = date_format($date, 'Y-m-d H:i:s');
$sys_date     = date_format($date, 'Y-m-d');


//=============================================
function showFileContent($f_file,$mode)
//=============================================
{
  
    $file = fopen($f_file, "r");
    if ($file)
    {
        //echo("<p>$f_file</p>");
        while(! feof($file))
        {
            $line = fgets($file);
            if ($mode==1)
                echo "$line";
            if ($mode==2)
                echo "<h1>$line</h1>";

        }
        fclose($file);
    }
    else
    {
        echo ("Error showing file: $f_file");
    }

    return;
}

//=============================================
function showAllCurrentFiles()
//=============================================
{
    system("ls  current*.saxeniot > list.work");

    $ffile = fopen("list.work", "r");
    if ($ffile)
    {
        $ii = 0;
        while(! feof($ffile))
        {
            $ii++;
            $line = fgets($ffile);
            if (strlen($line) > 2)
            {
              sscanf($line, "%s", $curFile);
              showFileContent($curFile,2);
            }
        }
        fclose($ffile);
    }
    else
    {
        echo ("Error showing all current value files");
    }

    return;
}

//=============================================
//  Handle requests from clients
//=============================================
if (isset($_GET['par']))
{

  $par = $_GET['par'];
  $label = $_GET['label'];

  $file = "current-$label-p$par.saxeniot";
  showFileContent($file,1);
} 
if (isset($_GET['power']))
{

  //$par = $_GET['par'];
  $label = $_GET['label'];

  $file = "current-$label-p1.saxeniot";
  showFileContent($file,1);
  $file = "sum-$label-p2-$sys_date.saxeniot";
  showFileContent($file,1);
} 
if (isset($_GET['all']))
{
  showAllCurrentFiles();
}


//echo "SAXENIOT Access ok\n";

//=============================================
// End of File
//=============================================
//echo("OK\n");
?>
