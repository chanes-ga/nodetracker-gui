<style>
	TD.ipblock{ font-family: Arial, Helvetica, non-serif; font-size: 8pt; color: black; vAlign: top}
	TD.maintable{ font-family: Arial, Helvetica, non-serif; font-size: 9pt; color: black}
	TH.maintableUsage{ font-family: Arial, Helvetica, non-serif; font-size: 9pt; color: white}
	TH.maintable{ font-family: Arial, Helvetica, non-serif; font-size: 9pt; color: black}
	INPUT{ font-family: Arial, Helvetica, non-serif; font-size: 9pt; color: black}
	TEXTAREA{ font-family: Courier New, non-serif; font-size: 11pt; color: black}
	PRE2{ background:#eeeeee;font-family: Courier New, non-serif; font-size: 8pt; color: black;
	        border: 1 1 1 1;
		padding: 2;
                border-color:#000000 #000000 #000000 #000000;
                border-style: solid;
		width:170;
		overflow: hidden;
	}
	
</style>
<?php
#############################################################
# Requires a previously established connection to a database
#############################################################

Function log2($val){
	$base=log10(2);
	$result=log10($val)/$base;
	return $result;
}

Function GetMaxBlockAvailable($parentClass,$subnet,$maxSize){
	$offset=gmp_sub($subnet,$parentClass);
	$maxBlock=gmp_gcd($offset,"256");
	$r=gmp_intval($maxBlock);
	while ($r>$maxSize){
		$r=$r/2;
	}
	return $r;
}
Function computeNetSize($mask){
	$hostbits=gmp_scan1($mask,0);
	$netsize=gmp_pow(2,$hostbits);
	$size=gmp_intval($netsize);
	#print "mask: $mask hostbits: $hostbits <br>";

	return $size;
}
Function getHeight($netsize){
	$scale=2;
	$base=2;
	$height=$scale*$netsize;	#log10($netsize)/log10($base);
	return $height;
}
Function computeNetAllocation($net,$size){
	#print "Net: ".gmp_strval($net)." Size: $size\n";
	$count=0;
	$netStart=gmp_strval($net);
	#$netStart=$net;
	#print "NetStart: $netStart\n";
	$netEnd=gmp_strval(gmp_add($net,$size));
	#$netEnd=$net+$size;
	#first check IP table
	#here we want everything in IP that is not in RouterIPs
	$sql="select count(IP.address) as c from IP left join RouterIPs on RouterIPs.address=IP.address
		where RouterIPs.address is null and IP.address>=$netStart and IP.address<$netEnd";
	#print "\n$sql\n";
	$q5=mysql_query($sql);
	$r=mysql_fetch_object($q5);
	$count=$r->c;
	#also check RouterIPs for interface IPs 
	# here we want everything in RouterIPs
        $sql="select count(distinct RouterIPs.address) as c from RouterIPs
                where RouterIPs.IP not in('0','1') and  RouterIPs.address>=$netStart and RouterIPs.address<$netEnd";

        $q5=mysql_query($sql); 
        $r=mysql_fetch_object($q5); 
        $count=$count+$r->c;
	#return $sql;
	return $count;

}
Function toMaskBits($mask){
	$netbits=32-gmp_scan1($mask,0);
	return $netbits;
}
include("ipconvert.inc");
Function getColor($index){
	$c[0]="#00AA00";
	$c[1]="#00FF00";
	$c[2]="#AAFF00";
	$c[3]="#FFFF00";
	$c[4]="#FF6600";
	$c[5]="#FF0000";
	$divisor=102/count($c);
	$i=floor($index/$divisor);
	return $c[$i];

}
Function writeRow($network,$assignment,$height,$color,$available,$used,$j,$rowcolor){
	$percentage=ceil(($used/($used+$available))*100);
	$element="document.all.block$j.style";
	echo "<tr height=$height id=row$j bgColor=$rowcolor>
		  <td class=maintable align=left>$network</td>
		  <td class=maintable align=left width=120>$assignment</td>
		  <td class=maintable align=center>".$available."</td>                    
                  <td class=maintable align=center>$used</td>
                  <th onMouseOver='showBlock(document.all.block$j,this);' 
			class=maintableUsage width=30 bgcolor=$color>$percentage%
		</th></tr>";


}


include("getEditBlockLayer.inc");
include("getBlockDetailLayer.inc");

