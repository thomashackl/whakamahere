<template>
    <article>
        <header>
            <h1>{{ semester.name }}</h1>
        </header>
        <section>
            <header>
                Statistik
            </header>
            <short-statistics v-if="isEnabled" :semester="semester"></short-statistics>
            <studip-messagebox v-else type="error"
                               message="Für dieses Semester sind keine Planungsdaten hinterlegt."></studip-messagebox>
        </section>
        <section class="align-center" id="publish-planning">
            <header>
                Planung veröffentlichen
            </header>
            <publish-planning v-if="isPublishingAllowed" :semester="semester"></publish-planning>
            <studip-messagebox v-if="!isEnabled" type="error"
                               message="Für dieses Semester sind keine Planungsdaten hinterlegt."></studip-messagebox>
            <studip-messagebox v-if="!isPublishingAllowed" type="warning"
                               :message="statusNoPublishing"></studip-messagebox>
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
            },
            isEnabled: {
                type: Boolean,
                default: true
            },
            isPublishingAllowed: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                statusNoPublishing: 'Das Semester ist aktuell im Status "' +
                    this.allStatus[this.semesterStatus] +
                    '", daher kann keine Veröffentlichung stattfinden.'
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
