<div id="schedule">
    <schedule :min-time="'<?= Config::get()->WHAKAMAHERE_PLANNING_START_HOUR ?>'"
              :max-time="'<?= Config::get()->WHAKAMAHERE_PLANNING_END_HOUR ?>'"></schedule>
</div>
<script>
    new Vue({
        el: '#schedule'
    });
</script>
