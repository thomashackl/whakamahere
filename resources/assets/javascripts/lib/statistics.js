import jqxGauge from 'jqwidgets-framework/jqwidgets/jqxgauge'

const WhakamahereStatistics = {

    init: function() {
        const element = $('#room-usage');

        $.ajax(
            element.data('get-usage-url'),
            {
                dataType: 'json',
                beforeSend: function(xhr, settings) {
                    element.html($('<img>').
                    attr('width', 64).
                    attr('height', 64).
                    attr('src', STUDIP.ASSETS_URL + 'images/ajax-indicator-black.svg'));
                },
                success: function(data, status, xhr) {
                    element.jqxGauge({
                        ranges: [
                            { startValue: 0, endValue: 35, style: { fill: '#e02628', stroke: '#e02628' }},
                            { startValue: 35, endValue: 50, style: { fill: '#ff8000', stroke: '#ff8000' }},
                            { startValue: 50, endValue: 60, style: { fill: '#ffd109', stroke: '#fbd109' }},
                            { startValue: 60, endValue: 85, style: { fill: '#4bb648', stroke: '#4bb648' }},
                            { startValue: 85, endValue: 92, style: { fill: '#ffd109', stroke: '#fbd109' }},
                            { startValue: 92, endValue: 96, style: { fill: '#ff8000', stroke: '#ff8000' }},
                            { startValue: 96, endValue: 100, style: { fill: '#e02628', stroke: '#e02628' }}
                        ],
                        ticksMinor: { interval: 5, size: '5%' },
                        ticksMajor: { interval: 10, size: '9%' },
                        value: data.totalUsage * 100,
                        colorScheme: 'scheme05',
                        animationDuration: 1200,
                        min: 0,
                        max: 100,
                        border: { visible: false },
                        caption: {
                            value: 'Auslastung Hörsäle + Seminarräume,<br>Mo - Fr, 10 - 20 Uhr',
                            position: 'bottom',
                            offset: [ 0, 50 ]
                        },
                        height: 300
                    });
                },
                error: function(xhr, status, error) {
                    alert(error);
                }
            }
        );
    }
};

export default WhakamahereStatistics;
