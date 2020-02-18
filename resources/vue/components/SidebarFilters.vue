<template>
    <form class="default">
        <semester-filter :semesters="semesters" :selected-semester="selectedSemester"/>
        <institute-filter :institutes="institutes" :selected-institute="selectedInstitute"/>
        <lecturer-filter :lecturers="lecturers" :selected-lecturer="selectedLecturer"
                         :get-lecturers-url="getLecturersUrl" :semester="selectedSemester"
                         :institute="selectedInstitute"/>
        <room-filter :rooms="rooms" :selected-room="selectedRoom"/>
    </form>
</template>

<script>
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
            }
        }
    }
</script>

<style lang="scss">
    form.default {
        label:not(.undecorated) {
            margin-bottom: 0;
        }
    }
</style>
