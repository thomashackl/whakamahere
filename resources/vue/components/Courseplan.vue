<template>
    <div id="courseplan">
        <studip-loading-indicator :is-loading="loadingPlanned || loadingUnplanned" :width="128" :height="128"
                                  reference-element="#whakamahere-courseplan"/>
        <schedule :min-time="minTime" :max-time="maxTime" :locale="locale"
                  :weekends="weekends" :lecture-start="lectureStart"
                  :courses="plannedCourseList" :institute="institute"
                  :get-slot-availability-url="getSlotAvailabilityUrl"></schedule>
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
                theLecturer: this.lecturer
            }
        },
        mounted() {
            // Catch event for changed semester in sidebar
            bus.$on('update-semester', (element) => {
                this.theSemester = element.value
                this.loadingPlanned = true
                this.loadingUnplanned = true
                if (element.value !== '') {
                    this.getUnplannedCourses()
                    this.getPlannedCourses()
                    bus.$emit('update-courses')
                } else {
                    this.unplannedCourseList = []
                    this.plannedCourseList = []
                    bus.$emit('update-courses')
                }
            })
            // Catch event for changed institute in sidebar
            bus.$on('update-institute', (value) => {
                this.theInstitute = value
                this.loadingPlanned = true
                this.loadingUnplanned = true
                if (value !== '') {
                    this.getUnplannedCourses()
                    this.getPlannedCourses()
                    bus.$emit('update-courses')
                } else {
                    this.unplannedCourseList = []
                    this.plannedCourseList = []
                    bus.$emit('update-courses')
                }
            })
            // Catch event for changed lecturer in sidebar
            bus.$on('update-lecturer', (value) => {
                this.theLecturer = value
                this.loadingPlanned = true;
                this.loadingUnplanned = true;
                this.getUnplannedCourses()
                this.getPlannedCourses()
                bus.$emit('update-courses')
            })

            bus.$on('save-course', (course) => {
                this.saveCourse(course)
            })

        },
        methods: {
            async getUnplannedCourses() {
                const data = {
                    semester: this.theSemester,
                    institute: this.theInstitute,
                    lecturer: this.theLecturer
                }
                const params = new URLSearchParams(data).toString()
                const response = await fetch(this.getUnplannedCoursesUrl + '?' + params, {
                    method: 'get'
                })
                const json = await response.json()
                this.unplannedCourseList = json
                this.loadingUnplanned = false;
                bus.$emit('update-unplanned-courses')
            },
            async getPlannedCourses() {
                const data = {
                    semester: this.theSemester,
                    institute: this.theInstitute,
                    lecturer: this.theLecturer
                }
                const params = new URLSearchParams(data).toString()
                const response = await fetch(this.getPlannedCoursesUrl + '?' + params, {
                    method: 'get'
                })
                response.json()
                    .then((json) => {
                        this.plannedCourseList = json
                        this.loadingPlanned = false;
                        bus.$emit('update-planned-courses')
                    })
            },
            saveCourse(data) {
                let formData = new FormData()
                let course = null
                if (data.draggedEl) {
                    formData.append('course', data.draggedEl.dataset.courseId)
                    formData.append('slot', data.draggedEl.dataset.slotId)
                    formData.append('start', this.formatDate(data.event.start))
                    formData.append('end', this.formatDate(data.event.end))
                } else {
                    formData.append('course', data.course_id)
                    formData.append('slot', data.slot_id)
                    formData.append('start', this.formatDate(data.start))
                    formData.append('end', this.formatDate(data.end))
                    course = {
                        id: data.course_id + '-' + data.slot_id,
                        course_id: data.course_id,
                        course_number: data.course_number,
                        course_name: data.course_name,
                        lecturer: data.lecturer,
                        weekday: data.start.getDay(),
                        start: ('0' + data.start.getHours()).slice(-2) + ':' + ('0' + data.start.getMinutes()).slice(-2) + ':00',
                        end: ('0' + data.end.getHours()).slice(-2) + ':' + ('0' + data.end.getMinutes()).slice(-2) + ':00'
                    }
                }
                fetch(this.storeCourseUrl, {
                    method: 'post',
                    body: formData
                }).then((response) => {
                    if (response.status == 200) {
                        if (course != null) {
                            this._data.plannedCourseList.push(course)
                        }
                        bus.$emit('course-saved', course)
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
                    bus.$emit('update-courses')
                }
            },
            loadingPlanned: function(value) {
                if (!value && !this.loadingUnplanned) {
                    bus.$emit('update-courses')
                }
            },
        }
    }
</script>
