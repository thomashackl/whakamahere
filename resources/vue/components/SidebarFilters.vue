<template>
    <form class="default">
        <semester-filter :semesters="semesters" :selected-semester="selectedSemester"/>
        <institute-filter :institutes="institutes" :selected-institute="selectedInstitute"/>
        <lecturer-filter :lecturers="lecturers" :selected-lecturer="selectedLecturer"
                         :get-lecturers-url="getLecturersUrl" :semester="selectedSemester"
                         :institute="selectedInstitute"/>
        <room-filter :rooms="rooms" :selected-room="selectedRoom"/>
        <seats-filter :min-seats="minSeats" :max-seats="maxSeats"/>
    </form>
</template>

<script>
    import bus from 'jsassets/bus'

    export default {
        name: 'SidebarFilters',
        props: {
            semesters: {
                type: Array,
                default: () => []
            },
            selectedSemester: {
                type: String,
                default: ''
            },
            institutes: {
                type: Array,
                default: () => []
            },
            selectedInstitute: {
                type: String,
                default: ''
            },
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
            rooms: {
                type: Array,
                default: () => []
            },
            selectedRoom: {
                type: String,
                default: ''
            },
            minSeats: {
                type: Number,
                default: 0
            },
            maxSeats: {
                type: Number,
                default: 0
            },
            storeSelectionUrl: {
                type: String,
                default: ''
            }
        },
        mounted() {
            bus.$on('updated-semester', (semester) => this.storeSelection('semester', semester.value))
            bus.$on('updated-institute', (institute) => this.storeSelection('institute', institute))
            bus.$on('updated-lecturer', (lecturer) => this.storeSelection('lecturer', lecturer))
            bus.$on('updated-room', (room) => this.storeSelection('lecturer', room))
            bus.$on('updated-seats', (seats) => this.storeSelection('seats', seats))
        },
        methods: {
            storeSelection: function(type, value) {
                let formData = new FormData()
                formData.append('type', type)
                formData.append('value', value)
                fetch(this.storeSelectionUrl, {
                    method: 'post',
                    body: formData
                }).then((response) => {
                    if (!response.ok) {
                        throw response
                    }
                }).catch((error) => {
                    alert('Es ist ein Fehler aufgetreten. Die Auswahl konnte nicht gespeichert werden.')
                    console.log(error)
                })
            }
        }
    }
</script>

<style lang="scss">
    form.default {
        section:not(.contentbox) {
            padding-top: 0.5em;

            label:not(.undecorated) {
                margin-bottom: 0;
            }
        }
    }
</style>
