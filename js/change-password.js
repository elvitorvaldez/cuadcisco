/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
(function(){
    var changePasswordModule=(function(){
        var utilities = Utilities();
        return {
            'init': function(){
                this.validate();
            },
            'validate': function(){
                var form=$("#form-change-password");
                form.validate({
                    'rules': {
                        'oldPassword' : {'required': true, 'minlength': 8, 'maxlength': 45},
                        'newPassword' : {'required': true, 'minlength': 8, 'maxlength': 45, 'distinctTo': '#oldPassword', 'securePassword': true},
                        'confirmPassword' : {'required': "#newPassword", 'equalTo' : "#newPassword"},
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
                                $("#ark_loader-change-pass").removeClass("hidden");
                                $("#submit").addClass("disabled");
                                $("#submit").attr("disabled","disabled");
                                $(form).find(".form-group .error").empty();
                            },
                            'complete': function(){
                                $("#ark_loader-change-pass").addClass("hidden");
                                $("#submit").removeClass("disabled");
                                $("#submit").removeAttr("disabled");  
                            },
                            'error': function(error) {
                                alertify.error('Error Inesperado');
                            },
                            'success':function(data){
                                utilities.checkRedirect(data);
                                if (data.success === true) {                                    
                                    $(form).remove();
                                    $("#pass-changed").find("h3 label").text(data.userId);
                                    $("#pass-changed").removeClass("hidden");
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
    changePasswordModule.init();
}(this));
