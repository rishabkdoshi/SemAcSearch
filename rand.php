<?php
$command = escapeshellcmd('python /opt/lampp/htdocs/seo/lematize.py');
$output = shell_exec($command);
echo $output;
?>
