#!/usr/bin/php -q

<?php
$par=$argv[1];
$channel= $par;

    $uid=time();

    $f1=fopen("/tmp/$uid.call","w");
    fputs($f1,"Channel: LOCAL/201@spy1\n");
    fputs($f1,"MaxRetries: 0\n");
    fputs($f1,"RetryTime: 600\n");
    fputs($f1,"WaitTime: 30\n");
    fputs($f1,"Context: spy0\n");
    fputs($f1,"Extension: s\n");
    fputs($f1,"Priority: 1\n");
    fputs($f1,"Set: audio=/var/lib/asterisk/mohwav/Office1\nSet: chan=$channel\n");
    fclose($f1);

    system("mv /tmp/$uid.call /var/spool/asterisk/outgoing/");
?>
