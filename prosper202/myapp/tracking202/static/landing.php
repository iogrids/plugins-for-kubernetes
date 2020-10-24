<?php
header('Content-type: application/javascript');
ob_start();
use UAParser\Parser;

include_once(substr(dirname( __FILE__ ), 0,-19) . '/202-config/connect2.php');
include_once (ROOT_PATH. '202-config/class-dataengine-slim.php');

//now this sets timezone
$timezone_sql = 'SELECT user_timezone from 202_users where user_id = 1';
$timezone_row = memcache_mysql_fetch_assoc($db, $timezone_sql);
if($timezone_row !== ''){
    date_default_timezone_set($timezone_row['user_timezone']);
}
else{
    date_default_timezone_set('UTC');
}
?>

if (!Array.prototype.indexOf) {
  Array.prototype.indexOf = function(value) {
    for (var i = 0; i < this.length; i++) {
      if (this[i] === value) {
        return i;
      }
    }

    return -1;
  }
}

function  t202GetVar(name){
var match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
    var urlvar=match && decodeURIComponent(match[1].replace(/\+/g, ' '))
    
    if( urlvar){
    return decodeURIComponent(urlvar)
    }
    else{
    return '';
    }
}

function t202GetAllVars(url) {
if(url===undefined || url=== ''){
	var url = window.location.href;
}

var vars = {};
url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
	vars[key.toLowerCase()] = value;
});

return vars;
}
	
function createCookie(name,value,days=30) {   
    if(trackingEnabled()){ //only set cookies if tracking is enabled
	   if (days) {
		  var date = new Date();
		  date.setTime(date.getTime()+(days*24*60*60*1000));
		  var expires = "; expires="+date.toGMTString();
	   }else{
          var expires = "";
       } 
      
     document.cookie = name+"="+value+expires+"; path=/;domain="+document.location.hostname+";secure;samesite=none";
     document.cookie = name+"-legacy="+value+expires+"; path=/;domain="+document.location.hostname   
    }
}

function trackingEnabled()
{
    return <?php echo (trackingEnabled()? 'true' : 'false'); ?>;
}

function readCookie(name) {
    var nameEQ = name + "=";
    var nameEQLegacy = name + "-legacy=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return urldecode(c.substring(nameEQ.length,c.length));
        if (c.indexOf(nameEQLegacy) == 0) return urldecode(c.substring(nameEQLegacy.length,c.length));
	}
	return false;
}

function eraseCookie(name) {
	createCookie(name,"",-1);
}

function ucfirst(string) {
    var rest = string.toLowerCase().slice(1);
    return string.charAt(0).toUpperCase() + rest;
}

