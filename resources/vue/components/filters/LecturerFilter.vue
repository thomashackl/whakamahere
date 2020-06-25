<template>
    <section id="whakamahere-lecturer-filter">
        <studip-loading-indicator v-if="loading" :is-loading="loading" :width="32" :height="32"
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
    import Select2 from '../common/Select2'

    export default {
        name: 'LecturerFilter',
        components: {
            Select2
        },
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
            bus.$on('updated-semester', (semester) => {
                this.theSemester = semester.value
                this.getLecturers()
            })
            bus.$on('updated-institute', (institute) => {
                this.theInstitute = institute
                this.getLecturers()
            })
        },
        methods: {
            onChange(value) {
                bus.$emit('updated-lecturer', value)
            },
            async getLecturers() {
                this.loading = true
                this.lecturerList = []
                this.selected = ''
                let formData = new FormData()
                formData.append('semester', this.theSemester)

                if (this.theInstitute != '') {
                    formData.append('institute', this.theInstitute)
                }

                fetch(this.getLecturersUrl, {
                    method: 'post',
                    body: formData
                }).then((response) => {
                    response.json().then((json) => {
                        this.lecturerList = json
                        this.loading = false
                    })
                })
            }
        }
    }
</script>
