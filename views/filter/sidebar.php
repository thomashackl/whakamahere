<div id="filters">
    <sidebar-filters :semesters='<?= studip_json_encode($semesters) ?>'
                     :selected-semester="'<?= $selectedSemester ?>'"
                     :institutes='<?= studip_json_encode($institutes) ?>'
                     :selected-institute="'<?= $selectedInstitute ?>'"
                     :rooms='<?= studip_json_encode($rooms) ?>'
                     :selected-room="'<?= $selectedRoom ?>'"></sidebar-filters>
</div>
<script>
    new Vue({
        el: '#filters'
    });
</script>
