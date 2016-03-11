<?php
$app_name = 'HTTP Server Inspector';
$app_version = '1.0.1';
$app_developer = 'Pooya Parsa (pooya@pi0.ir)';

function_exists('curl_version') || die('Error, php curl extension is required')

?>
<html>
<head>
    <title><?php echo $app_name ?></title>
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
</head>
<body>
<div class="container">
     <nav class="navbar navbar-default">
        <div class="container-fluid">
          <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href=""><?php echo "$app_name $app_version"; ?></a>
          </div>
          <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
            </ul>
            <ul class="nav navbar-nav navbar-right">
              <li><a href="https://github.com/pi0/httpinspector">View On GitHub</a></li>
            </ul>
          </div>
        </div>
      </nav>

    <br>
    <div>
        <form class="form form-horizontal" method="get">
            <label>Please Enter URL</label>
            <input class="form-control" name="url" placeholder="http://"
                   value="<?php echo isset($_REQUEST['url']) ? $_REQUEST['url'] : 'http://test.nyc.pi0.ir/test/' ?>">
            <input type="submit" class="btn btn-success form-control" value="Inspect!">
        </form>
    </div>

    <hr>
    <?php if (isset($_REQUEST['url'])) {
        echo "<h2>Server Status</h2>";
        $url = $_REQUEST['url'];

        function request_headers(&$array, $url, $opts = [])
        {
            static $allow_repeat = ['set-cookie'];
            $c = curl_init($url);
            curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($c, CURLOPT_HEADER, true);
            curl_setopt_array($c, $opts);
            $r = curl_exec($c);
            $headers = substr($r, 0, curl_getinfo($c, CURLINFO_HEADER_SIZE));
            $headers = explode("\r\n", $headers);
            foreach ($headers as &$s) {
                $s = preg_split("/ *: */", $s, 2);
                if (count($s) < 2)
                    $s = ['status', $s[0]];
                $name = strtolower($s[0]);
                $value = preg_split('/;|, (?=[^0-9])/', count($s) > 1 ? $s[1] : '');
                if (!isset($array[$name]) || (in_array($name, $allow_repeat) && !in_array($value, $array[$name])))
                    $array[$name][] = $value;
            }
        }

        $headers = [];
        request_headers($headers, $url, [CURLOPT_CUSTOMREQUEST => 'OPTIONS']);
        request_headers($headers, $url);

        function print_config($n, $v, $c = 'info')
        {
            if ($c === true)
                $c = 'success';
            else if ($c === false)
                $c = 'warning';
            echo "<div class='col-md-6'><h3> $n</h3><bold><span class='label label-$c'>$v</span></bold><br></div>";
        }

        echo "
        <div class='row'>";

        if (isset($headers['status'])) {
            $status = explode(' ', $headers['status'][0][0]);
            $status_class = (int)round(intval($status[1]) / 100);
            $status_class_css = ['1' => 'info', '2' => 'success', '3' => 'warning', '4' => 'warning', '5' => 'danger'];
            print_config('Protocol', $status[0]);
            print_config('Status', $status[2] . ' (' . $status[1] . ')', $status_class_css[$status_class]);
        }

        if (isset($headers['server']))
            print_config('Detected WebServer', $headers['server'][0][0]);

        if (isset($headers['connection']))
            print_config('Keep Alive', $headers['connection'][0][0], $headers['connection'][0][0] == 'keep-alive');

        if (isset($headers['allow']))
            print_config('Allowed HTTP Methods', join(' | ', $headers['allow'][0]), true);

        if (isset($headers['www-authenticate']))
            print_config('Authentication required', "YES, Type: " .
                explode(' ', $headers['www-authenticate'][0][0])[0], 'danger');
        else
            print_config('Authentication required', "NO", true);

        if (isset($headers['cache-control']))
            print_config('Cache Control', join(' | ', $headers['cache-control'][0]));

        echo "
        </div>";

        ?>

        <hr>
        <h1>Response Headers</h1>

        <table class="table">
            <tr>
                <th>Header Name</th>
                <th>Values</th>
            </tr>
            <?php foreach ($headers as $name => $values)
                foreach (is_array($values) ? $values : [$values] as $value) {
                    echo "<tr>";
                    echo "<td>$name</td>";
                    echo "<td><span class='badge'>" . join(' </span > <span class="badge"> ', $value) . "</span></td>";
                    echo "</tr>";
                }
            ?>
        </table>
    <?php } ?>		
<br>
<hr>
<p>Developed by <?php echo $app_developer ?></p>


</div>
   </body>
</html>