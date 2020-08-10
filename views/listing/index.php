<div id="whakamahere-courselisting">
    <course-listing semester="<?php echo $semester->name ?>"
                    :institutes='<?php echo json_encode($institutes, JSON_HEX_APOS) ?>'
                    :courses='<?php echo json_encode($courses, JSON_HEX_APOS) ?>'
                    :total="<?php echo $total ?>"></course-listing>
</div>
<script>
    new Vue({
        el: '#whakamahere-courselisting'
    });
</script>
