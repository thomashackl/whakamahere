<template>
    <section id="whakamahere-lecturer-filter">
        <studip-loading-indicator :is-loading="loading" :width="32" :height="32"
                                  reference-element="#filters"/>
        <label for="lecturer">
            Lehrende
        </label>
        <select2 v-model="selected" :options="theLecturers" id="lecturer" name="lecturer"
                 @change="onChange($event)"></select2>
    </section>
</template>

<script>
    import bus from 'jsassets/bus'

    export default {
        name: 'LecturerFilter',
        props: {
            lecturers: {
                type: Array,
                default: () => []
            },
            selectedLecturer: {
                type: String,
                default: ''
            },
            getLecturersUrl: {
                type: String,
                default: ''
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
                selected: this.selectedLecturer,
                theSemester: this.semester,
                theInstitute: this.institute,
                lecturerList: this.lecturers,
                loading: false
            }
        },
        computed: {
            theLecturers() {
                let entries = [{
                    id: '',
                    text: '-- alle --'
                }]

                if (this.lecturerList != null) {
                    this.lecturerList.map((lecturer) => {
                        entries.push({
                            id: lecturer.user_id,
                            text: lecturer.name
                        })
                    })
                }

                return entries
            }
        },
        mounted() {
            bus.$on('update-semester', (semester) => {
                this.theSemester = semester.value
                this.getLecturers()
            })
            bus.$on('update-institute', (institute) => {
                this.theInstitute = institute
                this.getLecturers()
            })
        },
        methods: {
            onChange(value) {
                bus.$emit('update-lecturer', value)
            },
            async getLecturers() {
                this.loading = true
                const data = {
                    semester: this.theSemester,
                    institute: this.theInstitute
                }
                const params = new URLSearchParams(data).toString()
                const response = await fetch(this.getLecturersUrl + '?' + params, {
                    method: 'get'
                })
                const json = await response.json()
                this.lecturerList = json
                this.loading = false
            }
        }
    }
</script>
