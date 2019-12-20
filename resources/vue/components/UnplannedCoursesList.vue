<template>
    <div id="whakamahere-unplanned-courses">
        <table v-if="courses.length > 0" class="default">
            <caption>
                {{ courses.length }} ungeplante Veranstaltung(en)
            </caption>
            <thead>
                <tr>
                    <th>Nummer</th>
                    <th>Name</th>
                    <th>Dauer (Stunden)</th>
                </tr>
            </thead>
            <tbody class="container" v-dragula="courses" drake="courselist">
                <tr v-for="course in courses" :id="course.id" class="course" :data-course-number="course.number"
                    :data-course-name="course.name" :data-course-duration="course.duration">
                    <td>{{ course.number }}</td>
                    <td>{{ course.name }}</td>
                    <td>{{ course.duration }}</td>
                </tr>
            </tbody>
        </table>
        <studip-messagebox v-else message="Es sind keine Veranstaltungen vorhanden."/>
    </div>
</template>

<script>
    import bus from 'jsassets/bus'

    export default {
        name: 'UnplannedCoursesList',
        props: {
            courses: {
                type: Array,
                default: () => []
            }
        },
        created() {
            const service = this.$dragula.$service
            service.options('courselist', {
                accepts: function(el, target, source, sibling) {
                    return false
                }
            })
        },
        mounted() {
            // Catch event if course from list is dropped on calendar
            bus.$on('drop-course', (element) => {
                this.courses = this.courses.filter(course => course.id !== element.id)
            })
        }
    }
</script>

<style lang="scss" scoped>
    #whakamahere-unplanned-courses {
        max-height: 200px;
        overflow-y: scroll;

        table {
            caption {
                font-size: 1em;
            }
            tr {
                font-size: 0.9em;
            }
        }
    }
</style>
