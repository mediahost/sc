var Global = function () {

    var handleInitPickers = function () {
        $('.input-daterange').datepicker();
        $('.input-daterange span').on('click', function () {
            $(this).next('input').datepicker('show');
        })
    };

    var handleRightbar = function () {
        if (!$('#right-sidebarbg').attr('data-show')) {
            $('body').data('supr').hideRightSidebar();
        }
    };

    var handleRating = function () {
        $('input.rating').rating();
    };
    
    var hadleSlider = function() {
        $('input.slider:not([data])').slider();
    };
    
    var handleImagUpload = function() {
        $(document).on('change', '.imageUpload', function() {
            if (this.files && this.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('img.imageUploadPreview').attr('src', e.target.result);
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    };

    var handleFBSharer = function () {
        $('[href="#share"]').on('click', function () {
            var fburl = 'https://source-code.com/';
            var sharerURL = "http://www.facebook.com/sharer/sharer.php?s=100&p[url]=" + encodeURI(fburl);
            window.open(sharerURL, 'facebook-share-dialog', 'width=600,height=400');
            return  false;
        })
    };

    var initAccordion = function () {
        $('.expandAll').find('.panel-collapse').removeClass('collapse');
        $('.expandFirst').find('.accordion-toggle').first().click();
    };

    var handleSocialLinks = function () {
        $(document).on('mouseover', '.social-editor .data', function (e) {
            $(this).find('a.edit:not(.empty)').show();
        });
        $(document).on('mouseout', '.social-editor .data', function (e) {
            $(this).find('a.edit:not(.empty)').hide();
        });
        $(document).on('click', '.social-editor a.edit', function (e) {
            e.preventDefault();
            $('.social-editor .data').hide();
            $('.social-editor .editor').show();
        });
        $(document).on('click', '.social-editor input[type=submit]', function (e) {
            e.preventDefault();
            $('.social-editor .data').show();
            $('.social-editor .editor').hide();
        });
    };

    var handlePersonalDetails = function () {
        var main = $('.personal-detail-editor .profile');
        $(document).on('click', '.personal-detail-editor a.edit', function (e) {
            e.preventDefault();
            var target = $(this).attr('href');
            $(target).show();
            $(main).hide();
        });
        $(document).on('click', '.personal-detail-editor input[type=submit]', function (e) {
            e.preventDefault();
            $(main).show();
            $('.personal-detail-editor .profile-edit').hide();
        });
    };

    var handleInterestedIn = function () {
        var main = $('.interestedIn-editor .interestedIn');
        $(document).on('click', '.interestedIn-editor a.edit', function (e) {
            e.preventDefault();
            var target = $(this).attr('href');
            $(target).show();
            $(main).hide();
        });
        $(document).on('click', '.interestedIn-editor input[type=submit]', function (e) {
            e.preventDefault();
            $(main).show();
            $('.interestedIn-editor .interestedIn-edit').hide();
        });
    };

    var handleTagsInput = function () {
        $('input[data-role="tagsinput"]').tagsinput({});
    };

    var handleModals = function () {
        $('body').on('click', 'button[data-action="modal"]', function() {
            $(this).closest('.modal-content').find('form').submit();
        });
        $('body').on('click', 'a[data-action="modal"]', function(e) {
            $.nette.ajax({}, this, e);
        });
    };
    
    var onModalLoad = function() {
        $('[data-opening="modal"]').modal('show')
            .on('hidden.bs.modal', function () {
                $(this).removeAttr('data-opening')
            });
    };

    var handleMessages = function () {
        $(document).on('click', '.recent-comments li.list-group-item', function (e) {
            window.location.href = $(this).find('a').attr('href');
        });
    };

    var handleDataTables = function (selector, options) {
        if (options.iDisplayLength == undefined) {
            options.iDisplayLength = 25;
        }
        if ($(selector).attr('data-filter')) {
            options.serverSide = true;
            options.ajax = {
                url: $(selector).attr('data-filter'),
                type: 'POST'
            }
        }
        $(selector).DataTable(options);
    };

    var handleDropzone = function () {
        Dropzone.options.frmCareerDocsControlForm = {
            init: function () {
                this.on("success", function (file) {
                    location.reload();
                });
            }
        }
    };
    
    var handleJobPage = function() {
        $(document).on('click', '.cv-filter', function() {
            var checkedStates = [];
            
            $('.cv-filter').each(function(index, item) {
                if($(item).attr('checked') == 'checked') {
                    checkedStates[checkedStates.length] = $(item).val();
                }
            });
            $('.gallery-item').each(function(index, cv) {
                var cvState = $(cv).attr('data-state');
                if(checkedStates.length  &&  $.inArray(cvState, checkedStates) == -1) {
                    $(cv).hide();
                } else {
                    $(cv).show();
                }
            }); 
        });
        $('#jobDescription, #jobSummary').summernote({
            height: 200
        });
        if ($('#mapView').length) {
            $('#mapView').mapView();
        }
    };

    return {
        init: function () {
            $(document).ready(function () {
                handleInitPickers();
                handleRating();
                hadleSlider();
                handleFBSharer();
                handleSocialLinks();
                handlePersonalDetails();
                handleInterestedIn();
                handleMessages();
                handleDropzone();
                handleTagsInput();
                onModalLoad();
                handleImagUpload();

                // Global components
                CustomTrees.init();
            });
            
            
            handleJobPage();
        },
        handleRightbar: handleRightbar,
        initAccordion: initAccordion,
        handleDataTables: handleDataTables,
        handleModals: handleModals,
        handleJobPage: handleJobPage
    };

}();
