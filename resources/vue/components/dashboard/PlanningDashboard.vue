<template>
    <short-statistics :semester="semester"></short-statistics>
</template>

<script>
    import ShortStatistics from './ShortStatistics'

    export default {
        name: 'PlanningDashboard',
        components: {
            ShortStatistics
        },
        props: {
            semester: {
                type: Object,
                required: true
            }
        }
    }
</script>
