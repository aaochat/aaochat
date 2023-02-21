$(document).ready(function () {
  windowFileId = 0;
  console.log('load aaochat sidebar');
  var aaochatTabPluginLoaded = false;
  var aaochatTabPlugin = function() {
    if(OCA.Files && OCA.Files.Sidebar && aaochatTabPluginLoaded == false){

      var AaochatSidebarView = new OCA.Files.Sidebar.Tab({
        id: 'aaochatTabView',
        name : t('aaochat', 'Aao'),
        icon : 'icon-aaochat',
        mount : (el, fileInfo) => {
          AaochatSidebarView.$el = el;

          console.log(fileInfo)
          AaochatSidebarView.getFileInfo = function(){
            return {attributes:fileInfo};
          }
          AaochatSidebarView._renderSelectList(el);
         
        },
        enabled: function(){
          return true;
        },
        update: function(){

        },
        destroy: function(){

        },
      });

      
      AaochatSidebarView.className= 'tab aaochatTabView';
      AaochatSidebarView.currentFileInfo= null;
      AaochatSidebarView.currentFileName= null;
      AaochatSidebarView.currentFileId= null;
      AaochatSidebarView.currentFilePath= null;
      AaochatSidebarView.imgBaseUrl= null;
      AaochatSidebarView.channelId= null;
      AaochatSidebarView.aaochtChannel= [];
      AaochatSidebarView.WebSocketObj= null;
      AaochatSidebarView.aaochtMessages= [];
      AaochatSidebarView.aaochtRootUser= [];
      AaochatSidebarView.aaochtChannelMessageCount=0;
      AaochatSidebarView.aaochtChannelMessageCounter=0;
      AaochatSidebarView.aaochtMessagesDates= [];
      AaochatSidebarView.aaochtRootUserId= null;
      AaochatSidebarView.aochat_tab_loader_html= '';
      AaochatSidebarView.previousAaochtMessageDate='';
      AaochatSidebarView.displayAaochtMessageDate='';
      AaochatSidebarView.chatMessageLoading= false; 
      AaochatSidebarView.chatMessageMenuIsOpen= false;

       
      /*
      AaochatSidebarView.events= {
          "click .aaochat-start-conversion a": "_onClickStartConversion",
          "keydown #txtMessage": "_onEnterSendMessage",
          "click .aaochat-send-message-btn": "_onClickSendMessage",
          "click .aaocchat-dropdown-menu-left-side-open .replyMessageAction a": "replyMessage",
          "click .aaocchat-dropdown-menu-left-side-open .replyPrivatelyAction a": "replyPrivately",
          "click .aaocchat-dropdown-menu-left-side-open .forwardMessageAction a": "forwardMessage",
          "click .aaocchat-dropdown-menu-left-side-open .starMessageWithTagAction a": "starMessageWithTag",
          "click .aaocchat-dropdown-menu-left-side-open .copyMessageAction a": "copyMessage",
          "click .aaocchat-dropdown-menu-left-side-open .downloadAttachmentAction a": "downloadAttachment",
          "click .aaocchat-dropdown-menu-left-side-open .viewMessageStatisticAction a": "viewMessageStatistic",
          "click .aaocchat-dropdown-menu-left-side-open .setReminderAction a": "setReminder",
          "click .aaocchat-dropdown-menu-left-side-open .deleteMessageAction a": "deleteMessage",
          "click .aaocchat-dropdown-menu-left-side-open .deleteMessageForMeAction a": "deleteMessageForMe",
          "click .open-message-send-option-btn":"openOptionSendMenu",
          "click .close-message-send-option-btn":"closeOptionSendMenu",
          "click .aaochat-msg-block .reply-message":"scrollToReply",
        };
        */
        $('#tab-aaochatTabView').on('click', '.aaochat-start-conversion a', function () {
          AaochatSidebarView._onClickStartConversion();
        });
        $('#tab-aaochatTabView').on('keydown', '#txtMessage', function () {
          AaochatSidebarView._onEnterSendMessage();
        });
        $('#tab-aaochatTabView').on('click', '.aaochat-send-message-btn', function () {
          AaochatSidebarView._onClickSendMessage();
        });
        $('#tab-aaochatTabView').on('click', '.open-message-send-option-btn', function () {
          AaochatSidebarView.openOptionSendMenu();
        });
        $('#tab-aaochatTabView').on('click', '.close-message-send-option-btn', function () {
          AaochatSidebarView._onClickStartConversion();
        });
        $('#tab-aaochatTabView').on('click', '.aaochat-msg-block .reply-message', function () {
          AaochatSidebarView.scrollToReply();
        });

        /*
        $('#tab-aaochatTabView').on('click', '.aaocchat-dropdown-menu-left-side-open .replyMessageAction a', function () {
          AaochatSidebarView.replyMessage();
        });
        $('#tab-aaochatTabView').on('click', '.aaocchat-dropdown-menu-left-side-open .replyPrivatelyAction a', function () {
          AaochatSidebarView.replyPrivately();
        });
        $('#tab-aaochatTabView').on('click', '.aaocchat-dropdown-menu-left-side-open .forwardMessageAction a', function () {
          AaochatSidebarView.forwardMessage();
        });
        $('#tab-aaochatTabView').on('click', '.aaocchat-dropdown-menu-left-side-open .starMessageWithTagAction a', function () {
          AaochatSidebarView.starMessageWithTag();
        });
        $('#tab-aaochatTabView').on('click', '.aaocchat-dropdown-menu-left-side-open .copyMessageAction a', function () {
          AaochatSidebarView.copyMessage();
        });
        $('#tab-aaochatTabView').on('click', '.aaocchat-dropdown-menu-left-side-open .downloadAttachmentAction a', function () {
          AaochatSidebarView.downloadAttachment();
        });
        $('#tab-aaochatTabView').on('click', '.aaocchat-dropdown-menu-left-side-open .viewMessageStatisticAction a', function () {
          AaochatSidebarView.viewMessageStatistic();
        });
        $('#tab-aaochatTabView').on('click', '.aaocchat-dropdown-menu-left-side-open .setReminderAction a', function () {
          AaochatSidebarView.setReminder();
        });
        $('#tab-aaochatTabView').on('click', '.aaocchat-dropdown-menu-left-side-open .deleteMessageAction a', function () {
          AaochatSidebarView.deleteMessage();
        });
        $('#tab-aaochatTabView').on('click', '.aaocchat-dropdown-menu-left-side-open .deleteMessageForMeAction a', function () {
          AaochatSidebarView.deleteMessageForMe();
        });
        */

        /**
         * Tab label
         */
         AaochatSidebarView._label= 'aaochat';

      

        /**
         * Clears all variables.
         */
         AaochatSidebarView.clear= () => {
          AaochatSidebarView.currentFileName = null;
          AaochatSidebarView.currentFileId = null;
          AaochatSidebarView.currentFilePath = null;

          AaochatSidebarView.clearDateVars();     
        };

        /**
         * Clears all variables.
         */
         AaochatSidebarView.clearDateVars= () => {
          AaochatSidebarView.aaochtMessagesDates = [];
          AaochatSidebarView.previousAaochtMessageDate = '';
          AaochatSidebarView.aaochtChannelMessageCounter = 0;
          AaochatSidebarView.aaochtChannelMessageCount = 0;        
      };

        /**
         * Renders this details view
         *
         * @abstract
         */
        /*AaochatSidebarView.render = () => {
          AaochatSidebarView._renderSelectList(AaochatSidebarView.$el);
        };
        */

        AaochatSidebarView._wsSend= async (message, callback) => {
          var self = AaochatSidebarView;
          self._waitForConnection(function () {        
            self.WebSocketObj.send(message);
            
            if (typeof callback !== 'undefined') {
              callback();
            }
          }, 1000);
        };
        AaochatSidebarView._waitForConnection= (callback, interval) => {
          var self = AaochatSidebarView;
          if (self.WebSocketObj.readyState === 1) {
            callback();
          } else {
              // optional: implement backoff for interval here
              setTimeout(function () {
                self._waitForConnection(callback, interval);
              }, interval);
          }
        };
        AaochatSidebarView._showLoader= () => {
          $('.aaochat_tab_loader').css({"display": "flex"});
        };
        AaochatSidebarView._hideLoader= () => {
          $('.aaochat_tab_loader').css({"display": "none"});
        };
        AaochatSidebarView._renderSelectList= ($el)=> {
          var self = AaochatSidebarView;
          self.currentFileInfo = AaochatSidebarView.getFileInfo();
          self.aaochtMessages.messages = [];

          var aaochat_tab_loader_image = OC.generateUrl('apps/aaochat/img/rolling.gif'); 
          var  aaochat_tab_loader_image_final = aaochat_tab_loader_image.replace("/index.php", "");
          //self.aaochat_tab_loader_html = '<div class="aaochat_tab_loader" style="background: black;height: 100%;width: 100%;opacity: 0.4;position:fixed;top:0;z-index: 999999;display:none;"></div><div class="aaochat_tab_loader" style="position:relative;bottom:40%;left:50%;z-index: 999999;width:100%;text-align: center;display:none;"><img src="'+aaochat_tab_loader_image_final+'" style="height: 72px;"></div>';
          self.aaochat_tab_loader_html = '<div class="aaochat_tab_loader" style="background: black;height: 100%;width: 100%;opacity: 0.1;position:absolute;top:0;z-index: 999999;display:none;align-items: center;justify-content: center;"><img src="'+aaochat_tab_loader_image_final+'" style="height: 72px;"></div>';
          
          var aaoChatDomain = 'business2.aaochat.com';
          if (typeof(Storage) !== "undefined") {
            var aaoChatServerURL = localStorage.getItem("nextcloud-AaoChatServerURL");
            aaoChatDomain = aaoChatServerURL.replace(/(^\w+:|^)\/\//, '');
          }
          if(AaochatSidebarView.WebSocketObj != null) {
            AaochatSidebarView.WebSocketObj.close();
          }
          
          const host = 'wss://'+aaoChatDomain+'/socket.io/?EIO=3&transport=websocket';
          AaochatSidebarView.WebSocketObj = new WebSocket(host);

          var messageListner = function(event) {
              if(event.data != null) {
              if (event.data.substring(0, 2) == '42') {
                  
                  let serverMessage = JSON.parse(event.data.substring(2));
                  if(serverMessage[0]!='user-last-seen' && serverMessage[0]!='user:connected'){
                    console.log('WebSocket message listner');
                    console.log(serverMessage);
                  }
                    if(serverMessage[0]=='message'){
                      self.clearDateVars();
                      let messageid = serverMessage[1].message_id;
                      let channelid = serverMessage[1].channel_id;                                     
                      if(channelid == self.channelId) {
                        let messageExists = -1;
                        let loggedInUser = OC.getCurrentUser(); 
                        if(serverMessage[1].user_id != loggedInUser.uid) {
                          if(!serverMessage[1].seen) {
                            self._wsSend('42' + JSON.stringify(['user:message-seen', {
                              message_id: serverMessage[1].message_id,
                              user_id: serverMessage[1].user_id,
                              channel_id: serverMessage[1].channel_id,
                              is_splash: serverMessage[1].is_splash                 
                            }]),function () {});
                          }
                        }

                        if(self.aaochtMessages.messages.length > 0) {
                          messageExists = self.aaochtMessages.messages.findIndex(function(singleMessage){
                            return singleMessage.message_id == messageid;
                          });
                        }
                        if(messageExists == -1) {
                          self.aaochtMessages.messages.unshift(serverMessage[1]);
                        }

                        self.updateChatMessageHistoryDisplay({status:"success",messages:self.aaochtMessages.messages}, true, false);
                      }
                    } else if(serverMessage[0]=='message-deleted') {
                      self.clearDateVars();
                      let messageid = serverMessage[1].message_id;
                      let channelid = serverMessage[1].channel_id;
                      if(channelid == self.channelId) {
                        let messageIndex = -1;
                        if(self.aaochtMessages.messages.length > 0) {
                          messageIndex = self.aaochtMessages.messages.findIndex(function(singleMessage){
                            return singleMessage.message_id == messageid;
                          });
                        }
                        if(messageIndex != -1) {
                          self.aaochtMessages.messages[messageIndex].trashed = true;
                        }
                        self.updateChatMessageHistoryDisplay({status:"success",messages:self.aaochtMessages.messages}, true, false);
                      }
                    } else if(serverMessage[0]=='message-seen') {
                      let messageid = serverMessage[1].message_id;
                      let channelid = serverMessage[1].channel_id;
                      if(channelid == self.channelId) {
                        let messageIndex = -1;
                        if(self.aaochtMessages.messages.length > 0) {
                          messageIndex = self.aaochtMessages.messages.findIndex(function(singleMessage){
                            return singleMessage.message_id == messageid;
                          });
                        }
                        if(messageIndex != -1) {
                          self.aaochtMessages.messages[messageIndex].seen = true;
                        }
                        let readStatusI = '<i class="icon-double-tick-icon seen-msg"></i>';
                        $('#message-'+messageid).find('small').html(readStatusI);
                      }

                    } 
                }
              }
          };

          var authKey = '';
          var aaoChatServerURL = '';
          if (typeof(Storage) !== "undefined") {
            authKey = localStorage.getItem("ngStorage-AuthKey");
            authKey = authKey.replace(/"/g,'');
          }
          
          //this.WebSocketObj.onopen = messageListner;
          AaochatSidebarView.WebSocketObj.onmessage = messageListner;

          AaochatSidebarView.WebSocketObj.onclose = function(closeEvent) {
            /*
            setTimeout(function() {
              if (self.WebSocketObj.readyState != WebSocket.OPEN) {
                self.WebSocketObj = new WebSocket(host);
                //self.WebSocketObj.onopen = messageListner;
                self.WebSocketObj.onmessage = messageListner;
                self.WebSocketObj.onopen = (event) => {
                  self._wsSend('42' + JSON.stringify(['user:connect', {
                    auth_key: authKey
                  }]),function () {});
                };
              }
            }, 1000);
            */
          };
          AaochatSidebarView.WebSocketObj.onerror = function(errorEvent) {
            console.error('Socket encountered error: ', errorEvent.message, 'Closing socket');
            self.WebSocketObj.close();
          };

          AaochatSidebarView.WebSocketObj.onopen = (event) => {
            self._wsSend('42' + JSON.stringify(['user:connect', {
              auth_key: authKey
            }]),function () {});
            /*self.WebSocketObj.send('42' + JSON.stringify(['user:connect', {
                  auth_key: authKey
              }]));*/
          }

          var connectWebsocketIfClosed = async function(event) {
            if (self.WebSocketObj.readyState != WebSocket.OPEN) {
              console.log('readyState:'+self.WebSocketObj.readyState);
              // Do your stuff...
              console.log('WebSocket reconnect stuff goes here...');
              self.WebSocketObj = new WebSocket(host);
              //self.WebSocketObj.onopen = messageListner;
              self.WebSocketObj.onmessage = messageListner;
              self.WebSocketObj.onopen = (event) => {
                self._wsSend('42' + JSON.stringify(['user:connect', {
                  auth_key: authKey
                }]),function () {});
                /*self.WebSocketObj.send('42' + JSON.stringify(['user:connect', {
                      auth_key: authKey
                  }]));*/
                  console.log('call from reconnect interval.');
                  //Reinitilize messages dates
                  self.aaochtMessagesDates = [];
                  self.previousAaochtMessageDate = '';
                  self.aaochtChannelMessageCounter = 0;
                  self.aaochtChannelMessageCount = 0;
                  self.getAaochatConversions($el, 'messages');
              };
            }
          };
          clearInterval(window.disconnectInterval);
          window.disconnectInterval = setInterval(connectWebsocketIfClosed,5000);

          // skip call if fileInfo is null
          if(null == self.currentFileInfo) {
            _self.updateDisplay({
              response: 'error',
              msg: t('aaochat', 'No fileinfo provided.')
            });
            return;
          } else {

          }

          AaochatSidebarView.clear();
          var attributes = self.currentFileInfo.attributes;
          console.log('attributes:');
          console.log(attributes);
          AaochatSidebarView.currentFileName = attributes.name;
          AaochatSidebarView.currentFileId = attributes.id;
          AaochatSidebarView.currentFilePath = attributes.path;

          console.log('here here here');

          var img_base_url_ori = OC.generateUrl('apps/aaochat/img/');
          var img_base_url = img_base_url_ori.replace("/index.php", "");
          AaochatSidebarView.imgBaseUrl = img_base_url;

          console.log('getAaochatConversions:');
          console.log($el);
          AaochatSidebarView.getAaochatConversions($el, 'view', AaochatSidebarView.currentFileId);

        };
        AaochatSidebarView.getAaochatConversions= ($el, loadType, loadingFileId) => {
          var self = AaochatSidebarView;
          //self._showLoader();
          if(loadType == 'view') {
            self.updateDisplay($el, [], loadingFileId);
          }
          var u = OC.getCurrentUser(); 
          var url = OC.generateUrl('/apps/aaochat/getgroup');
          url = url + '?fileid='+AaochatSidebarView.currentFileId;
          $.ajax({
              url: url,
              type: "GET",
              async:false,
              processData: false,
              contentType: "application/json"
          }).done((function (response, status) {

              var responseData = {status:"fail",data:''};
              if(response != '') {
                responseData = $.parseJSON(response);
              }
              if(responseData.status == 'error') {
                console.log('call startConversionDisplay');
                self.startConversionDisplay($el, responseData);
                self._hideLoader();
              } else if(responseData.status == 'success') {
                var channelData = responseData.data;
                var channelUsers = channelData.users;
                self.aaochtRootUser = channelUsers.find(function(singleUser){
                  return singleUser.user_name == u.uid;
                });
                self.aaochtRootUserId = self.aaochtRootUser.user_id
                self.aaochtChannel = channelData;
                self.channelId = channelData._id;
                console.log('call getMessageHistory');
                self.getMessageHistory($el,loadType, loadingFileId);
              }
              
          }))

        };
        AaochatSidebarView.canDisplay=  (fileInfo) => {

          /*
          var validFileTypes = ['doc','docx','ppt','pptx','xls','xlsx','pdf'];
          var currentFileName = fileInfo.name;
          var fileExtension = currentFileName.substr(currentFileName.lastIndexOf(".") + 1).toLowerCase();
          if(validFileTypes.includes(fileExtension) == false) {
            if(fileInfo != null) {
              if(!fileInfo.isDirectory()) {
                return true;
              }
            }
          }
          */
          return true;
        };
        /**
         * display message from ajax callback
         */
         AaochatSidebarView.startConversionDisplay= ($el, responseData) => {
          var self = AaochatSidebarView;

          var aaochatHtml = '<div id="'+AaochatSidebarView.id+'" class="'+AaochatSidebarView.className+'">';
          aaochatHtml += '<div class="aaochat-start-conversion">';
          aaochatHtml += '<span><a href="javascript::void()" class="">';
          aaochatHtml += 'Start Conversation';
          aaochatHtml += '</a></span>';
          aaochatHtml += '</div>';
          aaochatHtml += '</div>';
          

          $el.html(aaochatHtml);

        };
        AaochatSidebarView._onClickStartConversion=  (t) => {
          //creating group at aaochat
          t.preventDefault();
          var self = AaochatSidebarView;
          var u = OC.getCurrentUser(); 
          var url = OC.generateUrl('/apps/aaochat/syncshareinfo');
          url = url + '?fileid='+self.currentFileId;
          $.ajax({
              url: url,
              type: "GET",
              async:false,
              processData: false,
              contentType: "application/json"
          }).done((function (response, status) {
              var responseData = response;           
              if(responseData.status == 'success') {
                var channelData = responseData.data;
                var channelUsers = channelData.users;
                self.aaochtRootUser = channelUsers.find(function(singleUser){
                  return singleUser.user_name == u.uid;
                });
                if(self.aaochtRootUser != null) {
                  self.aaochtRootUserId = self.aaochtRootUser.user_id
                  self.aaochtChannel = channelData;
                  self.channelId = channelData._id;
                }
                self.updateDisplay(self.$el, [], self.currentFileId);
              }            
          }))
        };
        AaochatSidebarView.getMessageHistory= async  ($el, loadType, loadingFileId) => {
          console.log('in getMessageHistory');
          var self = AaochatSidebarView;
          
          var authKey = '';
          var aaoChatServerURL = '';
          if (typeof(Storage) !== "undefined") {
            authKey = localStorage.getItem("ngStorage-AuthKey");
            authKey = authKey.replace(/"/g,'');
            aaoChatServerURL = localStorage.getItem("nextcloud-AaoChatServerURL");
          }        
          var u = OC.getCurrentUser();        
          var postdata = {channel_id:self.channelId};
          var headers = {"Authorization": authKey,'content-type':'application/json'}
          var api_url = aaoChatServerURL+'/api/channel/messages?fetch_notification_messages=false';

        fetch(api_url, {
            method: 'POST', // *GET, POST, PUT, DELETE, etc.
            mode: 'cors', // no-cors, *cors, same-origin
            cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
            headers: headers,
            redirect: 'follow', // manual, *follow, error
            referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
            body: JSON.stringify(postdata) // body data type must match "Content-Type" header
          }).then(async function(response){
            var responseJson = await response.json();
            console.log(responseJson);

            if(loadType == 'view') {
              console.log('loading view');
              self.updateDisplay($el, responseJson, loadingFileId);
            } else if(loadType == 'messages') {
              console.log('loading messages');
              self.updateChatHistoryDisplay($el, responseJson, loadingFileId);
            }

            //self._hideLoader();
          }).catch(function(err){
            self._hideLoader();
          });

        };
        AaochatSidebarView.getChannelMessageHistory= async  (message_id) => {
          var self = AaochatSidebarView;
          //console.log('in getChannelMessageHistory');
          var authKey = '';
          var aaoChatServerURL = '';
          if (typeof(Storage) !== "undefined") {
            authKey = localStorage.getItem("ngStorage-AuthKey");
            authKey = authKey.replace(/"/g,'');
            aaoChatServerURL = localStorage.getItem("nextcloud-AaoChatServerURL");
          }        
          var u = OC.getCurrentUser();        
          var postdata = {channel_id:self.channelId,message_id:message_id};
          var headers = {"Authorization": authKey,'content-type':'application/json'}
          var api_url = aaoChatServerURL+'/api/channel/messages?fetch_notification_messages=false';
          
        fetch(api_url, {
            method: 'POST', // *GET, POST, PUT, DELETE, etc.
            mode: 'cors', // no-cors, *cors, same-origin
            cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
            headers: headers,
            redirect: 'follow', // manual, *follow, error
            referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
            body: JSON.stringify(postdata) // body data type must match "Content-Type" header
          }).then(async function(response){
            var responseData = await response.json();

            if(responseData.status == "success") {
              var responseMessages = responseData.messages;
              $(responseMessages).each(function( index, responseMessage ) {
                if(responseMessage.channel_id == self.channelId) {
                  var messageid = responseMessage.message_id;
                  var messageExists = -1;
                  if(self.aaochtMessages.messages.length > 0) {
                    messageExists = self.aaochtMessages.messages.findIndex(function(singleMessage){
                      return singleMessage.message_id == messageid;
                    });
                  }
                  if(messageExists == -1) {
                    self.aaochtMessages.messages.push(responseMessage);
                    //self.aaochtMessages.messages.unshift(responseMessage);
                  }
                }
              });
              //console.log(self.aaochtMessagesDates);
              //console.log(self.aaochtMessages.messages);
              self.updateChatMessageHistoryDisplay({status:"success",messages:self.aaochtMessages.messages}, false, true);
            }
          }).catch(function(err){
            self.chatMessageLoading = false;
            self._hideLoader();
          });
        },
        AaochatSidebarView.getChannelMessagesByTimestamp= async  (msg_timestamp, message_id) => {
          var self = AaochatSidebarView;
          self.clearDateVars();
          console.log('in getChannelMessagesByTimestamp');
          var authKey = '';
          var aaoChatServerURL = '';
          if (typeof(Storage) !== "undefined") {
            authKey = localStorage.getItem("ngStorage-AuthKey");
            authKey = authKey.replace(/"/g,'');
            aaoChatServerURL = localStorage.getItem("nextcloud-AaoChatServerURL");
          }        
          var u = OC.getCurrentUser();        
          var postdata = {channel_id:self.channelId,user_id:u.uid,timestamp:msg_timestamp};
          var headers = {"Authorization": authKey,'content-type':'application/json'}
          var api_url = aaoChatServerURL+'/api/channel/messages/after-timestamp?fetch_notification_messages=false';
          
        fetch(api_url, {
            method: 'POST', // *GET, POST, PUT, DELETE, etc.
            mode: 'cors', // no-cors, *cors, same-origin
            cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
            headers: headers,
            redirect: 'follow', // manual, *follow, error
            referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
            body: JSON.stringify(postdata) // body data type must match "Content-Type" header
          }).then(async function(response){
            var responseData = await response.json();
            console.log(responseData);
            if(responseData.status == "success") {
              var responseMessages = responseData.messages;
              console.log(responseMessages);
              $(responseMessages).each(function( index, responseMessage ) {
                if(responseMessage.channel_id == self.channelId) {
                  var messageid = responseMessage.message_id;
                  var messageExists = -1;
                  if(self.aaochtMessages.messages.length > 0) {
                    messageExists = self.aaochtMessages.messages.findIndex(function(singleMessage){
                      return singleMessage.message_id == messageid;
                    });
                  }
                  if(messageExists == -1) {
                    self.aaochtMessages.messages.push(responseMessage);
                  }
                }
              });
              //console.log('call from getChannelMessagesByTimestamp');
              self.updateChatMessagesDisplayByTimestamp({status:"success",messages:self.aaochtMessages.messages}, message_id);
            }
          }).catch(function(err){
            self.chatMessageLoading = false;
            self._hideLoader();
          });
        };
        AaochatSidebarView._onEnterSendMessage=  (t) => {
          //t.preventDefault();
          var self = AaochatSidebarView;
          if (t.key === "Enter" || t.keyCode === 13) {
            if(!t.shiftKey){
              self._onClickSendMessage(t);
            }
          }
        };
        AaochatSidebarView._onClickSendMessage=  (t) => {
          t.preventDefault();
          var self = AaochatSidebarView;

          var authKey = '';
          var aaoChatServerURL = '';
          if (typeof(Storage) !== "undefined") {
            authKey = localStorage.getItem("ngStorage-AuthKey");
            authKey = authKey.replace(/"/g,'');
            aaoChatServerURL = localStorage.getItem("nextcloud-AaoChatServerURL");
          }
          var message = $('.aaochat-send-message-row textarea#txtMessage').val();
          message = $.trim(message);
          if(message == '') {
            $('.aaochat-send-message-row textarea#txtMessage').val('');
            $('.aaochat-send-message-row textarea#txtMessage').focus();
            return false;
          }

          var u = OC.getCurrentUser();
          var postdata = {channels:self.channelId, message:message};
          var headers = {"Authorization": authKey}
          var api_url = aaoChatServerURL+'/api/channel/message/upload-file-and-send-message';

          $.ajax({
              url: api_url,
              headers: headers,
              type: "POST",
              data: JSON.stringify(postdata),
              async:false,
              processData: false,
              contentType: "application/json"
          }).done((function (response, status) {
              $('#aaochatTabView #newMessageContainer textarea').val('');

              setTimeout(function() {
                var newMessageId = $( "#chatMessageListContainer .aaochat-msg-block" ).last().attr('id');

                if($("#"+newMessageId).length > 0) {
                  $('#chat-history-wrapper').animate({
                    scrollTop: $("#chat-history-wrapper .chat").height()
                  }, 500);
                }
              }, 500);            
          }))
        };
        /**
         * update display message history after send message
         * Not in use
         */
         AaochatSidebarView.updateMessageHistory =  () => {
          var self = AaochatSidebarView;

          var authKey = '';
          var aaoChatServerURL = '';
          if (typeof(Storage) !== "undefined") {
            authKey = localStorage.getItem("ngStorage-AuthKey");
            authKey = authKey.replace(/"/g,'');
            aaoChatServerURL = localStorage.getItem("nextcloud-AaoChatServerURL");
          }        
          var u = OC.getCurrentUser();        
          var postdata = {channel_id:self.channelId};
          var headers = {"Authorization": authKey}
          var api_url = aaoChatServerURL+'/api/channel/messages?fetch_notification_messages=false';

          $.ajax({
              url: api_url,
              headers: headers,
              type: "POST",
              data: JSON.stringify(postdata),
              async:false,
              processData: false,
              contentType: "application/json"
          }).done((function (response, status) {
              self.updateChatHistoryDisplay(response);       
          }))
        };
        /**
         * display message from ajax callback
         * loadindg messages with form at first time
         */
         AaochatSidebarView.updateDisplay = async ($el, responseData, loadingFileId) => {
          var self = AaochatSidebarView;
          var u = OC.getCurrentUser();
          self.chatMessageMenuIsOpen = false;

          var elementHeight = $("#tab-aaochatTabView").height();
          var chatWindowHeight = elementHeight - 70;
          $( window ).resize(function() {
            var rElementHeight = $("#tab-aaochatTabView").height();
            var rChatWindowHeight = rElementHeight - 70;
            //$("#chat-history-wrapper").css({"height": rChatWindowHeight+"px"});
          });

          aaochatHtml = '';
          aaochatHtml = '<div id="'+self.id+'" class="'+self.className+'">';
          /*
          aaochatHtml = '<div class="header-aaochat">';
          //aaochatHtml += '<i class="icon fa fa-user-o" aria-hidden="true"></i>';
          aaochatHtml += '<p class="name">'+u.displayName+'</p>';
          aaochatHtml += '</div>';
          */

          //aaochatHtml += '<div id="chat-history-wrapper" class="chat-history" style="height:'+chatWindowHeight+'px;">';
          aaochatHtml += '<div id="chat-history-top" style="display:none;"></div>';
          aaochatHtml += '<div id="chat-history-wrapper" class="chat-history" >';
          aaochatHtml += '<div id="chatMessageListContainer" class="aaocaht_log">';

          if(typeof responseData != 'undefined' && responseData != '[]') {
            //var baseUrl = OC.getProtocol() + '://' + OC.getHost()+oc_webroot;
            if(responseData.status == "success") {
              var responseMessagesResult = responseData.messages;
              //maintain message array
              self.aaochtMessages.messages = responseMessagesResult;
              self.aaochtChannelMessageCount = responseMessagesResult.length;
              /*let sortBy = 'created_at';
              let responseMessages = Object.keys(responseMessagesResult).sort(function(a, b) {
                  var l = responseMessagesResult[a][sortBy], r = responseMessagesResult[b][sortBy];
              
                  if (l < r) { return -1; }
                  if (l > r) { return 1; }
                  return 0;
              });
              */
              
              aaochatHtml += '<section class="chat">';
              /*$(responseMessages).each(function( index, messageIndex ) {
                  var aaochatHistoryHtml = self.prepareAaochatHTML(responseMessagesResult[messageIndex]);
                  if(aaochatHistoryHtml != '') {
                    aaochatHtml += aaochatHistoryHtml;
                  }
              });*/
              $(responseMessagesResult).each(function( index, responseMessagesObj ) {
                var aaochatHistoryHtml = self.prepareAaochatHTML(responseMessagesObj);
                if(aaochatHistoryHtml != '') {
                  aaochatHtml += aaochatHistoryHtml;
                }
              });
              aaochatHtml += '</section>';
            }
          }
          

          aaochatHtml += '</div>';
          aaochatHtml += '</div>';
          
          aaochatHtml += '<form class="chat-message" onsubmit="return false;">';
          aaochatHtml += '<div class="row aaochat-send-message-row">';
          aaochatHtml += '<div class="col-md-12">';
          aaochatHtml += '<div class="input-group">';

          //For next version
          /*
          aaochatHtml += '<div class="input-group-btn attach-button">';
          aaochatHtml += '<button type="button" popover-is-open="popoverIsOpen" popover-append-to-body="true" popover-placement="top-left" popover-title="Attachments" class="btn btn-default btn-lg no-border-button ">';
          aaochatHtml += '<i class="aaochat-fa aaochat-fa-paperclip"></i>';
          aaochatHtml += '</button>';
          aaochatHtml += '</div>';
          */

          aaochatHtml += '<div style="" emoji-form="" emoji-message="emojiMessage" id="newMessageContainer">';
          aaochatHtml += '<textarea autogrow="" class="form-control input-lg ng-pristine ng-untouched ng-valid " placeholder="Type your message here..." id="txtMessage" style=""></textarea>';
          
          //For next version
          /*
          aaochatHtml += '<div class="emoji-wysiwyg-editor ng-valid form-control ng-empty ng-dirty ng-valid-parse ng-touched" contenteditable="true" id="messageDiv" ></div>';
          aaochatHtml += `<div class="newMessageOptionsContainer" style="position: relative;">
                          <button type="button" style="display:none" ng-disabled="channel.is_splash==true || emojiMessage.customReceivers.length > 0 || channel.is_splash==true || (channel.replyOf &amp;&amp; channel.replyOf.is_private==true)" uib-tooltip="Schedule Message" popover-is-open="schedulePopoverIsOpen" popover-append-to-body="true" popover-placement="top" popover-title="Schedule Message" class="option-btn user-selection-btn btn-default schedule-msg-option-btn">
                              <i class="aaochat-fa aaochat-fa-clock-o"></i>
                          </button>
                          <button type="button" style="display:none" uib-tooltip="Send Privately" popover-title="Send Privately" id="userSelectionBtn" class="option-btn user-selection-btn btn-default send-privately-option-btn">
                              <i class="aaochat-fa aaochat-fa-users"></i>
                          </button>
                          <button type="button" style="display:none" uib-tooltip="Self Destruct" id="splashBtn" ng-class="channel.is_splash==true ? 'btn btn-warning':'btn btn-default'" class="option-btn splash-btn btn-default splash-option-btn">
                              <img src="${self.imgBaseUrl}bomb.svg" style="display:${self.aaochtChannel.is_splash==true?'none':'block'}; margin-top: -3px; height: 15px" >
                              <img src="${self.imgBaseUrl}self_destruct_inverse.svg" style="display:${self.aaochtChannel.is_splash!=true?'none':'block'}; margin-top: -3px;height: 15px" class="ng-hide">
                          </button>
                          <button type="button" style="display:none" id="gifBtn" class="option-btn gif-btn btn-default gif-option-btn">
                              GIF
                          </button>
                          <button type="button" style="display:none" id="emojibtn" class="option-btn btn-default emoji-btn emoji-option-btn">
                              <i class="aaochat-fa aaochat-fa-smile-o"></i>
                          </button>
                          <button type="button" class="option-btn btn-default btn-toggle-message-buttons open-message-send-option-btn">
                              <i class="aaochat-fa ${self.chatMessageMenuIsOpen==false?'aaochat-fa-plus':'aaochat-fa-chevron-right'}"></i>
                          </button>
                      </div>`;
          */
          aaochatHtml += '</div>';

          aaochatHtml += '<div class="input-group-btn">';
          aaochatHtml += '<button type="submit " class="aaochat-send-message-btn" >';
          aaochatHtml += '<img src="'+self.imgBaseUrl+'send.png" alt="">';
          aaochatHtml += '</button>';
          aaochatHtml += '</div>';
          aaochatHtml += '</div>';
          aaochatHtml += '</div>';
          aaochatHtml += '</div>';
          aaochatHtml += '</form>';
          aaochatHtml += '</div>';

          //console.log('loadingFileId:'+loadingFileId);
          //console.log('currentFileId:'+self.currentFileId);

          if(loadingFileId == self.currentFileId) {
            $($el).html(aaochatHtml);
          }

          /*
          setTimeout(function() {
            $('#chat-history-wrapper').animate({
              scrollTop: $("#chat-history-wrapper .chat").height()
            }, 500);

            $( "#aaochatTabView" ).unbind( "click" ).one('click',function(event) {
              event.preventDefault();
              $('#chat-history-wrapper').animate({
                scrollTop: $("#chat-history-wrapper .chat").height()
              }, 500);
            });
          }, 1000);
          */

          setTimeout(function() {
            if ($('.aaochat_tab_loader').length === 0) {
                $("div#aaochatTabView").append( self.aaochat_tab_loader_html );
                //$("#chat-history-wrapper").append( self.aaochat_tab_loader_html );
            }

            jQuery( '[data-fancybox]' ).fancybox({
              buttons: [
                //"zoom",
                //"share",
                //"slideShow",
                //"fullScreen",
                "download",
                "close"
              ],
              infobar : false,
              afterShow : function(instance, item) {
                var src =  item.src;
                src = src + '&download=true';          
                $("[data-fancybox-download]").attr('href', src);
              }
            });
          }, 1000);

          setTimeout(function() {     
            var aaochatTabView = document.querySelector('#aaochatTabView')
            var chatMessageListContainer = document.querySelector('#chat-history-wrapper');
            
            if(chatMessageListContainer !== null) {
              var lastScrollTop = 0;
              var chatMessageListContainerTop = $("#chat-history-wrapper").offset().top;
              chatMessageListContainer.addEventListener('scroll',function() {
                chatMessageListContainerTop = $("#chat-history-wrapper").offset().top;
                var windowChatWrapperTop = $("#chat-history-wrapper .chat").offset().top;//$("#chat-history-wrapper .chat").height();
                chatMessageListContainerTop = parseInt(chatMessageListContainerTop);
                windowChatWrapperTop = parseInt(windowChatWrapperTop);
                var chatWindowTop = $("#chat-history-top").offset().top;//$("#chat-history-top").scrollTop();
                var windowScreenHeight = window.screen.availHeight;
                //console.log('window screen height 1:'+windowScreenHeight);
                //console.log('window chat wrapper top 1:'+(windowChatWrapperTop));
                //console.log('scroll wrapper top 1:'+(chatMessageListContainerTop));

                var chatWindowTopDiff = parseInt(chatMessageListContainerTop-windowChatWrapperTop);
                //console.log('chat window top diff 1:'+chatWindowTopDiff);

                var st = $(this).scrollTop();
                if (st > lastScrollTop){
                    // downscroll code
                } else {
                    // upscroll code
                  //if(chatMessageListContainerTop == windowChatWrapperTop || chatWindowTopDiff < 2){
                  if(
                    (chatMessageListContainerTop == windowChatWrapperTop && chatWindowTopDiff == 0 && self.chatMessageLoading == false) ||
                    (chatWindowTopDiff == 1 && self.chatMessageLoading == false)
                  ){
                    //console.log('load new messages');
                    
                    self.chatMessageLoading = true;
                    var cat_last_element_id = $(this).find('.aaochat-msg-block').last().attr('id');
                    console.log('older message id:'+cat_last_element_id);
                    if(typeof cat_last_element_id != 'undefined') {
                      self._showLoader();
                      //Reinitilize messages dates
                      self.aaochtMessagesDates = [];
                      self.previousAaochtMessageDate = '';
                      self.aaochtChannelMessageCounter = 0;
                      self.aaochtChannelMessageCount = 0;

                      let message_id = cat_last_element_id.replace('message-','');
                      self.getChannelMessageHistory(message_id);
                    }
                  }
                }
                lastScrollTop = st;


              });
            }
          }, 1000);

        };
        /**
         * display message history from ajax callback
         * load messages when socket reconnects
         */
         AaochatSidebarView.updateChatHistoryDisplay= async ($el, responseData, loadingFileId) => {
          var self = AaochatSidebarView;

          var u = OC.getCurrentUser();
          var aaochatHtml = '';
          if(typeof responseData != 'undefined' && responseData != '[]') {
            if(responseData.status == "success") {
              var responseMessagesResult = responseData.messages;
              self.aaochtChannelMessageCount = parseInt(self.aaochtChannelMessageCount) + parseInt(responseMessagesResult.length);
              
              /*let sortBy = 'created_at';
              let responseMessages = Object.keys(responseMessagesResult).sort(function(a, b) {
                  var l = responseMessagesResult[a][sortBy], r = responseMessagesResult[b][sortBy];
              
                  if (l < r) { return -1; }
                  if (l > r) { return 1; }
                  return 0;
              });
              */

              aaochatHtml += '<section class="chat">';
              //aaochatHtml += '<div class="header-aaochat">';
              //aaochatHtml += '<p class="name">'+u.displayName+'</p>';
              //aaochatHtml += '</div>';

              /*
              $(responseMessages).each(function( index, messageIndex ) {
                  var aaochatHistoryHtml = self.prepareAaochatHTML(responseMessagesResult[messageIndex]);
                  if(aaochatHistoryHtml != '') {
                    aaochatHtml += aaochatHistoryHtml;
                  }
              });
              */
            var messagePreviousIndex = 0;
              $(responseMessagesResult).each(function( index, responseMessagesObj ) {
                //Check if message not exists in local
                var messageExists = -1;
                if(self.aaochtMessages.messages.length > 0) {
                  messageExists = self.aaochtMessages.messages.findIndex(function(singleMessage){
                    return singleMessage.message_id == responseMessagesObj.message_id;
                  });
                }
                if(messageExists == -1) {
                  //self.aaochtMessages.messages.unshift(responseMessagesObj);
                  self.aaochtMessages.messages.splice(messagePreviousIndex, 0, responseMessagesObj);
                } else {
                  messagePreviousIndex = messageExists;
                }

                var aaochatHistoryHtml = self.prepareAaochatHTML(responseMessagesObj);
                if(aaochatHistoryHtml != '') {
                  aaochatHtml += aaochatHistoryHtml;
                }
              });
              aaochatHtml += '</section>';
            }
            $("#chatMessageListContainer").html(aaochatHtml);
          }
          self._hideLoader();

          setTimeout(function() {
            jQuery( '[data-fancybox]' ).fancybox({
              buttons: [
                //"zoom",
                //"share",
                //"slideShow",
                //"fullScreen",
                "download",
                "close"
              ],
              infobar : false,
              afterShow : function(instance, item) {
                var src =  item.src;
                src = src + '&download=true';          
                $("[data-fancybox-download]").attr('href', src);
              }
            });
          }, 1000);

        };
        /**
         * display message history from ajax callback
         * loading messages form listner, and older
         */
         AaochatSidebarView.updateChatMessageHistoryDisplay= (responseData, doScroll, resetLoadingVars) => {
          
          var self = AaochatSidebarView;
          var u = OC.getCurrentUser();
          var aaochatHtml = '';
          if(typeof responseData != 'undefined' && responseData != '[]') {
            //var baseUrl = OC.getProtocol() + '://' + OC.getHost()+oc_webroot;

            if(responseData.status == "success") {
              //message is already in self.aaochtMessages.messages
              var responseMessagesResult = responseData.messages;
              self.aaochtChannelMessageCount = parseInt(responseMessagesResult.length);
              
              /*let sortBy = 'created_at';
              let responseMessages = Object.keys(responseMessagesResult).sort(function(a, b) {
                var l = responseMessagesResult[a][sortBy], r = responseMessagesResult[b][sortBy];
            
                if (l < r) { return -1; }
                if (l > r) { return 1; }
                return 0;
              });
              */

              aaochatHtml += '<section class="chat">';
              //aaochatHtml += '<div class="header-aaochat">';
              //aaochatHtml += '<p class="name">'+u.displayName+'</p>';
              //aaochatHtml += '</div>';

              /*$(responseMessages).each(function( index, messageIndex ) {
                  var aaochatHistoryHtml = self.prepareAaochatHTML(responseMessagesResult[messageIndex]);
                  if(aaochatHistoryHtml != '') {
                    aaochatHtml += aaochatHistoryHtml;
                  }
              });
              */
              $(responseMessagesResult).each(function( index, responseMessagesObj ) {
                var aaochatHistoryHtml = self.prepareAaochatHTML(responseMessagesObj);
                if(aaochatHistoryHtml != '') {
                  aaochatHtml += aaochatHistoryHtml;
                }
              });
              aaochatHtml += '</section>';
            }
          }

          $("#chatMessageListContainer").html(aaochatHtml);

          if(doScroll == true) {
            setTimeout(function() {
              var scrollPositon = ($('#chat-history-wrapper').scrollTop()+100);
              var chatWindowHeight = $("#chat-history-wrapper .chat").height();
              var chatWindowDiff = (chatWindowHeight+scrollPositon);
              /*
              if((scrollPositon) < 150) {
                $('#chat-history-wrapper').animate({
                  scrollTop: $("#chat-history-wrapper .chat").height()
                }, 500);
              }*/
            }, 1000);
          }

          if(resetLoadingVars == true) {
            self._hideLoader();
            self.chatMessageLoading = false;
          }

          setTimeout(function() {
            jQuery( '[data-fancybox]' ).fancybox({
              buttons: [
                //"zoom",
                //"share",
                //"slideShow",
                //"fullScreen",
                "download",
                "close"
              ],
              infobar : false,
              afterShow : function(instance, item) {
                var src =  item.src;
                src = src + '&download=true';          
                $("[data-fancybox-download]").attr('href', src);
              }
            });
          }, 1000);

        },
        /**
         * display message by timestamp
         * loading messages till the timestamp
         */
         AaochatSidebarView.updateChatMessagesDisplayByTimestamp= (responseData, message_id) => {
          
          var self = AaochatSidebarView;
          var u = OC.getCurrentUser();
          var aaochatHtml = '';
          if(typeof responseData != 'undefined' && responseData != '[]') {
            //var baseUrl = OC.getProtocol() + '://' + OC.getHost()+oc_webroot;

            if(responseData.status == "success") {
              //message is already in self.aaochtMessages.messages
              var responseMessagesResult = responseData.messages;
              self.aaochtChannelMessageCount = parseInt(responseMessagesResult.length);
              
              aaochatHtml += '<section class="chat">';

              $(responseMessagesResult).each(function( index, responseMessagesObj ) {
                var aaochatHistoryHtml = self.prepareAaochatHTML(responseMessagesObj);
                if(aaochatHistoryHtml != '') {
                  aaochatHtml += aaochatHistoryHtml;
                }
              });
              aaochatHtml += '</section>';
            }
          }

          $("#chatMessageListContainer").html(aaochatHtml);

          setTimeout(function() {
            self._hideLoader();
            self.chatMessageLoading = false;

            if($("#message-"+message_id).length > 0) {
              console.log('scroll to original message');
              /*$('#chat-history-wrapper').animate({
                scrollTop: $("#message-"+message_id).offset().top
              }, 500);*/
              $('#reply-'+message_id).click();
            }
          }, 1000);

          setTimeout(function() {
            jQuery( '[data-fancybox]' ).fancybox({
              buttons: [
                //"zoom",
                //"share",
                //"slideShow",
                //"fullScreen",
                "download",
                "close"
              ],
              infobar : false,
              afterShow : function(instance, item) {
                var src =  item.src;
                src = src + '&download=true';          
                $("[data-fancybox-download]").attr('href', src);
              }
            });
          }, 1000);

        };
        /**
         * display message history from ajax callback
         */
         AaochatSidebarView.prepareAaochatHTML= (responseMessage) => {
          var self = AaochatSidebarView;
          var u = OC.getCurrentUser();

          var authKey = '';
          var aaoChatServerURL = '';
          var aaoChatFileServerURL = '';
          if (typeof(Storage) !== "undefined") {
            authKey = localStorage.getItem("ngStorage-AuthKey");
            authKey = authKey.replace(/"/g,'');
            aaoChatServerURL = localStorage.getItem("nextcloud-AaoChatServerURL");
            aaoChatFileServerURL = localStorage.getItem("nextcloud-AaoChatFileServerURL");
          }   

          self.aaochtChannelMessageCounter++;
          self.displayAaochtMessageDate = '';
          var aaochatMessageHtml = '';
          var aaochatMessageId = responseMessage.message_id;
          var aaochatMessage = responseMessage.message;
          var messageType = responseMessage.message_type;
          var chatUser = responseMessage.user;
          var timestamp = responseMessage.timestamp;
          var trashed = responseMessage.trashed;
          var attachments = responseMessage.attachments;
          var replyMessage = undefined;

          var replyOriginalMessage = '';
          if(responseMessage.reply && Object.keys(responseMessage.reply).length > 0) {
            replyMessage = responseMessage.reply;
            if(replyMessage.message == '') {
              var replyAttachments = replyMessage.attachments;
              if (replyAttachments.length !== 0) {
                replyOriginalMessage = `${replyAttachments[0].display_name}`;
              }
            } else {
              replyOriginalMessage = replyMessage.message;
            }
          }
          var chatDate = new Date(responseMessage.created_at);
          var formatedDay = chatDate.toLocaleDateString(undefined, {
            day:   'numeric'
          });
          var formatedMonth = chatDate.toLocaleDateString(undefined, {
            month: 'short'
          });
          var formatedYear = chatDate.toLocaleDateString(undefined, {
            year:  'numeric'
          });


          var formatedLogDate = formatedDay + "-" + formatedMonth + "-" + formatedYear;
          var formatedDate = formatedDay + "-" + formatedMonth + "-" + formatedYear;/*chatDate.toLocaleDateString(undefined, {
              day:   'numeric',
              month: 'short',
              year:  'numeric',
          });*/
          var formatedTime = chatDate.toLocaleTimeString('en-US', {
              hour:   '2-digit',
              minute: '2-digit',
          });
          var formatedChatDate = formatedDate+' '+formatedTime;
          var msgSeenClass = 'icon-single-tick-icon gray-txt';
          if(responseMessage.seen) {
            msgSeenClass = 'icon-double-tick-icon gray-txt';
          }
          if(responseMessage.seen) {
            msgSeenClass = 'icon-double-tick-icon seen-msg';
          }

          if(aaochatMessage == '') {
            if (attachments.length !== 0) {
              var iconExtension = attachments[0].extension;
              var lastCharcater = iconExtension.substring(iconExtension.length - 1);
              if(lastCharcater == 'x') {
                iconExtension = iconExtension.slice(0, -1) + '';
              }
              if(iconExtension == 'mp4' || iconExtension == 'mp3') { 
                iconExtension = 'play-media.png'; 
              } else {
                iconExtension = iconExtension+'.svg';
              }
              
              if(attachments[0].extension == 'png'||attachments[0].extension == 'jpg'||attachments[0].extension == 'jpeg'||attachments[0].extension == 'gif') {
                aaochatMessage = `<a href="${aaoChatFileServerURL+"/download/attachment/"+attachments[0].file_id}?authKey=${authKey}" data-fancybox class="aaochat-image-preview chat-attachment-box" >
                <div class="chat-image">
                  <img src="${aaoChatFileServerURL+"/download/attachment/thumb/"+attachments[0].file_id}.${attachments[0].extension}?authKey=${authKey}" alt="">
                </div>
                </a>`;

              } else {
                aaochatMessage = `<a href="${aaoChatFileServerURL+"/download/attachment/"+attachments[0].file_id}?download=true&authKey=${authKey}" download class="chat-attachment-box" >
                <div class="file-icon-container">
                <img src="${aaoChatServerURL}/public/images/${iconExtension}" alt="">
                </div>
                ${attachments[0].display_name}
                </a>`;
              }
            }
          } else {
            if (attachments.length !== 0) {
              var aaochatAttachmentMessage = '<pre>'+aaochatMessage+'</pre>';
              aaochatAttachmentMessage = aaochatAttachmentMessage.replace(
                    /((http|https|ftp|ftps):\/\/[\w?=&.\/-;#~%-]+(?![\w\s?&.\/;#~%"=-]*>))/g,
                    '<a target="_blank" href="$1">$1</a>'
                );
              if(attachments[0].extension == 'png'||attachments[0].extension == 'jpg'||attachments[0].extension == 'jpeg'||attachments[0].extension == 'gif') {
                aaochatMessage = `<a href="${aaoChatFileServerURL+"/download/attachment/"+attachments[0].file_id}?authKey=${authKey}" data-fancybox data-caption="${attachments[0].display_name}" class="aaochat-image-preview chat-attachment-box" >
                <div class="chat-image">
                  <img src="${aaoChatFileServerURL+"/download/attachment/thumb/"+attachments[0].file_id}.${attachments[0].extension}?authKey=${authKey}" alt="">
                </div>              
                </a>
                ${aaochatAttachmentMessage}
                `;

              } else {
                var iconExtension = attachments[0].extension;
                var lastCharcater = iconExtension.substring(iconExtension.length - 1);
                if(lastCharcater == 'x') {
                  iconExtension = iconExtension.slice(0, -1) + '';
                }
                if(iconExtension == 'mp4' || iconExtension == 'mp3') { 
                  iconExtension = 'play-media.png'; 
                } else {
                  iconExtension = iconExtension+'.svg';
                }
                aaochatMessage = `<a href="${aaoChatFileServerURL+"/download/attachment/"+attachments[0].file_id}?download=true&authKey=${authKey}" download class="chat-attachment-box" >
                  <div class="file-icon-container">
                    <img src="${aaoChatServerURL}/public/images/${iconExtension}" alt="">
                  </div>
                  ${attachments[0].display_name}
                  </a>`;
              }
            } else {
              aaochatMessage = '<pre>'+aaochatMessage+'</pre>';
              aaochatMessage = aaochatMessage.replace(
                    /((http|https|ftp|ftps):\/\/[\w?=&.\/-;#~%-]+(?![\w\s?&.\/;#~%"=-]*>))/g,
                    '<a target="_blank" href="$1">$1</a>'
                );
              }
          }

          var menuHtml = `<div  class="pull-right aaocchat-context-menu">
            <button class="aaocchat-context-menu-btn " >&nbsp;</button>
            </div>      
            <ul class="aaocchat-dropdown-menu-left-side-open" style="">
        
              <li class="replyMessageAction" data-msgid="${responseMessage.message_id}" style="display:${responseMessage.is_splash!=true?'block':'none'}">
                <a href="javascript::void()" >Reply </a>
              </li>

              <li class="replyPrivatelyAction" data-msgid="${responseMessage.message_id}" style="display:${responseMessage.is_reminder!=true && chatUser.user_name != u.uid && responseMessage.is_splash!=true && self.aaochtChannel.private_mode!=true && self.aaochtChannel.channel_type==1?'block':'none'}">
                <a href="javascript::void()"  >Reply Privately</a>
              </li>

              <li class="forwardMessageAction" data-msgid="${responseMessage.message_id}" style="display:${responseMessage.is_reminder!=true && responseMessage.is_splash!=true?'block':'none'}">
                <a href="javascript::void()" >Forward </a>
              </li>

              <li class="starMessageWithTagAction" data-msgid="${responseMessage.message_id}" style="display:${responseMessage.is_reminder!=true && responseMessage.is_splash!=true?'block':'none'}">
                <a href="javascript::void()" >Label </a>
              </li>

              <li class="copyMessageAction" data-msgid="${responseMessage.message_id}" style="display:${responseMessage.is_reminder!=true?'block':'none'}">
                <a href="javascript::void()" > Copy Text </a>
              </li>

              <li class="downloadAttachmentAction" data-msgid="${responseMessage.message_id}" style="display:${responseMessage.is_reminder!=true && responseMessage.attachments.length>0?'block':'none'}">
                <a href="javascript::void()" > Download Attachment </a>
              </li>

              <li class="viewMessageStatisticAction" data-msgid="${responseMessage.message_id}" style="display:${responseMessage.is_reminder!=true && self.aaochtChannel.channel_type==1 && chatUser.user_name==u.uid?'block':'none'}" >
                <a href="javascript::void()" >Message Info </a>
              </li>

              <li style="display:${responseMessage.is_reminder!=true?'block':'none'}" class="divider"></li>

              <li class="setReminderAction" data-msgid="${responseMessage.message_id}" style="display:${responseMessage.is_reminder!=true && responseMessage.is_splash!=true?'block':'none'}">
                <a href="javascript::void()" >Snooze</a>
              </li>

              <li class="deleteMessageAction" data-msgid="${responseMessage.message_id}" style="display:${responseMessage.is_reminder!=true && responseMessage.is_splash!=true && (chatUser.user_name==u.uid || self.aaochtChannel.admins.includes(self.aaochtRootUserId))?'block':'none'}">
                <a href="javascript::void()" >Delete For Everyone</a>
              </li>

              <li class="deleteMessageForMeAction" data-msgid="${responseMessage.message_id}" style="display:${responseMessage.is_splash!=true?'block':'none'}">
                <a href="javascript::void()" >Delete For Me</a>
              </li>
            </ul>`;
          
            if(messageType == 6) {
            } else {
              messsageDateExists = -1;
              if(self.aaochtMessagesDates.length > 0) {
                messsageDateExists = self.aaochtMessagesDates.findIndex(function(aaochtMessageDate){
                  return aaochtMessageDate == formatedLogDate;
                });
              }
              if(messsageDateExists == -1) {
                self.displayAaochtMessageDate = self.previousAaochtMessageDate;
                
                self.aaochtMessagesDates.push(formatedLogDate);
              }
              self.previousAaochtMessageDate = formatedLogDate;
              
              //console.log(self.displayAaochtMessageDate);
              //console.log(responseMessage.message_id);
              //console.log(self.aaochtMessagesDates);
                
              if(self.displayAaochtMessageDate != '') {
                aaochatMessageHtml += '<div class="notification-message alert alert-info">'+self.displayAaochtMessageDate+'</div>';  
              }
            }

          if(messageType == 6) {
            //Dont's show notification messages
            /*aaochatMessageHtml += `<div class="notification-message alert alert-warning">
              <span >${aaochatMessage}</span>
            </div>`;*/
          } else {

          //if(aaochatMessage != '') {
              if(chatUser.user_name == u.uid) {
                if(trashed == true) {
                  aaochatMessageHtml += '<div id="message-'+aaochatMessageId+'" class="responses-chat trashed aaochat-msg-block">';
                    aaochatMessageHtml += '<div class="response">';
                      aaochatMessageHtml += '<span class="text">This message is deleted.</span>';
                    aaochatMessageHtml += '</div>';                        
                  aaochatMessageHtml += '</div>';
                } else if(typeof replyMessage != 'undefined' && typeof replyMessage != 'null') {
                  aaochatMessageHtml += '<div id="message-'+aaochatMessageId+'" class="responses-chat aaochat-msg-block">';
                  if(responseMessage.is_private) {  
                    aaochatMessageHtml += '<div class="a-message private">';
                  } else {
                    aaochatMessageHtml += '<div class="a-message">';
                  }
                    //aaochatMessageHtml += menuHtml;
                    if(responseMessage.is_private) {
                      aaochatMessageHtml += '<div class="a-message-top">';
                      aaochatMessageHtml += '<span >Private</span>';
                      aaochatMessageHtml += '</div>';
                    }
                      aaochatMessageHtml += '<a href="#message-'+replyMessage.message_id+'">';
                        aaochatMessageHtml += '<div id="reply-'+replyMessage.message_id+'" data-time="'+replyMessage.timestamp+'" class="reply-message">';
                        if(u.uid != replyMessage.user.user_name) {
                          aaochatMessageHtml += '<div class="message-data" >';
                            aaochatMessageHtml += '<span class="message-data-name">'+replyMessage.user.full_name+'</span>';
                          aaochatMessageHtml += '</div>';
                        }
                          aaochatMessageHtml += '<pre class="text">'+replyOriginalMessage+'</pre>';
                        aaochatMessageHtml += '</div>';
                      aaochatMessageHtml += '</a>';
                      aaochatMessageHtml += '<div class="response">';
                        aaochatMessageHtml += '<span class="text">'+aaochatMessage+'</span>';
                      aaochatMessageHtml += '</div>';
                      aaochatMessageHtml += '<p class="response-time time">'+formatedChatDate+'</p>';
                      aaochatMessageHtml += '<small ><i class="'+msgSeenClass+'"></i></small>';
                    aaochatMessageHtml += '</div>';
                  aaochatMessageHtml += '</div>';
                }  else {
                  aaochatMessageHtml += '<div id="message-'+aaochatMessageId+'" class="responses-chat aaochat-msg-block">';
                  if(responseMessage.is_private) {  
                    aaochatMessageHtml += '<div class="a-message private">';
                  } else {
                    aaochatMessageHtml += '<div class="a-message">';
                  }
                    //aaochatMessageHtml += menuHtml;
                    if(responseMessage.is_private) {
                      aaochatMessageHtml += '<div class="a-message-top">';
                      aaochatMessageHtml += '<span >Private</span>';
                      aaochatMessageHtml += '</div>';
                    }
                      aaochatMessageHtml += '<div class="response">';
                        aaochatMessageHtml += '<span class="text">'+aaochatMessage+'</span>';
                      aaochatMessageHtml += '</div>';
                      aaochatMessageHtml += '<p class="response-time time">'+formatedChatDate+'</p>';
                      aaochatMessageHtml += '<small ><i class="'+msgSeenClass+'"></i></small>';
                    aaochatMessageHtml += '</div>';
                  aaochatMessageHtml += '</div>';
                }
              } else {
                if(trashed == false) {
                  if(!responseMessage.seen) {
                    self._wsSend('42' + JSON.stringify(['user:message-seen', {
                      message_id: responseMessage.message_id,
                      user_id: responseMessage.user_id,
                      channel_id: responseMessage.channel_id,
                      is_splash: responseMessage.is_splash                 
                    }]),function () {});
                  }

                  /*self.WebSocketObj.send('42' + JSON.stringify(['user:message-seen', {                  
                          message_id: responseMessage.message_id,
                          user_id: responseMessage.user_id,
                          channel_id: responseMessage.channel_id,
                          is_splash: responseMessage.is_splash                 
                  }]));*/
                }

                if(trashed == true) {
                  aaochatMessageHtml += '<div id="message-'+aaochatMessageId+'" class="messages-chat trashed aaochat-msg-block">';
                    aaochatMessageHtml += '<div class="message">';
                      aaochatMessageHtml += '<span class="text">This message is deleted.</span>';
                    aaochatMessageHtml += '</div>';                        
                  aaochatMessageHtml += '</div>';
                } else if(typeof replyMessage != 'undefined' && typeof replyMessage != 'null') {
                  //console.log('other user reply message');
                  aaochatMessageHtml += '<div id="message-'+aaochatMessageId+'" class="messages-chat aaochat-msg-block">';
                  if(responseMessage.is_private) {  
                    aaochatMessageHtml += '<div class="a-message private">';
                  } else {
                    aaochatMessageHtml += '<div class="a-message">';
                  }
                    //aaochatMessageHtml += menuHtml;

                    if(responseMessage.is_private) {
                      aaochatMessageHtml += '<div class="a-message-top">';
                      aaochatMessageHtml += '<span >Private</span>';
                      aaochatMessageHtml += '</div>';
                    }

                      aaochatMessageHtml += '<div class="message-data" >';
                        aaochatMessageHtml += '<span class="message-data-name">'+chatUser.full_name+'</span>';
                      aaochatMessageHtml += '</div>';
                      aaochatMessageHtml += '<a href="#message-'+replyMessage.message_id+'">';
                        aaochatMessageHtml += '<div id="reply-'+replyMessage.message_id+'" data-time="'+replyMessage.timestamp+'" class="reply-message">';
                        
                        if(u.uid != replyMessage.user.user_name) {
                          aaochatMessageHtml += '<div class="message-data" >';
                            aaochatMessageHtml += '<span class="message-data-name">'+replyMessage.user.full_name+'</span>';
                          aaochatMessageHtml += '</div>';
                        }

                          aaochatMessageHtml += '<pre class="text">'+replyOriginalMessage+'</pre>';
                        aaochatMessageHtml += '</div>';
                      aaochatMessageHtml += '</a>';
                        aaochatMessageHtml += '<div class="message">';
                          aaochatMessageHtml += '<span class="text">'+aaochatMessage+'</span>';
                        aaochatMessageHtml += '</div>';
                      aaochatMessageHtml += '<p class="time">'+formatedChatDate+'</p>';
                      //aaochatMessageHtml += '<small ><i class="'+msgSeenClass+'"></i></small>';
                    aaochatMessageHtml += '</div>';
                  aaochatMessageHtml += '</div>';
                } else {
                  aaochatMessageHtml += '<div id="message-'+aaochatMessageId+'" class="messages-chat aaochat-msg-block">';
                  if(responseMessage.is_private) {  
                    aaochatMessageHtml += '<div class="a-message private">';
                  } else {
                    aaochatMessageHtml += '<div class="a-message">';
                  }
                    //aaochatMessageHtml += menuHtml;

                    if(responseMessage.is_private) {
                      aaochatMessageHtml += '<div class="a-message-top">';
                      aaochatMessageHtml += '<span >Private</span>';
                      aaochatMessageHtml += '</div>';
                    }
                      aaochatMessageHtml += '<div class="message-data" >';
                        aaochatMessageHtml += '<span class="message-data-name">'+chatUser.full_name+'</span>';
                      aaochatMessageHtml += '</div>';
                      aaochatMessageHtml += '<div class="message">';
                        aaochatMessageHtml += '<span class="text">'+aaochatMessage+'</span>';
                      aaochatMessageHtml += '</div>';
                      aaochatMessageHtml += '<p class="time">'+formatedChatDate+'</p>';
                      //aaochatMessageHtml += '<small ><i class="'+msgSeenClass+'"></i></small>';
                    aaochatMessageHtml += '</div>';
                  aaochatMessageHtml += '</div>';
                }

              }
          //}

            //console.log('aaochtChannelMessageCount:'+self.aaochtChannelMessageCount);
            //console.log('aaochtChannelMessageCounter:'+self.aaochtChannelMessageCounter);
            if(self.aaochtChannelMessageCount == self.aaochtChannelMessageCounter) {
              //console.log('same counter ===');
              //self.displayAaochtMessageDate = self.previousAaochtMessageDate;
              //if(self.displayAaochtMessageDate != '') {
                aaochatMessageHtml += '<div class="notification-message alert alert-info">'+self.previousAaochtMessageDate+'</div>';  
              //}
            }

          }
          return aaochatMessageHtml;
        };
        AaochatSidebarView.openOptionSendMenu= (e) => {
          e.preventDefault();
          var currentElement = e.target;
          if(!$(currentElement).hasClass('open-message-send-option-btn')) {
            currentElement = $(currentElement).parent();
          }
          self.chatMessageMenuIsOpen = true;
          $(".newMessageOptionsContainer .schedule-msg-option-btn").show();
          //$(".newMessageOptionsContainer .send-privately-option-btn").show();
          $(".newMessageOptionsContainer .splash-option-btn").show();
          //$(".newMessageOptionsContainer .gif-option-btn").show();
          //$(".newMessageOptionsContainer .emoji-option-btn").show();

          $(currentElement).removeClass('open-message-send-option-btn');
          $(currentElement).addClass('close-message-send-option-btn');
          $(currentElement).find('i').removeClass('aaochat-fa-plus');
          $(currentElement).find('i').addClass('aaochat-fa-chevron-right');
        };
        AaochatSidebarView.closeOptionSendMenu= (e) => {
          e.preventDefault();
          var currentElement = e.target;
          if(!$(currentElement).hasClass('close-message-send-option-btn')) {
            currentElement = $(currentElement).parent();
          }
          self.chatMessageMenuIsOpen = false;       
          $(".newMessageOptionsContainer .schedule-msg-option-btn").hide();
          //$(".newMessageOptionsContainer .send-privately-option-btn").hide();
          $(".newMessageOptionsContainer .splash-option-btn").hide();
          //$(".newMessageOptionsContainer .gif-option-btn").hide();
          //$(".newMessageOptionsContainer .emoji-option-btn").hide();

          $(currentElement).removeClass('close-message-send-option-btn');
          $(currentElement).addClass('open-message-send-option-btn');
          $(currentElement).find('i').removeClass('aaochat-fa-chevron-right');
          $(currentElement).find('i').addClass('aaochat-fa-plus');
        };
        /**
         * stroll to reply message
         * currently not in use
         */
         AaochatSidebarView.scrollToReply= (e) => {
          //e.preventDefault();
          //e.stopPropagation();
          

          var self = AaochatSidebarView;
          var currentElement = e.target;
          if($(currentElement).hasClass('message-data')) {
            currentElement = $(currentElement).parent();
          } else if($(currentElement).hasClass('text')) {
            currentElement = $(currentElement).parent();
          }

          var element_id = $(currentElement).attr('id');
          var msg_timestamp = $(currentElement).data('time');
          var message_id = element_id.replace('reply-','');
          var containerTop = $('#chatMessageListContainer').offset().top;
          var containerHeight = $('#chatMessageListContainer').outerHeight();


          //$("#message-"+message_id).offset().top
          //console.log($("#message-"+message_id).length);
          if($("#message-"+message_id).length == 0) {
            console.log(message_id);
            console.log(msg_timestamp);
            console.log('reply clicked on older message');

            self.getChannelMessagesByTimestamp(msg_timestamp, message_id);

            /*
            if($("#message-"+message_id).length > 0) {
              console.log('scroll to original message');
              $('#chatMessageListContainer').animate({
                scrollTop: $("#message-"+message_id).offset().top
              }, 500);
            }
            */
            /*if($("#message-"+message_id).length > 0) {
              var destination = $("#message-"+message_id);
              console.log(destination);
              destination[0].scrollIntoView({
                behavior: 'smooth'
              });
            }*/
          } else {
            /*var destination = $("#message-"+message_id);
            console.log(destination);
            destination[0].scrollIntoView({
              behavior: 'smooth'
            });*/
          }
        };
        /**
         * reply message
         */
         AaochatSidebarView.replyMessage= (e) => {
          e.preventDefault();
          var self = AaochatSidebarView;
          var currentElement = e.target;
          var message_id = $(currentElement).parent().data('msgid');

          var currentMessage = null;
          if(Object.keys(self.aaochtMessages.messages).length > 0) {
            currentMessage = self.aaochtMessages.messages.find(function(singleMessage){
              return singleMessage.message_id == message_id;
            });
          }
          if(currentMessage) {

          }
        };
        /**
         * reply message
         */
         AaochatSidebarView.replyPrivatelyAction= (e) => {
          e.preventDefault();
          var self = AaochatSidebarView;
          var currentElement = e.target;
          var message_id = $(currentElement).parent().data('msgid');

          var currentMessage = null;
          if(Object.keys(self.aaochtMessages.messages).length > 0) {
            currentMessage = self.aaochtMessages.messages.find(function(singleMessage){
              return singleMessage.message_id == message_id;
            });
          }
          if(currentMessage) {

          }

        };
        /**
         * reply message
         */
         AaochatSidebarView.forwardMessage= (e) => {
          e.preventDefault();
          var self = AaochatSidebarView;
          var currentElement = e.target;
          var message_id = $(currentElement).parent().data('msgid');
          
          var currentMessage = null;
          if(Object.keys(self.aaochtMessages.messages).length > 0) {
            currentMessage = self.aaochtMessages.messages.find(function(singleMessage){
              return singleMessage.message_id == message_id;
            });
          }
          if(currentMessage) {

          }

        };
        /**
         * reply message
         */
         AaochatSidebarView.starMessageWithTag= (e) => {
          e.preventDefault();
          var self = AaochatSidebarView;
          var currentElement = e.target;
          var message_id = $(currentElement).parent().data('msgid');
          
          var currentMessage = null;
          if(Object.keys(self.aaochtMessages.messages).length > 0) {
            currentMessage = self.aaochtMessages.messages.find(function(singleMessage){
              return singleMessage.message_id == message_id;
            });
          }
          if(currentMessage) {

          }

        };
        /**
         * reply message
         */
         AaochatSidebarView.copyMessage= (e) => {
          e.preventDefault();
          var self = AaochatSidebarView;
          var currentElement = e.target;
          var message_id = $(currentElement).parent().data('msgid');

          var currentMessage = null;
          if(Object.keys(self.aaochtMessages.messages).length > 0) {
            currentMessage = self.aaochtMessages.messages.find(function(singleMessage){
              return singleMessage.message_id == message_id;
            });
          }
          if(currentMessage) {

          }

        };
        /**
         * reply message
         */
         AaochatSidebarView.downloadAttachment= (e) => {
          e.preventDefault();
          var self = AaochatSidebarView;
          var currentElement = e.target;
          var message_id = $(currentElement).parent().data('msgid');

          var currentMessage = null;
          if(Object.keys(self.aaochtMessages.messages).length > 0) {
            currentMessage = self.aaochtMessages.messages.find(function(singleMessage){
              return singleMessage.message_id == message_id;
            });
          }
          if(currentMessage) {

          }

        };
        /**
         * reply message
         */
         AaochatSidebarView.viewMessageStatistic= (e) => {
          e.preventDefault();
          var self = AaochatSidebarView;
          var currentElement = e.target;
          var message_id = $(currentElement).parent().data('msgid');

          var currentMessage = null;
          if(Object.keys(self.aaochtMessages.messages).length > 0) {
            currentMessage = self.aaochtMessages.messages.find(function(singleMessage){
              return singleMessage.message_id == message_id;
            });
          }
          if(currentMessage) {

          }

        };
        /**
         * reply message
         */
         AaochatSidebarView.setReminder= (e) => {
          e.preventDefault();
          var self = AaochatSidebarView;
          var currentElement = e.target;
          var message_id = $(currentElement).parent().data('msgid');

          var currentMessage = null;
          if(Object.keys(self.aaochtMessages.messages).length > 0) {
            currentMessage = self.aaochtMessages.messages.find(function(singleMessage){
              return singleMessage.message_id == message_id;
            });
          }
          if(currentMessage) {

          }

        };
        /**
         * reply message
         */
         AaochatSidebarView.deleteMessage= (e) => {
          e.preventDefault();
          var self = AaochatSidebarView;
          var currentElement = e.target;
          var message_id = $(currentElement).parent().data('msgid');

        };
        /**
         * reply message
         */
         AaochatSidebarView.deleteMessageForMe= (e) => {
          e.preventDefault();
          var self = AaochatSidebarView;
          var currentElement = e.target;
          var message_id = $(currentElement).parent().data('msgid');

        }
      
      OCA.Aaochattab = OCA.Aaochattab || {};
      OCA.Aaochattab.AaochatSidebarView = AaochatSidebarView;
    
      console.log(AaochatSidebarView);

    aaochatTabPluginLoaded = true;
    clearInterval(window.aaochatTabPluginInterval);
    console.log('aaochat-tabview loader interval cleared');
  }else{
    if(aaochatTabPluginLoaded == true) {
      clearInterval(window.aaochatTabPluginInterval);
      console.log('aaochat-tabview file object not found',aaochatTabPluginLoaded, OCA.Files);
    }
  }

}
console.log('aaochat-tabview called function for first time');
aaochatTabPlugin();
 
window.aaochatTabPluginInterval = setInterval(aaochatTabPlugin,2000);

window.isLoadedAaochatPlugin = false;
setTimeout(function(){
  if(OCA.Files && OCA.Files.Sidebar && window.isLoadedAaochatPlugin == false){
    window.isLoadedAaochatPlugin = true;
    OCA.Files.Sidebar.registerTab(OCA.Aaochattab.AaochatSidebarView)
  }
},1000);

});
