'use strict';
(function(){
    var logModule = (function(){
        return {
            'init': function(){
                $("#menu_logs li").hover(function (inHover) {
                    var liClass = $(this).attr("class");
                    var id = $(this).attr("id").split("li");
                    if (inHover.type !== "mouseleave") {
                        if (liClass !== "active") {
                            $("#img_ico_" + id[1]).addClass("hidden");
                            $("#img_ico_" + id[1] + "_blank").removeClass("hidden");
                        }
                    }
                    if (inHover.type === "mouseleave") {
                        if (liClass !== "active") {
                            $("#img_ico_" + id[1]).removeClass("hidden");
                            $("#img_ico_" + id[1] + "_blank").addClass("hidden");
                        }
                    }
                });
            },
        };
    }());
    logModule.init();
}());