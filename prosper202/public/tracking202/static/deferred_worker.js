var url = 'check_conversion.php'
var xhr = new XMLHttpRequest()
var intervalTime = 30 * 1000 // 30 seconds
var theInterval = setInterval(pingServer, intervalTime)

function stopPing () {
  clearInterval(theInterval)
  postMessage('DeferredPixelFire202')
}

function pingServer () {
  xhr.open('GET', url, true)
  xhr.onload = function (e) {
    if (xhr.readyState === 4) {
      if (xhr.status === 202) {
        stopPing()
      }
    }
  }
  xhr.send(null)
}
