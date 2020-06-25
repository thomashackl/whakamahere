<template>
    <form class="default">
        <studip-icon v-if="!allFiltersVisible" id="add-filter" shape="add" width="20" height="20"
                     @click="showAllFilters(true)"/>
        <studip-icon v-if="allFiltersVisible" id="remove-filter" shape="remove" width="20" height="20"
                     @click="showAllFilters(false)"/>
        <semester-filter v-if="visibleFilters.semester" :semesters="semesters" :selected-semester="theSemester"/>
        <text-filter v-if="visibleFilters.searchterm" :searchterm="theSearchterm"/>
        <seats-filter v-if="visibleFilters.seats" :min-seats="theMinSeats" :max-seats="theMaxSeats"/>
        <institute-filter v-if="visibleFilters.institute" :institutes="institutes" :selected-institute="theInstitute"/>
        <lecturer-filter v-if="visibleFilters.lecturer" :lecturers="lecturers" :selected-lecturer="theLecturer"
                         :get-lecturers-url="getLecturersUrl" :semester="theSemester"
                         :institute="theInstitute"/>
        <room-filter v-if="visibleFilters.room" :rooms="rooms" :selected-room="theRoom"/>
    </form>
</template>

<script>
    import bus from 'jsassets/bus'
    import { globalfunctions } from './mixins/globalfunctions'
    import StudipIcon from './StudipIcon'
    import SemesterFilter from './SemesterFilter'
    import TextFilter from './TextFilter'
    import SeatsFilter from './SeatsFilter'
    import InstituteFilter from './InstituteFilter'
    import LecturerFilter from './LecturerFilter'
    import RoomFilter from './RoomFilter'

    export default {
        name: 'SidebarFilters',
        components: {
            StudipIcon,
            SemesterFilter,
            TextFilter,
            SeatsFilter,
            InstituteFilter,
            LecturerFilter,
            RoomFilter
        },
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
            noRoom: {
                type: Boolean,
                default: false
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
                theRoom: this.selectedRoom,
                showNoRoom: this.noRoom,
                allFiltersVisible: false,
                fullscreenMode: document.querySelector('html').classList.contains('is-fullscreen')
            }
        },
        computed: {
            visibleFilters: function() {
                return {
                    semester: this.fullscreenMode ?
                        this.allFiltersVisible : (this.theSemester !== '' || this.allFiltersVisible),
                    searchterm: this.fullscreenMode ?
                        this.allFiltersVisible : (this.theSearchterm !== '' || this.allFiltersVisible),
                    seats: this.fullscreenMode ?
                        this.allFiltersVisible : (this.theMinSeats !== 0 || this.theMaxSeats !== 0 || this.allFiltersVisible),
                    institute: this.fullscreenMode ?
                        this.allFiltersVisible : (this.theInstitute !== '' || this.allFiltersVisible),
                    lecturer: this.fullscreenMode ?
                        this.allFiltersVisible : (this.theLecturer !== '' || this.allFiltersVisible),
                    room: this.fullscreenMode ?
                        this.allFiltersVisible : (this.theRoom !== '' || this.allFiltersVisible),
                    noRoom: this.fullscreenMode ?
                        this.allFiltersVisible : (this.showNoRoom || this.allFiltersVisible)
                }
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
            bus.$on('updated-no-room', (state) => {
                this.showNoRoom = state
                console.log('Show without room only: ' + state)
                this.storeSelection('no_room', state)
            })

            // Listen for fullscreen mode and apply custom changes
            STUDIP.domReady(() => {
                this.fullscreenMode = (sessionStorage.getItem('studip-fullscreen') === 'on')
                $('button.fullscreen-toggle').on('click', (event) => {
                    this.fullscreenMode = !document.querySelector('html').classList.contains('is-fullscreen')
                })
            }, true)

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
                    this.showAllFilters(false)
                }).catch((error) => {
                    this.showMessage('error', 'Fehler (' + error.status + ')', error.statusText)
                    this.showAllFilters(false)
                })
            },
            showAllFilters: function(state) {
                this.allFiltersVisible = state
            },
            isFullscreen: function() {
                return sessionStorage.getItem('studip-fullscreen') === 'on'
            }
        }
    }
</script>

<style lang="scss">
    #filters {
        font-size: 12px;

        form.default {

            position: relative;

            #add-filter, #remove-filter {
                cursor: pointer;
                left: 240px;
                position: absolute;
                top: -29px;
            }

            section:not(.contentbox) {
                padding-top: 0.5em;

                label:not(.undecorated) {
                    margin-bottom: 0;
                }
            }
        }
    }
</style>
