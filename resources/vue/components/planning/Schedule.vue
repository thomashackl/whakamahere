<template>
    <full-calendar ref="schedule" :plugins="calendarPlugins" default-view="timeGridWeek" :locale="locale"
                   droppable="true" :all-day-slot="false" :header="header" :weekends="weekends" :editable="true"
                   :column-header-format="columnHeaderFormat" week-number-calculation="ISO" :events="events"
                   :min-time="minTime" :max-time="maxTime" :default-date="lectureStart"
                   :valid-range="validRange" time-zone="local" :eventRender="renderEvent"
                   @eventReceive="dropCourse" @eventDragStart="markAvailableSlots" :custom-buttons="viewButtons"
                   @eventDragStop="this.unmarkAvailableSlots" @eventDrop="dropCourse" @eventResize="dropCourse"/>
</template>

<script>
    import bus from 'jsassets/bus'
    import { globalfunctions } from '../mixins/globalfunctions'
    import FullCalendar from '@fullcalendar/vue'
    import interactionPlugin, { ThirdPartyDraggable } from '@fullcalendar/interaction'
    import timeGridWeekPlugin from '@fullcalendar/timegrid'
    import timeGridDayPlugin from '@fullcalendar/timegrid'
    import RoomProposals from './RoomProposals'
    var RoomProposalsClass = Vue.extend(RoomProposals)
    import SlotDetails from './SlotDetails'
    var SlotDetailsClass = Vue.extend(SlotDetails)

    export default {
        name: 'Schedule',
        components: {
            FullCalendar
        },
        mixins: [
            globalfunctions
        ],
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
            }
        },
        data() {
            return {
                calendarPlugins: [ interactionPlugin, timeGridWeekPlugin, timeGridDayPlugin ],
                header: {
                    left: 'dayViewButton',
                    center: '',
                    right: ''
                },
                columnHeaderFormat: {
                    weekday: 'long'
                },
                drag: null,
                viewButtons: {
                    weekViewButton: {
                        text: 'Woche',
                        click: () => {
                            this.header = {
                                left: 'dayViewButton',
                                center: '',
                                right: ''
                            }
                            this.$refs.schedule.getApi().changeView('timeGridWeek')
                        }
                    },
                    dayViewButton: {
                        text: 'Tag',
                        click: () => {
                            this.header = {
                                left: 'weekViewButton',
                                center: '',
                                right: 'prev next'
                            }
                            this.$refs.schedule.getApi().changeView('timeGridDay')
                        }
                    }
                }
            }
        },
        computed: {
            /*
             * Calculate a valid date range for the current calendar view
             */
            validRange: function() {
                const start = this.lectureStart
                let end = new Date(this.lectureStart)
                end.setDate(end.getDate() + (this.weekend ? 6 : 4))

                const range = {
                    start: start + ' 00:00:00',
                    end: end.getFullYear() + '-' + (end.getMonth() + 1) + '-' + end.getDate() + ' 23:59:59'
                }

                return range
            },
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
                        bookings: this.courses[i].bookings,
                        courseId: this.courses[i].course_id,
                        courseName: this.courses[i].course_name,
                        courseNumber: this.courses[i].course_number,
                        editable: this.courses[i].pinned == 0 ? true : false,
                        end: new Date(day + ' ' + this.courses[i].end),
                        id: this.courses[i].course_id + '-' + this.courses[i].slot_id,
                        lecturerId: this.courses[i].lecturer_id,
                        lecturerName: this.courses[i].lecturer,
                        partial: this.courses[i].partial_bookings,
                        pinned: this.courses[i].pinned == 0 ? false : true,
                        rooms: this.courses[i].rooms,
                        slotEndTime: this.courses[i].end,
                        slotId: this.courses[i].slot_id,
                        slotStartTime: this.courses[i].start,
                        slotWeekday: this.courses[i].weekday,
                        source: 'database',
                        start: new Date(day + ' ' + this.courses[i].start),
                        timeId: this.courses[i].time_id,
                        title: title += '\n' + this.courses[i].lecturer,
                        turnout: this.courses[i].turnout
                    })
                }

                return entries
            }
        },
        mounted() {
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

            /*
             * Some trick to defeat the nasty error about "isWithinClipping".
             */
            this.$refs.schedule.getApi().prev()
            this.$nextTick(() => {
                this.$refs.schedule.getApi().prev()
            })
        },
        methods: {
            // When a course is dropped, we store the time assignment to database.
            dropCourse: function(info) {
                bus.$emit('save-course', info)
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
                            },
                            course_id: eventEl.dataset.courseId,
                            course_number: eventEl.dataset.courseNumber,
                            course_name: eventEl.dataset.courseName,
                            slot_id: eventEl.dataset.slotId,
                            bookings: [],
                            turnout: eventEl.dataset.turnout,
                            lecturer: eventEl.dataset.lecturer,
                            lecturer_id: eventEl.dataset.lecturerId,
                            rooms: eventEl.dataset.rooms
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
                occupied = []
                if (lecturerId != '') {
                    const response = await fetch(STUDIP.URLHelper.getURL(
                        this.$pluginBase + '/slot/availability/' + lecturerId))
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
                fetch(STUDIP.URLHelper.getURL(this.$pluginBase + '/slot/unplan/' + event.extendedProps.slotId))
                    .then(response => {
                        if (!response.ok) {
                            throw response
                        }
                        bus.$emit('remove-planned-course', event.extendedProps.slotId)
                        bus.$emit('add-unplanned-course', event)
                    })
                    .catch((error) => {
                        this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                    })
                return false
            },
            async pin(event, jsEvent) {
                fetch(STUDIP.URLHelper.getURL(this.$pluginBase + '/slot/setpin/' + event.extendedProps.slotId))
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
                    }).catch((error) => {
                        this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                    })
            },
            async showDetails(event, jsEvent) {
                fetch(STUDIP.URLHelper.getURL(this.$pluginBase + '/slot/details/' + event.extendedProps.slotId))
                    .then(response => {
                        if (!response.ok) {
                            throw response
                        }
                        response.json().then((json) => {
                            const details = new SlotDetailsClass({
                                propsData: {
                                    details: json
                                }
                            })
                            details.$mount()
                            STUDIP.Dialog.show(details.$el, {
                                height: window.offsetHeight * 0.8,
                                width: window.offsetWidth * 0.8,
                                title: 'Details'
                            })
                        })
                    }).catch((error) => {
                        this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                    })

                return false
            },
            async removeBookings(event, jsEvent) {
                fetch(STUDIP.URLHelper.getURL(this.$pluginBase + '/slot/remove_bookings/' + event.extendedProps.timeId))
                    .then(response => {
                        if (!response.ok) {
                            throw response
                        }
                        response.json().then((json) => {
                            bus.$emit('updated-course', json)
                        })
                    }).catch((error) => {
                        this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                    })

                return false
            },
            getRoomProposals: function(timeId) {
                const proposals = new RoomProposalsClass({
                    propsData: {
                        timeId: timeId
                    }
                })
                proposals.$mount()
                STUDIP.Dialog.show(proposals.$el, {
                    height: 450,
                    width: window.offsetWidth * 0.8,
                    title: 'Raumvorschläge'
                })
            },
            renderEvent: function(info) {
                if (info.event.rendering != 'background') {

                    // Add room names to time row if applicable
                    if (info.event.extendedProps.rooms != '') {
                        const timeDiv = info.el.querySelector('div.fc-time')
                        timeDiv.innerHTML = timeDiv.innerHTML + ' (' + info.event.extendedProps.rooms + ')'
                    }

                    // Mark events that have no room bookings yet.
                    if (info.event.extendedProps.bookings.length == 0) {
                        info.el.classList.remove('partially-booked')
                        info.el.classList.add('no-room')
                    } else {

                        // Extra mark for partially booked events.
                        if (info.event.extendedProps.partial) {
                            info.el.classList.add('partially-booked')
                        }
                        info.el.classList.remove('no-room')
                    }

                    // Mark pinned events.
                    if (info.event.extendedProps.pinned) {
                        info.el.classList.add('pinned')
                    } else {
                        info.el.classList.remove('pinned')
                    }

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
                const items = []
                items.push({
                    icon: 'room-request',
                    label: 'Raum auswählen',
                    click: (clickEvent) => {
                        clickEvent.preventDefault()
                        this.getRoomProposals(calendarEvent.extendedProps.timeId)
                        contextMenu.remove()
                    }
                })

                if (calendarEvent.extendedProps.bookings.length > 0) {
                    items.push({
                        icon: 'room-occupied',
                        label: 'Raumbuchung entfernen',
                        click: (clickEvent) => {
                            clickEvent.preventDefault()
                            this.removeBookings(calendarEvent)
                            contextMenu.remove()
                        }
                    })
                }

                items.push({
                    icon: 'trash',
                    label: 'Aus der Planung entfernen',
                    click: (clickEvent) => {
                        clickEvent.preventDefault()
                        this.unplan(calendarEvent)
                        contextMenu.remove()
                    }
                })
                items.push({
                    icon: 'place',
                    label: editable ? 'Anheften' : 'Lösen',
                    label2: editable ? 'Lösen' : 'Anheften',
                    click: (clickEvent) => {
                        clickEvent.preventDefault()
                        this.pin(calendarEvent, clickEvent)
                        contextMenu.remove()
                    }
                })
                items.push({
                    icon: 'info',
                    label: 'Details',
                    click: (clickEvent) => {
                        clickEvent.preventDefault()
                        this.showDetails(calendarEvent, clickEvent)
                        contextMenu.remove()
                    }
                })

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
                document.querySelector('body').appendChild(contextMenu)
            }
        }
    }
</script>

<style lang="scss">
    body {
        div.fc {
            font-size: 11px;
            overflow: hidden;

            .fc-event {
                background-color: #28497c;
                color: #ffffff;
                display: inline-block !important;

                .fc-time {
                    background-color: #3f72b4;
                }

                &.no-room {
                    .fc-time {
                        background-color: #d60000;
                    }
                }

                &.partially-booked {
                    .fc-time {
                        background-color: #ffbd33;
                        color: #28497c;
                    }
                }

                &.pinned {
                    background-color: #a9b6cb;
                }
            }

            a {
            }
        }

        #whakamahere-context-menu {

            background-color: #ffffff;
            border: 1px solid #000000;
            font-size: 90%;
            margin: 0;
            overflow: hidden;
            position: absolute;
            z-index: 997;

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

                    &:not(:last-child) {
                        margin-bottom: 3px;
                    }

                    a {
                        img, svg {
                            vertical-align: text-bottom;
                        }
                    }
                }
            }
        }
    }
</style>
