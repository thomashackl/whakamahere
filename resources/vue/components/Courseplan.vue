<template>
    <div>
        <schedule :min-time="minTime" :max-time="maxTime" :locale="locale"
                  :weekends="weekends" :lecture-start="lectureStart"></schedule>
        <unplanned-course-list :courses="courseList"></unplanned-course-list>
    </div>
</template>

<script>
    import bus from 'jsassets/bus'

    export default {
        name: 'Courseplan',
        props: {
            locale: String,
            minTime: String,
            maxTime: String,
            weekends: Boolean,
            lectureStart: String,
            getCoursesUrl: String,
            courses: Array,
            semester: String,
            institute: String
        },
        data() {
            return {
                courseList: this.courses
            }
        },
        mounted() {
            // Catch event for changed semester in sidebar
            bus.$on('update-semester', (element) => {
                this.getCourses(element.value, this.institute)
                bus.$emit('update-courses')
            })
            // Catch event for changed institute in sidebar
            bus.$on('update-institute', (value) => {
                this.getCourses(this.semester, value)
                bus.$emit('update-courses')
            })
        },
        methods: {
            async getCourses(semester, institute) {
                fetch(this.getCoursesUrl + '/' + semester + '/' + institute)
                    .then((response) => {
                        response.json().then((json) => this.courseList = json)
                    })
            }
        }
    }
</script>
