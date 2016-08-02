var CustomTrees = function () {

    var handleSkillsTree = function () {
        if (typeof jsonJobCategories != 'undefined' 
                &&  $.jstree.reference('#jobCategoriesTree') === null) {
            $('#jobCategoriesTree').jstree({
                'plugins': ["wholerow", "checkbox", "types"],
                'core': {
                    "dblclick_toggle": true,
                    "expand_selected_onload": true,
                    "themes": {
                        "responsive": false,
                        "icons": false
                    },
                    'data': jsonJobCategories
                }
            }).on('changed.jstree', function (e, data) {
                var formID = $(this).closest('form')[0].id;
                var i, j, r = [];
                for (i = 0, j = data.selected.length; i < j; i++) {
                    r.push(data.instance.get_node(data.selected[i]).id);
                }
                $('input.inCategoryTree').attr('checked', false);
                $.each(r, function (i, value) {
                    $('#' + formID + '-categories-' + value).attr('checked', true);
                });
            }).on('select_node.jstree', function (e, data) {
                var id = data.node.id;
                if (id !== undefined) {
                    if (!$("li[id=" + id + "]").hasClass("jstree-open")) {
                        $(this).jstree("open_node", "#" + id);
                    }
                }
            });
        }
        

        if (typeof jsonCountries != 'undefined'  
                &&  $.jstree.reference('#countryTree') === null) {

            $('#countryTree').jstree({
                'plugins': ["wholerow", "checkbox", "types"],
                'core': {
                    "dblclick_toggle": true,
                    "expand_selected_onload": true,
                    "themes": {
                        "responsive": false,
                        "icons": false
                    },
                    'data': jsonCountries
                }
            }).on('changed.jstree', function (e, data) {
                var formID = $(this).closest('form')[0].id;
                var i, j, r = [];
                for (i = 0, j = data.selected.length; i < j; i++) {
                    r.push(data.instance.get_node(data.selected[i]).id);
                }
                $('input.inCountryTree').attr('checked', false);
                $.each(r, function (i, value) {
                    $('#' + formID + '-countries-' + value).attr('checked', true);
                });
            }).on('select_node.jstree', function (e, data) {
                var id = data.node.id;
                if (id !== undefined) {
                    if (!$("li[id=" + id + "]").hasClass("jstree-open")) {
                        $(this).jstree("open_node", "#" + id);
                    }
                }
            });

        }

        if (typeof jsonFreelancer != 'undefined'
                &&  $.jstree.reference('#freelancerTree') === null) {

            $('#freelancerTree').jstree({
                'plugins': ["wholerow", "checkbox", "types"],
                'core': {
                    "dblclick_toggle": true,
                    "themes": {
                        "responsive": false,
                        "icons": false
                    },
                    'data': jsonFreelancer
                }
            }).on('changed.jstree', function (e, data) {
                var formID = $(this).closest('form')[0].id;
                $('#' + formID + '-freelancer').attr('checked', !!data.selected.length);
            });

        }

    };

    return {
        //main function to initiate the module
        init: function () {
            handleSkillsTree();
        }
    };

}();
