<template>
    <table id="whakamahere-unplanned-courses" class="default">
        <thead>
            <tr>
                <th>Nummer</th>
                <th>Name</th>
            </tr>
        </thead>
        <tbody>

            <tr v-for="course in courses" :id="course.id" class="course">
                <td>{{ course.number }}</td>
                <td>{{ course.name }}</td>
            </tr>
        </tbody>
    </table>
</template>

<script>
    export default {
        name: 'UnplannedCoursesList',
        data() {
            return {
                courses: [
                    {
                        id: 'abcde',
                        number: '123A',
                        name: 'Einführung Klingonisch'
                    },
                    {
                        id: 'f034d',
                        number: '456B',
                        name: 'Warptechnologie'
                    },
                    {
                        id: '97a33',
                        number: '789',
                        name: 'Subraumnavigation'
                    },
                    {
                        id: '1cab4',
                        number: '815',
                        name: 'Föderationsrecht'
                    },
                ]
            }
        }
    }
</script>
