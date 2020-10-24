<?php  
/*
if (isset($_COOKIE['tracking202rlp_' . $landing_page_id_public])) {
    $click_sql = "UPDATE 202_clicks SET aff_campaign_id = '" . $mysql['aff_campaign_id'] . "', click_payout = '" . $mysql['click_payout'] . "', click_filtered = '" . $mysql['click_filtered'] . "' WHERE click_id = '" . $mysql['click_id'] . "'";
} 
else{
	$mysql['ppc_account_id'] = $db->real_escape_string($tracker_row['ppc_account_id']);
}
  */  

$click_landing_site_url_id = INDEXES::get_site_url_id($db, $landing_site_url);
$mysql['click_landing_site_url_id'] = $db->real_escape_string($click_landing_site_url_id);

$outbound_site_url = getScheme().'://' . $_SERVER['SERVER_NAME'] . get_absolute_url() . 'tracking202/redirect/pci.php?pci=' . $click_id_public;
$click_outbound_site_url_id = INDEXES::get_site_url_id($db, $outbound_site_url);
$mysql['click_outbound_site_url_id'] = $db->real_escape_string($click_outbound_site_url_id);

if ($cloaking_on == true) {
    $cloaking_site_url = getScheme().'://' . $_SERVER['SERVER_NAME'] . get_absolute_url() . 'tracking202/redirect/cl.php?pci=' . $click_id_public;
    $click_cloaking_site_url_id = INDEXES::get_site_url_id($db, $cloaking_site_url);
    $mysql['click_cloaking_site_url_id'] = $db->real_escape_string($click_cloaking_site_url_id);
} else {
    $mysql['click_cloaking_site_url_id'] = '0';
}

$redirect_site_url = rotateTrackerUrl($db, $tracker_row);
$raw_redirect_site_url = $redirect_site_url;

$redirect_site_url = replaceTrackerPlaceholders($db, $redirect_site_url, $click_id, $mysql);

$click_redirect_site_url_id = INDEXES::get_site_url_id($db, $redirect_site_url);
$mysql['click_redirect_site_url_id'] = $db->real_escape_string($click_redirect_site_url_id);

// insert this
insertClicksSite($mysql);

// set the cookie
setClickIdCookie($mysql['click_id'], $mysql['aff_campaign_id']);
// set outbound cookie
setOutboundCookie($outbound_site_url);

// set dirty hour
setDirtyHour($mysql);

function strReplaceAssoc(array $replace, $subject) {
    return str_replace(array_keys($replace), array_values($replace), $subject);
}

$replace = array(
    '[[' => '\\\\[\\\\[',
    ']]' => '\\\\]\\\\]',
    '?' => "\\\\?"
);
$js_raw_redirect_site_url=$raw_redirect_site_url;
$raw_redirect_site_url=strReplaceAssoc($replace,$raw_redirect_site_url);

?> 

