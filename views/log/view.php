<div id="whakamahere-log-view">
    <publish-log-viewer semester="<?php echo $semester ?>"
                        :entries='<?php echo json_encode($entries, JSON_HEX_APOS) ?>'
                        :total="<?php echo $total ?>"></publish-log-viewer>
</div>
<script>
    new Vue({
        el: '#whakamahere-log-view'
    });
</script>
