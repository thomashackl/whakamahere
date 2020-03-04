<div id="whakamahere-courseplan">
    <courseplan :min-time="'<?= $minTime ?>'" :max-time="'<?= $maxTime ?>'" :locale="'<?= $locale ?>'"
                :weekends="<?= $weekends ?>" :lecture-start="'<?= $semesterStart->format('Y-m-d') ?>'"
                :get-unplanned-courses-url="'<?= $controller->link_for('planning/unplanned_courses') ?>'"
                :get-planned-courses-url="'<?= $controller->link_for('planning/planned_courses') ?>'"
                :store-course-url="'<?= $controller->link_for('planning/store_course') ?>'"
                :get-slot-availability-url="'<?= $controller->link_for('planning/slot_availability') ?>'"
                :unplan-slot-url="'<?= $controller->link_for('planning/unplan') ?>'"
                :unplanned-courses='<?= studip_json_encode($unplanned_courses) ?>'
                :planned-courses='<?= studip_json_encode($planned_courses) ?>'
                :semester="'<?= $selectedSemester ?>'" :institute="'<?= $selectedInstitute ?>'"
                :lecturer="'<?= $selectedLecturer ?>'" :min-seats="<?= $minSeats ?>" :max-seats="<?= $maxSeats ?>"/>
</div>
<script>
    new Vue({
        el: '#whakamahere-courseplan'
    });
</script>
