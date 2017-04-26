<?php
$endtime=time();
#$endtime=floor($endtime/$timeunit)*$timeunit;
$starttime=$endtime-3600*24;

$params="graphtype=$graphtype&ip=$ip";

echo "<FRAMESET ROWS=\"90,*\" >
	<FRAME NAME=control SRC=\"icmpControl.php?$params\" MARGINWIDTH=2 MARGINHEIGHT=2 SCROLLING=Auto Frameborder=yes>
	<FRAME NAME=graph
	SRC=\"/cgi-bin/icmpChart.pl?$params\" MARGINWIDTH=8 MARGINHEIGHT=4 SCROLLING=Auto FrameBorder=no>
      </FRAMESET>";

?>
