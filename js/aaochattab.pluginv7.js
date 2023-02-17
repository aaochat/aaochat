$(document).ready(function () {

  window.isLoadedAaochatPlugin = false;


  OCA.Aaochattab = OCA.Aaochattab || {};

  /**
   * @namespace
   */
  OCA.Aaochattab.Util = {

    /**
     * Initialize the Printer plugin.
     *
     * @param {OCA.Files.FileList} fileList file list to be extended
     */
    attach: function(fileList) {
 
      var newTabs = [];
      var detailTabs = OCA.Files.Sidebar.state.tabs;
      
      setTimeout(function(){
        /*
        $(detailTabs).each(function( index, detailTab ) {
          if(detailTab.id != 'versionsTabView') {
            newTabs.push(detailTab);
          }     
        });

        OCA.Files.Sidebar.state.tabs = newTabs;
        */
        console.log('aaochat-tab registerTabView called');
        fileList.registerTabView(new OCA.Aaochattab.AaochatTabView('AaochatTabView', {order:-51}));
        window.isLoadedAaochatPlugin =  true;

      }, 500);

    }
  };


  setInterval(function(){
    if( window.isLoadedAaochatPlugin==false){
      console.log(OC.Plugins._plugins);
      OC.Plugins.register('OCA.Files.Aaochat', OCA.Aaochattab.Util);
    }
  },500);

});

// OC.Plugins.register('OCA.Files.Aaochat', OCA.Aaochattab.Util)