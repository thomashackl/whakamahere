<div id="whakamahere-dashboard">
    <planning-dashboard :semester='<?php echo studip_json_encode($selectedSemester) ?>'
                        semester-status="<?php echo $semesterStatus ?>"
                        :all-status='<?php echo studip_json_encode($status) ?>'
                        :is-enabled="<?php echo $isEnabled ? 'true' : 'false' ?>"
                        :is-publishing-allowed="<?php echo $isPublishingAllowed ? 'true' : 'false' ?>"></planning-dashboard>
</div>
<script>
    new Vue({
        el: '#whakamahere-dashboard'
    });
</script>