function t202Data() {
var t202vars=t202GetAllVars();

	<?php 
$data = getGeoData($ip_address); 
		if($data['country']==='Unknown country')
		    $data['country']=''; //set to blank if it's unknown
		if($data['country_code']==='non')
		   $data['country_code']=''; //set to blank if it's unknown
		if($data['region']==='Unknown region')
		    $data['region']=''; //set to blank if it's unknown
		if($data['city']==='Unknown city')
		    $data['city']=''; //set to blank if it's unknown
		if($data['postal_code']==='Unknown postal code')
		    $data['postal_code']=''; //set to blank if it's unknown
		//User-agent parser
		$parser = Parser::create();
		
		//Device type
		$detect = new Mobile_Detect;
		$ua = $detect->getUserAgent();
		$result = $parser->parse($ua);
		
            $IspData = getIspData($ip_address);
		    if($IspData==="Unknown ISP/Carrier")
		        $data['isp']=''; //set to blank if it's unknown
		    else 
		        $data['isp']=$IspData; //set to blank if it's unknown
		//set ip
		 $data['ip']=$ip_address->address; 

		//set date and time data

		 $timeNow= time();
		 
		 $data['DayOfWeek'] = date('l',$timeNow);
		 $data['DayOfWeekShort'] = date('D',$timeNow);
		 $data['MonthOfYear'] = date('F',$timeNow);
		 $data['MonthOfYearShort'] = date('M',$timeNow);
		 $data['DayOfMonth'] = date('j',$timeNow);
		 $data['Year'] = date('Y',$timeNow);
		 $data['YearShort'] = date('y',$timeNow);
         $data['gclid'] = '';
         
		echo "this.t202DataObj = {
        
	    
	    t202Pci: '',
		t202Id: '',
        t202Country: '".$data['country']."', 
        t202CountryCode: '".$data['country_code']."', 
        t202Region: '".$data['region']."', 
        t202City: '".$data['city']."', 
        t202Postal: '".$data['postal_code']."',
        t202Browser: '".$result->ua->family."',
        t202OS: '".$result->os->family."',
        t202Device: '".$result->device->family."',
		t202ISP: '".$data['isp']."',
		t202IP: '".$data['ip']."',
		t202DayOfWeek:'".$data['DayOfWeek']."',
	    t202DayOfWeekShort:'".$data['DayOfWeekShort']."',
	    t202MonthOfYear:'".$data['MonthOfYear']."',
	    t202MonthOfYearShort:'".$data['MonthOfYearShort']."',
	    t202DayOfMonth:'".$data['DayOfMonth']."',
		t202Year:'".$data['Year']."',
        t202YearShort:'".$data['YearShort']."',
       
	    "
	?>
t202Subid: t202GetSubid(),
t202SubidOrig: t202GetSubidOrig(),
t202Pci: getData(t202vars,'pci'),
t202Id: getData(t202vars,'t202id'),
t202Kw: getData(t202vars,'t202kw'),
t202C1: getData(t202vars,'c1'),
t202C2: getData(t202vars,'c2'),
t202C3: getData(t202vars,'c3'),
t202C4: getData(t202vars,'c4'),
t202Utm_source: getData(t202vars,'utm_source'),
t202Utm_medium: getData(t202vars,'utm_medium'),
t202Utm_term: getData(t202vars,'utm_term'),
t202Utm_content: getData(t202vars,'utm_content'),
t202Utm_campaign: getData(t202vars,'utm_campaign'),
t202Referrer: document.referrer,
t202Random: t202GetRand(1000000, 9999999),
t202SourceId: t202GetsourceId()


};

function t202GetRand(min, max) {
  min = Math.ceil(min);
  max = Math.floor(max);
  return Math.floor(Math.random() * (max - min + 1)) + min; //The maximum is inclusive and the minimum is inclusive 
}

var changes = changes || {};
    var renderData = []

//loop over named elements
for (var key in this.t202DataObj) {
    if (this.t202DataObj.hasOwnProperty(key)) {
        elements=document.querySelectorAll('[name*="'+key+'"]');
        if(elements.length != 0){
            for (var i = 0; i < elements.length; ++i) {
                
                var item = elements[i];
                if(this.t202DataObj[key]){
                    t202Value=this.t202DataObj[key];      //if we have a value in the dataObject use it
                }else{
                    t202Value=item.getAttribute('t202Default');      //if not use the default value
                }
                if(item.tagName=='INPUT'){
                    item.value = t202Value;
                }else{
                    item.innerHTML = t202Value;
                }
            }
        }
    }
}
    
