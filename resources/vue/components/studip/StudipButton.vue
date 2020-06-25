
<template>
    <button class="button" :class="[icon]" type="submit" :name="name" @click="onClick">{{ label }}</button>
</template>

<script>
    import bus from 'jsassets/bus'

    export default {
        props: {
            name: {
                type: String,
            },
            icon: {
                type: String,
                validator(value) {
                    return ['', 'accept', 'cancel', 'edit', 'move-up', 'move-down', 'add', 'download', 'search'].includes(value)
                },
                default: ''
            },
            label: {
                type: String
            },
            eventName: {
                type: String
            },
            preventDefault: {
                type: Boolean,
                default: true
            }
        },
        methods: {
            onClick(event) {
                if (this.preventDefault) {
                    event.preventDefault()
                }
                bus.$emit(this.eventName, event.target)
            }
        }
    }
</script>
