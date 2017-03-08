

$(document).ready(function(){
	document.getElementById("nav_bg").style.display = "none";
	$(window).scroll(function() { // check if scroll event happened
        if ($(document).scrollTop() > 50) { // check if user scrolled more than 50 from top of the browser window
          $("#nav_bg").css("display", "block"); // if yes, then change the color of class "navbar-fixed-top" to white (#f8f8f8)
        } else if($(document).scrollTop() < 50){
          $("#nav_bg").css("display", "none"); // if not, change it back to transparent
        } else{
          $("#nav_bg").css("display", "block");
        }
      });
});


