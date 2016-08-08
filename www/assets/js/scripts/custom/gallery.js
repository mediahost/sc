
// Gallery plugin
var Gallery = function() {
    
    var closeJobs = function() {
        if ($( ".droppable" ).hasClass('ui-droppable')) {
            $( ".droppable" ).droppable('destroy');
        }
        $('.job').removeClass('droppable');
    };
    
    var openJob = function(job) {
        if ($(job).hasClass('job')) {
            $(job).addClass('droppable');
            
            $('.droppable').droppable({
                drop: function (event, ui) {
                    invite(ui.draggable, true);
                }
            });
        }
    };
    
    var invite = function(element, checked) {
        var url = $('.droppable').attr('data-action');
        url = url.replace('__CVS__', $(element).attr('data-cv')).replace('__MATCHED__', checked);
        $.nette.ajax({
            url: url
        });
    };
    
    
    return {
        init: function() {
            $(document).on('click', '.job', function () {
                closeJobs();
                openJob(this);
            });
            
            $(document).on('click', '.gallery-item input[type="checkbox"], .droppable input[type="checkbox"]', function (e) {
                if ($('.gallery-nav').find('.droppable').length) {
                    var checked = $(this).attr('checked') ? 1 : 0;
                    invite($(this).closest('.gallery-item, .matched'), checked);
                } 
                return false;
                e.stopPropagation();
            });
            
            $('.draggable').draggable({
                revert: 'invalid',
                helper: 'clone',
            });
        }
    };
}();




var Gallery1 = function () {
    var initialized = false;
    
    var updateLabel = function(jobObject) {
        var cvs = jobObject.attr('data-cvs').split(',');
        var labelObj = jobObject.find('[data-label]');
        var label = cvs.length + labelObj.attr('data-label');
        labelObj.html(label);
    };
    
    var closeJobs = function() {
        $( ".droppable" ).droppable('destroy');
        $('.job').removeClass('droppable');
        $('.job .gallery-item').each(function(index, item) {
            var el = $(item);
            match(el, false);
        });
    };
    
    var openJob = function(job) {
        if ($(job).hasClass('job')) {
            $(job).addClass('droppable');
            
            $('.droppable').droppable({
                drop: function (event, ui) {
                    match(ui.draggable.clone(), true);
                    invite();
                }
            });
            //loadJobCvs();
        }
    };
    
    var loadJobCvs = function(cvs) {
        var jobObject = $('.droppable');
        var cvs = jobObject.attr('data-cvs').split(',');
        $.each(cvs, function(index, cv) {
            var cvId = cv.split('|')[0];
            var el = $('.gallery-item[data-cv="' + cvId + '"]');
            match(el.clone(), true);
        });
    };
    
    //checked true - cv is matching
    var match = function (element, checked) {
        if (checked  &&  !isMatched(element)) {
            element.addClass('matched').appendTo('.droppable section').animate({left: '0px', top: '0px'});
        } 
        if (!checked  &&  !isInGallery(element)) {
            element.removeClass('matched').addClass('col-lg-4 col-sm-6 col-xs-12');
            $('.gallery').append(element);
        }
        element.find('input[type="checkbox"]').attr('checked', checked);
    };
    
    var isInGallery = function (element) {
        $('.gallery').find('.gallery-item').each(function(index, item) {
            if ($(item).attr('data-cv') === element.attr('data-cv')) {
                return true;
            }
        });
        return false;
    };
    
    var isMatched = function(element) {
        $('.droppable section').find('.gallery-item').each(function(index, item) {
            alert($(item).attr('data-cv'))
            if ($(item).attr('data-cv') === element.attr('data-cv')) {
                return true;
            }
        });
        return false;
    };
    
    var invite = function() {
        var cvIds = [];
        var jobObject = $('.droppable');
        jobObject.find('.matched').each(function(index, item) {
            cvIds[cvIds.length] = $(item).attr('data-cv');
        });
        var url = jobObject.attr('data-action');
        url = url.replace('__CVS__', cvIds.toString());
        $.nette.ajax({
            url: url,
            success: function(payload) {
                jobObject.attr('data-cvs', payload.cvs);
                updateLabel(jobObject);
            }
        });
    };
    

    return {
        init: function () {
            if (this.initialized) {
                return;
            }
            this.initialized = true;
            
            $('.draggable').draggable({
                revert: 'invalid',
                helper: 'clone',
            });

            $(document).on('click', '.job', function () {
                closeJobs();
                openJob(this);
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

