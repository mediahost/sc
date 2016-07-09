
// Gallery plugin
var Gallery = function () {
    //checked true - cv is matching
    var match = function (element, checked) {
        if (checked) {
            element.addClass('matched').appendTo('.droppable section').animate({left: '0px', top: '0px'});
            
        } else {
            element.removeClass('matched').addClass('col-sm-4 col-xs-6').appendTo('.gallery');
        }
        element.find('input[type="checkbox"]').attr('checked', checked);
        invite();
    };
    
    var invite = function() {
        var cvIds = [];
        var jobObject = $('.droppable');
        jobObject.find('.matched').each(function(index, item) {
            cvIds[cvIds.length] = $(item).attr('data-cv');
        });
        var url = jobObject.attr('data-action');
        url = url.replace('__CVS__', cvIds.toString());
        $.post(url);
    };

    return {
        init: function () {
            $('.draggable').draggable({
                revert: 'invalid'
            });

            $(document).on('click', '.job', function () {
                $('.job').removeClass('droppable');
                $(this).addClass('droppable');
                $('.droppable').droppable({
                    drop: function (event, ui) {
                        match(ui.draggable, true);
                    }
                });
            });

            $(document).on('click', '.gallery-item input[type="checkbox"]', function (e) {
                if ($('.gallery-nav').find('.droppable').length) {
                    match($(this).closest('.draggable'), this.checked);
                } else {
                    return false;
                }
            });
        }
    };
}();

