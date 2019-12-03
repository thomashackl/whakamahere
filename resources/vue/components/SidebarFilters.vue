<template>
    <form class="default">
        <semester-filter :semesters="semesters" :selected-semester="selectedSemester"></semester-filter>
        <institute-filter :institutes="institutes" :selected-institute="selectedInstitute"></institute-filter>
        <room-filter :rooms="rooms" :selected-room="selectedRoom"></room-filter>
    </form>
</template>

<script>
    export default {
        name: 'SidebarFilters',
        props: {
            semesters: Array,
            selectedSemester: String,
            institutes: Array,
            selectedInstitute: String,
            rooms: Array,
            selectedRoom: String
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
