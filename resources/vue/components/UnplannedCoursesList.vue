<template>
    <div id="whakamahere-unplanned-courses">
        <table v-if="courseList.length > 0" class="default">
            <caption>
                {{ courseList.length }} ungeplante Veranstaltung(en)
            </caption>
            <colgroup>
                <col/>
                <col width="100"/>
                <col width="200"/>
                <col width="100"/>
                <col width="20"/>
            </colgroup>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Dauer (Minuten)</th>
                    <th>Dozent</th>
                    <th>Wunschzeit</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody class="container" v-dragula="courseList" drake="courselist">
                <tr v-for="course in courseList" :id="course.course_id + '-' + course.slot_id"
                    class="course"  :data-course-id="course.course_id" :data-slot-id="course.slot_id"
                    :data-course-number="course.course_number" :data-course-name="course.course_name"
                    :data-weekday="course.weekday" :data-time="course.time" :data-duration="course.duration">
                    <td class="course-name">{{ course.course_number }} {{ course.course_name }}</td>
                    <td class="course-duration">{{ course.duration }}</td>
                    <td class="course-lecturer">{{ course.lecturer }}</td>
                    <td class="course-preftime">{{ getWeekday(course.weekday) }} {{ course.time.slice(0, 5) }}</td>
                    <td class="course-actions">
                        <a href="#" @click="acceptPreference">
                            <studip-icon shape="check-circle"/>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
        <studip-messagebox v-else message="Es sind keine Veranstaltungen vorhanden."/>
    </div>
</template>

<script>
    import bus from 'jsassets/bus'
    import interactionPlugin from "@fullcalendar/interaction";
    import timeGridWeekPlugin from "@fullcalendar/timegrid";

    export default {
        name: 'UnplannedCoursesList',
        props: {
            courses: {
                type: Array,
                default: () => []
            },
            lectureStart: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                courseList: this.courses
            }
        },
        created() {
            const service = this.$dragula.$service
            service.options('courselist', {
                accepts: function(el, target, source, sibling) {
                    return false
                }
            })
            service.eventBus.$on('drag', (args) => {
                bus.$emit('start-drag-course', args.el.dataset)
            })
            service.eventBus.$on('cancel', (args) => {
                bus.$emit('cancel-drag-course', args.el.dataset)
            })
        },
        mounted() {
            // Catch event if course from list is dropped on calendar
            bus.$on('course-saved', (element) => {
                this.courseList = this.courseList.filter(course => course.id !== element.id)
            })

            this.$el.style.maxHeight = (
                document.getElementById('layout_content').offsetHeight -
                document.getElementsByClassName('fc')[0].offsetHeight -
                45
            ) + 'px'
         },
        watch: {
            courses: function(value) {
                this.courseList = this.courses
            }
        },
        methods: {
            // Calculates a weekday name out of the given weekday number (1 is Monday)
            getWeekday: function(number) {
                // Starting with today...
                let today = new Date()
                let date = new Date()
                // ... calculate back to last Sunday...
                date.setDate(date.getDate() - today.getDay())
                // ... and add given days
                date.setDate(date.getDate() + parseInt(number))
                return date.toLocaleString('de-DE', { weekday: 'short' })
            },
            acceptPreference: function(event) {
                const dataEl = event.currentTarget.parentNode.parentNode
                let start = new Date(this.lectureStart + ' ' + dataEl.dataset.time);
                start.setDate(start.getDate() - start.getDay())
                start.setDate(start.getDate() + parseInt(dataEl.dataset.weekday))
                let end = new Date(start.getTime() + parseInt(dataEl.dataset.duration) * 60000)

                let data = {
                    id: dataEl.dataset.courseId + '-' + dataEl.dataset.slotId,
                    course_id: dataEl.dataset.courseId,
                    slot_id: dataEl.dataset.slotId,
                    course_number: dataEl.dataset.courseNumber,
                    course_name: dataEl.dataset.courseName,
                    weekday: start.getDay(),
                    start: start,
                    end: end
                }
                bus.$emit('save-course', data)
            }
        }
    }
</script>

<style lang="scss" scoped>
    #whakamahere-unplanned-courses {
        background-color: #ffffff;
        overflow-y: scroll;

        table {
            caption {
                font-size: 1em;
            }
            tr {
                font-size: 0.9em;

                &.course {
                    cursor: move;

                    .course-actions {
                        img, svg {
                            cursor: pointer;
                        }
                    }
                }
            }
        }
    }

    .gu-mirror {
        background-color: #28487c;
        color: #ffffff;
        display: inline-block !important;
        font-size: 0.8em;
        height: 100px !important;
        max-height: 100px;
        max-width: 200px;
        width: 200px;

        .course-name {
            background-color: #3f72b4;
            display: inline-block;
            width: 100%;
        }
        .course-duration {
            display: block;
        }
    }
</style>
