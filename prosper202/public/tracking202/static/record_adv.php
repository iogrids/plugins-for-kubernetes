<?php 

$click_landing_site_url_id = INDEXES::get_site_url_id($db, $landing_site_url); 
$mysql['click_landing_site_url_id'] = $db->real_escape_string($click_landing_site_url_id);

$old_lp_site_url = getScheme().'://'.$_SERVER['SERVER_NAME'].'/lp/'.$landing_page_id_public;  

//insert this
insertClicksSite($mysql);

//set the cookie
setClickIdCookie($mysql['click_id'],$mysql['aff_campaign_id']);

//set the PCI Cookie
setPCIdCookie($mysql['click_id_public']);

//set dirty hour
setDirtyHour($mysql);

?>

function t202GetSubid(){
     return <?php echo $click_id; ?>;
}

function t202GetSubidOrig(){
     return <?php echo $original_click_id; ?>;
}

function t202GetsourceId(){
     return <?php echo $mysql['ppc_account_id']; ?>;
}

function t202GetAllVars(url) {
if(url===undefined || url=== ''){
	var url = window.location.href;
}

var vars = {};
var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
vars[key.toLowerCase()] = value;
});
return vars;
}

var _202links = document.links;
for (i = 0; i < _202links.length; i++) {
    txt = _202links[i].href

    if((txt.search(/\[\[subid\]\]/i) != -1) || (txt.search(/acip=/i) != -1)){
        _202links[i].href=txt.replace(/\[\[subid\]\]/i,t202GetSubid());
        var urlvars= t202GetAllVars(_202links[i].href);
        addListener(_202links[i],'click');
    }
   

    if(txt.search(/go.php\?(lpip|acip|rpi)=[0-9]*$/i) != -1){
    _202links[i].href += "&pci=<?php echo $click_id_public;?>&click_id=<?php echo $click_id;?>"
    <?php if($mysql['deferred_pixel']){?>
    _202links[i].target= '_blank';
    _202links[i].addEventListener("click", activateDeferredPixel);
    <?php }?>    
    }
}

<?php if($mysql['deferred_pixel']){
  include_once('deferred_pixel.php');
 }?>   


function addListener (el,evt){
      if(navigator.sendBeacon){
      el.addEventListener(evt, function (event){
        var urlvars= t202GetAllVars(event.target.href)
        var formData = new FormData();
        formData.append("lpip", <?php echo $landing_page_id_public; ?>);
        formData.append("click_id", <?php echo $click_id; ?>);
        formData.append("landing_page_type", "1");
        formData.append("acip", urlvars['acip']);
        <?php if(isset($mysql['tracker_id_public'])){ //only set if there is a tracker_id_public ?>
        formData.append("t202id", <?php echo $mysql['tracker_id_public']; ?>);
        <?php } ?>
        formData.append("click_outbound_site_url", el.href);
        navigator.sendBeacon("//<?php echo $_SERVER['SERVER_NAME'].get_absolute_url(); ?>tracking202/redirect/lp2.php", formData);
        })
      }else{
          el.addEventListener(evt, imgPing);
       }
}

function imgPing (el){
    var urlvars= t202GetAllVars(el.target.href)
    var ourl = '//<?php echo $_SERVER['SERVER_NAME'].get_absolute_url(); ?>tracking202/redirect/lp2.php?lpip=<?php echo $landing_page_id_public; ?>&click_id=<?php echo $click_id; ?>&landing_page_type=1&t202id=<?php if(isset($mysql['tracker_id_public'])){ echo $mysql['tracker_id_public']; }?>&click_outbound_site_url='+encodeURIComponent(el.target.href)+'=&acip='+urlvars['acip']; 
    var img = new Image();
    img.src = ourl
    img.width = 0;
    img.height = 0;
    document.body.appendChild(img);
}

 <?php
//if there's a leavebehind process it
if ($tracker_row['leave_behind_page_url']) {
    ?>
var lb_url ='<?php echo $tracker_row['leave_behind_page_url']; ?>';
function leavebehind202() {

this.target="_blank";
	setTimeout('window.location.href =lb_url', 200);
return true;
}

var _202links = document.links;
var txt = "";
var i;
for (i = 0; i < _202links.length; i++) {
    if((txt.search("acip=")!==-1) || (_202links[i].dataset.lb202 ==='1')){
  	  _202links[i].addEventListener("click", leavebehind202);
	}
}
var el = document.getElementById("202_lb");
if(el)
    el.addEventListener("click", leavebehind202);
 <?php
}
?>

