#!/usr/local/bin/coffee

spawn = require("child_process").spawn
require "colors"

tail = spawn "tail", ["-f", process.argv[2]]

tail.stdout.on "data", (chunk) ->
    data = chunk.toString "utf8"

    lines = data.split "\n"

    for line in lines
        matches = line.match(/^(\d{2}\/\d{2}\/\d{4}) (\d{2}:\d{2}:\d{2}) \((\w+)\)(\s+)- (.+)$/)
        if matches
            str = "#{matches[1].cyan} #{matches[2].magenta}"

            switch matches[3].toLowerCase()
                when "verbose", "debug" then color = "green"
                when "info"             then color = "yellow"
                when "warn"             then color = "red"

            str += " (#{matches[3][color]})#{matches[4]}- "

            detail = matches[5]

            knownDetail = false
            for pattern in patterns
                matches = detail.match pattern.regex
                if matches
                    # wahey!
                    for match,i in matches when i > 0
                        if pattern.colors[i]?
                            str += match[pattern.colors[i]]
                        else
                            str += match

                    knownDetail = true
                    break
            
            str += detail if not knownDetail

            str += "\n"
            process.stdout.write str

###
# these patterns are specific to the detail segment of each line (match offset 5)
###
patterns = [
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
