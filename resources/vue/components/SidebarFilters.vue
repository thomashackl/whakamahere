<template>
    <form class="default">
        <semester-filter :semesters="semesters" :selected-semester="selectedSemester"></semester-filter>
        <institute-filter :institutes="institutes" :selected-institute="selectedInstitute"></institute-filter>
    </form>
</template>

<script>
    export default {
        name: 'SidebarFilters',
        props: {
            semesters: Array,
            selectedSemester: String,
            institutes: Array,
            selectedInstitute: String
        }
    }
</script>

<style lang="scss" scoped>
    form.default {
        label:not(.undecorated) {
            margin-bottom: 0;
        }
    }
</style>
