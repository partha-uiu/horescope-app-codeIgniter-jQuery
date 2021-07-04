$(document).ready(function(){
//Hide the tooglebox when page load
$(".togglebox").hide(); 
//slide up and down when hover over heading 2
$("h2").hover(function(){
// slide toggle effect set to slow you can set it to fast too.
$(this).next(".togglebox").slideToggle("medium");
return true;
});
});

$(document).ready(function(){

	//Hide (Collapse) the toggle containers on load
	$(".togglebox1").hide();
	
	$(".togglebox-show").show(); 

	//Slide up and down on hover
	$(".toggleclick").click(function(){
		$(this).next(".togglebox1").slideToggle("medium");
	});
	
	//Slide up and down on hover
	$(".toggleclick").click(function(){
		$(this).next(".togglebox-show").slideToggle("medium");
	});

});