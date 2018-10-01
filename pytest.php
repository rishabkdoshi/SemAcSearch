<?php 
$a = 'databases management';
$command = escapeshellcmd('python /opt/lampp/htdocs/seo/lematize.py '.$a);
$output = shell_exec($command);
echo $output;
?>
