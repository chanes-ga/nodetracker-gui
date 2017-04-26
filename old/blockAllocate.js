<SCRIPT LANGUAGE="JavaScript1.2">
     var selectedElem = null;    //holds reference to a selected object
     var offsetX, offsetY;       //hold click location relative to object
     var doc=null;
     var ipblockSize;
     var ipblockBase;
     var scalefactor;
     var blockid=10;		//start here to allow us to use lower values for initial blocks
     var ip=new Array();
     var blockDescriptor=new Array();
     var subnet=new Array();
// Determines which element has been selected
function whichObject(evt) {
    var imgElem = window.event.srcElement;
    if (imgElem.parentElement.id){
    	selectedElem = imgElem.parentElement;
    }else{
	selectedElem=null;
    }
    return;
}                       

// Determines the mouse cursor position relative to element
function selectIt(evt) {
    whichObject(evt);
    if (selectedElem) {   
        selectedElem.style.zIndex = 100;
	if (selectedElem.style.slot){
		blockDescriptor[parseInt(selectedElem.id)].style.visibility="hidden";
	}
           offsetX = window.event.offsetX;
           offsetY = window.event.offsetY;
    }
    return false;
}
        
// Positions the dragging element
function dragIt(evt) {
    if (selectedElem) {
          selectedElem.style.pixelLeft = window.event.clientX - offsetX;
          selectedElem.style.pixelTop = window.event.clientY - offsetY;
          return false
    }
}

// Releases the element
function dropIt() { 
   if (selectedElem){
      insideBlock();
      selectedElem.style.zIndex = 0;
   }
      selectedElem = null;
} 

function insideBlock(){
	blockLE=selectedElem.style.pixelLeft;
	blockRE=blockLE+selectedElem.clientWidth;
	blockBE=selectedElem.style.pixelTop;
	blockTE=blockBE+selectedElem.clientHeight;
	ipblockLE=doc.ipblock.style.pixelLeft;
	ipblockRE=ipblockLE+doc.ipblock.clientWidth;
	ipblockBE=doc.ipblock.style.pixelTop;
	ipblockTE=doc.ipblock.clientHeight+ipblockBE;		
	//doc.cah.ta.value=ipblockLE+" "+ipblockRE+"\n"+blockLE+" "+blockRE;
	//doc.cah.ta.value=ipblockTE+" "+ipblockBE+"\n"+blockTE+" "+blockBE;
	if (((ipblockLE>=blockLE)&&(ipblockLE<=blockRE))||((ipblockRE>=blockLE)&&(ipblockRE<=blockRE))){
		//block is inside horizontally; check its vertical position
		if ((ipblockTE>=blockBE)&&(ipblockBE<=blockTE)){
			//vertical position is correct
			height=blockTE-ipblockBE;
			maxSlot=(ipblock.style.pixelHeight/selectedElem.currentStyle.pixelHeight);
			slot=Math.round(height/selectedElem.currentStyle.pixelHeight);
			if(slot==0){
				slot=1;
			}
			if(slot>maxSlot){
				slot=maxSlot;
			}
			finalHeight=ipblockBE+(slot-1)*selectedElem.currentStyle.pixelHeight;	
			

			//verify no other block occupies the target space
			if (ipblockOccupied(slot,selectedElem.currentStyle.blockSize)==0){				
				//indicate block has been dropped successfully
				selectedElem.style.pixelLeft=ipblockLE;
				selectedElem.style.pixelTop=finalHeight;			
				selectedElem.style.droppedBefore=1;
				markAsOccupied(slot,selectedElem.currentStyle.blockSize,parseInt(selectedElem.id));

				doc.cah.ta.value+=ipblockBE+" "+slot+" "+selectedElem.currentStyle.blockSize+"\n";
				replaceBlock(); 
			}else{
				//user tried to put block is a space already used; reset!
				replaceBlock();
				removeBlock();
			}

		}else{
			removeBlock();
		}
	}else{
		//this block is totally outside target area and is not a never dropped block
		if (selectedElem.style.slot){
			removeBlock();
		}
	}

} 
function markAsOccupied(slot,blocksize,id){
	//has this block occupied a different slot; if so, clear the old slot
	if (selectedElem.style.slot){
		setIPRange(selectedElem.style.slot,blocksize,0);
	}else{
		//first time
		makeBlockDescription(selectedElem);
	}
	//set the new slot and mark as occupied
	selectedElem.style.slot=slot;
	subnet[id]=selectedElem;
	setIPRange(slot,blocksize,id);
	setBlockDescription(selectedElem);
}
function setIPRange(slot,blocksize,val){
        var sOffset=(slot-1)*blocksize;
        var eOffset=sOffset+parseInt(blocksize);
        var i;
	//doc.cah.ta.value="setIPRange:" +slot+" "+blocksize+" "+val+"\n";
        for (i=sOffset;i<eOffset;i++){
		ip[i]=val;
        }
}
function ipblockOccupied(slot,blocksize){
	var sOffset=(slot-1)*blocksize;
	var eOffset=sOffset+parseInt(blocksize);
	var i;
	var occupied=0;
	//doc.cah.ta.value=sOffset+" "+eOffset+"\n";
	for (i=sOffset;i<eOffset;i++){
		if (ip[i]!=0){
			occupied=1;
		}
	}
	return occupied;
}
function removeBlock(){
	//block is not within bounds; if it was previously dropped and now removed, delete it
        if (selectedElem.style.slot){
                setIPRange(selectedElem.style.slot,selectedElem.currentStyle.blockSize,0);
		blockDescriptor[parseInt(selectedElem.id)].removeNode(true);
        }

	selectedElem.removeNode(true);

}

