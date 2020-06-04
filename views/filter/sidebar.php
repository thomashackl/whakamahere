<div id="filters">
    <sidebar-filters :semesters='<?php echo  studip_json_encode($semesters) ?>'
                     :selected-semester="'<?php echo  $selectedSemester ?>'"
                     :searchterm="'<?php echo $searchterm ?>'"
                     :min-seats="<?php echo  $minSeats ?>" :max-seats="<?= $maxSeats ?>"
                     :institutes='<?php echo  studip_json_encode($institutes) ?>'
                     :selected-institute="'<?php echo  $selectedInstitute ?>'"
                     :lecturers='<?php echo  studip_json_encode($lecturers) ?>'
                     :selected-lecturer="'<?php echo  $selectedLecturer ?>'"
                     get-lecturers-url="<?php echo  $controller->link_for('planning/lecturers') ?>"
                     :rooms='<?php echo  studip_json_encode($rooms) ?>'
                     :selected-room="'<?php echo  $selectedRoom ?>'"
                     store-selection-url="<?php echo  $controller->link_for('filter/store_selection') ?>">
    </sidebar-filters>
</div>
<script>
    new Vue({
        el: '#filters'
    });
</script>
