var Background = function() {

    var initBackground = function() {
        $.backstretch([
            basePath + "images/bg/1.jpg",
            basePath + "images/bg/2.jpg",
            basePath + "images/bg/3.jpg",
            basePath + "images/bg/4.jpg"
        ], {
            fade: 1000,
            duration: 10000
        });
    };

    return {
        //main function to initiate the module
        init: function() {

            if ($("body").hasClass("deny") || $("body").hasClass("login")) {
                initBackground();
            }

        }

    };

}();