//loop over t202attribute 
for (var key in this.t202DataObj) {
    if (this.t202DataObj.hasOwnProperty(key)) {
        elements=document.querySelectorAll('[t202attribute="'+key+'"]');
        if(elements.length != 0){
            for (var i = 0; i < elements.length; ++i) {
                var item = elements[i];
                if(this.t202DataObj[key]){
                    t202Value=this.t202DataObj[key];      //if we have a value in the dataObject use it
                }else{
                    t202Value=item.getAttribute('t202Default');      //if not use the default value
                }
                if(item.tagName=='INPUT'){
                    item.value = t202Value;
                }else{
                    item.innerHTML = t202Value;
                }
            }
        }
    }
}


};
/*
//merge data in
t202Data.prototype.updateDataObj = function(mergeDataObj){

var newDataObj = {};
    for (var attrname in this.t202DataObj) { newDataObj[attrname] = this.t202DataObj[attrname]; }
    for (var attrname in mergeDataObj) { 
        newDataObj[attrname] = mergeDataObj[attrname]; 
        renderData.push(attrname);
    }
  this.t202DataObj = newDataObj;
  
  //render changes to screen if needed
    this.updateDataObj(mergeDataObj)     

}
*/
t202Data.prototype.renderChanges = function(changes){
    if(Array.isArray(changes)){
    changes.forEach(function (element, index, array){
    elements=document.querySelectorAll('[name*="'+element+'"]');
    if(elements.length != 0){ //check to see if the element exists
         for (var i = 0; i < elements.length; ++i) {
             var item = elements[i];
            if(this.t202DataObj[element]){
                t202Value=this.t202DataObj[element];      //if we have a value in the dataObject use it
            }else{
                t202Value=item.getAttribute('t202Default');      //if not use the default value
            }
    
            if(item.tagName=='INPUT'){
                item.value = t202Value;
            }else{
             item.innerHTML = t202Value;
            }
        }
    } 
},this);
      
          changes.forEach(function (element, index, array){
    elements=document.querySelectorAll('[t202attribute="'+element+'"]');
    if(elements.length != 0){ //check to see if the element exists
         for (var i = 0; i < elements.length; ++i) {
             var item = elements[i];
            if(this.t202DataObj[element]){
                t202Value=this.t202DataObj[element];      //if we have a value in the dataObject use it
            }else{
                t202Value=item.getAttribute('t202Default');      //if not use the default value
            }
    
            if(item.tagName=='INPUT'){
                item.value = t202Value;
            }else{
             item.innerHTML = t202Value;
            }
        }
    } 
},this);
    }     
}

//merge data in
t202Data.prototype.addDataObj = function(mergeDataObj){

var newDataObj = {};
    for (var attrname in this.t202DataObj) { newDataObj[attrname] = this.t202DataObj[attrname]; }
    for (var attrname in mergeDataObj) { newDataObj[attrname] = mergeDataObj[attrname]; }
  this.t202DataObj = newDataObj;

}    

//update dataObj with new data
t202Data.prototype.updateDataObj = function(changes){
    var changes = changes || {};
    var renderData = []
    
    for (var key in changes) {
        if (changes.hasOwnProperty(key)) {
    
            if(this.t202DataObj.hasOwnProperty(key)){
                renderData.push(key);
                this.t202DataObj[key]=changes[key]
                
            }   
            
        }
    }
    //render changes to screen if needed
    this.renderChanges(renderData)     
    
    //update server side data 
    if(renderData.indexOf('t202Subid') == -1){
        renderData.push('t202Subid'); //push the subid if it's not there
    }
    
    if(renderData.indexOf('t202SubidOrig') == -1){
        renderData.push('t202SubidOrig'); //push the subidOrig  if it's not there
    }
       if(renderData.length>=2){ //only submit if there's enough data
            var formData2 = new FormData();
            var urlStr='';
            
            for (var i = 0; i < renderData.length; ++i) {
                formData2.append(renderData[i],  this.t202DataObj[renderData[i]]);
                urlStr+= renderData[i]+'='+this.t202DataObj[renderData[i]]+'&';
            } 
            if(navigator.sendBeacon){
                navigator.sendBeacon("//<?php echo getTrackingDomain() . get_absolute_url(); ?>tracking202/redirect/u.php", formData2);
            }
            else{
                urlStr="//<?php echo  getTrackingDomain() . get_absolute_url(); ?>tracking202/redirect/u.php?"+urlStr.substr(0, urlStr.length - 1);
                var img = new Image();
                img.src = urlStr
                img.width = 0;
                img.height = 0;
                document.body.appendChild(img);
            }
       }
}

var dcs = new t202Data;

<?php 
//include record.php serverside instead of via javascript on the client side
include_once("record.php");
while (ob_get_level() > 0) {
    ob_end_flush();
}
