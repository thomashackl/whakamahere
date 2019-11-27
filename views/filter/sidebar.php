<div id="filters">
    <sidebar-filters :semesters='<?= studip_json_encode($semesters) ?>'
                     :selected-semester="'<?= $selectedSemester ?>'"
                     :institutes='<?= studip_json_encode($institutes) ?>'
                     :selected-institute="'<?= $selectedInstitute ?>'"></sidebar-filters>
</div>
<script>
    new Vue({
        el: '#filters'
    });
</script>
