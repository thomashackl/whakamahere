<template>
    <form class="default">
        <semester-filter :semesters="semesters" :selected-semester="theSemester"/>
        <text-filter :searchterm="theSearchterm"/>
        <seats-filter :min-seats="theMinSeats" :max-seats="theMaxSeats"/>
        <institute-filter :institutes="institutes" :selected-institute="theInstitute"/>
        <lecturer-filter :lecturers="lecturers" :selected-lecturer="theLecturer"
                         :get-lecturers-url="getLecturersUrl" :semester="theSemester"
                         :institute="theInstitute"/>
        <room-filter :rooms="rooms" :selected-room="theRoom"/>
    </form>
</template>

<script>
    import bus from 'jsassets/bus'
    import { globalfunctions } from './mixins/globalfunctions'

    export default {
        name: 'SidebarFilters',
        mixins: [
            globalfunctions
        ],
        props: {
            semesters: {
                type: Array,
                default: () => []
            },
            selectedSemester: {
                type: String,
                default: ''
            },
            searchterm: {
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
            storeSelectionUrl: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                theSemester: this.selectedSemester,
                theSearchterm: this.searchterm,
                theMinSeats: this.minSeats,
                theMaxSeats: this.maxSeats,
                theInstitute: this.selectedInstitute,
                theLecturer: this.selectedLecturer,
                theRoom: this.selectedRoom
            }
        },
        mounted() {
            bus.$on('updated-semester', (semester) => {
                this.theSemester = semester.value
                this.storeSelection('semester', semester.value)
            })
            bus.$on('updated-searchterm', (search) => {
                this.theSearchterm = search
                this.storeSelection('searchterm', search)
            })
            bus.$on('updated-seats', (seats) => {
                const value = JSON.parse(seats)
                this.theMinSeats = value.min
                this.theMaxSeats = value.max
                this.storeSelection('seats', seats)
            })
            bus.$on('updated-institute', (institute) => {
                this.theInstitute = institute
                this.storeSelection('institute', institute)
            })
            bus.$on('updated-lecturer', (lecturer) => {
                this.theLecturer = lecturer
                this.storeSelection('lecturer', lecturer)
            })
            bus.$on('updated-room', (room) => {
                this.theRoom = room
                this.storeSelection('room', room)
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
                    this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                })
            }
        }
    }
</script>

<style lang="scss">
    #filters {
        font-size: 12px;

        form.default {
            section:not(.contentbox) {
                padding-top: 0.5em;

                label:not(.undecorated) {
                    margin-bottom: 0;
                }
            }
        }
    }
</style>
