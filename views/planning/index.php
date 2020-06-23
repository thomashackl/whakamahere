<div id="whakamahere-courseplan">
    <courseplan :min-time="'<?php echo $minTime ?>'" :max-time="'<?php echo $maxTime ?>'"
                :locale="'<?php echo $locale ?>'" mode="<?php echo $view ?>" :show-weekends="<?php echo $weekends ?>"
                lecture-start="<?php echo $semesterStart->format('Y-m-d') ?>"
                :semester-weeks='<?php echo json_encode($weeks) ?>'
                <?php if ($selectedSemester) : ?>
                :semester="'<?php echo $selectedSemester ?>'"
                <?php endif ?>
                <?php if ($searchterm) : ?>
                :searchterm="'<?php echo $searchterm ?>'"
                <?php endif ?>
                <?php if ($minSeats) : ?>
                    :min-seats="<?php echo $minSeats ?>"
                <?php endif ?>
                <?php if ($maxSeats) : ?>
                    :max-seats="<?php echo $maxSeats ?>"
                <?php endif ?>
                <?php if ($selectedInstitute) : ?>
                :institute="'<?php echo $selectedInstitute ?>'"
                <?php endif ?>
                <?php if ($selectedLecturer) : ?>
                :lecturer="'<?php echo $selectedLecturer ?>'"
                <?php endif ?>
                <?php if ($selectedRoom) : ?>
                :room="'<?php echo $selectedRoom ?>'"
                <?php endif ?>
                <?php if ($selectedWeek) : ?>
                :selected-week="'<?php echo $selectedWeek ?>'"
                <?php endif ?>
    ></courseplan>
</div>
<script>
    new Vue({
        el: '#whakamahere-courseplan'
    });
</script>
