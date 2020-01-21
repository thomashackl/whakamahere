<template>
    <div>
        <studip-loading-indicator :is-loading="loadingPlanned || loadingUnplanned" :width="128" :height="128"
                                  reference-element="#whakamahere-courseplan"/>
        <schedule :min-time="minTime" :max-time="maxTime" :locale="locale"
                  :weekends="weekends" :lecture-start="lectureStart"
                  :store-course-url="storeCourseUrl"
                  :courses="plannedCourseList" :institute="institute"></schedule>
        <unplanned-courses-list :courses="unplannedCourseList"></unplanned-courses-list>
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
            }
        },
        data() {
            return {
                plannedCourseList: this.plannedCourses,
                unplannedCourseList: this.unplannedCourses,
                loadingPlanned: false,
                loadingUnplanned: false
            }
        },
        mounted() {
            // Catch event for changed semester in sidebar
            bus.$on('update-semester', (element) => {
                this.loadingPlanned = true;
                this.loadingUnplanned = true;
                if (element.value !== '') {
                    this.getUnplannedCourses(element.value, this.institute)
                    this.getPlannedCourses(element.value, this.institute)
                } else {
                    this.unplannedCourseList = []
                    this.plannedCourseList = []
                    bus.$emit('update-courses')
                }
            })
            // Catch event for changed institute in sidebar
            bus.$on('update-institute', (value) => {
                this.loadingPlanned = true;
                this.loadingUnplanned = true;
                if (value !== '') {
                    this.getUnplannedCourses(this.semester, value)
                    this.getPlannedCourses(this.semester, value)
                    bus.$emit('update-courses')
                } else {
                    this.unplannedCourseList = []
                    this.plannedCourseList = []
                    bus.$emit('update-courses')
                }
            })
        },
        methods: {
            async getUnplannedCourses(semester, institute) {
                fetch(this.getUnplannedCoursesUrl + '/' + semester + '/' + institute)
                    .then((response) => {
                        response.json().then((json) => {
                                this.unplannedCourseList = json
                                this.loadingUnplanned = false;
                                bus.$emit('update-unplanned-courses')
                            })
                    })
            },
            async getPlannedCourses(semester, institute) {
                fetch(this.getPlannedCoursesUrl + '/' + semester + '/' + institute)
                    .then((response) => {
                        response.json().then((json) => {
                            this.plannedCourseList = json
                            this.loadingPlanned = false;
                            bus.$emit('update-planned-courses')
                        })
                    })
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
            }
        }
    }
</script>
