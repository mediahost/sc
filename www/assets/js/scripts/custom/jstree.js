var CustomTrees = function () {

    var formID = 'frm-completeCandidateSecond-form';

    var handleSkillsTree = function () {
        if (typeof jsonSkills != 'undefined') {

            $('#skillTree').jstree({
                'plugins': ["wholerow", "checkbox", "types"],
                'core': {
                    "dblclick_toggle": true,
                    "expand_selected_onload": false,
                    "themes": {
                        "responsive": false,
                        "icons": false
                    },
                    'data': jsonSkills
                }
            }).on('changed.jstree', function (e, data) {
                var i, j, r = [];
                for (i = 0, j = data.selected.length; i < j; i++) {
                    r.push(data.instance.get_node(data.selected[i]).id);
                }
                $('input.inSkillTree').attr('checked', false);
                $.each(r, function (i, value) {
                    $('#' + formID + '-skills-' + value).attr('checked', true);
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


        if (typeof jsonCountries != 'undefined') {

            $('#countryTree').jstree({
                'plugins': ["wholerow", "checkbox", "types"],
                'core': {
                    "dblclick_toggle": true,
                    "expand_selected_onload": false,
                    "themes": {
                        "responsive": false,
                        "icons": false
                    },
                    'data': jsonCountries
                }
            }).on('changed.jstree', function (e, data) {
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

        if (typeof jsonFreelancer != 'undefined') {

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
