<?php
        Function getIfDescHTML($shortoid){
                $sql="select shortoid,description from SNMP_OID order by description";
                $q=mysql_query($sql);
                $r=mysql_fetch_object($q);
                $html="<select name=ifDescShortOID><option value=0>User Supplied";
                while ($r){
                        if ($shortoid==$r->shortoid){
                                $selected="selected";
                        }else{
                                $selected="";
                        }
                        $html=$html."<option value=$r->shortoid $selected>$r->description\n";
                        $r=mysql_fetch_object($q);
                }
                $html=$html."</select>\n";
                return $html;
        }

        Function getGroupHTML($groupid){
                $sql="select * from Groups order by name";
                $q=mysql_query($sql);
                $r=mysql_fetch_object($q);
                $html="<select name=groupid>";
                while ($r){
                        if ($groupid==$r->id){
                                $selected="selected";
                        }else{
                                $selected="";
                        }
                        $html=$html."<option value=$r->id $selected>$r->name\n";
                        $r=mysql_fetch_object($q);
                }
                $html=$html."</select>\n";
                return $html;
        }
        Function getOwnerHTML($ownerid){
                $sql="select * from Owners order by name";
                $q=mysql_query($sql);
                $r=mysql_fetch_object($q);
                $html="<select name=ownerid>";
                while ($r){
                        if ($ownerid==$r->id){
                                $selected="selected";
                        }else{
                                $selected="";
                        }
                        $html=$html."<option value=$r->id $selected>$r->name\n";
                        $r=mysql_fetch_object($q);
                }
                $html=$html."</select>\n";
                return $html;
        }

	Function getEditBlock($nodeID,$device,$runNMAP,$public,$primaryIP,$type,$shortoid,$snmpver,$groupid,$icmpscan,$active,$ownerid){
		$ifdescHTML=getIfDescHTML($shortoid);
		$groupHTML=getGroupHTML($groupid);
		$ownerHTML=getOwnerHTML($ownerid);
                $html="<div id=editBlock
                        style=\"position: absolute;
                        top: 10; left: 10;
                        width: 350;
                        background-color: #cccccc;
                        visibility: hidden\">
                        <form id=editBlockFrm name=editBlockFrmName><table border=0 width=350>
                        <tr><th>Device Description</th>
			<td><input type=text name=description value=\"$device\" maxlength=30></td></tr>\n
                        <tr><th>SNMP RO community</th><td>
			<input type=text name=public value=\"$public\" maxlength=64>
			<input type=checkbox name=cleartext>Clear text?
			</td></tr>
			<tr><th>SNMP Version</th><td><input type=text size=5 name=snmpver value=$snmpver></td></tr>\n";

                $html=$html."<tr><th>If Description OID</th><td>$ifdescHTML</td></tr>
				<tr><th>Scan with NMAP?</th><td>";
                if ($runNMAP==0)
                        $html=$html."<input type=checkbox name=nmap>";
                else
                        $html=$html."<input type=checkbox name=nmap checked>";

		$html=$html."</td></tr>\n<tr><th>ICMP Monitor?</th><td>";
                if ($icmpscan==0)
                        $html=$html."<input type=checkbox name=icmpscan>";
                else
                        $html=$html."<input type=checkbox name=icmpscan checked>";
		
                $html=$html."</td></tr>\n<tr><th>Node Active?</th><td>";
                if ($active==0)
                        $html=$html."<input type=checkbox name=active>";
                else
                        $html=$html."<input type=checkbox name=active checked>";


                $html=$html."</td></tr>\n<tr><th>Primary IP</th>
			<td><input type=text name=primaryip value=\"$primaryIP\"></td></tr>\n
			<tr><th>Device Type</th><td><select name=devicetype>";
		switch ($type){
			case 0:
				#ordinary node
				$typeoption="Node";
				break;
			case 2:
				#layer 2 device
				$typeoption="Switch";
				break;
			case 3:
				#layer 3 device
				$typeoption="Router";
				break;
		}
                $html=$html."
			<option selected value=$type>$typeoption
			<option value=0>Node
			<option value=2>Switch
			<option value=3>Router
			</select>
			</th></tr>

			<tr><th>Group</th><td>$groupHTML</td></tr>
			<tr><th>Owner</th><td>$ownerHTML</td></tr>
	
			<tr><th colspan=2><input type=submit value=\" Save \"> &nbsp
                        <input type=button value=\" Cancel \" onClick=\"showBlock(document.all.editBlock,0);\"></tr>\n
			<input type=button value=\" Delete \" onClick=\"verify2(document.editBlockFrmName);\"></tr>\n
			</table>\n
                        <input type=hidden name=updateRecord value=1>
                        <input type=hidden name=nodeID value=$nodeID>
                        </form>\n
                        </div>\n";
		return $html;
	}
?>
