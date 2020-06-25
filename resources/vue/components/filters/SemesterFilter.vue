<template>
    <section>
        <label for="semester">
            Semester
        </label>
        <select name="semester" id="semester" v-model="selected" @change="onChange($event)">
            <option v-for="semester in semesters" :value="semester.id" :key="semester.id">
                {{ semester.name }}
            </option>
        </select>
    </section>
</template>

<script>
    import bus from 'jsassets/bus'

    export default {
        name: 'SemesterFilter',
        props: {
            semesters: Array,
            selectedSemester: String
        },
        data() {
            return {
                selected: this.selectedSemester
            }
        },
        methods: {
            onChange: function(event) {
                bus.$emit('updated-semester', event.target)
            }
        }
    }
</script>
