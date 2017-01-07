/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
'use strict';
(function(){
    var resetPasswordModule=(function(){
        return {
            'init': function(){
                this.validate();
            },
            'validate': function(){
                var form=$("#form-reset-password");
                form.validate({
                    'rules': {
                        'userId' : {'required': true, 'maxlength': 45},
                    },
                    'errorPlacement': function ($error, $element) {
                        var name = $element.attr("name");
                        $("#error" + name).append($error);
                    },
                    "submitHandler": function(form){
                        $.ajax({
                            type: "POST",
                            url: $(form).attr('action'),
                            data: $(form).serialize(),
                            'beforeSend': function(){   
                                $("#ark_loader-reset-pass").removeClass("hidden");
                                $("#submit").addClass("disabled");
                                $("#submit").attr("disabled","disabled");
                                $(form).find(".form-group .error").empty();
                            },
                            'complete': function(){
                                $("#ark_loader-reset-pass").addClass("hidden");
                                $("#submit").removeClass("disabled");
                                $("#submit").removeAttr("disabled");  
                            },
                            'error': function(error) {
                                alertify.error('Error Inesperado');
                            },
                            'success':function(data){                                
                                if (data.success === 1) {
                                    alertify.notify(data.message, 'success', 3, function(){  location.href = data.url; });
                                } else {
                                    alertify.error(data.message);
                                    if(data.validation!==undefined){
                                        $.each(data.validation, function(index, value){
                                            $("#error"+value.name).html('<label id="'+value.name+'-error" class="'+value.name+'" for="'+value.name+'">'+value.message+'</label>');
                                        });
                                    }
                                }
                            }
                        });
                    }
                });
            }
        }
    })();
    resetPasswordModule.init();
}(this));
