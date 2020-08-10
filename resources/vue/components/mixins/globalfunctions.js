import StudipMessagebox from '../studip/StudipMessagebox'
var MessageboxClass = Vue.extend(StudipMessagebox)

export const globalfunctions = {
    methods: {
        getWeekdays: function() {
            return [
                { number: 1, name: 'Montag' },
                { number: 2, name: 'Dienstag' },
                { number: 3, name: 'Mittwoch' },
                { number: 4, name: 'Donnerstag' },
                { number: 5, name: 'Freitag' },
                { number: 6, name: 'Samstag' },
                { number: 0, name: 'Sonntag' }
            ]
        },
        showMessage: function(type, title, message) {
            const box = new MessageboxClass({
                propsData: {
                    type: type,
                    message: message
                }
            })
            box.$mount()
            STUDIP.Dialog.show(box.$el, {
                height: 250,
                width: 400,
                title: title
            })
        },
        getCourseUrl: function (id, target) {
            return STUDIP.URLHelper.getURL('dispatch.php/course/' + target, {cid: id})
        },
    }
}
