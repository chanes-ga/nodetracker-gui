<?
	Function getMonitorBlock($nodeID,$usedOIDList){
                $html= "\n<div id=monitorBlock
                        style=\"position: absolute;
                        top: 10; left: 10;
                        width: 370; height:80;
                        background-color: #999999;
			overflow:hidden;
                        visibility: hidden\"> 
                        <form id=monitorBlockFrm>
                        <input type=hidden name=updateRecord value=2>
                        <input type=hidden name=nodeID value=$nodeID>
			<table width=370 height=80><tr><th>OID Template</th><th>
                        <select name=shortoid>";
		if (strlen($usedOIDList)>0){
			$wheresql=" where shortoid not in($usedOIDList) ";
		}else{
			$wheresql="";
		}
                $sql="select shortoid, description from SNMP_OID $wheresql order by description";
                $q2=mysql_query($sql);
                for ($i=0;$i<mysql_num_rows($q2);$i++){
                        $row=mysql_fetch_row($q2);
                        $html=$html."<option value=$row[0]>$row[1]\n";
                }

                $html=$html."</select>\n</th></tr><tr><th colspan=2>
			<input type=submit value=\"   Add   \">
                        <input type=button value=\" Cancel \" onClick=\"showBlock(document.all.monitorBlock,0);\">\n";
 		$html=$html."</th></tr></table>
                        </form>
                        </div>\n";  
		return $html;

	}
?>
