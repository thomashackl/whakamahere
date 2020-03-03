<div id="filters">
    <sidebar-filters :semesters='<?= studip_json_encode($semesters) ?>'
                     :selected-semester="'<?= $selectedSemester ?>'"
                     :institutes='<?= studip_json_encode($institutes) ?>'
                     :selected-institute="'<?= $selectedInstitute ?>'"
                     :lecturers='<?= studip_json_encode($lecturers) ?>'
                     :selected-lecturer="'<?= $selectedLecturer ?>'"
                     get-lecturers-url="<?= $controller->link_for('planning/lecturers') ?>"
                     :rooms='<?= studip_json_encode($rooms) ?>'
                     :selected-room="'<?= $selectedRoom ?>'"
                     store-selection-url="<?= $controller->link_for('filter/store_selection') ?>"></sidebar-filters>
</div>
<script>
    new Vue({
        el: '#filters'
    });
</script>
