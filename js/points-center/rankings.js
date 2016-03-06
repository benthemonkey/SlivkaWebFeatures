'use strict';

var ajaxRoot = require('./utils').ajaxRoot;

var qtrToQuarter = function(qtr) {
    var yr = Math.floor(qtr / 100);
    var q = qtr - yr * 100;

    switch (q) {
    case 1:
        return 'Winter 20' + yr;
    case 2:
        return 'Spring 20' + yr;
    case 3:
    default:
        return 'Fall 20' + yr;
    }
};

module.exports = {
    init: function() {
        $.getJSON(ajaxRoot + '/ajax/getRankings.php', function(data) {
            var tmp, row, i, j, mtable, ftable, mj, fj;
            var males = [];
            var females = [];
            var underCutoff = [];
            var numQtrs = data.qtrs.length;
            var cutoffNum = 39;
            var colDefs = [
                { title: '#', className: 'num', width: '5px' },
                { title: 'Name', className: 'name', width: '140px' }
            ];

            for (i = 0; i < numQtrs; i++) {
                colDefs.push({
                    title: qtrToQuarter(data.qtrs[i]),
                    width: '20px'
                });
            }

            colDefs.push(
                { title: 'Total',
                    width: '20px' },
                { title: 'Mult',
                    width: '20px' },
                { title: 'Total w/ Mult',
                    width: '30px' },
                { visible: false });

            for (i = 0; i < data.rankings.length; i++) {
                row = data.rankings[i];
                tmp = ['', row.full_name];

                for (j = 0; j < numQtrs; j++) {
                    tmp.push(parseInt(row[data.qtrs[j]], 10));
                }

                tmp.push(row.total, row.mult, row.total_w_mult, row.abstains);

                if (row.gender === 'm') {
                    males.push(tmp);
                } else {
                    females.push(tmp);
                }
            }

            // apply styles for cutoffs
            males.sort(function(a, b) {
                return b[numQtrs + 4] - a[numQtrs + 4];
            });
            females.sort(function(a, b) {
                return b[numQtrs + 4] - a[numQtrs + 4];
            });

            mtable = $('#males_table').dataTable({
                data: males,
                columns: colDefs,
                order: [[numQtrs + 4, 'desc']],
                paging: false,
                autoWidth: false,
                dom: 't'
            });

            ftable = $('#females_table').dataTable({
                data: females,
                columns: colDefs,
                order: [[numQtrs + 4, 'desc']],
                paging: false,
                autoWidth: false,
                dom: 't'
            });

            mj = 0;
            fj = 0;
            row = mtable.find('tr');

            for (i = 0; i < males.length; i++) {
                if (males[i][numQtrs + 5]) {
                    row.eq(i + 1).addClass('red');
                } else if (mj < cutoffNum) {
                    row.eq(i + 1).addClass('green').find('.num').text(mj + 1);
                    mj++;
                } else if (mj === cutoffNum) {
                    underCutoff.push(['m', row.eq(i + 1), males[i][numQtrs + 4]]);
                }
            }

            row = ftable.find('tr');

            for (i = 0; i < females.length; i++) {
                if (females[i][numQtrs + 5]) {
                    row.eq(i + 1).addClass('red');
                } else if (fj < cutoffNum) {
                    row.eq(i + 1).addClass('green').find('.num').text(fj + 1);
                    fj++;
                } else if (fj === cutoffNum) {
                    underCutoff.push(['f', row.eq(i + 1), females[i][numQtrs + 4]]);
                }
            }

            underCutoff.sort(function(a, b) {
                return b[2] - a[2];
            });

            if (underCutoff.length > 0) {
                for (i = 0; i < Math.min(4, underCutoff.length); i++) {
                    if (underCutoff[i][0] === 'm') {
                        mj++;
                        underCutoff[i][1].addClass('green').find('.num').text(mj);
                    } else {
                        fj++;
                        underCutoff[i][1].addClass('green').find('.num').text(fj);
                    }
                }
            }
        });
    }
};
