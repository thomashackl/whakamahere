const WhakamahereSemesterstatus = {
    init: function() {
        $('select').on('change', function() {
            $.post(
                $('#semesters').data('update-url'),
                {
                    semester: $(this).data('semester-id'),
                    status: $(this).children('option:selected').val(),
                    security_token: $('input[name="security_token"]').val()
                },
            ).done(function(data) {
                $('#update-status').html(data);
            }).fail(function(xhr, status, error) {
                $('#update-status').html(error);
            });
        });
    }
};

export default WhakamahereSemesterstatus;
