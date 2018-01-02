//copy this file for every action
(function(window, $){
    'use strict';
    var document = window.document;
    
    var action = function(el){
        //include any helpful variables here
        
        //call the init to get things started
        this.init();
    };
    
    action.prototype.init = function(){
        //this kicks off the function
    };
    
    action.prototype.helper = function(){
        //This is an example of something helpful for the function
    };
    
    $(document).ready(function(){
        //searches the DOM and constructs for all elements with this function
        $('element').each(function(){
            new action(this); 
        });
    });
})(this, jQuery);