
/* Data set - can contain whatever information you want */
//var aDataSet = new Array();

/* Formating function for row details */
(function (jQuery) {
    var oTable;

    jQuery.fn.dataTableExt.oApi.fnGetColumnData = function (oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty) {
        // check that we have a column id
        if (typeof iColumn == "undefined") return new Array();

        // by default we only wany unique data
        if (typeof bUnique == "undefined") bUnique = true;

        // by default we do want to only look at filtered data
        if (typeof bFiltered == "undefined") bFiltered = true;

        // by default we do not wany to include empty values
        if (typeof bIgnoreEmpty == "undefined") bIgnoreEmpty = true;

        // list of rows which we're going to loop through
        var aiRows;

        // use only filtered rows
        if (bFiltered == true) aiRows = oSettings.aiDisplay;
        // use all rows
        else aiRows = oSettings.aiDisplayMaster; // all row numbers

        // set up data array
        var asResultData = new Array();

        for (var i = 0, c = aiRows.length; i < c; i++) {
            iRow = aiRows[i];
            var aData = this.fnGetData(iRow);
            var sValue = aData[iColumn];

            // ignore empty values?
            if (bIgnoreEmpty == true && sValue.length == 0) continue;

            // ignore unique values?
            else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;

            // else push the value onto the result data array
            else asResultData.push(sValue);
        }

        return asResultData;
    } 
} (jQuery));

jQuery(document).ready(function() {
    var aDataSet = new Array();
    jQuery.ajax({
        dataType : "json",
        async : false,
        url: "ajax/getDirectory.php", //http://slivka.northwestern.edu/directory
        
        success: function(data){
        var path = "../img/";
        for (entry in data){
            aDataSet.push([data[entry][0],data[entry][1],data[entry][2],data[entry][3],data[entry][4],
                           '<img class="directoryphoto" src="' + path + data[entry][5] + '.jpg" title="'+data[entry][0]+' '+data[entry][1]+'" style="height: 100px; display: none;" />'])
        }
        }
        })

    jQuery('#directory').html( '<table cellpadding="0" cellspacing="0" border="0" class="display" id="example"></table>' );
    
    /*
     * Initialse DataTables, with no sorting on the 'details' column
     */
     oTable = jQuery('#example').dataTable( {
    "aaData": aDataSet,
    
    "aoColumns": [
    { "sTitle": "First Name" },
    { "sTitle": "Last Name" },
    { "sTitle": "Year" },
    { "sTitle": "Major" },
    { "sTitle": "Suite" },
    { "sTitle": "Photo" },
    
    ],
                
            "aoColumnDefs": [ 
            { "bSortable": false, "aTargets": [ 6 ] },
            //{"sWidth": "200px", "aTargets": [1] },
            //{"sWidth": "100px", "aTargets": [1] } 
            ],
    "aaSorting": [[0, 'asc']],
    "bPaginate" : false,
                
                
    
                "aLengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                    iDisplayLength: -1,
                    iDisplayStart: 0
    });
    
    // Hide cols
    //oTable.fnSetColumnVis(7, false);
    
    // new FixedHeader( oTable );
    
    // directory password
    // This is a very insecure measure, mostly so the photos 
    // aren't displayed by default and you have to know javascript to display them
    jQuery('#submitdirectorypass').click(function(){
        if ("discoverslivka" == jQuery('#directorypass').val()){
            jQuery('.directoryphoto').show();

            // dumb but works: saving password in localstorage
            localStorage.directorypass = "discoverslivka";
        }
    });
    
    jQuery('td').css('height','auto');
} );

jQuery(document).ready(function(){
    $('#directorypass').val(localStorage.directorypass);
    $('#submitdirectorypass').click();
})