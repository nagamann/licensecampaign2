$(function(){	
	google.maps.event.addDomListener(window, 'load', function() {
		var mapdiv = document.getElementById('map');
		var latlng = new google.maps.LatLng(35.658246,139.758940);
		
		var myOptions = {
			zoom: 13,
			center: latlng,
			disableDefaultUI: true,
			zoomControl: true,
			zoomControlOptions: {
				style: google.maps.ZoomControlStyle.SMALL,
				position: google.maps.ControlPosition.LEFT_CENTER
			},
			scrollwheel: false,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			//scaleControl: true,
		};
		var map = new google.maps.Map(mapdiv, myOptions);
		
		var iconImage = {
			url : 'images/map-marker.png',
			scaledSize : new google.maps.Size(95, 95)
		}

		
		var marker = new google.maps.Marker({
			map: map,
			position: latlng,
			title: '株式会社UTコンサルティングジャパン',
			icon: iconImage
		});
				
		google.maps.event.addListener(marker, 'click', function() {
			infowindow.open(map,marker);
		});
	});
});


$(window).on('load resize', function(){
	if(!checkWindowSize()){
		var _release = null;
		$('#map').on('pointerdown pointermove pointerup touchstart touchmove touchend mouseenter mouseleave',function(e){
			var $self = $(this);
			if(!$self.hasClass('release')){
				if( e.type=='mouseleave' || _release && (e.type.indexOf('touch') != -1 || e.type.indexOf('point') != -1 ) ){
						clearTimeout(_release);
				}
				if( e.type=='mouseenter' || e.type=='touchstart' || e.type=='pointerdown' ){
					_release = setTimeout(function(){
						$self.addClass('release');
					}, 500 );
				}
			}
		});
	}
});