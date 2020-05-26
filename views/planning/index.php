<div id="whakamahere-courseplan">
    <courseplan :min-time="'<?= $minTime ?>'" :max-time="'<?= $maxTime ?>'" :locale="'<?= $locale ?>'"
                :weekends="<?= $weekends ?>" :lecture-start="'<?= $semesterStart->format('Y-m-d') ?>'"
                <?php if ($selectedSemester) : ?>
                :semester="'<?= $selectedSemester ?>'"
                <?php endif ?>
                <?php if ($selectedInstitute) : ?>
                :institute="'<?= $selectedInstitute ?>'"
                <?php endif ?>
                <?php if ($selectedLecturer) : ?>
                :lecturer="'<?= $selectedLecturer ?>'"
                <?php endif ?>
                <?php if ($minSeats) : ?>
                :min-seats="<?= $minSeats ?>"
                <?php endif ?>
                <?php if ($maxSeats) : ?>
                :max-seats="<?= $maxSeats ?>"
                <?php endif ?>
    ></courseplan>
</div>
<script>
    new Vue({
        el: '#whakamahere-courseplan'
    });
</script>
