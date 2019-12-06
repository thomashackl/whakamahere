<template>
    <full-calendar ref="schedule" :plugins="calendarPlugins" default-view="timeGridWeek" :locale="locale"
                   droppable="true" :all-day-slot="false" :header="header" :weekends="weekends" :editable="true"
                   :column-header-format="columnHeaderFormat" week-number-calculation="ISO"
                   :min-time="minTime" :max-time="maxTime" :default-date="lectureStart"
                   @drop="dropCourse"></full-calendar>
</template>

<script>
    import bus from 'jsassets/bus'
    import FullCalendar from '@fullcalendar/vue'
    import interactionPlugin, { ThirdPartyDraggable } from '@fullcalendar/interaction'
    import timeGridWeekPlugin from '@fullcalendar/timegrid'
    import dragula from 'dragula'

    export default {
        name: 'Schedule',
        components: {
            FullCalendar
        },
        props: {
            locale: String,
            minTime: String,
            maxTime: String,
            weekends: Boolean,
            lectureStart: String
        },
        data() {
            return {
                calendarPlugins: [ interactionPlugin, timeGridWeekPlugin ],
                header: {
                    left: '',
                    center: '',
                    right: ''
                },
                columnHeaderFormat: {
                    weekday: 'long'
                }
            }
        },
        mounted() {
            // Adjust calendar height
            const start = this.$el.querySelector('.fc-view-container').getBoundingClientRect().top
            const end = this.$el.querySelector('.fc-divider').getBoundingClientRect().top
            this.$refs.schedule.height = end - start + 25

            const container = document.querySelector('#whakamahere-unplanned-courses table.default tbody')

            // Now connect dragula to fullcalendar
            new ThirdPartyDraggable(container, {
                itemSelector: '.course',
                mirrorSelector: '.gu-mirror',
                eventData: function(eventEl) {
                    let title = eventEl.dataset.courseName
                    if (eventEl.dataset.courseNumber != '') {
                        title = eventEl.dataset.courseNumber + ' ' + title
                    }
                    return {
                        id: eventEl.id,
                        title: title,
                        start: '10:00',
                        end: '12:00',
                        duration: {
                            hours: eventEl.dataset.courseDuration
                        }
                    }
                }
            })
        },
        methods: {
            dropCourse: function(el) {
                bus.$emit('drop-course', el.draggedEl)
            }
        }
    }
</script>

<style lang="scss">
    div.fc {
        div.fc-toolbar {
            display: none !important;
        }
    }
</style>
