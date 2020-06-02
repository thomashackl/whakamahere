<template>
    <div id="courseplan">
        <studip-loading-indicator :is-loading="loadingPlanned || loadingUnplanned" :width="128" :height="128"
                                  reference-element="#whakamahere-courseplan"/>
        <schedule :min-time="minTime" :max-time="maxTime" :locale="locale"
                  :weekends="weekends" :lecture-start="lectureStart"
                  :courses="plannedCourseList" :institute="institute"></schedule>
        <unplanned-courses-list :courses="unplannedCourseList" :lectureStart="lectureStart"></unplanned-courses-list>
    </div>
</template>

<script>
    import bus from 'jsassets/bus'
    import { globalfunctions } from './mixins/globalfunctions'

    export default {
        name: 'Courseplan',
        mixins: [
            globalfunctions
        ],
        props: {
            locale: {
                type: String,
                default: 'de'
            },
            minTime: {
                type: String,
                default: '08:00'
            },
            maxTime: {
                type: String,
                default: '22:00'
            },
            weekends: {
                type: Boolean,
                default: false
            },
            lectureStart: {
                type: String,
                default: ''
            },
            plannedCourses: {
                type: Array,
                default: () => []
            },
            unplannedCourses: {
                type: Array,
                default: () => []
            },
            semester: {
                type: String,
                default: ''
            },
            institute: {
                type: String,
                default: ''
            },
            lecturer: {
                type: String,
                default: ''
            },
            minSeats: {
                type: Number,
                default: 0
            },
            maxSeats: {
                type: Number,
                default: 0
            }
        },
        data() {
            return {
                plannedCourseList: this.plannedCourses,
                unplannedCourseList: this.unplannedCourses,
                loadingPlanned: false,
                loadingUnplanned: false,
                theSemester: this.semester,
                theInstitute: this.institute,
                theLecturer: this.lecturer,
                theMinSeats: this.minSeats,
                theMaxSeats: this.maxSeats
            }
        },
        mounted() {
            this.updateData()
            // Catch event for changed semester in sidebar
            bus.$on('updated-semester', (element) => {
                this.theSemester = element.value
                this.updateData()
            })
            // Catch event for changed institute in sidebar
            bus.$on('updated-institute', (value) => {
                this.theInstitute = value
                this.updateData()
            })
            // Catch event for changed lecturer in sidebar
            bus.$on('updated-lecturer', (value) => {
                this.theLecturer = value
                this.updateData()
            })
            // Catch event for changed seats limits in sidebar
            bus.$on('updated-seats', (value) => {
                const seats = JSON.parse(value)
                this.theMinSeats = seats.min
                this.theMaxSeats = seats.max
                this.updateData()
            })

            // Catch event for unplanning an already planned course
            bus.$on('remove-planned-course', (slotId) => {
                this.plannedCourseList = this.plannedCourseList.filter(course => course.slot_id !== slotId)
                bus.$emit('updated-planned-courses')
            })

            // Catch event for adding a new course to unplanned list
            bus.$on('add-unplanned-course', (event) => {
                this.loadingUnplanned = true;
                this.getUnplannedCourses()
                bus.$emit('updated-unplanned-courses')
            })

            // Saved course data to database
            bus.$on('save-course', (course) => {
                this.saveCourse(course)
            })

            // Pinned or unpinned a slot
            bus.$on('slot-pinned', (slot) => {
                this.plannedCourseList.map((course) => {
                    if (course.slot_id == slot.extendedProps.slotId) {
                        course.pinned = slot.editable ? false : true
                    }
                })
            })

            // Booked a room for a slot
            bus.$on('room-booked', (data) => {
                this.plannedCourseList.map((course) => {
                    if (course.slot_id == data.slot) {
                        course.rooms = data.roomData.room_names
                        course.bookings = data.roomData.booked
                    }
                })
            })

            // Updated a single course
            bus.$on('updated-course', (course) => {
                this._data.plannedCourseList =
                    this._data.plannedCourseList.filter((one) => one.slot_id != course.slot_id)
                this._data.plannedCourseList.push(course)
            })

        },
        methods: {
            updateData() {
                this.plannedCourseList = []
                this.unplannedCourseList = []
                if (this.theMinSeats != 0 || this.theMaxSeats != 0 ||
                        this.theInstitute != '' || this.theLecturer != '' || this.theRoom != '') {
                    this.getPlannedCourses()
                    this.getUnplannedCourses()
                }
            },
            async getUnplannedCourses() {
                this.loadingUnplanned = true
                let formData = new FormData()
                formData.append('semester', this.theSemester)

                if (this.theInstitute != '') {
                    formData.append('institute', this.theInstitute)
                }

                if (this.theLecturer != '') {
                    formData.append('lecturer', this.theLecturer)
                }

                if (this.theMinSeats != 0 || this.theMaxSeats != 0) {
                    let seats = {}
                    if (this.theMinSeats != 0) {
                        seats.min = this.theMinSeats
                    }
                    if (this.theMaxSeats != 0) {
                        seats.max = this.theMaxSeats
                    }
                    formData.append('seats', JSON.stringify(seats))
                }

                const response = await fetch(
                    STUDIP.URLHelper.getURL(this.$pluginBase + '/planning/unplanned_courses'), {
                    method: 'post',
                    body: formData
                }).then((response) => {
                    if (!response.ok) {
                        throw response
                    }
                    response.json()
                        .then((json) => {
                            this.unplannedCourseList = json
                            this.loadingUnplanned = false;
                            bus.$emit('updated-unplanned-courses')
                        })
                }).catch((error) => {
                    this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                })
            },
            async getPlannedCourses() {
                this.loadingPlanned = true
                let formData = new FormData()
                formData.append('semester', this.theSemester)

                if (this.theInstitute != '') {
                    formData.append('institute', this.theInstitute)
                }

                if (this.theLecturer != '') {
                    formData.append('lecturer', this.theLecturer)
                }

                if (this.theMinSeats != 0 || this.theMaxSeats != 0) {
                    let seats = {}
                    if (this.theMinSeats != 0) {
                        seats.min = this.theMinSeats
                    }
                    if (this.theMaxSeats != 0) {
                        seats.max = this.theMaxSeats
                    }
                    formData.append('seats', JSON.stringify(seats))
                }

                const response = await fetch(
                    STUDIP.URLHelper.getURL(this.$pluginBase + '/planning/planned_courses'), {
                    method: 'post',
                    body: formData
                }).then((response) => {
                    response.json()
                        .then((json) => {
                            this.plannedCourseList = json
                            this.loadingPlanned = false;
                            bus.$emit('updated-planned-courses')
                        })
                })
            },
            saveCourse(data) {
                let course = null
                let startRaw = null
                let endRaw = null
                let add = true
                /*
                 * We have a draggedEl -> drag & drop from unplanned courses.
                 */
                if (data.draggedEl != null) {
                    course = {
                        id: data.draggedEl.dataset.courseId + '-' + data.draggedEl.dataset.slotId,
                        bookings: [],
                        course_id: data.draggedEl.dataset.courseId,
                        course_name: data.draggedEl.dataset.courseName,
                        course_number: data.draggedEl.dataset.courseNumber,
                        end: ('0' + data.event.end.getHours()).slice(-2) + ':' +
                            ('0' + data.event.end.getMinutes()).slice(-2) + ':00',
                        lecturer: data.draggedEl.dataset.lecturer,
                        lecturer_id: data.draggedEl.dataset.lecturerId,
                        pinned: false,
                        slot_id: data.draggedEl.dataset.slotId,
                        start: ('0' + data.event.start.getHours()).slice(-2) + ':' +
                            ('0' + data.event.start.getMinutes()).slice(-2) + ':00',
                        turnout: data.draggedEl.dataset.turnout,
                        weekday: data.event.start.getDay()
                    }
                    startRaw = data.event.start
                    endRaw = data.event.end
                } else {
                    /*
                     * data has extendedProps -> this is a regular Fullcalendar event.
                     */
                    if (data.event != null) {
                        course = {
                            id: data.event.extendedProps.courseId + '-' + data.event.extendedProps.slotId,
                            bookings: data.event.extendedProps.bookings,
                            course_id: data.event.extendedProps.courseId,
                            course_name: data.event.extendedProps.courseName,
                            course_number: data.event.extendedProps.courseNumber,
                            end: ('0' + data.event.end.getHours()).slice(-2) + ':' +
                                ('0' + data.event.end.getMinutes()).slice(-2) + ':00',
                            lecturer: data.event.extendedProps.lecturerName,
                            lecturer_id: data.event.extendedProps.lecturerId,
                            pinned: false,
                            slot_id: data.event.extendedProps.slotId,
                            start: ('0' + data.event.start.getHours()).slice(-2) + ':' +
                                ('0' + data.event.start.getMinutes()).slice(-2) + ':00',
                            time_id: data.event.extendedProps.timeId,
                            turnout: data.event.extendedProps.turnout,
                            weekday: data.event.start.getDay()
                        }
                        startRaw = data.event.start
                        endRaw = data.event.end
                    /*
                     * Element comes from manually accepting time preference.
                     */
                    } else {
                        course = {
                            id: data.courseId + '-' + data.slotId,
                            bookings: [],
                            course_id: data.courseId,
                            course_name: data.courseName,
                            course_number: data.courseNumber,
                            end: ('0' + data.end.getHours()).slice(-2) + ':' +
                                ('0' + data.end.getMinutes()).slice(-2) + ':00',
                            lecturer: data.lecturer,
                            lecturer_id: data.lecturerId,
                            pinned: false,
                            slot_id: data.slotId,
                            start: ('0' + data.start.getHours()).slice(-2) + ':' +
                                ('0' + data.start.getMinutes()).slice(-2) + ':00',
                            turnout: data.turnout,
                            weekday: data.start.getDay()
                        }
                        startRaw = data.start
                        endRaw = data.end
                    }
                }
                let formData = new FormData()
                if (course.time_id != null) {
                    formData.append('time_id', course.time_id)
                }
                formData.append('course', course.course_id)
                formData.append('slot', course.slot_id)
                formData.append('start', this.formatDate(startRaw))
                formData.append('end', this.formatDate(endRaw))
                fetch(STUDIP.URLHelper.getURL(this.$pluginBase + '/slot/store_time'), {
                    method: 'post',
                    body: formData
                }).then((response) => {
                    if (!response.ok) {
                        throw response
                    }
                    if (data.event != null) {
                        data.event.remove()
                    }
                    response.json().then((json) => {
                        this._data.plannedCourseList =
                            this._data.plannedCourseList.filter((one) => one.slot_id != json.slot_id)
                        this._data.plannedCourseList.push(json)
                        this._data.unplannedCourseList =
                            this._data.unplannedCourseList.filter((one) => one.slot_id != json.slot_id)
                    })
                }).catch((error) => {
                    this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                })
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
        },
        watch: {
            loadingUnplanned: function(value) {
                if (!value && !this.loadingPlanned) {
                    bus.$emit('updated-courses')
                }
            },
            loadingPlanned: function(value) {
                if (!value && !this.loadingUnplanned) {
                    bus.$emit('updated-courses')
                }
            }
        }
    }
</script>
