jQuery(document).ready(function($) {
    var aDataSet = new Array();
    $.getJSON("http://slivka.northwestern.edu/points/ajax/getDirectory.php",function(data){
        var path = "http://slivka.northwestern.edu/points/img/slivkans/";
        for (entry in data){
            aDataSet.push([data[entry][0],data[entry][1],data[entry][2],data[entry][3],data[entry][4],
             '<img class="directoryphoto" src="' + path + data[entry][5] + '.jpg" title="'+data[entry][0]+' '+data[entry][1]+'" style="height: 100px; display: none;" />'])
        }

        /*
         * Initialse DataTables, with no sorting on the 'details' column
         */
         oTable = $('#example').dataTable( {
            "aaData": aDataSet,

            "aoColumns": [
            { "sTitle": "First Name" },
            { "sTitle": "Last Name" },
            { "sTitle": "Year" },
            { "sTitle": "Major" },
            { "sTitle": "Suite" },
            { "sTitle": "Photo" },

            ],

            "aoColumnDefs": [ { "bSortable": false, "aTargets": [ 5 ] }, ],
            "aaSorting": [[0, 'asc']],
            "bPaginate" : false,
            "aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
            iDisplayLength: -1,
            iDisplayStart: 0
        });

        // Hide cols
        //oTable.fnSetColumnVis(7, false);
        
        // directory password
        // This is a very insecure measure, mostly so the photos 
        // aren't displayed by default and you have to know javascript to display them
        $('#submitdirectorypass').click(function(){
            if ("discoverslivka" == $('#directorypass').val()){
                $('.directoryphoto').show();

                // dumb but works: saving password in localstorage
                localStorage.directorypass = "discoverslivka";
            }
        });
        
        $('td').css('height','auto');

        $('#directorypass').val(localStorage.directorypass);
        $('#submitdirectorypass').click();
    });

    $('#directory').html( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="example"></table>' );
    
} );