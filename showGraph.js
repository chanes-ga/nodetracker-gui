<script>
        function showGraph(ifnum,inrefid,outrefid,ifdesc,speed){
                //url="showGraph.php?ip="+ip+"&ifnum="+ifnum+"&ifdesc="+escape(ifdesc)+"&speed="+speed;
                url="showGraph.php?ifnum="+ifnum+"&inrefid="+inrefid+"&outrefid="+outrefid+"&ifdesc="+escape(ifdesc)+"&speed="+speed;
                var w=700;
                var h=520;
                var l = (screen.width - w) / 2;
                var t = (screen.height - h) / 2;
                var features="dependent=yes,resizable=no,width="+w+",height="+h+",left="+l+",top="+t;

                window.open(url,"",features);
        }

</script>

