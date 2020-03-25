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
    import { VueContext } from 'vue-context'
    var ContextClass = Vue.extend(VueContext)

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
            },
            pinSlotUrl: {
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
                        editable: this.courses[i].pinned == 0 ? true : false,
                        slotWeekday: this.courses[i].weekday,
                        slotStartTime: this.courses[i].start,
                        slotEndTime: this.courses[i].end,
                        lecturerId: this.courses[i].lecturer_id,
                        lecturerName: this.courses[i].lecturer,
                        turnout: this.courses[i].turnout,
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

                let slots = []
                occupied.map((one) => {
                    let date = ('0' + (lStart.getDate() + (one.weekday - 1))).slice(-2)
                    let day = lStart.getFullYear() + '-' + month + '-' + date

                    slots.push({
                        start: day + ' ' + one.start,
                        end: day + ' ' + one.end,
                        rendering: 'background',
                        color: one.free ? '#90ee90' : '#ff0000'
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
                return false
            },
            async pin(event, jsEvent) {
                fetch(this.pinSlotUrl + '/' + event.extendedProps.slotId)
                    .then(response => {
                        if (!response.ok) {
                            throw response
                        }
                        const editable = (event.startEditable && event.durationEditable) || event.editable
                        event.editable = !editable
                        const oldLabel = jsEvent.target.innerHTML
                        jsEvent.target.innerHTML = jsEvent.target.dataset.label2
                        jsEvent.target.setAttribute('data-label2', oldLabel)
                        bus.$emit('slot-pinned', event)
                    })
                    .catch(error => {
                        console.log(error)
                    })

                return false
            },
            renderEvent: function(info) {
                if (info.event.rendering != 'background') {
                    info.el.addEventListener('contextmenu', (event) => {
                        event.preventDefault()
                        this.showContextMenu(event, info.event)
                    }, true)
                }
            },
            showContextMenu(event, calendarEvent) {
                // Remove other open menus
                const oldMenu = document.querySelector('#whakamahere-context-menu')
                if (oldMenu != null) {
                    oldMenu.remove()
                }

                // The context menu itself
                let contextMenu = document.createElement('nav')
                contextMenu.id = 'whakamahere-context-menu'

                // Header with a title and a close icon
                let menuHeader = document.createElement('header')
                let closeAction = document.createElement('a')
                closeAction.addEventListener('click', () => {
                    contextMenu.remove()
                })
                menuHeader.appendChild(document.createTextNode('Aktionen'))
                let closeIcon = document.createElement('img')
                closeIcon.setAttribute('src', STUDIP.ASSETS_URL + 'images/icons/blue/decline.svg')
                closeAction.appendChild(closeIcon)
                menuHeader.appendChild(closeAction)
                contextMenu.appendChild(menuHeader)

                // Check if calendar event is editable
                const editable = (calendarEvent.startEditable && calendarEvent.durationEditable) ||
                    calendarEvent.editable

                // Define available menu items
                let menuItems = document.createElement('ul')
                const items = [
                    {
                        icon: 'room-request',
                        label: 'Raum auswählen',
                        click: (clickEvent) => {
                            clickEvent.preventDefault()
                            this.getAvailableRooms(calendarEvent.extendedProps.time_id)
                            contextMenu.remove()
                        }
                    },
                    {
                        icon: 'trash',
                        label: 'Aus der Planung entfernen',
                        click: (clickEvent) => {
                            clickEvent.preventDefault()
                            this.unplan(calendarEvent)
                            contextMenu.remove()
                        }
                    },
                    {
                        icon: 'place',
                        label: editable ? 'Anheften' : 'Lösen',
                        label2: editable ? 'Lösen' : 'Anheften',
                        click: (clickEvent) => {
                            clickEvent.preventDefault()
                            this.pin(calendarEvent, clickEvent)
                            contextMenu.remove()
                        }
                    }
                ]

                // Build given menu items as HTML elements.
                for (let i = 0; i < items.length; i++) {
                    let entry = document.createElement('li')
                    let anchor = document.createElement('a')
                    anchor.href = ''
                    if (items[i].click != null) {
                        anchor.addEventListener('click', items[i].click)
                    }
                    if (items[i].label2 != null) {
                        anchor.setAttribute('data-label2', items[i].label2)
                    }
                    let icon = document.createElement('img')
                    icon.width = 16
                    icon.height = 16
                    icon.src = STUDIP.ASSETS_URL + 'images/icons/blue/' + items[i].icon + '.svg'
                    anchor.appendChild(icon)
                    anchor.appendChild(document.createTextNode(items[i].label))
                    entry.appendChild(anchor)
                    menuItems.appendChild(entry)
                }
                // Adjust position so that the menu appears under the cursor.
                contextMenu.style.left = event.clientX + 'px'
                contextMenu.style.top = event.clientY + 'px'
                contextMenu.appendChild(menuItems)
                document.querySelector('#courseplan').appendChild(contextMenu)
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

    #whakamahere-context-menu {

        background-color: #ffffff;
        border: 1px solid #000000;
        font-size: 90%;
        margin: 0;
        position: absolute;
        z-index: 999;

        header {
            background-color: #d3dbe5;
            border-bottom: 1px solid #000000;
            padding: 3px;
            padding-left: 5px;

            img {
                float: right;
            }
        }

        ul {
            margin: 0;
            padding: 0;

            li {
                list-style-type: none;
                padding-bottom: 1px;
                padding-left: 5px;
                padding-right: 5px;
                padding-top: 1px;

                &:hover {
                    color: #ff0000;
                }
            }
        }
    }
</style>
