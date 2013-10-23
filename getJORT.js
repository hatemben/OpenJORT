// Download Jort PDF files inside a webpage (10 at a time)
// Then jump to next page :)
// Visit iort website and search publication by year, then run the script in firebug http://www.iort.gov.tn
// PS : Make sure you hit save download file location by default, to start download immediately instead of showing the download menu
// Enjoy ! 
// @hatem



var url = window.location.href;
var page = url.match(/ZR_RechercheArijJORTPlusieurs\=(\d+)$/)[1];
var newpage = parseInt(page)+10;
var newurl = url.replace(new RegExp("ZR_RechercheArijJORTPlusieurs="+page, 'g'), "ZR_RechercheArijJORTPlusieurs="+newpage )

var i = 1;
function myDownload()
{
if (i<=10) {
_PAGE_.A3.value=i;
_JSL(_PAGE_,'A15','_self','',''));
console.log('Downloading PDF '+i);
++i;
} else if (i == 11){
if (newpage <=100){
window.location.href= newurl;
}
clearInterval(intervalId);
}
}
// if your internet is slow 10000 should be good, try to tweak it to 5000 for fast links.
var intervalId = setInterval(myDownload, 10000);



