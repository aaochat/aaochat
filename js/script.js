jQuery(document).ready(function(){

	//$("head").append("<meta http-equiv=\"Content-Security-Policy\" content=\"connect-src 'self'\" />");

	var loader_image = OC.generateUrl('apps/aaochat/img/rolling.gif'); 
	var loader_image_final = loader_image.replace("/index.php", "");

	var loader_html = '<div class="aaochat_loader" style="background: black;height: 100%;width: 100%;opacity: 0.4;position:fixed;top:0;z-index: 999999;display:none;"></div><div class="aaochat_loader" style="position:fixed;top:40%;z-index: 999999;width:100%;text-align: center;display:none;"><img src="'+loader_image_final+'" style="height: 72px;"></div>';
	$('body').append( loader_html );


 	jQuery('li[data-id="aaochat"]').find('a').click(function(e) {
 		e.preventDefault();
 		var aaochat_url = OC.generateUrl("/apps/aaochat");
		let newTab = window.open();
 		newTab.location.href = aaochat_url;
		return false;
	});

	//jQuery('li[data-app-id="aaochat"]').find('a').click(function(e) {
	jQuery( document ).on( "click", 'li[data-app-id="aaochat"] a',function(e) {
		e.preventDefault();
		var aaochat_url = OC.generateUrl("/apps/aaochat");
	    let newTab = window.open();
		newTab.location.href = aaochat_url;
	    return false;
   	});
	
	var ncUserToken = jQuery.cookie('ncUserAuthKey'); //readCookieByName('ncUserAuthKey');
	var aaochatServerUrl = jQuery.cookie('aaochatServerUrl'); //readCookieByName('ncUserAuthKey');
	var aaochatFileServerUrl = jQuery.cookie('aaochatFileServerUrl'); //readCookieByName('ncUserAuthKey');

	if (typeof(Storage) !== "undefined") {
		var existsNcUserToken = localStorage.getItem("ngStorage-AuthKey");
		if((typeof(ncUserToken) != 'undefined' ||  ncUserToken!= null) && ncUserToken != existsNcUserToken) {
			localStorage.setItem("ngStorage-AuthKey", ncUserToken);
			localStorage.setItem("nextcloud-AaoChatServerURL", aaochatServerUrl);
			localStorage.setItem("nextcloud-AaoChatFileServerURL", aaochatFileServerUrl);
		}
	}


	jQuery( document ).on({
		mouseover: function () {
			jQuery( this ).find('.aaocchat-context-menu').css({visibility: "visible"});
		},
		mouseleave: function () {
			jQuery( this ).find('.aaocchat-context-menu').css({visibility: "hidden"});
		}
	},'.responses-chat');
	jQuery( document ).on({
		mouseover: function () {
			jQuery( this ).find('.aaocchat-context-menu').css({visibility: "visible"});
		},
		mouseleave: function () {
			jQuery( this ).find('.aaocchat-context-menu').css({visibility: "hidden"});
		}
	},'.messages-chat');

	jQuery( document ).on('click','.aaocchat-context-menu-btn',function() {
		//jQuery( this ).find('.aaocchat-dropdown-menu-left-side-ope').css({display: "block"});
		console.log('click on context menu');
		console.log(jQuery( this ).parents('.aaocchat-context-menu'));
		//jQuery( this ).parents('.aaocchat-context-menu').closest('.aaocchat-dropdown-menu-left-side-open').show();
		jQuery( this ).parents('.aaochat-msg-block').find('.aaocchat-dropdown-menu-left-side-open').show();
	});

	jQuery( document ).on( "click", function(e) {
		var aaocchatContextMenuContainer = $(".aaocchat-context-menu-btn");
		// if the target of the click isn't the container nor a descendant of the container
		if (!aaocchatContextMenuContainer.is(e.target) && aaocchatContextMenuContainer.has(e.target).length === 0) 
		{
			jQuery('.aaocchat-dropdown-menu-left-side-open').hide();
		}
	});

	/*
	jQuery( "#chatMessageListContainer" ).on('click','.responses-chat',
		function() {
			console.log('chat on hover');
			jQuery( this ).find('.aaocchat-context-menu').css({visibility: "visible"});
		}, function() {
			console.log('chat on hover out');
			jQuery( this ).find('.aaocchat-context-menu').css({visibility: "hidden"});
		}
	);
	jQuery( "#chatMessageListContainer" ).on('click','.messages-chat',
		function() {
			console.log('chat on hover');
			jQuery( this ).find('.aaocchat-context-menu').css({visibility: "visible"});
		}, function() {
			console.log('chat on hover out');
			jQuery( this ).find('.aaocchat-context-menu').css({visibility: "hidden"});
		}
	);
	*/


	/*jQuery('.aaochat-image-preview').fancybox({
		caption : function( instance, item ) {
			var caption = $(this).data('caption') || '';
			console.log('image preview');
			if ( item.type === 'image' ) {
				caption = (caption.length ? caption + '&nbsp;' : '') + '<a href="' + item.src + '">Download image</a>' ;
			}
	
			return caption;
		}
	});*/

	
}); 

