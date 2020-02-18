<div id="whakamahere-courseplan">
    <courseplan :min-time="'<?= $minTime ?>'" :max-time="'<?= $maxTime ?>'" :locale="'<?= $locale ?>'"
                :weekends="<?= $weekends ?>" :lecture-start="'<?= $semesterStart->format('Y-m-d') ?>'"
                :get-unplanned-courses-url="'<?= $controller->link_for('planning/unplanned_courses') ?>'"
                :get-planned-courses-url="'<?= $controller->link_for('planning/planned_courses') ?>'"
                :store-course-url="'<?= $controller->link_for('planning/store_course') ?>'"
                :unplanned-courses='<?= studip_json_encode($unplanned_courses) ?>'
                :planned-courses='<?= studip_json_encode($planned_courses) ?>'
                :semester="'<?= $selectedSemester ?>'" :institute="'<?= $selectedInstitute ?>'"/>
</div>
<script>
    new Vue({
        el: '#whakamahere-courseplan'
    });
</script>
