<template>
    <full-calendar ref="schedule" :plugins="calendarPlugins" default-view="timeGridWeek" :locale="locale"
                   droppable="true" :all-day-slot="false" :header="header" :weekends="weekends" :editable="true"
                   :column-header-format="columnHeaderFormat" week-number-calculation="ISO" :events="events"
                   :min-time="minTime" :max-time="maxTime" :default-date="lectureStart"
                   @eventReceive="dropCourse" @eventDragStart="markAvailableSlots"/>
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
                drag: null,
                slots: []
            }
        },
        computed: {
            /*
             * Re-structure the given course data: we need a "dummy" day
             * for displaying the dates in the calendar view, and attributes
             * must be named so that FullCalendar understands them.
             */
            events: function() {
                let entries = []

                for (let i = 0 ; i < this.courses.length ; i++) {
                    let title = this.courses[i].course_name;
                    if (this.courses[i].course_number != '') {
                        title = this.courses[i].course_number + ' ' + title
                    }

                    // The virtual begin of our semester view - place dates there.
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

                return entries.concat(this.slots)
            }
        },
        mounted() {
            // Adjust calendar height
            const start = this.$el.querySelector('.fc-view-container').getBoundingClientRect().top
            const end = this.$el.querySelector('.fc-divider').getBoundingClientRect().top
            this.$refs.schedule.height = end - start
            document.getElementsByClassName('fc')[0].style.maxHeight = (end - start) + 'px'

            // Re-initialize drag & drop after courses changed
            bus.$on('update-courses', (value) => {
                this.drag.destroy()

                /*
                  * We need to do this in the next tick, as only then the HTML
                  * elements will have been rendered.
                  */
                this.$nextTick(() => {
                    this.initDragAndDrop()
                })
            })

            // Set drag element width to day column width.
            bus.$on('start-drag-course', (data) => {
                this.$nextTick(() => {
                    document.getElementsByClassName('gu-mirror')[0].style.width =
                        document.getElementsByClassName('fc-day-header')[0].offsetWidth + 'px'
                    this.markAvailableSlots()
                })
            })
            // Unmark slots on drag cancel event
            bus.$on('cancel-drag-course', (data) => {
                this.unmarkAvailableSlots()
            })

            this.initDragAndDrop()

        },
        methods: {
            // When a course is dropped, we store the time assignment to database.
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
                this.unmarkAvailableSlots()
            },
            // Initialize the drag & drop functionality with Dragula and FullCalendar.
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
            // Mark slots where a course can or cannot be dropped.
            markAvailableSlots: function(info) {
                // The virtual begin of our semester view - place dates there.
                let lStart = new Date(this.lectureStart)

                let month = ('0' + (lStart.getMonth() + 1)).slice(-2)

                for (let weekday = 0 ; weekday < 5 ; weekday++) {
                    let date = ('0' + (lStart.getDate() + weekday)).slice(-2)
                    let day = lStart.getFullYear() + '-' + month + '-' + date

                    for (let hour = 8; hour < 22; hour += 2) {
                        this.slots.push({
                            start: day + ' ' + ('0' + hour).slice(-2) + ':00',
                            end: day + ' ' + ('0' + (hour + 2)).slice(-2) + ':00',
                            rendering: 'background'
                        })
                    }
                }
            },
            unmarkAvailableSlots: function() {
                this.slots = []
            },
            // Format a given date object according to German locale.
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
        }
    }
</script>

<style lang="scss">
    div.fc {
        overflow: hidden;

        div.fc-toolbar {
            display: none !important;
        }

        .fc-event {
            background-color: #28487c;
            color: #ffffff;
            display: inline-block !important;

            .fc-time {
                background-color: #3f72b4;
            }
        }
    }
</style>
