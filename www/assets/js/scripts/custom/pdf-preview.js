var PdfPreview = function () {

	var handlePdfPreview = function () {
		$('.pdf-preview').each(function () {

			var preview = $(this);
			var id = preview.attr('id');
			var source = preview.attr('data-source');
			var startPage = parseInt(preview.attr('data-page')) > 1 ? parseInt(preview.attr('data-page')) : 1;
			var startScale = parseFloat(preview.attr('data-scale')) > 0 ? parseFloat(preview.attr('data-scale')) : 1;
			var minScale = parseFloat(preview.attr('data-min-scale')) > 0 ? parseFloat(preview.attr('data-min-scale')) : 0.8;
			var maxScale = parseFloat(preview.attr('data-max-scale')) > 0 ? parseFloat(preview.attr('data-max-scale')) : 1.2;
			var scalestep = parseFloat(preview.attr('data-scale-step')) > 0 ? parseFloat(preview.attr('data-scale-step')) : 0.2;

			if (file_exists(source)) {
				PdfViewer.init(id, source, startPage, startScale, scalestep, minScale, maxScale);
			} else {
				var errClass = preview.attr('data-error-class') ? preview.attr('data-error-class') : 'note note-danger';
				var errMsg = preview.attr('data-error-message') ? preview.attr('data-error-message') : 'We couldn\'t find requested PDF.';
				var errDiv = $('<div>').addClass(errClass).html(errMsg);
				preview.after(errDiv);
				preview.hide();
			}
		});
	};

	return {
		init: function () {
			var source = $('.pdf-preview').attr('data-source');
			var theme = 'default';
			var print = true;
			
			$('#profileTab').on('click', '[href="#sourceCode"], [href="#europass"], [href="#standard1"], [href="#standard2"]', function() {
				theme = $(this).attr('href').substring(1);
				source += '&theme=' + theme;
				$('.pdf-preview').attr('data-source', source);
				handlePdfPreview();
			});
			
			
			if (typeof PDFJS !== 'undefined') {
				PDFJS.workerSrc = basePath + '/assets/js/pdfjs/pdf.worker.js';
				handlePdfPreview();
			}
		}
	};

}();

