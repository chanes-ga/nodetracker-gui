<HTML>
<HEAD>
<style>
        TD{ font-family: Arial, Helvetica, non-serif; font-size: 8pt; color: black; background-color:#eeeeee}
        TH{ font-family: Arial, Helvetica, non-serif; font-size: 8pt; color: black;}

</style>
<?php
	include("blockAllocate.js");
?> 
</HEAD>



<?php
	$scale=pow(2,($netbits-25));	#all style elements created based on /25 blocksize
	#echo $scale;		
	$left=30;
	#create blocks to allocate from
	switch($netbits){
		case 24:
		case 25:
			$smallestBlock=29;
			break;
		case 26:
			$smallestBlock=30;
		case 27:
		case 28:
		case 29:
		case 30:
		case 31:
			$smallestBlock=32;

	}


	$id=1;
	$style="<STYLE TYPE=\"text/css\">\n
		.blockImg{z-index:0};
		.blockInfo {position:absolute; top:0; left:0; z-index:1; height:13;
				background-color:white; 
				font-family: Arial, Helvetica, non-serif; font-size: 10pt; color: black};
		#ipblock {position:absolute; left:".($left+200)."; blockSize:".pow(2,(32-$netbits))."; network: $network;
                	top:10; width:152; height:512; z-index:-10;border: 2px outset white;
                        background: buttonface;}\n";

	$div="";
	$yoffset=10;
	for($i=($netbits+1);$i<=$smallestBlock;$i++){
		if ($i!=31){
			$height=$scale*512/(pow(2,$i-25));
			$style=$style.".$i{position:absolute; left:$left; top:$yoffset;";
			$style=$style." pixelHeight:$height; blockSize:".pow(2,32-$i)."; pixelWidth:150; z-index:0;}\n";
			$div=$div."\n<div id=$id class=$i>
				<img class=blockImg src=/$db/common/images/slash".$i."_".$scale.".png height=$height width=150>
				\n</div>\n";	
			$yoffset+=$height+5; #5px separation between block divs
			$id++;
		}
	}
	$style=$style."</STYLE>";
	echo $style;
	echo "<BODY onLoad=\"eventHandling($scale)\">";
	echo $div;

?>
<DIV ID=ipblock>
</DIV>
<script>
	function getMask(hostbits){
                var tmp=parseInt("0xFFFFFFFF");   
                mask=tmp-(Math.pow(2,hostbits))+1;
		return mask;
	}
	function buildSQL(){
		var blocksize,slot, network;
		var s=";";
		var incomplete=0;
		var prevID=0;
		var curID=ip[0];
		var hostbits=Math.log(ipblockSize)/Math.log(2);
		mask=getMask(hostbits);
		var sql="delete from RouterIPs where network="+ipblockBase+ " and mask="+mask;
		sql=sql+s+"delete from IPAllocations where network="+ipblockBase+" and mask="+mask;
		for (i=0;i<ipblockSize;i++){
			curID=ip[i];
			if (ip[i]==0){
				incomplete=1;
			}else{
				//then block exists
	                        if (curID!=prevID){
        	                        //document.all.cah.ta.value=document.all.cah.ta.value+i+":"+ip[i]+"\n";  
	               	                blocksize=subnet[ip[i]].currentStyle.blockSize;
                			hostbits=Math.log(blocksize)/Math.log(2);
                			mask=getMask(hostbits);
                        	        slot=subnet[ip[i]].currentStyle.slot;
                                	network=ipblockBase+(slot-1)*blocksize;
	                                sql=sql+s+"insert into RouterIPs values(1,"+network+","+mask+","+network+",0,0)";
	                                sql=sql+s+"insert into IPAllocations(network,mask,description) values("+network+","+mask+",\"PreAllocated\")";
        	                }
			}
			prevID=curID;
		}

		if(incomplete==1){
			sql="";
		}
		return sql;
	}



</script>
<div style="position: absolute; left=530; top=450">
<form name=cah>
<input type=hidden name=ta>

<!textarea rows=5 cols=10 name=ta></textarea><br>

<table>
<tr>
<th>
<input type=button value="   Save    " onClick=closeBlockAllocate()>
</th></tr><tr>
<th>
<input type=button value="  Cancel  " onClick="window.close();">
</th>
</tr>
</table>
</form>
</div>
<div style="position: absolute; left=430; top:20;
		border: 2 2 2 2;
		border-color:#000000 #000000 #000000 #000000;
		border-style: solid;
		background-color: #cccccc
		"

>
<table border=0 cellspacing=3>
<tr><th>Network Bits</th><th valign=top>Network Mask</th><th align=right>Usable IPs</th></tr>
<tr><td align=center>32</td><td>255.255.255.255</td><td align=right>1</td></tr>
<tr><td align=center>30</td><td>255.255.255.252</td><td align=right>2</td></tr>
<tr><td align=center>29</td><td>255.255.255.248</td><td align=right>6</td></tr>
<tr><td align=center>28</td><td>255.255.255.240</td><td align=right>14</td></tr>
<tr><td align=center>27</td><td>255.255.255.224</td><td align=right>30</td></tr>
<tr><td align=center>26</td><td>255.255.255.192</td><td align=right>62</td></tr>
<tr><td align=center>25</td><td>255.255.255.128</td><td align=right>126</td></tr>
<tr><td align=center>24</td><td>255.255.255.0</td><td align=right>254</td></tr>

</table>
</div>

</BODY>
</HTML>

 



