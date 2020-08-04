<div id="whakamahere-log-view">
    <publish-log-viewer semester="<?php echo $semester ?>"
                        :entries='<?php echo studip_json_encode($entries) ?>'
                        :total="<?php echo $total ?>"></publish-log-viewer>
</div>
<script>
    new Vue({
        el: '#whakamahere-log-view'
    });
</script>
