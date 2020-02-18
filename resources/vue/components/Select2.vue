<template>
    <select :name="name" :id="id" :required="required" :disabled="disabled">
        <template v-for="option in options" >
            <optgroup v-if="option.children != null && option.children.length > 0" :label="option.text" :key="option.id">
                <option v-for="child in option.children" :value="child.id" :key="child.id">
                    {{ child.text }}
                </option>
            </optgroup>
            <option v-else :value="option.id" :key="option.id">
                {{ option.text }}
            </option>
        </template>
    </select>
</template>

<script>
    import bus from 'jsassets/bus'

    export default {
        name: 'Select2',
        props: {
            name: {
                type: String,
                default: ''
            },
            id: {
                type: String,
                default: ''
            },
            options: {
                type: Array,
                default: () => []
            },
            required: {
                type: Boolean,
                default: false
            },
            disabled: {
                type: Boolean,
                default: false
            },
            settings: {
                type: Object,
                default: () => {}
            },
            value: null
        },
        data() {
            return {
                select2: null
            }
        },
        model: {
            event: 'change',
            prop: 'value'
        },
        watch: {
            options(val) {
                this.setOption(val)
            },
            value(val) {
                this.setValue(val)
            }
        },
        methods: {
            setOption(val = []) {
                this.select2.empty()
                this.select2.select2({
                    ...this.settings,
                    data: val
                })
                this.setValue(this.value)
            },
            setValue(val) {
                if (val instanceof Array) {
                    this.select2.val([...val])
                } else {
                    this.select2.val([val])
                }
                this.select2.trigger('change')
            }
        },
        mounted() {
            this.select2 = $('#' + this.id)
                .select2({
                    ...this.settings
                })
                .on('select2:select select2:unselect', event => {
                    this.$emit('change', this.select2.val())
                    this.$emit('select', event['params']['data'])
                })
            this.setValue(this.value)
        },
        beforeDestroy() {
            this.select2.select2('destroy')
        }
    }
</script>
