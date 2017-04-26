<?
function makeUsageCharts($db,$ip,$inrefid,$outrefid){
	include("config.inc");

	$prefix="/var/www/html";
	$basedir="/$db/data/";
	$ifs=array();

	$sql="select * from IFDescriptions where ip='$ip' order by ifnum";
	$q=mysql_query($sql);
	$r=mysql_fetch_object($q);

	while($r){
		if($r->opStatus==1){
			$ifs[$r->ifnum]=$r->speed;
		}else{
			$ifs[$r->ifnum]=0;
		}
		#print $ifs[$r->ifnum]."<br>";
		$r=mysql_fetch_object($q);
	}


	$inrrdfile=$prefix.$basedir."$inrefid.rrd";
	$outrrdfile=$prefix.$basedir."$outrefid.rrd";

	$lasttime=time();
	$lasttime=floor($lasttime/$timeunit)*$timeunit;



	#print "Lasttime:".$lasttime." ".strftime("%D %T",$lasttime)." $inrrdfile $outrrdfile<br>";
	$opts = array ( "AVERAGE", "--start", $lasttime,"--end",$lasttime,"--resolution", 600 );

	$inrrd = rrd_fetch($inrrdfile, $opts, count($opts));        
	$outrrd = rrd_fetch($outrrdfile, $opts, count($opts));      
	$graphname=$prefix.$basedir."tmp/$ip";

	for($i=0;$i<count($ifs);$i++){
		if(($ifs[$i]!=0)&&(strcmp($inrrd[data][$i-1],"NAN")!=0)){
			$inperc=$inrrd[data][$i-1]/$ifs[$i];
			$outperc=$outrrd[data][$i-1]/$ifs[$i];
			#print $i."\t".$ifs[$i]."\t".$inrrd[ds_namv][$i-1]."\t".$inrrd[data][$i-1]."\t".$outrrd[data][$i-1];
			makeGraph(80,18, $inperc,$outperc,$graphname.".$i.png");
			#print "<br>\t$inperc\t$outperc<br><br>";

		}

	}

}  

function makeGraph($width,$height,$inperc,$outperc,$name)
{

        $im = @ImageCreate ($width,$height);
        $background_color = ImageColorAllocate ($im, 255, 255, 255);
	#return;

        $text_color = ImageColorAllocate ($im, 233, 14, 91);
        $black=ImageColorAllocate($im,0,0,0);
        $blue=ImageColorAllocate($im,0,0,255);
        $green=ImageColorAllocate($im,0,255,0);

        ImageFilledRectangle($im,0,0,$inperc*$width,$height/2-1,$green);
        ImageFilledRectangle($im,0,$height/2,$outperc*$width,$height,$blue);
        ImageRectangle($im,0,0,$width-1,$height-1,$black);

	$inperc=sprintf("%1.2f",$inperc*100)."%";
	$outperc=sprintf("%1.2f",$outperc*100)."%";
        ImageString ($im, 1, $width- (strlen($inperc)*5+1 ), 1,  $inperc, $text_color);
        ImageString ($im, 1, $width- (strlen($outperc)*5+1 ), $height/2,  $outperc, $text_color);
        ImageGif ($im,$name);
}




?>
