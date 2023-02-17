jQuery(document).ready(function(){

	$( "#side-menu-lead-create" ).on( "click", function(e) {
			var aaochat_lead_name = $('#aaochat_lead_name').val();
			var aaochat_lead_email = $('#aaochat_lead_email').val();
			var aaochat_lead_phone = $('#aaochat_lead_phone').val();
			var aaochat_lead_organization = $('#aaochat_lead_organization').val();
			$(this).attr('disabled','disabled');
			if(aaochat_lead_name == '') {
				alert('Please enter name');
				$(this).removeAttr('disabled','disabled');
				return false;
			}

			if(aaochat_lead_email == '') {
				alert('Please enter email');
				$(this).removeAttr('disabled','disabled');
				return false;
			}

			if(aaochat_lead_email != '') {
				console.log((/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(aaochat_lead_email)));
				if ((/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(aaochat_lead_email))==false)
				{					
					alert('Please enter valid email');
					$(this).removeAttr('disabled','disabled');
					return false;
				}
			}

			if(aaochat_lead_phone == '') {
				alert('Please enter phone number');
				$(this).removeAttr('disabled','disabled');
				return false;
			}

			if(aaochat_lead_organization == '') {
				alert('Please enter organization');
				$(this).removeAttr('disabled','disabled');
				return false;
			}

			$('.aaochat_loader').show();
			$.ajax({
				url: OC.generateUrl('/apps/aaochat/createlead'),
				type: 'POST',
				data: { aaochat_lead_name: aaochat_lead_name,
				aaochat_lead_email: aaochat_lead_email,
				aaochat_lead_phone: aaochat_lead_phone,
				aaochat_lead_organization: aaochat_lead_organization  },
				success: function (lead_response) {
					
		    		if( lead_response.status == 'success' )
		    		{
		    			var responseData = lead_response.data;
		    			var id = responseData._id;
		    			var name = responseData.name;
		    			var email = responseData.email;		    			
		    			var phoneNo = responseData.phoneNo;
		    			var organization = responseData.organization;
		    			var status = responseData.status;
						var isLeadCreated = 'no';
						if(id == '') {
							isLeadCreated = 'yes';
						} else {
							$(this).removeAttr('disabled','disabled');
						}

						$('#aaochat_lead_status').val(status);
						$('#aaochat_lead_id').val(id);
						$('#aaochat_lead_status_msg').html('You will receive the license key in your registered email address within 24 hours. <br/>Once you receive the license key, please enter below and activate it.');
						$('#aaochat-lead .side-menu-setting-table input').attr('readonly','readonly');
						setTimeout(function(){
							//window.location.reload();
							$('.aaochat_loader').hide();
							$('#activate-licence').css({'display':'block'});
						}, 1500);
		    		}
		    		else 
		    		{
		    			if(typeof lead_response.message != 'undefined') {
		    				alert(lead_response.message);
							$("#side-menu-lead-create").removeAttr('disabled','disabled');
							$('.aaochat_loader').hide();
		    			}
		    		}
				},
				error: function(xhr, status, error){
					$("#side-menu-lead-create").removeAttr('disabled','disabled');
					$('.aaochat_loader').hide();
				 }
			}); 

	});

	$( "#side-menu-lead-status" ).on( "click", function(e) {
			var aaochat_lead_id = $('#aaochat_lead_id').val();
			if(aaochat_lead_id == '') {
				alert("We didn't find your reference. Please try later or contact administrator.");
				return false;
			}

			$('.aaochat_loader').show();
			$.ajax({
				url: OC.generateUrl('/apps/aaochat/leadstatus'),
				type: 'POST',
				data: { aaochat_lead_id: aaochat_lead_id },
				success: function (lead_response) {
					
		    		if( lead_response.status == 'success' )
		    		{
		    			var responseData = lead_response.data;
		    			var status = responseData.status;
						$('#aaochat_lead_status').val(status);
						//console.log(status);
						if(status == 'pending') {
							$('.aaochat_loader').hide();
							$('#aaochat_lead_status_msg').html('Your profile is in review. You will receive the license key soon.');
						} else {
							setTimeout(function(){
								window.location.reload();
							}, 1500);
						}
		    		}
		    		else 
		    		{
		    			if(typeof lead_response.message != 'undefined') {
		    				alert(lead_response.message);
							$('.aaochat_loader').hide();
		    			}
		    		}
				}
			}); 

	});

	//var settingPageUrl = OC.generateUrl('/settings/admin/aaochat');
	$( "#side-menu-license-activate" ).on( "click", function(e) {
		var aaochat_license_key = $('#aaochat_license_key').val();
			$(this).attr('disabled','disabled');

			if(aaochat_license_key == '') {
				alert('Please enter license key');
				$(this).removeAttr('disabled','disabled');
				return false;
			}
			$('.aaochat_loader').show();
			$.ajax({
				url: OC.generateUrl('/apps/aaochat/activatelicense'),
				type: 'POST',
				data: { licenseKey: aaochat_license_key },
				success: function (license_response) {
					
		    		if( license_response.status == 'success' )
		    		{
		    			var responseData = license_response.data;
		    			var id = responseData._id;
		    			var clientId = responseData.clientId;
		    			var licenseKey = responseData.licenseKey;		    			
		    			var serverDetails = responseData.serverDetails;
		    			var serverInstanceType = serverDetails.serverInstanceType;
		    			var url = serverDetails.url;
		    			var fileServerUrl = serverDetails.fileServerUrl;
		    			var storageSize = serverDetails.storageSize;
		    			var serverDetailsId = serverDetails._id;
						var status = responseData.status;
						var activationDate = responseData.activationDate;
						var ipAddress = responseData.ipAddress;
						var isLicenseValid = 'no';
						if(status == 'active') {
							isLicenseValid = 'yes';
						}

						$('#aaochat_client_id').val(clientId);
						$('#aaochat_license_key').val(licenseKey);
						$('#aaochat_is_license_valid').val(isLicenseValid);

						$('#aaochat_ser_instance_type').val(serverInstanceType);
						$('#aaochat_ser_url').val(url);
						$('#aaochat_ser_file_server_url').val(fileServerUrl);
						$('#aaochat_ser_storage_size').val(storageSize);
						$('#aaochat_ser_id').val(serverDetailsId);

						$('#aaochat_license_status').val(status);
						$('#aaochat_license_id').val(id);
						$('#aaochat_activation_date').val(activationDate);
						$('#aaochat_ipaddress').val(ipAddress);

						$('.aaochat-setting-extra-fields').removeClass('hide');
						$('.aaochat-setting-extra-fields').addClass('show');

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
						}, 30000);
		    		}
		    		else 
		    		{
		    			if(typeof license_response.message != 'undefined') {
		    				alert(license_response.message);
							$("#side-menu-license-activate").removeAttr('disabled','disabled');
							$('.aaochat_loader').hide();
		    			}
		    		}
				},
				error: function(xhr, status, error){
					$("#side-menu-license-activate").removeAttr('disabled','disabled');
					$('.aaochat_loader').hide();
				 }
			}); 

	});

	$( "#side-menu-license-surrender" ).on( "click", function(e) {
		var aaochat_license_key = $('#aaochat_license_key').val();
		if(aaochat_license_key == '') {
			alert('Please enter license key');
			$(this).removeAttr('disabled','disabled');
			return false;
		}
		$(this).attr('disabled','disabled');
			$('.aaochat_loader').show();
			$.ajax({
				url: OC.generateUrl('/apps/aaochat/surrenderlicense'),
				type: 'POST',
				data: { licenseKey: aaochat_license_key },
				success: function (license_response) {
					//console.log(license_response);
		    		if( license_response.status == 'success' )
		    		{
		    			var responseData = license_response.data;
		    			var id = responseData._id;
		    			var clientId = responseData.clientId;
		    			var licenseKey = responseData.licenseKey;		    			
		    			var serverDetails = responseData.serverDetails;
		    			var serverInstanceType = serverDetails.serverInstanceType;
		    			var url = serverDetails.url;
		    			var fileServerUrl = serverDetails.fileServerUrl;
		    			var storageSize = serverDetails.storageSize;
		    			var serverDetailsId = serverDetails._id;
						var status = responseData.status;
						var activationDate = responseData.activationDate;
						var ipAddress = responseData.ipAddress;
						var isLicenseValid = 'no';
						if(status == 'active') {
							isLicenseValid = 'yes';
						}

						$('#aaochat_client_id').val(clientId);
						$('#aaochat_license_key').val(licenseKey);
						$('#aaochat_is_license_valid').val(isLicenseValid);

						$('#aaochat_ser_instance_type').val(serverInstanceType);
						$('#aaochat_ser_url').val(url);
						$('#aaochat_ser_file_server_url').val(fileServerUrl);
						$('#aaochat_ser_storage_size').val(storageSize);
						$('#aaochat_ser_id').val(serverDetailsId);

						$('#aaochat_license_status').val(status);
						$('#aaochat_license_id').val(id);
						$('#aaochat_activation_date').val(activationDate);
						$('#aaochat_ipaddress').val(ipAddress);

						$('.aaochat-setting-extra-fields').removeClass('hide');
						$('.aaochat-setting-extra-fields').addClass('show');

						setTimeout(function(){
							window.location.reload();
						}, 5000);
		    		}
		    		else 
		    		{
		    			if(typeof license_response.message != 'undefined') {
		    				alert(license_response.message);
							$("#side-menu-license-surrender").removeAttr('disabled','disabled');
							$('.aaochat_loader').hide();
		    			}
		    		}
				},
				error: function(xhr, status, error){
					$("#side-menu-license-surrender").removeAttr('disabled','disabled');
					$('.aaochat_loader').hide();
				 }
			}); 

	});
}); 