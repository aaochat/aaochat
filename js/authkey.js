jQuery(document).ready(function(){

	var existsNcUserToken = null;
	if (typeof(Storage) !== "undefined") {
		existsNcUserToken = localStorage.getItem("ngStorage-AuthKey");
	}
	if((typeof(existsNcUserToken) == 'undefined' ||  existsNcUserToken== null || existsNcUserToken == '123' || existsNcUserToken == '"123"')) {

		//window.location.reload();
		$('.aaochat_loader').show();
		$.ajax({
			url: OC.generateUrl('/apps/aaochat/getauthkey'),
			type: 'POST',
			data: { },
			success: function (authkey_response) {
				if(authkey_response != 'null' && authkey_response != null)
				{
					if( authkey_response.status == 'success' )
					{
						var responseData = authkey_response.data;

						var localStorageData = responseData.localStorageData;
						var ncUserToken = localStorageData.ncUserAuthKey;
						var aaochatServerUrl = localStorageData.aaochatServerUrl;
						var aaochatFileServerUrl = localStorageData.aaochatFileServerUrl;
						if (typeof(Storage) !== "undefined") {
							var existsNcUserToken = localStorage.getItem("ngStorage-AuthKey");
							if((typeof(ncUserToken) != 'undefined' ||  ncUserToken!= null) && ncUserToken != existsNcUserToken) {
								localStorage.setItem("ngStorage-AuthKey", ncUserToken);
								localStorage.setItem("nextcloud-AaoChatServerURL", aaochatServerUrl);
								localStorage.setItem("nextcloud-AaoChatFileServerURL", aaochatFileServerUrl);
							}
						}

						setTimeout(function(){
							window.location.reload();
						}, 3000);
					}
					else 
					{	
						if(typeof authkey_response.message != 'undefined') {
							$('.aaochat_loader').hide();
						}
					}
				}
				else
				{
					$('.aaochat_loader').hide();
				}
			},
			error: function(xhr, status, error){
				$('.aaochat_loader').hide();
			}
		});
	}

}); 

