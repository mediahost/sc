jQuery(document).ready(function () {
	Layout.init();
	Layout.initUniform();
	$.nette.init(); // https://github.com/vojtech-dobes/nette.ajax.js
	
	Layout.initTwitter();
	
	Maps.init();
});
