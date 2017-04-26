<?php
include ("showGraph.js");
$inOID=21;
$outOID=22;
Function graphIF($basedir,$ifnum,$interval,$inrefid,$outrefid,$width,$height,$desc,$clickable,$speed,$suffix){
   #get rid of escaped \
   $desc=ereg_replace("\\\\", "", $desc);
   $prefix="/var/www/html";
   $basedir="/".$basedir."/data/";
   $incolor="#00ff00";
   $outcolor="#0000ff";
   $inrrd=$prefix.$basedir."$inrefid.rrd";
   $outrrd=$prefix.$basedir."$outrefid.rrd";

   $fname=$basedir."tmp/$ifnum.$inrefid.$outrefid$suffix";
   switch($interval){
	case "1":
		$start="-1wk";
		$fname=$fname."wk";
		break;
	case "2":
		$start="-0wk-1m";
		$fname=$fname."mo";
		$xgrid="DAY:1:WEEK:1:WEEK:1:0:%D";
		break;
	case "3":
		$start="-1yr";
		$fname=$fname."yr";
		break;
	default:
		$start="-1d";
		$fname=$fname."dy";
   }
   $fname=$fname.".png";
   $imgname=$fname;
   $fname=$prefix.$fname;

#	print "$fname<Br>$imgname<br>$inrrd $outrrd<br>$width<br>$height <hr>";

   $opts = array( "--start", $start, "-l 0","--lazy","--imgformat","PNG","--width",$width,"--height",$height,
                  "DEF:in=$inrrd:if$ifnum:AVERAGE",
                  "DEF:out=$outrrd:if$ifnum:AVERAGE",
                  "AREA:in$incolor:Incoming Traffic",
                  "LINE2:out$outcolor:Outgoing Traffic",
                  "PRINT:in:LAST:%1.2lf %s",
                  "PRINT:out:LAST:%1.2lf %S",
                  "PRINT:in:AVERAGE:%1.2lf %S",
                  "PRINT:out:AVERAGE:%1.2lf %S",
                  "PRINT:in:MAX:%1.2lf %S",
                  "PRINT:out:MAX:%1.2lf %S",
                  "PRINT:in:LAST:%1.2lf",
                  "PRINT:out:LAST:%1.2lf",
                  "PRINT:in:AVERAGE:%1.2lf",
                  "PRINT:out:AVERAGE:%1.2lf",
                  "PRINT:in:MAX:%1.2lf",
                  "PRINT:out:MAX:%1.2lf"
                );
   if (strlen($xgrid)>0){
	array_push($opts,"--x-grid",$xgrid);

   }
   $lasttime=rrd_last($inrrd);
   $ret = rrd_graph($fname, $opts, count($opts));
  
   if ( is_array($ret) )
   {

	#$url="showGraph.php?ip=$ip&ifnum=$ifnum&ifdesc=$ifdesc&device=".$device;
	#$trans = get_html_translation_table (HTML_ENTITIES);
	#$encoded = strtr ($url, $trans);
      	#$url=$encoded;
	#begin main table
	echo "<br><table border=0><tr><th>$desc</th></tr>";	
	
	if ($clickable==1){
		#make images clickable to pop up detail stats
		$desc=urlencode($desc);
		echo "<tr><th><a href=javascript:showGraph($ifnum,$inrefid,$outrefid,\"$desc\",$speed);>
			<img src=$imgname>
			</a>
			</th>";       
	}else{
		echo "<tr><th><img src=$imgname></th>";       
	}
	$tdattr=" align=right";
	$infont="<font color=$incolor><b>";
	$outfont="<font color=$outcolor><b>";
	$tdspace="<td width=15><br></td>";
	$rate="B/s";
	#this second table is embedded in the first
	echo "<tr><th><table border=0>";
	echo "<tr><td $tdattr>Cur $infont In:</td><td>".$ret[calcpr][0]."$rate ".getPercentage($ret[calcpr][6],$speed)."</td>$tdspace
		  <td $tdattr>Avg $infont In:</td><td>".$ret[calcpr][2]."$rate ".getPercentage($ret[calcpr][8],$speed)."</td>$tdspace
		  <td $tdattr>Max $infont In:</td><td>".$ret[calcpr][4]."$rate ".getPercentage($ret[calcpr][10],$speed)."</td></tr>";
	echo "<tr><td $tdattr>Cur $outfont Out:</td><td>".$ret[calcpr][1]."$rate ".getPercentage($ret[calcpr][7],$speed)."</td>$tdspace
		  <td $tdattr>Avg $outfont Out:</td><td>".$ret[calcpr][3]."$rate ".getPercentage($ret[calcpr][9],$speed)."</td>$tdspace
		  <td $tdattr>Max $outfont Out:</td><td>".$ret[calcpr][5]."$rate ".getPercentage($ret[calcpr][11],$speed)."</td></tr>";

        echo "</table>";
	echo "</th></tr>";
	echo "<tr><td align=center>Last Updated ".strftime("%D %T",$lasttime)."</td></tr>";

	echo "	</table>";

   }
   else
   {
       $err = rrd_error();
       echo "rrd_graph() ERROR: $err\n";
   }
}

?>
<?
Function getPercentage($val,$max){
	$t="(".sprintf("%1.1f", ($val/$max)*100)."%)";
	return $t;
}
?>
