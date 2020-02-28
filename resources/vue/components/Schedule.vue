<template>
    <full-calendar ref="schedule" :plugins="calendarPlugins" default-view="timeGridWeek" :locale="locale"
                   droppable="true" :all-day-slot="false" :header="header" :weekends="weekends" :editable="true"
                   :column-header-format="columnHeaderFormat" week-number-calculation="ISO" :events="events"
                   :min-time="minTime" :max-time="maxTime" :default-date="lectureStart"
                   @eventReceive="dropCourse" @eventDragStart="markAvailableSlots" @eventDragStop="dropCourse"
                   :eventRender="renderEvent"/>
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
            locale: {
                type: String,
                default: 'de-de'
            },
            minTime: {
                type: String,
                default: ''
            },
            maxTime: {
                type: String,
                default: ''
            },
            weekends: {
                type: Boolean,
                default: false
            },
            lectureStart: {
                type: String,
                default: ''
            },
            courses: {
                type: Array,
                default: () => []
            },
            getSlotAvailabilityUrl: {
                type: String,
                default: ''
            },
            unplanSlotUrl: {
                type: String,
                default: ''
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
                        source: 'database',
                        id: this.courses[i].course_id + '-' + this.courses[i].slot_id,
                        title: title + '\n' + this.courses[i].lecturer,
                        start: day + ' ' + this.courses[i].start,
                        end: day + ' ' + this.courses[i].end,
                        courseId: this.courses[i].course_id,
                        slotId: this.courses[i].slot_id,
                        editable: this.courses[i].pinned == 1 ? false : true,
                        slotWeekday: this.courses[i].weekday,
                        slotStartTime: this.courses[i].start,
                        slotEndTime: this.courses[i].end,
                        lecturerId: this.courses[i].lecturer_id,
                        lecturerName: this.courses[i].lecturer
                    })
                }

                return entries
            }
        },
        mounted() {
            // Adjust calendar height
            const start = this.$el.querySelector('.fc-view-container').getBoundingClientRect().top
            const end = this.$el.querySelector('.fc-divider').getBoundingClientRect().top
            this.$refs.schedule.height = end - start
            document.getElementsByClassName('fc')[0].style.maxHeight = (end - start) + 'px'

            // Re-initialize drag & drop after courses changed
            bus.$on('updated-courses', (value) => {
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
                    this.markAvailableSlots(data)
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
                bus.$emit('save-course', el)
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
                                minutes: eventEl.dataset.duration
                            }
                        }
                    }
                })
            },
            // Mark slots where a course can or cannot be dropped.
            async markAvailableSlots(info) {
                // The virtual begin of our semester view - place dates there.
                let lStart = new Date(this.lectureStart)

                let month = ('0' + (lStart.getMonth() + 1)).slice(-2)

                if (info.event != null) {
                    var lecturerId = info.event.extendedProps.lecturerId
                } else {
                    var lecturerId = info.lecturerId
                }

                // Check availability info for slot lecturer.
                if (lecturerId != '') {
                    const response = await fetch(this.getSlotAvailabilityUrl + '/' + lecturerId)
                    var occupied = await response.json()
                }

                /*for (let weekday = 0 ; weekday < 5 ; weekday++) {
                    let date = ('0' + (lStart.getDate() + weekday)).slice(-2)
                    let day = lStart.getFullYear() + '-' + month + '-' + date

                    for (let hour = 8; hour < 22; hour += 2) {

                        let isOccupied = false
                        this.slots.push({
                            start: day + ' ' + ('0' + hour).slice(-2) + ':00',
                            end: day + ' ' + ('0' + (hour + 2)).slice(-2) + ':00',
                            rendering: 'background'
                        })
                    }
                }*/

                let slots = []
                occupied.map((one) => {
                    let date = ('0' + (lStart.getDate() + (one.weekday - 1))).slice(-2)
                    let day = lStart.getFullYear() + '-' + month + '-' + date

                    slots.push({
                        start: day + ' ' + one.start,
                        end: day + ' ' + one.end,
                        rendering: 'background',
                        color: '#ff0000'
                    })
                })

                let source = {
                    id: 'availability',
                    events: slots
                }

                this.$refs.schedule.getApi().addEventSource(source)
            },
            unmarkAvailableSlots: function() {
                const source = this.$refs.schedule.getApi().getEventSourceById('availability')
                if (source != null) {
                    source.remove()
                }
            },
            async unplan(event) {
                fetch(this.unplanSlotUrl + '/' + event.extendedProps.slotId)
                    .then(response => {
                        if (!response.ok) {
                            throw response
                        }
                        bus.$emit('remove-planned-course', event.extendedProps.slotId)
                        bus.$emit('add-unplanned-course', event)
                    })
                    .catch(error => {
                        console.log(error)
                    })
            },
            pin: function(event) {
                event.editable = false
            },
            renderEvent: function(info) {
                if (info.event.rendering != 'background' &&
                        info.el.querySelector('.whakamahere-event-actions') == null) {
                    let actions = document.createElement('span')
                    actions.classList.add('whakamahere-event-actions')

                    let remove = document.createElement('img')
                    remove.setAttribute('src', STUDIP.ASSETS_URL + 'images/icons/white/decline.svg')
                    remove.setAttribute('width', '16')
                    remove.addEventListener('click', (event) => {
                        this.unplan(info.event)
                    })
                    actions.appendChild(remove)

                    let pin = document.createElement('img')
                    pin.setAttribute('src', STUDIP.ASSETS_URL + 'images/icons/white/exclaim.svg')
                    pin.setAttribute('width', '16')
                    pin.addEventListener('click', (event) => {
                        this.pin(info.event)
                    })
                    actions.appendChild(pin)

                    info.el.querySelector('.fc-time').appendChild(actions)
                }
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

                .whakamahere-event-actions {
                    float: right;
                }
            }
        }
    }
</style>
