var Global = function () {

	var handleInitSelect2 = function () {
		$('.select2').select2();
	};

	var handleInitPickers = function () {
		$('.input-daterange, .input-datepicker input').datepicker();
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
		$('input.rating').rating({
			extendSymbol: function () {
				$(this).on('rating.rateenter', function (e, rate) {
					var $parent = $(e.target).closest('rating-circles');
					if (rate == 6) {
						$parent.addClass('rating-full');
					} else {
						$parent.removeClass('rating-full');
					}
				});
			}
		});
	};

	var hadleSlider = function () {
		$('input.slider[data-skill]:not([data])').slider({
			formatter: function(val) {
				if (typeof val == "number"  &&  skillLevels[val] != undefined) {
					return skillLevels[val];
				}
				var val1 = skillLevels[val[0]-1];
				var val2 = skillLevels[val[1]-1];
				if (val1 != undefined && val2 != undefined) {
					return val1 + ' - ' + val2;
				}
				return val;
			}
		});
		$('input.slider[data-postfix]:not([data])').slider({
			formatter: function(val) {
				if(val[1] == this.max) {
					return val[0] + ' - ' + val[1] + '+';
				}
				return val[0] + ' - ' + val[1];
			}
		});
		$('input.slider:not([data]):not([data-skill])').slider();
	};

	var handleImagUpload = function () {
		$(document).on('change', '.imageUpload', function () {
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
			return false;
		})
	};

	var initAccordion = function () {
		$('.expandFirst').find('.accordion-toggle').click();
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

	var handleProfileId = function () {
		$(document).on('click', '.profileId a.edit', function (e) {
			e.preventDefault();
			$('.profileId .editor').show();
			$('.profileId .preview').hide();
		});
	};

	var handleWysiwyg = function () {
		$('.wysihtml5').summernote({
			height: 200
		});
	};

	var handleTagsInput = function () {
		$('input[data-role="tagsinput"]').tagsinput({});
	};

	var handleModals = function () {
		$(document).on('click', 'button[data-action="modal"]', function () {
			$(this).closest('.modal-content').find('form').submit();
		});
		$(document).on('click', 'a[data-action="modal"]', function (e) {
			$.nette.ajax({}, this, e);
		});
	};

	var onModalLoad = function () {
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

	var handleDropzone = function () {
		Dropzone.options.frmCareerDocsControlForm = {
			init: function () {
				this.on("success", function (file) {
					location.reload();
				});
			}
		}
		Dropzone.options.frmCompleteCvForm = {
			acceptedFiles: 'application/pdf,' +
			'application/msword,' +
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			uploadMultiple: false,
			init: function () {
				this.on("success", function (file) {
					location.reload();
				});
			}
		}
	};

	var handleJobPage = function () {
		$(document).on('click', '.cv-filter', function () {
			var checkedStates = [];

			$('.cv-filter').each(function (index, item) {
				if ($(item).attr('checked') == 'checked') {
					checkedStates[checkedStates.length] = $(item).val();
				}
			});
			$('.gallery-item').each(function (index, cv) {
				var cvState = $(cv).attr('data-state');
				if (checkedStates.length && $.inArray(cvState, checkedStates) == -1) {
					$(cv).hide();
				} else {
					$(cv).show();
				}
			});
		});
		if ($('#mapView').length) {
			$('#mapView').mapView();
		}
	};

	var handleEditable = function() {
		$(document).on('mouseover', '.editable-preview', function() {
			$(this).find('.editable-icon').show();
		});
		$(document).on('mouseout', '.editable-preview', function() {
			$(this).find('.editable-icon').hide();
		});
		$(document).on('click', '.editable-icon', function() {
			var target = $(this).attr('data-target');
			$('[data-form="' + target + '"]').show();
			$(this).closest('[data-editable]').find('.editable-preview').hide();
		});
	}

	var handleConfirm = function () {
		$(document).on('click', 'a[data-confirm]', function (e) {
			var $target = $(e.target);
			var question = $target.attr('data-confirm');
			return confirm(question);
		});
	}

	var handleEditMatchNotes = function () {
		$(document).on('click', '.message a.edit-note', function (e) {
			e.preventDefault();
			var messageBlock = $(e.target).closest('.message');
			var noteId = messageBlock.data('noteId');
			var noteIdInput = $(e.target).closest('ul.messages').find('form input[type=hidden][name=noteId]');
			noteIdInput.val(noteId);

			var content = $(messageBlock.find('.message-content')).html();
			var contentTextbox = $(e.target).closest('ul.messages').find('form textarea[name=message]');
			contentTextbox.val($.trim(content).replace(/<br>/g, "\r"));
		});
	};

	return {
		init: function () {
			$(document).ready(function () {
				handleInitSelect2();
				handleInitPickers();
				handleRating();
				handleEditMatchNotes();
				hadleSlider();
				handleFBSharer();
				handleSocialLinks();
				handlePersonalDetails();
				handleInterestedIn();
				handleProfileId();
				handleMessages();
				handleDropzone();
				onModalLoad();
				handleImagUpload();
				handleEditable();
				handleConfirm();

				// Global components
				CustomTrees.init();
			});

			handleJobPage();
			handleTagsInput();
			handleWysiwyg();
		},
		handleRightbar: handleRightbar,
		initAccordion: initAccordion,
		handleModals: handleModals,
		handleJobPage: handleJobPage
	};

}();
