var sportResponse = "";
window.onload = function() {
var xmlhttp = new XMLHttpRequest();
  if(!xmlhttp){
    throw 'Unable to create HttpRequest.';
  }
  xmlhttp.onreadystatechange = function(){
    if(this.readyState === 4){
		sportResponse = JSON.parse(this.responseText);
    }
  };
  
  xmlhttp.open('GET', 'http://api.sportradar.us/nfl-t1/2014/REG/schedule.json?api_key=j346whw2jbfaadtsvjnwtwnu');
  xmlhttp.send();




};
