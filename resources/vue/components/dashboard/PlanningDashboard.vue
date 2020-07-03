<template>
    <article>
        <header>
            <h1>{{ semester.name }}</h1>
        </header>
        <section>
            <header>
                Statistik
            </header>
            <short-statistics :semester="semester"></short-statistics>
        </section>
        <section class="align-center" id="publish-planning">
            <header>
                Planung veröffentlichen
            </header>
            <publish-planning :semester="semester"></publish-planning>
        </section>
    </article>
</template>

<script>
    import ShortStatistics from './ShortStatistics'
    import PublishPlanning from './PublishPlanning'

    export default {
        name: 'PlanningDashboard',
        components: {
            ShortStatistics,
            PublishPlanning
        },
        props: {
            semester: {
                type: Object,
                required: true
            }
        }
    }
</script>

<style lang="scss">
    article {
        section {
            border: 1px solid #d0d7e3;
            margin: 2px;

            &.align-center {
                text-align: center;
            }

            header {
                background-color: #e7ebf1;
                text-align: left;
                padding: 5px;
                font-weight: bold;
            }
        }
    }
</style>
