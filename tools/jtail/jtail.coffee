#!/usr/bin/env coffee

spawn    = require("child_process").spawn
patterns = require "./patterns"
require "colors"

args = process.argv.slice 2

tail = spawn "tail", process.argv.slice 2

tail.stdout.on "data", (chunk) ->
    data = chunk.toString "utf8"

    lines = data.split "\n"

    for line in lines
        matches = line.match(/^(\d{2}\/\d{2}\/\d{4}) (\d{2}:\d{2}:\d{2}) \((\w+)\)(\s+)- ([\s\S]+)$/)
        if matches
            str = "#{matches[1].cyan} #{matches[2].magenta}"

            switch matches[3].toLowerCase()
                when "verbose", "debug", "db" then color = "green"
                when "info"                   then color = "yellow"
                when "warn"                   then color = "red"

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

# proxy any stderr
tail.stderr.on "data", (chunk) ->
    process.stderr.write chunk

tail.on "exit", (code) ->
    process.exit 0
