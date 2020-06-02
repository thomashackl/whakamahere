<template>
    <div id="whakamahere-unplanned-courses">
        <table v-if="courseList.length > 0" class="default">
            <caption>
                {{ courseList.length }} ungeplante Veranstaltung(en)
                <span class="actions">
                    <a href="#" @click="acceptAllTimePreferences">
                        <studip-icon shape="check-circle"/>
                    </a>
                </span>
            </caption>
            <colgroup>
                <col/>
                <col width="120"/>
                <col width="120"/>
                <col width="200"/>
                <col width="100"/>
                <col width="20"/>
            </colgroup>
            <thead>
                <tr>
                    <th>Name</th>
                    <th># Teiln.</th>
                    <th>Dauer (Min.)</th>
                    <th>Dozent</th>
                    <th>Wunschzeit</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody class="container" v-dragula="courseList" drake="courselist">
                <tr v-for="course in courseList" :id="course.course_id + '-' + course.slot_id"
                    class="course" :data-course-id="course.course_id" :data-slot-id="course.slot_id"
                    :data-course-number="course.course_number" :data-course-name="course.course_name"
                    :data-turnout="course.turnout" :data-weekday="course.weekday" :data-time="course.time"
                    :data-duration="course.duration" :data-lecturer-id="course.lecturer_id"
                    :data-lecturer="course.lecturer" :data-bookings="[]" :data-partial="false">
                    <td class="course-name">{{ course.course_number }} {{ course.course_name }}</td>
                    <td class="course-turnout">{{ course.turnout }}</td>
                    <td class="course-duration">{{ course.duration }}</td>
                    <td class="course-lecturer">{{ course.lecturer }}</td>
                    <td class="course-preftime">{{ getWeekday(course.weekday) }} {{ course.time.slice(0, 5) }}</td>
                    <td class="course-actions">
                        <a href="#" @click="acceptTimePreference($event)" v-if="course.time != ''">
                            <studip-icon shape="check-circle"/>
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
        <studip-messagebox v-else message="Es sind keine ungeplanten Veranstaltungen vorhanden."/>
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
            this.$el.style.maxHeight = (
                window.innerHeight -
                document.getElementById('barBottomContainer').offsetHeight -
                document.getElementById('flex-header').offsetHeight -
                document.querySelector('nav.secondary-navigation').offsetHeight -
                document.getElementById('page_title_container').offsetHeight -
                document.querySelector('.fc').offsetHeight -
                25
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
            acceptTimePreference: function(event, dataEl) {
                if (dataEl == null) {
                    dataEl = event.currentTarget.parentNode.parentNode
                }
                let start = new Date(this.lectureStart + ' ' + dataEl.dataset.time);
                start.setDate(start.getDate() - start.getDay())
                start.setDate(start.getDate() + parseInt(dataEl.dataset.weekday))
                let end = new Date(start.getTime() + parseInt(dataEl.dataset.duration) * 60000)

                let data = {
                    id: dataEl.dataset.courseId + '-' + dataEl.dataset.slotId,
                    courseId: dataEl.dataset.courseId,
                    slotId: dataEl.dataset.slotId,
                    courseNumber: dataEl.dataset.courseNumber,
                    courseName: dataEl.dataset.courseName,
                    turnout: dataEl.dataset.turnout,
                    lecturer: dataEl.dataset.lecturer,
                    lecturerId: dataEl.dataset.lecturerId,
                    pinned: false,
                    weekday: start.getDay(),
                    start: start,
                    end: end
                }
                bus.$emit('save-course', data)
            },
            acceptAllTimePreferences: function(event) {
                const unplanned = document.querySelectorAll('#whakamahere-unplanned-courses tr.course')
                for (let i = 0 ; i < unplanned.length ; i++) {
                    this.acceptTimePreference(event, unplanned[i])
                }
            }
        }
    }
</script>

<style lang="scss">
    #whakamahere-unplanned-courses {
        background-color: #ffffff;
        overflow-y: scroll;

        table {
            caption {
                background-color: #ffffff;
                border-top: 1px solid #000000;
                font-size: 12px;
                padding: 0;
                padding-left: 5px;
                padding-right: 5px;
            }

            tr {
                font-size: 11px;
                padding: 1px;

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