rawLink='<?php echo $js_raw_redirect_site_url;?>'
function replaceTokens(url){
    tokenMap= {
        'subid':'t202Subid',
        't202kw':'t202Kw',
        't202pubid':'t202Pubid',
        'c1':'t202C1',
        'c2':'t202C2',
        'c3':'t202C3',
        'c4':'t202C4',
        'random':'t202Random',
        'referer':'t202Referrer',
        'referrer':'t202Referrer',
        'sourceid':'t202SourceId',
        'gclid':'',
        'msclkid':'',
        'fbclid':'',        
        'utm_source':'t202Utm_source',
        'utm_medium':'t202Utm_medium',
        'utm_campaign':'t202Utm_campaign',
        'utm_term':'t202Utm_term',
        'utm_content':'t202Utm_content',
        'payout':'',
        'cpc':'',
        'cpc2':'',
        'timestamp':''}
   
    nurl=url
    for (k in tokenMap){
        re= new RegExp('\\[\\['+k+'\\]\\]','ig')
        //only replace if there's a value
     if(dcs.t202DataObj[tokenMap[k]]){
        nurl=nurl.replace(re,dcs.t202DataObj[tokenMap[k]])
     }
    }

    if(nurl!==url){
    return nurl
  }else{

  }
    
}
function updatePurLink()
{
    var _202links = document.links;
var _202forms = document.forms;

var txt = "";
var i;
var match = 0;

for (i = 0; i < _202links.length; i++) {
    
    if(_202links[i].dataset.t202rawlink){
      newRedirect=replaceTokens(_202links[i].dataset.t202rawlink)
      _202links[i].href = newRedirect 
    }
    txt = _202links[i].href

    if(txt.search('<?php echo $raw_redirect_site_url;?>') != -1){
    _202links[i].href = '<?php echo $redirect_site_url;?>'
    
    //set data attribute so we can set leave behind later
    _202links[i].setAttribute('data-lb202',1);
    
    <?php if(isset($mysql['deferred_pixel']) && $mysql['deferred_pixel'] == 1){?>
        _202links[i].target= '_blank';
        _202links[i].addEventListener("click", activateDeferredPixel);    
    <?php 
    }?>

    //set ping for async post
    _202links[i].setAttribute('ping','//<?php echo $_SERVER['SERVER_NAME'].get_absolute_url(); ?>tracking202/redirect/lp2.php?lpip=<?php echo $landing_page_id_public; ?>&click_id=<?php echo $click_id; ?>');
   
    addListener(_202links[i],'click')
    //mark that no redirect link found
    match = 1;
    }
 

    if(txt.search(/go.php\?(lpip|acip|rpi)=[0-9]*$/i) != -1){
    _202links[i].href += "&pci=<?php echo $click_id_public;?>&click_id=<?php echo $click_id;?>"
    <?php if(isset($mysql['deferred_pixel']) && $mysql['deferred_pixel'] == 1){?>
        //woot woot <?php echo $raw_redirect_site_url;?>
        _202links[i].target= '_blank';
        _202links[i].addEventListener("click", activateDeferredPixel);    
    <?php 
    }
    if(isset($mysql['b202_fbpa_status']) && $mysql['b202_fbpa_status'] == 1){?>    
        _202links[i].addEventListener("click", activateBot202PixelFBAssistant);    
    <?php
    } //php
        ?>    
    }
}

<?php if(isset($mysql['deferred_pixel']) && $mysql['deferred_pixel'] == 1){
  include_once('deferred_pixel.php');
 }
 
 if(isset($mysql['b202_fbpa_status']) && $mysql['b202_fbpa_status'] == 1){
    include_once('bot202_fb_pixel.php');
   }
 ?> 

for (i = 0; i < _202forms.length; i++) {
    txt = _202forms[i].action
    var _202linkVars = <?php 
     parse_str(parse_url($redirect_site_url, PHP_URL_QUERY),$queryarray);
     echo(trim(json_encode($queryarray, JSON_PRETTY_PRINT)));?>
     
     <?php //the empty line above is needed to prevent errors, don't remove?>
    if(txt.search('<?php echo $raw_redirect_site_url;?>') != -1){
    _202forms[i].action = '<?php echo $redirect_site_url;?>'    
    addListener(_202forms[i],'submit')
    //mark that no redirect link found
    match = 1;
    
    //add all the url vars as hidden elements so that it submits correctly
    for (var key in _202linkVars) {
    if (_202linkVars.hasOwnProperty(key)) {
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = _202linkVars[key];
        if(_202forms[i].elements[key]===void 0){//only add element if it's not there
            _202forms[i].appendChild(input);
        }
    }
    
    }
    }
}

for (i = 0; i < _202forms.length; i++) {
    _202inputs = _202forms[i].getElementsByTagName("input");
    for (j = 0; j < _202inputs.length; j++) {
        txt = _202inputs[j].value
        if(txt.search('<?php echo $raw_redirect_site_url;?>') != -1){
            _202inputs[j].value = '<?php echo $redirect_site_url;?>'    
            addListener(_202forms[i],'submit')
            match = 1; //mark that no redirect link found
        }
    }    
}   
}


var _202links = document.links;
var _202forms = document.forms;

var txt = "";
var i;
var match = 0;

for (i = 0; i < _202links.length; i++) {
    txt = _202links[i].href

    if(txt.search('<?php echo $raw_redirect_site_url;?>') != -1){
    _202links[i].setAttribute('data-t202rawlink',_202links[i].href);
    _202links[i].href = '<?php echo $redirect_site_url;?>'
    
    //set data attribute so we can set leave behind later
    _202links[i].setAttribute('data-lb202',1);
    
    <?php if(isset($mysql['deferred_pixel']) && $mysql['deferred_pixel'] == 1){?>
        _202links[i].target= '_blank';
     //   _202links[i].addEventListener("click", activateDeferredPixel);    
    <?php 
    }?>

    //set ping for async post
    _202links[i].setAttribute('ping','//<?php echo $_SERVER['SERVER_NAME'].get_absolute_url(); ?>tracking202/redirect/lp2.php?lpip=<?php echo $landing_page_id_public; ?>&click_id=<?php echo $click_id; ?>');
   
    addListener(_202links[i],'click')
    //mark that no redirect link found
    match = 1;
    }

    if(txt.search(/go.php\?(lpip|acip|rpi)=[0-9]*$/i) != -1){
    _202links[i].href += "&pci=<?php echo $click_id_public;?>&click_id=<?php echo $click_id;?>"
    <?php if(isset($mysql['deferred_pixel']) && $mysql['deferred_pixel'] == 1){?>
        _202links[i].target= '_blank';
        _202links[i].addEventListener("click", activateDeferredPixel);    
    <?php 
    }
    if(isset($mysql['b202_fbpa_status']) && $mysql['b202_fbpa_status'] == 1){?>    
        _202links[i].addEventListener("click", activateBot202PixelFBAssistant);    
    <?php
    } //php
        ?>    
    }
}

<?php if(isset($mysql['deferred_pixel']) && $mysql['deferred_pixel'] == 1){
  include_once('deferred_pixel.php');
 }
 
 if(isset($mysql['b202_fbpa_status']) && $mysql['b202_fbpa_status'] == 1){
    include_once('bot202_fb_pixel.php');
   }
 ?> 