Function showIPBlocks($network,$netend){
	$layers=array();
	$editBlockLayers=array();
	$sql="select distinct RouterIPs.network,RouterIPs.mask,IPAllocations.ownerid,IPAllocations.Description,IPAllocations.Notes 
		from RouterIPs left join IPAllocations using(network,mask)
		where RouterIPs.network>=$network and RouterIPs.network<$netend order by RouterIPs.network,mask desc";
	$q2=mysql_query($sql);
	#echo $sql;
	echo "<body bgcolor=white>";
	echo "<div id=dummy></div><div id=maintable style=\"position: absolute ; top: 0; left: 0; background-color: white\">
		<table border=0 width=335 bgcolor=black>
		<tr bgcolor=white><th class=maintable>IP Block</th><th class=maintable>Assignment</th>
		<th class=maintable>Available</th>
		<th class=maintable colspan=2>Used</th></tr>";
	$prevNetwork=0;
	for ($j=0;$j<mysql_num_rows($q2);$j++){
		$r=mysql_fetch_object($q2);
		if (($j%2)==0){
			$rowcolor="#dddddd";
		}else{
			$rowcolor="#cfcfcf";
		}
		$subnetsize=computeNetSize($r->mask);
		$usage=computeNetAllocation($r->network,$subnetsize);


		#check to see if this block can be merged with previous one
		$mergeStr="";
		if (($previousNetSize==$subnetsize)&&($previousUsage==0)&&($usage==0)){
			#possibly a candidate for merging into larger block;
			$blockEnd=gmp_sub($r->network,$network);
			$mergedNet=gmp_strval(gmp_sub($r->network,$subnetsize));
			$blockEnd=gmp_add($blockEnd,$subnetsize);
			$mergedSize=$subnetsize*2;
			$mergedNetBits=32 - log2($mergedSize);
			$gcd=gmp_intval(gmp_gcd($blockEnd,$mergedSize));
			if ($gcd==$mergedSize){
				$mergeStr="<th height=20><form mergeFrm$j>
					<input type=hidden name=updateAction value=3>
					<input type=hidden name=network value=$network>
					<input type=hidden name=mynetwork value=$mergedNet>
					<input type=hidden name=mergedSize value=$mergedSize>
					<input type=submit value=\"Collapse\"></th></form>";
			}
		}
		$available=$subnetsize-$usage;
		if ($subnetsize>2){
			#take away network and broadcast addresses from available address count
			$available=$available-2;
		}
		$usagePercent=ceil($usage/($available+$usage)*100);
		$color=getColor($usagePercent);
		$height=30;#getHeight($subnetsize);
		$netstr="<table width=100% border=0 bgcolor=$rowcolor><tr><td class=maintable>".toIP($r->network).
			"</td><td class=maintable>/".toMaskBits($r->mask)."</td></tr></table>";
		$assignment="<a href=javascript:showEditBlock(document.all.editBlock$j,document.all.row$j);>$r->Description</a>";
		if ($previousNet!=$r->network){
			writeRow($netstr,$assignment,$height,$color,$available,$usage,$j,$rowcolor);
			$layers[$j]=getBlockDetailLayer($r->network,$subnetsize,$j,$rowcolor,$usage,$mergeStr,$r->Notes);
			$editBlockLayers[$j]=getEditBlockLayer($network,$r->network,$r->mask,$r->Description,$j,$r->Notes,$r->ownerid);
		}else{
			print "Mask inconsistencies for network $r->network (".toIP($r->network).")<br>";
		}
		$previousNetSize=$subnetsize;
		$previousUsage=$usage;
		$previousNet=$r->network;
		$nextNet=gmp_add($r->network,$subnetsize);
	}
	echo "</table></div>";
	for ($j=0;$j<mysql_num_rows($q2);$j++){
		echo $editBlockLayers[$j];
		echo $layers[$j];

	}

	echo "<script>var lastBlock=document.all.dummy; var lastEditBlock=document.all.dummy;</script>
	<form name=subnetForm>
	<input type=hidden name=network value=$network>
	<input type=hidden name=sql><input type=hidden name=updateAction value=2>
	</form>";

}


?>

<script>
	function getRealTop(el) {
	   	yPos = el.offsetTop;
		tempEl = el.offsetParent;
    		while (tempEl != null) {
        		yPos += tempEl.offsetTop;
        		tempEl = tempEl.offsetParent;
    		}
    		return yPos;
	}
	function showBlock(block,tableElement){
		block.style.pixelTop=getRealTop(tableElement);
		
		//is this the same block as last selected
		if(lastBlock.id==block.id){
			if (block.style.visibility=="hidden"){
				block.style.visibility="visible";
			}else{
				//block.style.visibility="hidden";
			}
		}else{
			lastBlock.style.visibility="hidden";
			block.style.visibility="visible";
			lastBlock=block;
		}
	}

        function showEditBlock(block,tableElement){
                block.style.pixelTop=getRealTop(tableElement);

                //is this the same edit block as last selected
                if(lastEditBlock.id==block.id){
                        if (block.style.visibility=="hidden"){
                                block.style.visibility="visible";
                        }
                }else{
                        lastBlock.style.visibility="hidden";
			block.style.zIndex=1;
                        block.style.visibility="visible";
                        lastBlock=block; 
                }


        }

	function hideBlock(block){
		block.style.visibility="hidden";
	}
	function openBlockAllocate(j,network,netsize){
		var netbits=32-Math.log(netsize)/Math.log(2);
		var url="blockAllocate.php?netbits="+netbits+"&network="+network+"&j="+j;
		var w=630;
		var h=540;
		var l = (screen.width - w) / 2;
		var t = (screen.height - h) / 2;
		var features="dependent=yes,resizable=no,width="+w+",height="+h+",left="+l+",top="+t;
		var con=window.open(url,"Subnet",features);
		con.creator=self;
	}

</script>


