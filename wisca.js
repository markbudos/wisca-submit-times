var Wisca = (function() {
 
    var id= 0;
 
    return {
        ajax: function(url, callback) {
            var xmlhttp = new XMLHttpRequest();
            
            xmlhttp.onreadystatechange = function() {
                if (xmlhttp.readyState == XMLHttpRequest.DONE) {   // XMLHttpRequest.DONE == 4
                    if (xmlhttp.status == 200) {
                        callback(xmlhttp.responseText);
                    } else {
                        callback(null);
                    }
                }
            };
            xmlhttp.open("GET", url, true);
            xmlhttp.send();
        }
    };  
})();   
