jQuery(function ($) {

    $(document).ajaxComplete(function (event, request, settings) {
        if (settings.data) {
            var request_data = unserialize_text(settings.data);
            if (request_data.action && request_data.action == "edit-theme-plugin-file") {
                if (request_data.plugin) {
                    jQuery.post(
                            theme_login_activity_log.ajax_url,
                            { 
                                action: 'plugin_file_change_log',
                                edit_file: request_data.file
                            },
                            function (response) { 
                                
                            }
                    );

                }
            }
        }
    });

    function unserialize_text(serializedString) {
        var str = decodeURI(serializedString);
        var pairs = str.split('&');
        var obj = {}, p, idx, val;
        for (var i = 0, n = pairs.length; i < n; i++) {
            p = pairs[i].split('=');
            idx = p[0];

            if (idx.indexOf("[]") == (idx.length - 2)) {
                // Eh um vetor
                var ind = idx.substring(0, idx.length - 2)
                if (obj[ind] === undefined) {
                    obj[ind] = [];
                }
                obj[ind].push(p[1]);
            } else {
                obj[idx] = p[1];
            }
        }
        return obj;
    }
});