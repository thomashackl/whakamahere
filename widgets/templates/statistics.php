<div id="whakamahere-statistics">
    <statistics-gauge :min="0" :max="100" :get-value-url="'<?= $widget->url_for('roomUsage') ?>'"></statistics-gauge>
</div>
<script>
    new Vue({
        el: '#whakamahere-statistics'
    });
</script>
