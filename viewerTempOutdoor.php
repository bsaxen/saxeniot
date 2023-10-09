<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $_SESSION['date'] = $_GET["date"];
}

//$select = 'sum';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $select =  $_POST["date"];
    $parameter = $_POST["param"];
    if (isset($_POST['form2'])) {
        $_SESSION['date'] = $select;
    }
    if (isset($_POST['form1'])) {
        $_SESSION['par'] = $parameter;
    }
}
$select = $_SESSION['date'];
$parameter = $_SESSION['par'];

echo("<h1> Datum $select Temperatur $parameter</h1>");

$fil = 'log-kvv32_outdoor_temp-'.$parameter.'-A0-20-A6-10-39-7B-'.$select.'.saxeniot';

echo("$fil $select<br>");
echo("<h2>Outdoor Temperature KVV2</h2>");
echo ("<a href=\"viewerTempOutdoor.php?date=sum\">Summary</a><br>");
exec("ls sum*outdoor_temp-".$parameter."*.saxeniot > work.txt");
$lines = file('work.txt');
$count = 0;
foreach($lines as $line) {
    $line = trim($line);
    //echo"$line";echo"=";
    $res = file($line);
    foreach($res as $r) {
        $sum = $r;
    }
    $count += 1;
    $x = explode('.', $line);
    $name = $x[0];
    $x = explode('-', $name);
    $year  = $x[3];
    $month = $x[4];
    $day   = $x[5];
    $date  = $year.'-'.$month.'-'.$day;
    $data[$count] = $sum;
    $dates[$count] = $date;
    //<a href="http://www.yahoo.se" target="_blank">Gå till Yahoo</a>
    //echo str_pad($count, 2, 0, STR_PAD_LEFT).". <a href=\"pow.php?date=$date\">".$date."</a> $sum<br>";
}
?>
<html>
<head>
  <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
      google.charts.load('current', {'packages':['line']});
      google.charts.setOnLoadCallback(drawChart);

    function drawChart() {

      var data = new google.visualization.DataTable();

    <?php
    if ($select == 'sum')
    {
      echo("data.addColumn('string', 'Day');\n");
      echo("data.addColumn('number', 'Celcius');\n");

      echo("data.addRows([\n");

        $size = count($data);
        for ($ii = 1; $ii <= $size; $ii++) {
            $value = $data[$ii];
            $datum = $dates[$ii];
            if ($ii < $size) echo "['$datum',$value],";
            else echo "['$datum',$value]";
        }
    }

    
    if ($select != 'sum')
    {
        echo("data.addColumn('timeofday', 'Time');\n");
        echo("data.addColumn('number', 'Celcius');\n");
  
        echo("data.addRows([\n");
        $count = 0;
        
        $lines = file($fil);
        foreach($lines as $line) {
            $line = trim($line);    
            $count += 1;
            $x = explode(' ', $line);
            $times[$count] = $x[1];
            $value = $x[2]; 
            $val[$count] = $value;
        }

        $size = count($val);
        for ($ii = 1; $ii <= $size; $ii++) {
            $value = $val[$ii];
            $time = $times[$ii];
            $arr = explode(':', $time);
            $hour = $arr[0];
            $min = $arr[1];
            $sec = $arr[2];

            if ($ii < $size) echo "[[$hour,$min,$sec],$value],";
            else echo "[[$hour,$min,$sec],$value]";
        }
    }
    ?>

      ]);


      var options = {
        chart: {
          title: 'Outdoor Temperatures KVV32',
          subtitle: 'in Celcius'
        },
        width: 1600,
        height: 900,
        hAxis: { 
          title: 'Time of day',
          gridlines: {count: 24}
        }
      };


      //var chart = new google.charts.Line(document.getElementById('curve_chart'));
      var chart = new google.charts.Line(document.getElementById('line_top_x'));
      //chart.draw(data, options);
      chart.draw(data, google.charts.Line.convertOptions(options));
    }
  </script>
</head>
<body>
<form action="viewerTempOutdoor.php" method="post">
      <label for="lang">Parameters</label>
      <select name="param" id="pp">
      <?php
        echo("<option value=\"p1\">P1</option>");
     ?>
      </select>
      <input type="submit" name='form1' value="Select" />
</form>
<form action="viewerTempOutdoor.php" method="post">
      <label for="lang">Dates</label>
      <select name="date" id="lang">
      <?php
      $size = count($dates);
      for ($ii = 1; $ii <= $size; $ii++) {
        $date = $dates[$ii];
        $sum = $data[$ii];
        echo("<option value=\"$date\">$date $sum</option>");
      }
     ?>
      </select>
      <input type="submit" name='form2' value="Dates" />
</form>
  <div id="line_top_x"></div>
</body>
</html>