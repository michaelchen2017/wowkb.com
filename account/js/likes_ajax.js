function getlikes(){
	var userid = document.getElementById("userid").value;
	var love = $(this); 
	
	$.ajax({
		type: "POST",
		dataType: "json",
	    url: "/include/plugins/jquery.php?app=account,ajax_likes&func=likes",
  	    data: { 
  	    			id: userid,
  	    },
  	    	
  	    	success: function(res){
	    		var likes =	res;
	    		
	    		if(likes == '1'){
	    			alert('已经赞过了~');
	    			return false;
	    		}
	    		
	    		document.getElementById("likes").innerHTML = likes;
		},
	});
	
	return false;
}