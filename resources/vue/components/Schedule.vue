<template>
    <full-calendar ref="schedule" :plugins="calendarPlugins" default-view="timeGridWeek" :locale="locale"
                   droppable="true" :all-day-slot="false" :headers="headers"
                   :column-header-format="columnHeaderFormat" week-number-calculation="ISO"
                   :min-time="minTime" :max-time="maxTime" :default-date="lectureStart"
                   @eventReceive="dropCourse"></full-calendar>
</template>

<script>
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

            const container = document.getElementById('whakamahere-unplanned-courses')

            let drake = dragula({
                containers: [ container ],
                copy: true
            });
            new ThirdPartyDraggable(container, {
                itemSelector: '.course',
                mirrorSelector: '.gu-mirror',
                eventData: function(eventEl) {
                    return {
                        title: eventEl.innerText
                    }
                }
            })
        },
        methods: {
            dropCourse(info) {
                console.log(info)
                const source = document.getElementById(info.draggedEl.id)
                source.parentNode.removeChild(source)
            }
        }
    }
</script>