var PdfViewer = function () {

	var data = {
		pdf: null,
		id: null,
		source: null,
		page: 1,
		scale: 1,
		scaleStep: 0.2,
		scaleMax: 1.4,
		scaleMin: 0.6,
		rendering: false,
		pending: null,
		canvas: null,
		context: null
	};
	var navigation = {};

	/**
	 * Set start data
	 */
	var setStartData = function (id, source, startPage, startScale, scaleStep, minScale, maxScale) {
		data.id = id;
		data.source = source;
		data.page = startPage;
		data.scale = startScale;
		data.scaleStep = scaleStep;
		data.scaleMin = minScale;
		data.scaleMax = maxScale;
		data.pdf = null;
		data.canvas = null;
		data.context = null;
		data.pending = null;
		data.rendering = false;
	};

	/**
	 * Set navidation items
	 */
	var setNavigation = function (id) {
		var classes = {
			navigation: '.pdf-preview-navigation',
			closestParent: '.portlet',
			button: '.pdf-preview-navigation-button',
			paginator: {
				text: {
					main: '.pdf-preview-navigation-paginator',
					actualPage: '.pdf-preview-navigation-paginator-actualPage',
					separator: '.pdf-preview-navigation-paginator-separator',
					totalPage: '.pdf-preview-navigation-paginator-totalPage',
					loading: '.pdf-preview-navigation-paginator-loading'
				}
			}
		};
		var navigationBar = $(classes.navigation + '[data-for="' + id + '"]');
		navigation = {
			parent: navigationBar.closest(classes.closestParent),
			prev: navigationBar.find(classes.button + '[data-navigation="prev"]'),
			next: navigationBar.find(classes.button + '[data-navigation="next"]'),
			zoomIn: navigationBar.find(classes.button + '[data-navigation="zoomIn"]'),
			zoomOut: navigationBar.find(classes.button + '[data-navigation="zoomOut"]'),
			paginator: {
				text: {
					main: navigationBar.find(classes.paginator.text.main),
					actualPage: navigationBar.find(classes.paginator.text.actualPage),
					totalPage: navigationBar.find(classes.paginator.text.totalPage),
					separator: navigationBar.find(classes.paginator.text.separator),
					loading: navigationBar.find(classes.paginator.text.loading)
				}
			}
		};
		addEvents();
	};

	/**
	 * Add events to its buttons
	 */
	var addEvents = function () {
		navigation.prev.on('click', onPrevPage);
		navigation.next.on('click', onNextPage);
		navigation.zoomIn.on('click', onZoomIn);
		navigation.zoomOut.on('click', onZoomOut);
	};

	/**
	 * Displays previous page.
	 */
	var onPrevPage = function () {
		if (data.page <= 1) {
			return;
		}
		data.page--;
		queueRenderPage(data.page, data.scale);
	};

	/**
	 * Displays next page.
	 */
	var onNextPage = function () {
		if (data.page >= data.pdf.numPages) {
			return;
		}
		data.page++;
		queueRenderPage(data.page, data.scale);
	};

	/**
	 * Zoom IN
	 */
	var onZoomIn = function () {
		data.scale = round(data.scale, 1);
		if (round(data.scale + data.scaleStep, 1) >= round(data.scaleMax + data.scaleStep, 1)) {
			return;
		}
		data.scale = round(data.scale + data.scaleStep, 1);
		queueRenderPage(data.page, data.scale);
	};

	/**
	 * Zoom OUT
	 */
	var onZoomOut = function () {
		data.scale = round(data.scale, 1);
		if (round(data.scale - data.scaleStep, 1) < data.scaleMin) {
			return;
		}
		data.scale = round(data.scale - data.scaleStep, 1);
		queueRenderPage(data.page, data.scale);
	};

	/**
	 * If another page rendering in progress, waits until the rendering is
	 * finised. Otherwise, executes rendering immediately.
	 * @param {int} pageNum
	 * @param {int} scaleNum
	 */
	function queueRenderPage(pageNum, scaleNum) {
		if (data.rendering) {
			data.pending = {
				page: pageNum,
				scale: scaleNum
			};
		} else {
			renderPage(pageNum, scaleNum);
		}
	}

	/**
	 * Set total page to right navigation items
	 * @param {int} totalPages
	 */
	var setTotalPages = function (totalPages) {
		navigation.paginator.text.totalPage.html(totalPages);
	};

	/**
	 * Set rendering process status
	 * @param {bool} processing
	 */
	var onRendering = function (processing) {
		if (processing) {
			data.rendering = true;
		} else {
			data.rendering = false;
		}
	};

	/**
	 * Before loading PDF
	 * Do it once before first load document
	 */
	var beforeLoadingPdf = function () {
		navigation.paginator.text.main.addClass('loading');
		navigation.paginator.text.actualPage.hide();
		navigation.paginator.text.separator.hide();
		navigation.paginator.text.totalPage.hide();
		navigation.paginator.text.loading.show();
	};

	/**
	 * After loading PDF
	 * Do it once after load document
	 */
	var afterLoadingPdf = function () {
		navigation.paginator.text.main.removeClass('loading');
		navigation.paginator.text.actualPage.show();
		navigation.paginator.text.separator.show();
		navigation.paginator.text.totalPage.show();
		navigation.paginator.text.loading.hide();
	};

	/**
	 * Set scale number to right navigation
	 * @param {int} scaleNum
	 */
	var setScaleNumber = function (scaleNum) {
		data.scale = scaleNum;

		if (round(scaleNum + data.scaleStep, 1) >= round(data.scaleMax + data.scaleStep, 1)) {
			navigation.zoomIn.addClass('disabled');
		} else {
			navigation.zoomIn.removeClass('disabled');
		}
		if (round(scaleNum - data.scaleStep, 1) <= round(data.scaleMin - data.scaleStep, 1)) {
			navigation.zoomOut.addClass('disabled');
		} else {
			navigation.zoomOut.removeClass('disabled');
		}
	};

	/**
	 * Set page number to right navigation
	 * @param {int} pageNum
	 */
	var setPageNumber = function (pageNum) {
		data.page = pageNum;
		navigation.paginator.text.actualPage.html(pageNum);

		if (pageNum >= data.pdf.numPages) {
			navigation.next.addClass('disabled');
		} else {
			navigation.next.removeClass('disabled');
		}
		if (pageNum <= 1) {
			navigation.prev.addClass('disabled');
		} else {
			navigation.prev.removeClass('disabled');
		}
	};

	/**
	 * Render PDF page
	 * @param {int} pageNum
	 * @param {int} scaleNum
	 */
	var renderPage = function (pageNum, scaleNum) {

		if (data.pdf === null) {
			PDFJS.getDocument(data.source).then(function (pdf) {
				data.pdf = pdf;
				setTotalPages(data.pdf.numPages);
				afterLoadingPdf();
				renderPage(pageNum, scaleNum);
			});
			return;
		}

		pageNum = pageNum > data.pdf.numPages ? data.pdf.numPages : pageNum;
		scaleNum = scaleNum > 0 ? scaleNum : 1;

		onRendering(true);

		// Using promise to fetch the page
		data.pdf.getPage(data.page).then(function (page) {
			var viewport = page.getViewport(scaleNum);

			// Prepare canvas using PDF page dimensions
			data.canvas = document.getElementById(data.id);
			data.context = data.canvas.getContext('2d');
			data.canvas.height = viewport.height;
			data.canvas.width = viewport.width;

			// Render PDF page into canvas context
			var renderContext = {
				canvasContext: data.context,
				viewport: viewport
			};
			var renderTask = page.render(renderContext);

			// Wait for rendering to finish
			renderTask.promise.then(function () {
				setPageNumber(pageNum);
				setScaleNumber(scaleNum);
				onRendering(false);
				if (data.pending !== null) {
					// New page rendering is pending
					renderPage(data.pending.page, data.pending.scale);
					data.pending = null;
				}
			});
		});

	};

	return {
		init: function (id, source, startPage, startScale, scaleStep, minScale, maxScale) {
			setStartData(id, source, startPage, startScale, scaleStep, minScale, maxScale);
			setNavigation(id);
			beforeLoadingPdf();
			renderPage(startPage, startScale);
		}
	};

}();


function round(num, precision) {
	return +(Math.round(num + "e+" + precision) + "e-" + precision);
}