function makeBlockDescription(block){
	var i=parseInt(block.id);
	blockDescriptor[i]=document.createElement("DIV");
	blockDescriptor[i].className="blockInfo";
	blockDescriptor[i].style.left=doc.ipblock.style.pixelLeft+160;
	blockDescriptor[i].style.width=100;
	blockDescriptor[i].style.top=block.currentStyle.top;
//	var blockh=parseInt(block.currentStyle.pixelHeight);
//	var h = blockh/2;
	
	document.body.insertBefore(blockDescriptor[i],null);
}
function setBlockDescription(block){
	var i=parseInt(block.id);
	var size=parseInt(block.currentStyle.blockSize);
	var h=parseInt(block.currentStyle.pixelHeight)/2;
	var h2=parseInt(blockDescriptor[i].currentStyle.height)/2;
	var t=parseInt(block.currentStyle.top)+h - h2;
	var network=ipblockBase+size*(parseInt(block.currentStyle.slot)-1);
	var mask=32-Math.log(parseInt(block.currentStyle.blockSize))/Math.log(2);
	//blockDescriptor[i].innerHTML="<center>"+toIP(network)+"/"+mask+" </center>";
	blockDescriptor[i].innerHTML="<div style=\"border: 1 1 1 1;border-color:#000000 #000000 #000000 #000000;border-style:solid;background-color: #eeeeee\"><center>"+toIP(network)+"/"+mask+" </center></div>";
	blockDescriptor[i].style.top=t;
	blockDescriptor[i].style.visibility="visible";
}
function replaceBlock(){
	//get next id for a new block
	blockid+=1;
	var newBlock=document.createElement("DIV");
	newBlock.id=blockid;
        var newImage=document.createElement("IMG");
        newImage.src="images/slash"+selectedElem.className+"_"+scalefactor+".png";
	newImage.height=selectedElem.currentStyle.pixelHeight;
        newBlock.insertBefore(newImage, null);
        newBlock.className=selectedElem.className;
        document.body.insertBefore(newBlock, null);
}
function toIP(val){
	hex=val.toString(16);
	while (hex.length<8){
		hex="0"+hex;
	}
	a=parseInt(hex.substr(0,2),16);
	b=parseInt(hex.substr(2,2),16);
	c=parseInt(hex.substr(4,2),16);
	d=parseInt(hex.substr(6,2),16);

	var myip=a+"."+b+"."+c+"."+d;
	return myip;
}


function init(e){
        e.style.pixelLeft=parseInt(e.currentStyle.left);
        e.style.pixelTop=parseInt(e.currentStyle.top);
        e.style.pixelHeight=parseInt(e.currentStyle.height);
        ipblockBase=parseInt(e.currentStyle.network);
        ipblockSize=e.currentStyle.blockSize;
        for(i=0;i<ipblockSize;i++){
                ip[i]=0;
        }
}
// Captures and handles the events
function eventHandling(scale) {
        scalefactor=scale;
        document.onmousedown = selectIt;
        document.onmousemove = dragIt;
        document.onmouseup = dropIt;
        doc=document.all;
        init(doc.ipblock);
}


function closeBlockAllocate(){
	var sql="";
	sql=buildSQL();
	if (sql!=""){
		creator.document.subnetForm.sql.value=sql;
		creator.document.subnetForm.submit();
		window.close();
	}else{
		alert("Please completely subnet this block!");
	}
}


</SCRIPT>

 



