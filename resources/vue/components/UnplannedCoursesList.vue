<template>
    <div id="whakamahere-unplanned-courses">
        <table v-if="courseList.length > 0" class="default">
            <caption>
                {{ courseList.length }} ungeplante Veranstaltung(en)
            </caption>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Dauer (Stunden)</th>
                </tr>
            </thead>
            <tbody class="container" v-dragula="courseList" drake="courselist">
                <tr v-for="course in courseList" :id="course.id" class="course" :data-course-number="course.number"
                    :data-course-name="course.name" :data-course-duration="course.duration">
                    <td class="course-name">{{ course.number }} {{ course.name }}</td>
                    <td class="course-duration">{{ course.duration }}</td>
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
        },
        mounted() {
            // Catch event if course from list is dropped on calendar
            bus.$on('drop-course', (element) => {
                this.courseList = this.courseList.filter(course => course.id !== element.id)
            })
        },
        watch: {
            courses: function(value) {
                this.courseList = this.courses
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
