<div id="whakamahere-dashboard">
    <planning-dashboard :semester='<?php echo studip_json_encode($selectedSemester) ?>'></planning-dashboard>
</div>
<script>
    new Vue({
        el: '#whakamahere-dashboard'
    });
</script>
