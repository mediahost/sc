
// Gallery plugin
var Gallery = function () {
    var initialized = false;
    
    
    
    var closeJobs = function() {
        $('.job').removeClass('droppable');
        $('.job .gallery-item').each(function(index, item) {
            var el = $(item);
            match(el, false);
        });
    };
    
    //checked true - cv is matching
    var match = function (element, checked) {
        if (checked) {
            element.addClass('matched').appendTo('.droppable section').animate({left: '0px', top: '0px'});
        } else {
            element.removeClass('matched').addClass('col-sm-4 col-xs-6').appendTo('.gallery');
        }
        element.find('input[type="checkbox"]').attr('checked', checked);
        
    };
    
    var invite = function() {
        var cvIds = [];
        var jobObject = $('.droppable');
        jobObject.find('.matched').each(function(index, item) {
            cvIds[cvIds.length] = $(item).attr('data-cv');
        });
        var url = jobObject.attr('data-action');
        url = url.replace('__CVS__', cvIds.toString());
        closeJobs();
        $.nette.ajax({url: url});
    };
    
    var loadJobCvs = function() {
        var jobObject = $('.droppable');
        var cvs = jobObject.attr('data-cvs').split(',');
        $.each(cvs, function(index, cv) {
            var cvId = cv.split('|')[0];
            var el = $('.gallery-item[data-cv="' + cvId + '"]');
            match(el, true);
        });
    };

    return {
        init: function () {
            if (this.initialized) {
                return;
            }
            this.initialized = true;
            
            $('.draggable').draggable({
                revert: 'invalid'
            });

            $(document).on('click', '.job', function () {
                closeJobs();
                if ($(this).hasClass('job')) {
                    $(this).addClass('droppable');
                    $('.droppable').droppable({
                        drop: function (event, ui) {
                            match(ui.draggable, true);
                            invite();
                        }
                    });
                    loadJobCvs();
                }
            });

            $(document).on('click', '.gallery-item input[type="checkbox"]', function (e) {
                if ($('.gallery-nav').find('.droppable').length) {
                    match($(this).closest('.draggable'), this.checked);
                    invite();
                } else {
                    return false;
                }
                e.stopPropagation();
            });
        }
    };
}();

