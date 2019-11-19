<template>
    <div>
        <schedule :min-time="minTime" :max-time="maxTime" :locale="locale"
                  :weekends="weekends" :lecture-start="lectureStart"></schedule>
        <unplanned-course-list></unplanned-course-list>
    </div>
</template>

<script>
    export default {
        name: 'Courseplan',
        props: {
            locale: String,
            minTime: String,
            maxTime: String,
            weekends: Boolean,
            lectureStart: String
        }
    }
</script>
