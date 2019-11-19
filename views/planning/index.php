<div id="courseplan">
    <courseplan :min-time="'<?= $minTime ?>'" :max-time="'<?= $maxTime ?>'" :locale="'<?= $locale ?>'"
                :weekends="<?= $weekends ?>" :lecture-start="'<?= $semesterStart->format('Y-m-d') ?>'"></courseplan>
</div>
<script>
    new Vue({
        el: '#courseplan'
    });
</script>
