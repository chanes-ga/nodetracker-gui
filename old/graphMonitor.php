<?php
include("config.inc");
$vars="";
$endtime=time();
$endtime=floor($endtime/$timeunit)*$timeunit;
$starttime=$endtime-3600*24;

$plotpoints=($endtime-$starttime)/$timeunit + 1;
$uri="/$db/data/tmp";
$basepath="/var/www/html$uri";
$rrd=$basepath."/$refid.rrd";
$timeclause="floor(timestamp/$timeunit)";

#echo "S:$starttime\t".strftime("%D %T",$starttime)."\tP:$plotpoints\t$rrd\n";
#echo "|$rrd_dataType|$rrd_stacked|$rrd_graphType\n";
$params="plotType=$plotType&starttime=$starttime&endtime=$endtime&shortoid=$shortoid
        &timeunit=$timeunit&refids=$refid&rrd_dataType=$rrd_dataType&rrd_stacked=$rrd_stacked&rrd_graphType=$rrd_graphType&ignoredval=$ignoredval";
echo "<FRAMESET ROWS=\"150,*\" >
	<FRAME NAME=control SRC=\"graphControl.php?$params\" MARGINWIDTH=2 MARGINHEIGHT=2 SCROLLING=Auto Frameborder=yes>
	<FRAME NAME=graph
	SRC=\"graphSNMP.php?$params\" MARGINWIDTH=8 MARGINHEIGHT=4 SCROLLING=Auto FrameBorder=no>
      </FRAMESET>";

?>
