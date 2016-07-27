$(function(){
	$("a[href^='#']").click(function() {
		var speed = 300;
		var href= $(this).attr("href");
		var target = $(href == "#" || href == "" ? 'html' : href);
		if( typeof target.offset() != 'undefined') {
			var position = target.offset().top;
			$('body,html').animate({scrollTop:position}, speed, 'swing');
		}
		return false;
	});
	
	$('#globalNav > h2').click(function(){
		var navIcon = $(this);
		if(navIcon.hasClass('active')) {
			checkNavWidth();
			$(this).removeClass('active');
			$(this).next('div').css('visibility','hidden');
			$(this).next('div').fadeOut(100);
		} else {
			checkNavWidth('On');
			$(this).addClass('active');
			$(this).next('div').css('visibility','visible');
			$(this).next('div').fadeIn(200);
		}
	});
	
	$('#globalNav > div > ul > li > a').click(function(){
		var navBox = $('#globalNav');
		navBox.children('h2').removeClass('active');
		navBox.children('div').css('visibility','hidden');
		navBox.children('div').fadeOut(100);
		$('body').css('overflow','auto');
	});
	
	$('#policyButton').click(function(){
		var policyButton = $(this);
		if(policyButton.hasClass('active')) {
			policyButton.removeClass('active');
			policyButton.children('span').text("個人情報取得同意書を見る");
			policyButton.next('div').slideUp(100);
		} else {
			policyButton.addClass('active');
			policyButton.children('span').text("個人情報取得同意書を閉じる");
			policyButton.next('div').slideDown(200);
		}
	});
	
	var pagetop = $('#pagetoplink');
	var pagetop_offset = 50;
	
	if( checkWindowSize() ) {
		$(window).on('scroll', function(){
			if($(this).scrollTop() > pagetop_offset){
				pagetop.addClass('visible');
			} else {
				pagetop.removeClass('visible');
			}
		});		
	} else {
		pagetop.addClass('pagetopsp');
	}
});

$(window).on('load resize', function(){
	if( checkWindowSize() ) {
		var mainVisualHeight = $('#mainVisual').height();
		var sideBoxBlockHeight = mainVisualHeight/2;
		$('#visualNav > li').each(function() {
			$(this).height(sideBoxBlockHeight);
		});
		$('#pagetoplink').removeClass('pagetopsp');
	} else {
		$('#visualNav > li').each(function() {
			$(this).removeAttr('style');
		});
		$('#pagetoplink').addClass('pagetopsp');
	}
});

function checkNavWidth(onOff) {
	var bodyBlock = $('body');
	var browserWidth = $(window).width();
	var browserHeight = window.innerHeight;
	var navBox = $("#globalNav > div");
	//var navHeight = $("#globalNav > div > ul").height();
	if(onOff == 'On') {
		if( browserHeight > 350 ) {
			navBox.css('height', browserHeight+"px");
			bodyBlock.css('width', browserWidth+"px");
			bodyBlock.css('height', browserHeight+"px");
			bodyBlock.css('overflow', "hidden");
		}
		navBox.css('width', browserWidth+"px");
	} else {
		bodyBlock.css('width', "100%");
		bodyBlock.css('height', "auto");
		bodyBlock.css('overflow', "auto");
	}
}

function checkWindowSize() {
	var browserWidth = $(window).width();
	if(browserWidth > 889) {
		return "large";
	} else if( 890 > browserWidth > 415) {
		return "half";
	} else {
		return false;
	}
}