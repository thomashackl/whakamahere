<template>
    <div class="vld-parent">
        <loading :active="isLoading" :can-cancel="false" :is-full-page="fullPage" loader="dots"
                 :width="width" :height="height" color="#fff"/>
    </div>
</template>

<script>
    import Loading from 'vue-loading-overlay'

    export default {
        name: 'StudipLoadingIndicator',
        components: {
            Loading
        },
        props: {
            isLoading: {
                type: Boolean,
                default: false
            },
            fullPage: {
                type: Boolean,
                default: false
            },
            width: {
                type: Number,
                default: 64
            },
            height: {
                type: Number,
                default: 64
            },
            referenceElement: {
                type: String,
                default: ''
            }
        },
        mounted() {
            if (this.isLoading) {
                this.setDimensions()
            }
        },
        methods: {
            setDimensions() {
                const parent = this.referenceElement != '' ?
                    document.querySelector(this.referenceElement) :
                    this.$el.parentNode
                this.$el.style.display = this.isLoading ? 'block' : 'none'
                this.$el.style.height = parent.offsetHeight + 'px'
                this.$el.style.left = parent.offsetLeft + 'px'
                this.$el.style.top = parent.offsetTop + 'px'
                this.$el.style.width = parent.offsetWidth + 'px'

                let svg = this.$el.querySelector('svg')
                svg.style.marginLeft = ((parent.offsetWidth - this.width) / 2) + 'px'
                svg.style.marginTop = ((parent.offsetHeight - this.height) / 2) + 'px'
            }
        },
        watch: {
            isLoading: function(value) {
                this.setDimensions()
            }
        }
    }
</script>

<style lang="scss">
    .vld-parent {
        background-color: rgba(40, 72, 124, 0.5);
        margin: 0;
        position: absolute;
        z-index: 999;
    }
</style>
