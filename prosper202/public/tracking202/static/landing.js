(function() {
  var t202script =
    document.currentScript ||
    (function() {
      return document.getElementById("t202js");
    })();
  var _202id = t202script.id;
  var _t202Vars = t202GetAllVars(t202script.src);
  var _202Src =
    "https:" +
    t202script.src.match(/(\/\/.*)(landing\.js)/i)[1] +
    "landing.php?lpip=" +
    _t202Vars["lpip"];
  if (_t202Vars["defpixel"] === "1") {
    _202Src += "&defpixel=" + _t202Vars["defpixel"];
  }
  if (_t202Vars["rsid"] === "1") {
    _202Src += "&rsid=" + _t202Vars["rsid"];
  }
  _202Src += "&r=" + Math.random() * 5;
  var _202LpUrl = escape(document.location.href);
  var _202Ref = escape(document.referrer);
  var _202UrlVars = window.location.search.substring(1);
  _202Src = _202Src + "&referer=" + _202Ref;
  _202Src = _202Src + "&t202LpUrl=" + _202LpUrl;
  if (_202UrlVars) {
    _202Src = _202Src + "&" + _202UrlVars;
  }

  var _202Rand = Math.floor(Math.random() * 1000000000 + 1);
  _202Src = _202Src + "&202r=" + _202Rand;
  (function() {
    var _p202 = document.createElement("script");
    _p202.type = "text/javascript";
    _p202.async = true;
    _p202.src = _202Src;
    var s = document.getElementsByTagName("script")[0];
    s.parentNode.insertBefore(_p202, s);
  })();
})();

function t202GetAllVars(url) {
  if (url === undefined || url === "") {
    url = window.location.href;
  }

  var vars = {};
  url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
    vars[key.toLowerCase()] = value;
  });
  return vars;
}
