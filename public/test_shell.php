<?php
echo "<pre>";
echo "Composer version:\n";
echo shell_exec('composer --version 2>&1') ?: 'shell_exec failed or composer not found';
echo "</pre>";
