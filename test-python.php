<?php
$command = __DIR__ . '\\scraper.py';
$output = shell_exec($command);
echo "<pre>$output</pre>";