for (i = 0; i < _202forms.length; i++) {
    txt = _202forms[i].action
    var _202linkVars = <?php 
     parse_str(parse_url($redirect_site_url, PHP_URL_QUERY),$queryarray);
     echo(trim(json_encode($queryarray, JSON_PRETTY_PRINT)));?>
     
     <?php //the empty line above is needed to prevent errors, don't remove?>
    if(txt.search('<?php echo $raw_redirect_site_url;?>') != -1){
    _202forms[i].action = '<?php echo $redirect_site_url;?>'    
    addListener(_202forms[i],'submit')
    //mark that no redirect link found
    match = 1;
    
    //add all the url vars as hidden elements so that it submits correctly
    for (var key in _202linkVars) {
    if (_202linkVars.hasOwnProperty(key)) {
        var input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = _202linkVars[key];
        if(_202forms[i].elements[key]===void 0){//only add element if it's not there
            _202forms[i].appendChild(input);
        }
    }
    
    }
    }
}

for (i = 0; i < _202forms.length; i++) {
    _202inputs = _202forms[i].getElementsByTagName("input");
    for (j = 0; j < _202inputs.length; j++) {
        txt = _202inputs[j].value
        if(txt.search('<?php echo $raw_redirect_site_url;?>') != -1){
            _202inputs[j].value = '<?php echo $redirect_site_url;?>'    
            addListener(_202forms[i],'submit')
            match = 1; //mark that no redirect link found
        }
    }    
}


function addListener (el,evt){
      if(navigator.sendBeacon){
      el.addEventListener(evt, function (event){
        var formData = new FormData();
        formData.append("lpip", <?php echo $landing_page_id_public; ?>);
        formData.append("click_id", <?php echo $click_id; ?>);
        navigator.sendBeacon("//<?php echo $_SERVER['SERVER_NAME'].get_absolute_url(); ?>tracking202/redirect/lp2.php", formData);
        })
      }else{
          el.addEventListener(evt, imgPing);
       }
}

function imgPing (event){
    var ourl = '//<?php echo $_SERVER['SERVER_NAME'].get_absolute_url(); ?>tracking202/redirect/lp2.php?lpip=<?php echo $landing_page_id_public; ?>&click_id=<?php echo $click_id; ?>'; 
    var img = new Image();
    img.src = ourl
    img.width = 0;
    img.height = 0;
    document.body.appendChild(img);
}

if(match){
  
    //set referer to origin so that privacy is protected
    var meta = document.createElement('meta');
    meta.name = "referrer";
    meta.content = "origin";
    document.head.appendChild(meta);
    
    //find hostname   
    var speedlink = document.createElement('a');
    speedlink.href = "<?php echo $redirect_site_url;?>";
    var theUrl = speedlink.protocol+'//'+speedlink.hostname+'/' 
     
    //prefetch dns for campaign url domain
    var prefetch = document.createElement('link');
    prefetch.rel='dns-prefetch'
    prefetch.href=theUrl//speedlink.origin+'/'
    document.head.appendChild(prefetch);

    //preconnect dns for campaign url domain
    var preconnect = document.createElement('link');
    preconnect.rel='preconnect'
    preconnect.href=theUrl
    document.head.appendChild(preconnect);
    
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
    txt = _202links[i].href;
    if((txt.search("lpip=")!==-1) || (_202links[i].dataset.lb202 ==='1')){
      _202links[i].addEventListener("click", leavebehind202);
    }
}

var el = document.getElementById("202_lb");
//do leavebehind if it's set
if(el)
    el.addEventListener("click", leavebehind202);
 <?php
}
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
	createCookie('tracking202outbound','<?php echo $outbound_site_url; ?>',90);
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

<?php

if($mysql['b202_fbpa_status'] == 1){ //bot202 pixel assistant activated
    getDynamicEPVPixelId($mysql); // get dynamic epv for past 7 days
    if($mysql['pixel_id'] != 0){

    
?>
 (function () {
    
    var bot202FbPixelObj ={
        'pixel_id' : '<?php echo $mysql['pixel_id']; ?>',
        'content_name' : '<?php echo $mysql['b202_fbpa_content_name']; ?>',
        'content_type': '<?php echo $mysql['content_type']; ?>',
        'event_type': '<?php echo $mysql['event_type']; ?>',
        'currency': '<?php echo $mysql['user_account_currency']; ?>',
        'dynamic_epv': '<?php echo $mysql['dynamic_epv_value']; ?>'
    }

    dcs.addDataObj(bot202FbPixelObj)

    var _202Rand = Math.floor(Math.random() * 1000000000 + 1);
    var head = document.getElementsByTagName('head')[0]
    script = document.createElement('script');
    script.src = "//<?php echo $_SERVER['SERVER_NAME'].get_absolute_url(); ?>tracking202/static/fbpx.js?cb="+_202Rand;    
    a=head.appendChild(script);

})();


<?php
    }
}

?>
//slp