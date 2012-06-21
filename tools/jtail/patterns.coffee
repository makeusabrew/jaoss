###
# these patterns are specific to the detail segment of each line (match offset 5)
###
module.exports = [
    {
        regex: /^(Init  |Start )(\[)(.+)(\])$/,
        colors: {
            "3": "blue"
        }
    }, {
        regex: /^(End   )(\[)([^\]]+)(\])(.*)$/,
        colors: {
            "3": "blue"
        }
    }, {
        regex: /^(Response Code: 2\d{2})$/,
        colors: {
            "1": "green"
        }
    }, {
        regex: /^(Response Code: 4\d{2})$/,
        colors: {
            "1": "red"
        }
    }, {
        regex: /^(Response Code: 5\d{2})$/,
        colors: {
            "1": "red"
        }
    }, {
        regex: /^(Response Code: 3\d{2})$/,
        colors: {
            "1": "yellow"
        }
    }, {
        regex: /^(Handling error.+)$/,
        colors: {
            "1": "red"
        }
    }, {
        regex: /^(Validate.+\[)(OK)(\])$/,
        colors: {
            "2": "green"
        }
    }, {
        regex: /^(Validate.+\[)(FAIL)(\])$/,
        colors: {
            "2": "red"
        }
    }, {
        regex: /^(GET \[[^\]]+\])(.+)$/,
        colors: {
            "1": "bold"
        }
    }, {
        regex: /^(POST \[[^\]]+\])(.+)$/,
        colors: {
            "1": "bold"
        }
    }
]
