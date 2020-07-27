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
            <publish-planning v-if="isInPlanning" :semester="semester"></publish-planning>
            <studip-messagebox v-if="planningInactive" type="error"
                               message="Die Planung für das gewählte Semester wurde bereits veröffentlicht."></studip-messagebox>
            <studip-messagebox v-if="planningNotYet" type="warning"
                               message="Die Planung für das gewählte Semester hat noch nicht begonnen."></studip-messagebox>
        </section>
    </article>
</template>

<script>
    import ShortStatistics from './ShortStatistics'
    import PublishPlanning from './PublishPlanning'
    import StudipMessagebox from '../studip/StudipMessagebox'

    export default {
        name: 'PlanningDashboard',
        components: {
            ShortStatistics,
            PublishPlanning,
            StudipMessagebox
        },
        props: {
            semester: {
                type: Object,
                required: true
            },
            semesterStatus: {
                type: String,
                required: true
            },
            allStatus: {
                type: Object,
                required: true
            }
        },
        computed: {
            isInPlanning: function() {
                return ['planning', 'review'].includes(this.semesterStatus)
            },
            planningInactive: function() {
                return ['closed', 'finished'].includes(this.semesterStatus)
            },
            planningNotYet: function() {
                return ['input', 'prepare'].includes(this.semesterStatus)
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
