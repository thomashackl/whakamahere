<template>
    <form class="default">
        <semester-filter :semesters="semesters" :selected-semester="theSemester"/>
        <institute-filter v-if="theSemester != ''" :institutes="institutes" :selected-institute="theInstitute"/>
        <lecturer-filter v-if="theInstitute != ''" :lecturers="lecturers" :selected-lecturer="theLecturer"
                         :get-lecturers-url="getLecturersUrl" :semester="theSemester"
                         :institute="theInstitute"/>
        <room-filter v-if="theInstitute != ''" :rooms="rooms" :selected-room="theRoom"/>
        <seats-filter v-if="theInstitute != ''" :min-seats="theMinSeats" :max-seats="theMaxSeats"/>
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
        data() {
            return {
                theSemester: this.selectedSemester,
                theInstitute: this.selectedInstitute,
                theLecturer: this.selectedLecturer,
                theRoom: this.selectedRoom,
                theMinSeats: this.minSeats,
                theMaxSeats: this.maxSeats
            }
        },
        mounted() {
            bus.$on('updated-semester', (semester) => {
                this.storeSelection('semester', semester.value)
                this.theSemester = semester.value
            })
            bus.$on('updated-institute', (institute) => {
                this.storeSelection('institute', institute)
                this.theInstitute = institute
            })
            bus.$on('updated-lecturer', (lecturer) => {
                this.storeSelection('lecturer', lecturer)
                this.theLecturer = lecturer
            })
            bus.$on('updated-room', (room) => {
                this.storeSelection('room', room)
                this.theRoom
            })
            bus.$on('updated-seats', (seats) => {
                this.storeSelection('seats', seats)
                const value = JSON.parse(seats)
                this.theMinSeats = value.min
                this.theMaxSeats = value.max
            })
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
