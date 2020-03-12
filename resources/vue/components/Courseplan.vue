<template>
    <div id="courseplan">
        <studip-loading-indicator :is-loading="loadingPlanned || loadingUnplanned" :width="128" :height="128"
                                  reference-element="#whakamahere-courseplan"/>
        <schedule :min-time="minTime" :max-time="maxTime" :locale="locale"
                  :weekends="weekends" :lecture-start="lectureStart"
                  :courses="plannedCourseList" :institute="institute"
                  :get-slot-availability-url="getSlotAvailabilityUrl"
                  :unplan-slot-url="unplanSlotUrl" :pin-slot-url="pinSlotUrl"></schedule>
        <unplanned-courses-list :courses="unplannedCourseList" :lectureStart="lectureStart"></unplanned-courses-list>
    </div>
</template>

<script>
    import bus from 'jsassets/bus'

    export default {
        name: 'Courseplan',
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
            getUnplannedCoursesUrl: {
                type: String,
                default: ''
            },
            getPlannedCoursesUrl: {
                type: String,
                default: ''
            },
            storeCourseUrl: {
                type: String,
                default: ''
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

            bus.$on('save-course', (course) => {
                this.saveCourse(course)
            })

            bus.$on('slot-pinned', (slot) => {
                this.plannedCourseList.map((course) => {
                    if (course.slot_id == slot.extendedProps.slotId) {
                        course.pinned = slot.editable ? 0 : 1
                    }
                })
            })

        },
        methods: {
            updateData() {
                if (this.theMinSeats != 0 || this.theMaxSeats != 0 ||
                        this.theInstitute != '' || this.theLecturer != '' || this.theRoom != '') {
                    this.getPlannedCourses()
                    this.getUnplannedCourses()
                } else {
                    this.plannedCourseList = []
                    this.unplannedCourseList = []
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

                const response = await fetch(this.getUnplannedCoursesUrl, {
                    method: 'post',
                    body: formData
                }).then((response) => {
                    response.json()
                        .then((json) => {
                            this.unplannedCourseList = json
                            this.loadingUnplanned = false;
                            bus.$emit('updated-unplanned-courses')
                        })
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

                const response = await fetch(this.getPlannedCoursesUrl, {
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
                let formData = new FormData()
                let course = null
                if (data.draggedEl != null) {
                    formData.append('course', data.draggedEl.dataset.courseId)
                    formData.append('slot', data.draggedEl.dataset.slotId)
                    formData.append('start', this.formatDate(data.event.start))
                    formData.append('end', this.formatDate(data.event.end))
                    course = {
                        id: data.draggedEl.dataset.courseId + '-' + data.draggedEl.dataset.slotId,
                        course_id: data.draggedEl.dataset.courseId,
                        slot_id: data.draggedEl.dataset.slotId,
                        course_number: data.draggedEl.dataset.courseNumber,
                        course_name: data.draggedEl.dataset.courseName,
                        lecturer: data.draggedEl.dataset.lecturer,
                        lecturer_id: data.draggedEl.dataset.lecturerId,
                        pinned: data.draggedEl.dataset.pinned,
                        weekday: data.event.start.getDay(),
                        start: ('0' + data.event.start.getHours()).slice(-2) + ':' +
                            ('0' + data.event.start.getMinutes()).slice(-2) + ':00',
                        end: ('0' + data.event.end.getHours()).slice(-2) + ':' +
                            ('0' + data.event.end.getMinutes()).slice(-2) + ':00'
                    }
                    data.event.remove()
                } else {
                    formData.append('course', data.course_id)
                    formData.append('slot', data.slot_id)
                    formData.append('start', this.formatDate(data.start))
                    formData.append('end', this.formatDate(data.end))
                    course = data
                    course.start = ('0' + course.start.getHours()).slice(-2) + ':' +
                        ('0' + course.start.getMinutes()).slice(-2) + ':00'
                    course.end = ('0' + course.end.getHours()).slice(-2) + ':' +
                        ('0' + course.end.getMinutes()).slice(-2) + ':00'
                }
                fetch(this.storeCourseUrl, {
                    method: 'post',
                    body: formData
                }).then((response) => {
                    if (response.ok) {
                        this._data.plannedCourseList.push(course)
                        this._data.unplannedCourseList =
                            this._data.unplannedCourseList.filter((one) => one.slot_id != course.slot_id)
                    } else {
                        console.log('Date could not be saved.')
                        console.log(response)
                    }
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
