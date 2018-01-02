//instagram slider - this program will be the basis for other slider programs

(function($, window, document){
 
    $.pluginName = function(element, UserOptions){
        
        var plugin = this;
        
        var $element = $(element),
             element = element;        
        
        plugin.options = $.extend( {}, $.fn.pluginName.options, UserOptions);        
        
        plugin.controller = function(){
            //this is the initializer  
        };
        
        
         // Public Method code
        plugin.foo_public_method = function() {

        };
        
        // Model code
        var model_private_method = function() {

        }; 
        
        // Helper method 
        var helper_private_method = function() {

        };
        
        // View method 
        var views_private_method = function() {

        };        
        
        //call the plugin
        plugin.controller();
        
    };
    
    $.fn.PluginName = function(UserOptions){
        return this.each(function(){
            if (undefined == $(this).data('pluginName')) {
                var newPlugin = new $.pluginName(this, UserOptions);
                    newPlugin.controller();    
                
                    $(this).data('pluginName', newPlugin);
            }
        });
    };
    
    
    $.fn.PluginName.options = {
        
    };
    
})(jQuery, window, document);