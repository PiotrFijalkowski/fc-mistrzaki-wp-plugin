jQuery(document).ready(function($) {
    // Wyszukiwarka zawodników
    $('#zawodnik-search').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#zawodnicy-table tbody tr').filter(function() { 
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1) 
        });
    });

    // Sprawia, że cała komórka kalendarza jest klikalna
    $('#trening-calendar td:not(.pad)').on('click', function(e) {
        // Upewnij się, że nie kliknięto już na link w środku
        if (e.target.tagName !== 'A' && e.target.tagName !== 'STRONG') {
            var link = $(this).find('a');
            if (link.length) {
                window.location.href = link.attr('href');
            }
        }
    });
});