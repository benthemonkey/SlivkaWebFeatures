/* eslint strict:0 */

jQuery(function() {
    'use strict';

    var dataTable = null;

    var displayDirectory = function(data) {
        var i;
        var path = 'http://slivka.northwestern.edu/points/img/slivkans/';
        var hasCorrectPassword = data[0].length === 6;

        for (i = 0; i < data.length; i++) {
            if (!hasCorrectPassword) {
                data[i].push('-', 'missing.jpg');
            }

            data[i][5] = [
                '<img class="directoryphoto" src="', path, data[i][5], '"',
                'title="', data[i][0], ' ', data[i][1], '"',
                'style="height: 100px; display: none;" />'
            ].join('');
        }

        if (!dataTable) {
            // eslint-disable-next-line new-cap
            dataTable = $('#directory').DataTable( {
                data: data,
                columns: [
                    { title: 'First Name' },
                    { title: 'Last Name' },
                    { title: 'Year' },
                    { title: 'Major' },
                    { title: 'Suite' },
                    { title: 'Photo', orderable: false }
                ],
                order: [[0, 'asc']],
                paging: false
            });
        } else {
            dataTable.clear();
            dataTable.rows.add(data);
            dataTable.draw();
        }

        if (hasCorrectPassword) {
            $('.directoryphoto').show();
        }
    };

    var init = function() {
        $.getJSON('/points/ajax/getDirectory.php', displayDirectory);

        // directory password
        $('#directorypass').submit(function() {
            var password = $('#directorypass input').val();

            $.post('/points/ajax/getDirectory.php', { password: password }, displayDirectory, 'json');
            return false;
        });
    };

    var checkForDataTables = function() {
        if (typeof($().DataTable) === 'undefined') {
            setTimeout(checkForDataTables, 100);
        } else {
            init();
        }
    };

    $.getScript('/points/node_modules/datatables/media/js/jquery.dataTables.min.js', checkForDataTables);
});
