<template>
    <full-calendar ref="schedule" :plugins="calendarPlugins" default-view="timeGridWeek" :locale="locale"
                   droppable="true" :all-day-slot="false" :header="header" :weekends="weekends" :editable="true"
                   :column-header-format="columnHeaderFormat" week-number-calculation="ISO" :events="events"
                   :min-time="minTime" :max-time="maxTime" :default-date="lectureStart" @eventReceive="dropCourse"/>
</template>

<script>
    import bus from 'jsassets/bus'
    import FullCalendar from '@fullcalendar/vue'
    import interactionPlugin, { ThirdPartyDraggable } from '@fullcalendar/interaction'
    import timeGridWeekPlugin from '@fullcalendar/timegrid'

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
            lectureStart: String,
            storeCourseUrl: String,
            courses: {
                type: Array,
                default: () => []
            }
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
                },
                drag: null
            }
        },
        computed: {
            events: function() {
                let entries = []

                for (let i = 0 ; i < this.courses.length ; i++) {
                    let title = this.courses[i].course_name;
                    if (this.courses[i].course_number != '') {
                        title = this.courses[i].course_number + ' ' + title
                    }

                    let lStart = new Date(this.lectureStart)
                    lStart.setDate(lStart.getDate() + (this.courses[i].weekday - 1))

                    let month = ('0' + (lStart.getMonth() + 1)).slice(-2)
                    let date = ('0' + lStart.getDate()).slice(-2)
                    let day = lStart.getFullYear() + '-' + month + '-' + date

                    entries.push({
                        id: this.courses[i].course_id,
                        title: title,
                        start: day + ' ' + this.courses[i].start,
                        end: day + ' ' + this.courses[i].end
                    })
                }

                return entries
            }
        },
        mounted() {
            // Adjust calendar height
            const start = this.$el.querySelector('.fc-view-container').getBoundingClientRect().top
            const end = this.$el.querySelector('.fc-divider').getBoundingClientRect().top
            this.$refs.schedule.height = end - start + 25

            bus.$on('update-courses', (value) => {
                this.drag.destroy()
                this.$nextTick(() => {
                    this.initDragAndDrop()
                })
            })

            this.initDragAndDrop()
        },
        methods: {
            dropCourse: function(el) {
                var formData = new FormData()
                formData.append('course', el.event.id)
                formData.append('start', this.formatDate(el.event.start))
                formData.append('end', this.formatDate(el.event.end))
                fetch(this.storeCourseUrl, {
                    method: 'post',
                    body: formData
                }).then((response) => {
                    if (response.status == 200) {
                        bus.$emit('drop-course', el.draggedEl)
                    } else {
                        console.log('Date could not be saved.')
                        console.log(response)
                    }
                })
            },
            initDragAndDrop: function() {
                const container = document.querySelector('#whakamahere-unplanned-courses table.default tbody')

                // Now connect dragula to fullcalendar
                this.drag = new ThirdPartyDraggable(container, {
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
            formatDate: function(date) {
                const options = {
                    year: 'numeric',
                    month: 'numeric',
                    day: 'numeric',
                    hour: 'numeric',
                    minute: 'numeric',
                    hour12: false
                }
                return new Intl.DateTimeFormat('de-DE', options).format(date)
            }
        },
        watch: {
            events: function(val) {
                console.log('Events changed:')
                console.log(val)
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
