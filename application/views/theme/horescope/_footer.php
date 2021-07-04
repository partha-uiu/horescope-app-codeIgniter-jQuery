<footer>
        <div id="footer-area">
            <div class="container">
                <div class="row">
                    <h3 style="text-align: center;color: #fff;font-weight: bold;">QUOTES</h3>
                    <div class="col-md-1"></div>
                    <?php $quotes = get_quotes(); //pr($categories);
			foreach($quotes as $q){ 
                    ?>
                    <div class="col-md-3 col-sm-4 col-xs-12 quotes">                       
                        <p><i style="color: darkgoldenrod !important;" class="fa fa-quote-left"></i> <?php echo $q->quote; ?> <i style="color: darkgoldenrod !important;" class="fa fa-quote-right"></i></p>
                        <span>--by <?php echo $q->author; ?></span>
                    </div>
                    <?php } ?>
<!--                    <div class="col-md-3 col-sm-4 quotes">
                        <p><i class="fa fa-quote-left"></i> Modern, practical education and step-by-step 
                            training from some of the world's top numerology experts and teachers. <i class="fa fa-quote-right"></i></p>
                        <span>--by Techofbliss</span>
                    </div>
                    <div class="col-md-3 col-sm-4 quotes">   
                        <p><i class="fa fa-quote-left"></i> Modern, practical education and step-by-step 
                            training from some of the world's top numerology experts and teachers. <i class="fa fa-quote-right"></i></p>
                        <span>--by Techofbliss</span>
                    </div>-->
                    <div class="col-md-1"></div>
                </div>
            </div>
        </div>
        <div class="main-footer">
            <div class="row">
                <div class="col-md-4 col-sm-4">
                    <p class="copyright">&copy;All rights reserved by technologyofbliss. </p>
                </div>
                <div class="col-md-3 col-sm-3">
                    <p class="copyright" style="margin-left: 24%;">Email: technologyofbliss.com</p>
                </div>
                <div class="col-md-5 col-sm-5 social_icon">
                    <ul>
                        <li><a href=""><i class="fa fa-facebook-square"></i></a></li>
                        <li><a href=""><i class="fa fa-google-plus-square"></i></a></li>
                        <li><a href=""><i class="fa fa-twitter-square"></i></a></li>
                    </ul>
                </div>
            </div>           
        </div>
    </footer>
    
    <section>
        <a href="#" class="back-to-top"><i class="fa fa-caret-square-o-up"></i></a>
        <script>
            jQuery(document).ready(function() {
            var offset = 220;
            var duration = 500;
            jQuery(window).scroll(function() {
                if (jQuery(this).scrollTop() > offset) {
                    jQuery('.back-to-top').fadeIn(duration);
                } else {
                    jQuery('.back-to-top').fadeOut(duration);
                }
            });

            jQuery('.back-to-top').click(function(event) {
                event.preventDefault();
                jQuery('html, body').animate({scrollTop: 0}, duration);
                return false;
            })
        });
        </script>
    </section>
    <script>
function initialize() {
  var mapProp = {
    center:new google.maps.LatLng(40.712784,-74.005941),
    zoom:5,
    mapTypeId:google.maps.MapTypeId.ROADMAP
  };
  var map=new google.maps.Map(document.getElementById("googleMap"),mapProp);
}
google.maps.event.addDomListener(window, 'load', initialize);
</script>

<script type="text/javascript">
    $(function() {
		if ($.browser.msie && $.browser.version.substr(0,1)<7)
		{
		$('li').has('ul').mouseover(function(){
			$(this).children('ul').css('visibility','visible');
			}).mouseout(function(){
			$(this).children('ul').css('visibility','hidden');
			})
		}

		/* Mobile */
		$('#menu-wrap').prepend('<div id="menu-trigger">Menu</div>');		
		$("#menu-trigger").on("click", function(){
			$("#menu").slideToggle();
		});

		// iPad
		var isiPad = navigator.userAgent.match(/iPad/i) != null;
		if (isiPad) $('#menu ul').addClass('no-transition');      
    });          
</script>

 