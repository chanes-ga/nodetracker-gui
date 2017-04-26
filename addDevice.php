<?php
	include ("addDevice.css");

	if ($type==2){
		print "<h3>Add Switch</h3><br>";
	}else{
		print "<h3>Add Router</h3><br>";
	}
	print "<form method=post action=addDeviceNow.php><table border=0 width=350>
		<input type=hidden name=devicetype value=$type>";

?>

                        <tr><th>Device Description</th>
		
			<td><input type=text name=description maxlength=30></td></tr>

                        <tr><th>SNMP RO community</th><td>
			<input type=text name=public maxlength=64>
			<input type=checkbox name=cleartext>Clear text?
			</td></tr>
			<tr><th>Primary IP</th>
			<td><input type=text name=primaryip></td></tr>
			<tr><th>MAC</th>
			<td><input type=text name=mac></td></tr>

			<tr><th colspan=2><input type=submit value=" Save "></tr>
</table>

                        </form>

