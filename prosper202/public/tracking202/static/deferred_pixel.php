var url = "//<?php echo $_SERVER['SERVER_NAME'].get_absolute_url(); ?>tracking202/static/check_conversion.php";
var xhr = new XMLHttpRequest();
var theInterval =''
var counter = 0;
var intervalTime = 30  * 1000 //30 seconds

function activateDeferredPixel(){
    if( window.Worker){
   
    var blobCode=['self.onmessage=function(a){url=a.data+"//<?php echo $_SERVER['SERVER_NAME'].get_absolute_url(); ?>tracking202/static/check_conversion.php?t202subid=<?php echo $click_id; ?>";xhr=new XMLHttpRequest;intervalTime=3E4;theInterval=setInterval(b,intervalTime)};function b(){xhr.open("GET",url,!0);xhr.onload=function(){4===xhr.readyState&&202===xhr.status&&(clearInterval(theInterval),postMessage("DeferredPixelFire202-"+xhr.responseText))};xhr.send(null)};'];
    var blob = new Blob(blobCode, {type :'application/javascript'});

    var worker = new Worker(window.URL.createObjectURL(blob));
    worker.postMessage(document.location.protocol); // Start the worker.
    
    worker.onmessage = function(e) {
        theData = e.data.split('-')
        if(theData[0]=='DeferredPixelFire202'){
            if(dcs.t202DataObj.pixel_id && dcs.t202DataObj.pixel_id != 0){             
              activateBot202PixelFBAssistantPurchase(theData[1]);
            }else{
              loadPixel();
            }            
        }
	}
}
else{
    var theInterval = setInterval(pingServer, intervalTime);
} 
}

function stopPing() {
    clearInterval(theInterval);
}
  
function pingServer() {
    xhr.open('GET', url+'?t202subid=<?php echo $click_id; ?>', true);
    xhr.onload = function(e) {
      if (xhr.readyState === 4) {
        if (xhr.status === 202) {
            stopPing(); 
            if(dcs.t202DataObj.pixel_id && dcs.t202DataObj.pixel_id != 0){
              activateBot202PixelFBAssistantPurchase(xhr.responseText);
            }else{
              loadPixel();
            }    
        } 
      }
    };
    xhr.send(null);
  }
  
function loadPixel() {
    (function(d, s) {
      var js,
        upxf = d.getElementsByTagName(s)[0],
        load = function(url, id) {
          if (d.getElementById(id)) {
            return
          }
          if202 = d.createElement('iframe')
          if202.src = url
          if202.id = id
          if202.height = 0
          if202.width = 0
          if202.frameBorder = 0
          if202.scrolling = 'no'
          if202.noResize = true
          upxf.appendChild(if202)
        };
      load(url + '?show=1&t202subid=<?php echo $click_id; ?>', 'defpixif')
    })(document, "head")
  }