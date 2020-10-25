<?php
$vars=@explode(" ",base64_decode($_GET['202v']));
include_once(str_repeat("../", 2).'202-config/connect2.php');
if(isset($vars[1])){
	$_GET['pci']=$vars[1];
}
$expire = time() + 2592000;
if(trackingEnabled()){

	   //set the cookie for the PIXEL to fire, expire in 30 days
	   $expire = time() + (60 *  60 * 24 * 30);
	   $expire_header = 60 *  60 * 24 * 30;
	   $path = '/';
	   $domain = $_SERVER['HTTP_HOST'];
	   $secure = TRUE;
	   $httponly = FALSE;
	   
	   //legacy cookies
	   
	   @setcookie('tracking202subid-legacy', $vars[0], $expire, '/', $domain);
	   @setcookie('tracking202subid_a_' . $vars[2].'-legacy', $vars[0], $expire, '/', $domain);
	   @setcookie('tracking202pci-legacy', $vars[1], $expire, '/', $domain);

	   //samesite=none secure cookies
	   if (PHP_VERSION_ID < 70300) {
		   header('Set-Cookie: tracking202subid='.$vars[0].';max-age='.$expire_header.';Path=/;Domain='.$domain.';SameSite=None; Secure');
		  header('Set-Cookie: tracking202subid_a_' . $vars[2].'='.$vars[0].'; max-age='.$expire_header.';Path=/;Domain='.$domain.';SameSite=None; Secure'); 
		  header('Set-Cookie: tracking202pci='.$vars[1].';max-age='.$expire_header.';Path=/;Domain='.$domain.';SameSite=None; Secure');
	   }
	   else {
		   @setcookie('tracking202subid-legacy', $vars[0], ['expires' => $expire,'path' => '/','domain' => $domain,'secure' => $secure,'httponly' => $httponly,'samesite' => 'None']);
		   @setcookie('tracking202subid_a_' . $vars[2].'-legacy', $vars[0], ['expires' => $expire,'path' => '/','domain' => $domain,'secure' => $secure,'httponly' => $httponly,'samesite' => 'None']);
		   @setcookie('tracking202pci', $vars[1], ['expires' => $expire,'path' => '/','domain' => $domain,'secure' => $secure,'httponly' => $httponly,'samesite' => 'None']);
	
		}	
}
$redirect_site_url='';

//Simple LP redirect
  if (isset($_GET['lpip']) && !empty($_GET['lpip'])) {
	if(isset($_GET['pci'])){
		$outbound_site_url = 'http://' . $_SERVER['SERVER_NAME'] . get_absolute_url() . 'tracking202/redirect/pci.php?pci=' . $_GET['pci'];
		header('location: '.$outbound_site_url);
	}
	
	else if(null !== (getCookie202('tracking202outbound'))) {
		$outbound_site_url = getCookie202('tracking202outbound');
	}	
	else {
		
		require_once(dirname( __FILE__ ). '/lp.php');
	}
	
	header('location: '.$outbound_site_url);
}

//Advanced LP redirect
  if (isset($_GET['acip']) && !empty($_GET['acip'])) {
	
	include_once(dirname( __FILE__ ) . '/off.php');
}

//Rotator redirect on ALP
  if (isset($_GET['rpi']) && !empty($_GET['rpi'])) {
	
	include_once(dirname( __FILE__ ) . '/offrtr.php');
}

die("Missing LPIP, ACIP or RPI variable!");
