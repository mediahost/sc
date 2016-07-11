
// Gallery plugin
var Gallery = function () {
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
                    match(ui.draggable, true);
                    invite();
                }
            });
            loadJobCvs();
        }
    };
    
    var loadJobCvs = function(cvs) {
        var jobObject = $('.droppable');
        var cvs = jobObject.attr('data-cvs').split(',');
        $.each(cvs, function(index, cv) {
            var cvId = cv.split('|')[0];
            var el = $('.gallery-item[data-cv="' + cvId + '"]');
            match(el, true);
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
                revert: 'invalid'
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

