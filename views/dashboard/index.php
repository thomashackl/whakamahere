<div id="whakamahere-dashboard">
    <planning-dashboard :semester='<?php echo studip_json_encode($selectedSemester) ?>'
                        semester-status="<?php echo $semesterStatus ?>"
                        :all-status='<?php echo studip_json_encode($status) ?>'></planning-dashboard>
</div>
<script>
    new Vue({
        el: '#whakamahere-dashboard'
    });
</script>
