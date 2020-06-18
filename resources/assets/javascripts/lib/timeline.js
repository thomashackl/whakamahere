import timeline from 'jquery.timeline.psk/dist/timeline.min';

const WhakamahereTimeline = {

    init: function() {
        const element = $('#phases');

        $.ajax(
            element.data('dates-url'),
            {
                dataType: 'json',
                beforeSend: function(xhr, settings) {
                    element.html($('<img>').
                        attr('width', 64).
                        attr('height', 64).
                        attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg'));
                },
                success: function(data, status, xhr) {

                    if (data != null && data.length > 0) {

                        let ul = $('<ul>').addClass('timeline-events');

                        let current = [];

                        $.each(data, function (index, phase) {
                            let li = $('<li>')
                                .attr('data-timeline-node', "{ eventId: '" + phase.id +
                                    "', start: '" + phase.start +
                                    "', end: '" + phase.end +
                                    "', content: '" + phase.title +
                                    "', bgColor: '" + phase.color +
                                    "', color: '" + WhakamahereTimeline.getContrastColor(phase.color) +
                                    "'}")
                                .attr('data-phase-color', phase.color)
                                .attr('data-title', phase.title)
                                .text(phase.title);
                            ul.append(li);

                            if (phase.current) {
                                current.push(phase.id);
                            }
                        });

                        element.append(ul);

                        element.timeline({
                            startDatetime: '2020-04-01',
                            type: 'bar',
                            range: 6,
                            rows: 1,
                            scale: 'months',
                            rangeAlign: 'current',
                            langsDir: element.data('plugin-url') + '/assets/timeline/langs/',
                            minGridSize: 5
                        });

                        element.on('afterRender.timeline', function () {
                            for (let i = 0; i < current.length; i++) {
                                $('#evt-' + current[i]).addClass('timeline-current');
                            }

                            if (current.length > 0) {
                                $('.timeline-events').css('height', '48px');
                                $('.timeline-grids').css('height', '48px');
                            }

                            $('.timeline-node').hover(
                                function (event) {
                                    const fulltitle = $('<div>')
                                        .attr('id', 'timeline-fulltitle')
                                        .css('background-color', $(this).css('background-color'))
                                        .css('color', $(this).css('color'))
                                        .html($(this).html());
                                    element.append(fulltitle);
                                    fulltitle.fadeIn();
                                },
                                function (event) {
                                    $('#timeline-fulltitle')
                                        .fadeOut()
                                        .remove();
                                }
                            );
                        });

                    } else {

                        element.html(
                            $('<div>')
                                .addClass('messagebox')
                                .addClass('messagebox_info')
                                .html(element.data('no-phases-message'))
                        );

                    }
                },
                error: function(xhr, status, error) {
                    alert(error);
                }
            }
        );
    },

    getContrastColor: function(hexcolor) {
        hexcolor = hexcolor.replace("#", "");
        const r = parseInt(hexcolor.substr(0,2),16);
        const g = parseInt(hexcolor.substr(2,2),16);
        const b = parseInt(hexcolor.substr(4,2),16);
        const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
        return (yiq >= 144) ? '#000000' : '#ffffff';
    }
};

export default WhakamahereTimeline;
