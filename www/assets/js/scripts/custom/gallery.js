
// Gallery plugin
var Gallery = function () {
    //checked true - cv is matching
    var match = function (element, checked) {
        if (checked) {
                    //var checkbox = $('<small></small>').html($('.checkbox-custom').html());
            //$('.checkbox-custom').html(checkbox);
            element.addClass('matched')
               // .removeClass('col-sm-4 col-xs-6')
                .appendTo('.droppable section').animate({left: '0px', top: '0px'});
           // $('.gallery-item input[type="checkbox"]').animate({width: '30%'})
            
        } else {
            element.removeClass('matched')
                .addClass('col-sm-4 col-xs-6')
                .appendTo('.gallery');
        }
    };

    return {
        init: function () {
            $('.draggable').draggable({
                revert: 'invalid'
            });

            $('.job').on('click', function () {
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

