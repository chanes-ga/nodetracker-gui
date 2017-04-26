<?php
	Function getOIDInstances($nodeID,$excludeSQL){

		$html="<div id=oidInstancesBlock
                        style=\"position: absolute;
                        top: 70; left: 370;
                        width: 360;
                        background-color: #dddddd;
                        \">";

		$tmp=array();
		$sql="select OID_Instances.shortoid as shortoid,lasterror,plotType,
			rrd_graphType,rrd_dataType,rrd_stacked,
			refid,lasttime,OID_Instances.shortoid,description,ignoredvalue 
			from OID_Instances left join SNMP_OID using(shortoid) 
			where nodeID=$nodeID order by description";
		#print $sql;
		$q2=mysql_query($sql);
		if(mysql_num_rows($q2)>0){
			$html=$html."<table width=360 border=0><tr><th>SNMP Monitor</th><th>Last Updated</th><th>Action</th></tr>";
		}
		for ($i=0;$i<mysql_num_rows($q2);$i++){
        		$row2=mysql_fetch_object($q2);
			array_push($tmp,$row2->shortoid);
			if (!is_null($row2->ignoredvalue)){
				$ignoredparam=$row2->ignoredvalue;
			}else{
				$ignoredparam="null";
			}
			$html=$html."<tr><td width=100>$row2->description<br><font size=-3>$row2->refid</font><br>
				<font size=1 color=red>$row2->lasterror</font></td>
				<td>".strftime("%D %T",$row2->lasttime)."</td><th width=120>
				<form name=monitorActionFrm$row2->refid>
				<input type=hidden name=updateRecord value=3>
				<input type=hidden name=refid value=$row2->refid>
				<input type=hidden name=nodeID value=$nodeID>
				<input type=button value=Graph 
				
onClick=openGraphWindow($row2->shortoid,$row2->refid,'$row2->rrd_dataType',$row2->rrd_stacked,'$row2->rrd_graphType',$row2->plotType,$ignoredparam)>
				<input type=button value=Delete onClick='verify(document.monitorActionFrm$row2->refid);'>
				</th></tr></form>";
		}
		$html=$html."</table></div>\n";
		$excludeSQL=join(",",$tmp);
		return $html;
	
	}
?>
