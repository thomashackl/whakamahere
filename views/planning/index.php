<div id="courseplan">
    <courseplan :min-time="'<?= $minTime ?>'" :max-time="'<?= $maxTime ?>'" :locale="'<?= $locale ?>'"
                :weekends="<?= $weekends ?>" :lecture-start="'<?= $semesterStart->format('Y-m-d') ?>'"
                :get-courses-url="'<?= $controller->link_for('planning/courses') ?>'"
                :courses='<?= studip_json_encode($courses) ?>'
                :semester="'<?= $selectedSemester ?>'" :institute="'<?= $selectedInstitute ?>'"></courseplan>
</div>
<script>
    new Vue({
        el: '#courseplan'
    });
</script>
