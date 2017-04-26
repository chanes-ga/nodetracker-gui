<?
	Function getICMPBlock($ip){
                $html= "\n<div id=icmpBlock
                        style=\"position: absolute;
                        top: 10; left: 370;
                        width: 170; height:40;
                        background-color: #dddddd;
                        \"><center><table border=0><tr><th colspan=2>ICMP Stats</th></tr><tr><form>";

		$html=$html."<td><input type=button value=\"Latency\" onClick=\"openICMPWindow('1','$ip');\"></td><td> ";
		$html=$html."<input type=button value=\"Packet Loss\" onClick=\"openICMPWindow('2','$ip');\"></td>
				</form></tr></table> ";
                $html=$html."</div>\n";  
		#$html="";
		return $html;

	}
?>