var getSettings = function(updatedData){
    if(typeof t202Settings != "undefined"){
    for (var key in t202Settings) {
        if (t202Settings.hasOwnProperty(key)) {
            updatedData[key]=t202Settings[key];
            if(key=='t202UrlVars' && typeof t202Settings[key]=='object'){
                updatedData=mapUrlVars(t202Settings[key],updatedData);  
            }
        }
    }    
    }
   return updatedData;
}

//function to reassign built-in 202 url vars to any other custom var
var mapUrlVars = function(urlMapping,updatedData){
        for (var key in urlMapping) {
        if (updatedData.hasOwnProperty(key)) {
            if(t202GetVar(urlMapping[key])!=''){ //update if not blank value
            updatedData[key]=t202GetVar(urlMapping[key]);
            }
        }
    }
    return updatedData; //return new object values
}

var init202 = function(){
    var updatedData= {};
    updatedData=getSettings(updatedData);
    if(Object.keys(updatedData).length !== 0 && updatedData.constructor === Object){
        dcs.updateDataObj(updatedData);
    } 
    var affCampaignId ='<?php echo $mysql['aff_campaign_id']; ?>';
    var subid ='<?php echo $click_id; ?>';
    createCookie('tracking202subid',<?php echo $click_id; ?>,90);
    createCookie('tracking202subid_a_'+affCampaignId,<?php echo $click_id; ?>,90);
	createCookie('tracking202pci',<?php echo $click_id_public; ?>,90);
    createCookie('tracking202rlp_<?php echo $landing_page_id_public; ?>',<?php echo $click_id_public; ?>,90);   
    <?php
//set the cookie for the PIXEL to fire, expire in 30 days
         $expire = time() + (60 *  60 * 24 * 90);
         $expire_header = 60 *  60 * 24 * 90;
         $path = '/';
         $domain = $_SERVER['HTTP_HOST'];
         $secure = TRUE;
         $httponly = FALSE;
         $affcampaignid=$mysql['aff_campaign_id'];
        
         //legacy cookies
         setcookie('tracking202subid-legacy', $click_id, $expire, '/', $domain);
         setcookie('tracking202subid_a_'.$affcampaignid.'-legacy', $click_id, $expire, '/', $domain);
         setcookie('tracking202pci-legacy', $click_id_public, $expire, '/', $domain);
         setcookie('tracking202rlp_' . $landing_page_id_public.'-legacy', $click_id_public, $expire, '/', $domain);
  
        //samesite=none secure cookies
        if (PHP_VERSION_ID < 70300) {
            header('Set-Cookie: tracking202subid='.$click_id.';max-age='.$expire_header.';Path=/;Domain='.$domain.';SameSite=None; Secure');        
            header('Set-Cookie: tracking202subid_a_'.$affcampaignid.'='.$click_id.';max-age='.$expire_header.';Path=/;Domain='.$domain.';SameSite=None; Secure');        
            header('Set-Cookie: tracking202pci='.$click_id_public.';max-age='.$expire_header.';Path=/;Domain='.$domain.';SameSite=None; Secure');        
            header('Set-Cookie: tracking202rlp_' . $landing_page_id_public.'='.$click_id_public.';max-age='.$expire_header.';Path=/;Domain='.$domain.';SameSite=None; Secure');        
        }
        else {
            setcookie('tracking202subid', $click_id, ['expires' => $expire,'path' => '/','domain' => $domain,'secure' => $secure,'httponly' => $httponly,'samesite' => 'None']);
            setcookie('tracking202subid_a_'.$affcampaignid, $click_id, ['expires' => $expire,'path' => '/','domain' => $domain,'secure' => $secure,'httponly' => $httponly,'samesite' => 'None']);
            setcookie('tracking202pci' , $click_id_public, ['expires' => $expire,'path' => '/','domain' => $domain,'secure' => $secure,'httponly' => $httponly,'samesite' => 'None']);
            setcookie('tracking202rlp_' . $landing_page_id_public, $click_id_public, ['expires' => $expire,'path' => '/','domain' => $domain,'secure' => $secure,'httponly' => $httponly,'samesite' => 'None']);
        }?>
}

function gclidData(){
    return <?php if(isset($pageData)){
        echo $pageData;}
        else{
            echo "{}";
        }
        ?>
}

function getData(data,t202Var){
    pageData= gclidData();
    
    if(!data[t202Var]){  
        t202Data=pageData[t202Var];
    }
    else{
        t202Data=data[t202Var];
    }

    return t202Data;
}
init202();

//alp