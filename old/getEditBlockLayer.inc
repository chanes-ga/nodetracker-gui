<style type="text/css">
.editBlock{	position: absolute;
                top: 0; left: 0;
                width: 335;
                height:300;
                overflow: hidden;
                background-color: #dddddd;
                font-family: Arial, Helvetica, non-serif; font-size: 9pt; color: black;
                border: 2 2 2 2;
                border-color:#00000 #000000 #000000 #000000;
                border-style: solid;
                padding: 8;
                visibility: hidden
	}
</style>

<?
Function getEditBlockLayer($wholeblock,$mynetwork,$mymask,$description,$j,$notes){
        $html="\n<div id=editBlock$j class=editBlock>
                <form name=editBlock".$j."Form>
                <input type=hidden name=network value=$wholeblock>
                <input type=hidden name=mynetwork value=$mynetwork>
                <input type=hidden name=mymask value=$mymask>
                <input type=hidden name=updateAction value=1>Block Name:
                <input type=text name=description maxlength=50 size=40 value=\"$description\">
                <br>Notes:<br>
                <textarea name=notes cols=30 rows=12>$notes</textarea><br>
                <input type=button value=Cancel onClick=\"hideBlock(editBlock$j);\">
                <input type=submit value=Save>
                </form>
                </center>  
        </div>
        ";
        return $html;
}
?>
