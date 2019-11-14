<template>
    <FullCalendar default-view="timeGridWeek" :plugins="calendarPlugins"
                  :min-time="minTime" :max-time="maxTime" :all-day-slot="false"/>
</template>

<script>
import FullCalendar from '@fullcalendar/vue'
import timeGridWeekPlugin from '@fullcalendar/timegrid'

export default {
    components: {
        FullCalendar
    },
    props: {
        minTime: String,
        maxTime: String
    },
    data() {
        return {
            calendarPlugins: [ timeGridWeekPlugin ]
        }
    },
    mounted() {
        console.log('Showing events from ' + this.minTime + ' to '  + this.maxTime)
    }
}
</script>
