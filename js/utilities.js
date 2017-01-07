/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
'use strict';

var Utilities=function(){
    return {
        'checkRedirect': function(json){
            if(json.redirect!==undefined){
                location.href=json.redirect;
            }
        }
    };
};

jQuery.validator.addMethod("pattern", function(value, element, param) {
    if (this.optional(element)) {
        return true;
    }
    if (typeof param === 'string') {
        param = new RegExp('^(?:' + param + ')$');
    }
    return param.test(value);
}, "Este campo es incorrecto.");

jQuery.validator.addMethod('selectcheck', function (value) {
    return (value != '0');
}, "Seleccione una opción válida.");

jQuery.validator.addMethod("securePassword", function(value, element) {
    return this.optional(element) || /(?=^.{8,}$)(?=.*\d)(?=.*[\.,\-_!@#$%^&*]+)(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/.test(value);
}, "Por favor, escriba una contraseña segura.");

jQuery.validator.addMethod("distinctTo", function(value, element, param){
    var target = $( param );
    if ( this.settings.onfocusout ) {
        target.off( ".validate-equalTo" ).on( "blur.validate-equalTo", function() {
            $( element ).valid();
        });
    }
    return value !== target.val();
}, "Por favor, no puede repetir el mismo valor.");