<div id="whakamahere-statistics">
    <statistics-gauge :get-value-url="'<?= $widget->url_for('roomUsage') ?>'"></statistics-gauge>
</div>
<script>
    new Vue({
        el: '#whakamahere-statistics'
    });
</script>
