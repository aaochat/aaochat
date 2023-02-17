(function() {

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
      if (fileList.id === 'trashbin' || fileList.id === 'files.public') {
        return;
      }

      
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

      }, 500);

    }
  };
})();

OC.Plugins.register('OCA.Files.FileList', OCA.Aaochattab.Util);

