const WhakamahereFilter = {

    init: function() {

        $('#semester').on('change', function() {
            $.post(
                $('#semester').data('select-url'),
                {
                    semester: $(this).children('option:selected').val()
                },
            );
        });

        $('#institute').on('change', function() {
            $.post(
                $('#institute').data('select-url'),
                {
                    institute: $(this).children('option:selected').val()
                },
            );
        });

        $('#room').on('change', function() {
            $.post(
                $('#room').data('select-url'),
                {
                    room: $(this).children('option:selected').val()
                },
            );
        });

    }

};

export default WhakamahereFilter;
