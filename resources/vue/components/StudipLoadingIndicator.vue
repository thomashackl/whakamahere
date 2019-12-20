<template>
    <div :class="parentClass">
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
                default: true
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
        data() {
            return {
                parentClass: 'vld-parent'
            }
        },
        watch: {
            isLoading(val) {
                let container = document.querySelector('.' + this.parentClass)
                if (val) {
                    this.setDimension(container)
                    container.style.display = 'inline-block'
                } else {
                    container.style.display = 'none'
                }
            }
        },
        mounted() {
            if (this.isLoading) {
                let container = document.querySelector('.' + this.parentClass)
                this.setDimension(container)
                container.style.display = 'inline-block'
            }
        },
        methods: {
            setDimension: function(container) {
                const parent = this.referenceElement !== '' ?
                    document.querySelector(this.referenceElement) :
                    container.parentElement
                console.log(parent.offsetWidth + ' x ' + parent.offsetHeight)
                const padding = (parent.offsetHeight / 2 - this.height / 2)
                container.style.paddingTop = padding + 'px'
                container.style.width = parent.offsetWidth + 'px'
                container.style.height = (parent.offsetHeight - padding) + 'px'
            }
        }
    }
</script>

<style lang="scss">
    .vld-parent {
        background-color: rgba(40, 72, 124, 0.5);
        display: none;
        margin: 0;
        padding: 0;
        position: absolute;
        text-align: center;
        z-index: 999;
    }
</style>
