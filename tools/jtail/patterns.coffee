###
# these patterns are specific to the detail segment of each line (match offset 5)
###
module.exports = [
    {
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
        regex: /^(Error in file.+)$/,
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
        regex: /^(GET|POST)( \[)([^\]]+)(\] matches \[)(.+)(\] => \[)([\w\/]+\/)(\w+)(::)([\w-]+)(\])$/,
        colors: {
            "1": "bold",
            "3": "cyan",
            "5": "cyan",
            "7": "blue",
            "8": "blue",
            "10": "blue"
        }
    }
]
