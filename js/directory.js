/* eslint strict:0 */

jQuery(function() {
    'use strict';

    var checkForDataTables = function() {
        if (typeof($().DataTable) === 'undefined') {
            setTimeout(checkForDataTables, 100);
        } else {
            init();
        }
    },
    init = function() {
        $.getJSON('/points/ajax/getDirectory.php', function(data) {
            var i;
            var path = 'http://slivka.northwestern.edu/points/img/slivkans/';

            for (i = 0; i < data.length; i++) {
                data[i][5] = [
                    '<img class="directoryphoto" src="', path, data[i][5], '"',
                    'title="', data[i][0], ' ', data[i][1], '"',
                    'style="height: 100px; display: none;" />'
                ].join('');
            }

            $('#directory').dataTable( {
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

            // directory password
            // This is a very insecure measure, mostly so the photos
            // aren't displayed by default and you have to know javascript to display them
            $('#submitdirectorypass').click(function() {
                if ('discoverslivka' === $('#directorypass').val()) {
                    $('.directoryphoto').show();

                    // dumb but works: saving password in localstorage
                    localStorage.directorypass = 'discoverslivka';
                }
            });

            if (localStorage.directorypass) {
                $('#directorypass').val(localStorage.directorypass);
                $('#submitdirectorypass').click();
            }
        });
    }

    $.getScript('/points/node_modules/datatables/media/js/jquery.dataTables.min.js', checkForDataTables);
});
