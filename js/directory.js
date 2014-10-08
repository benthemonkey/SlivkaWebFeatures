$(function() {
    'use strict';
    $.getJSON('http://slivka.northwestern.edu/points/ajax/getDirectory.php',function(data){
        var path = 'http://slivka.northwestern.edu/points/img/slivkans/';
        for (var i=0; i<data.length; i++){
            data[i][5] = [
                '<img class="directoryphoto" src="', path, data[i][5], '"',
                'title="', data[i][0], ' ', data[i][1], '"',
                'style="height: 100px; display: none;" />'
            ].join('');
        }

        $('#directory').dataTable( {
            aaData: data,
            aoColumns: [
            { sTitle: 'First Name' },
            { sTitle: 'Last Name' },
            { sTitle: 'Year' },
            { sTitle: 'Major' },
            { sTitle: 'Suite' },
            { sTitle: 'Photo', bSortable: false },
            ],
            aaSorting: [[0, 'asc']],
            bPaginate : false
        });

        // directory password
        // This is a very insecure measure, mostly so the photos
        // aren't displayed by default and you have to know javascript to display them
        $('#submitdirectorypass').click(function(){
            if ('discoverslivka' == $('#directorypass').val()){
                $('.directoryphoto').show();

                // dumb but works: saving password in localstorage
                localStorage.directorypass = 'discoverslivka';
            }
        });

        if(localStorage.directorypass){
            $('#directorypass').val(localStorage.directorypass);
            $('#submitdirectorypass').click();
        }
    });
});
