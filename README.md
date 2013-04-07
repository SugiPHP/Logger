Logger
======

Logger extends Monolog\Logger by adding the ability to log with custom error levels.
Default Monolog levels are:
- 600 -> EMERGENCY
- 550 -> ALERT
- 500 -> CRITICAL
- 400 -> ERROR
- 300 -> WARNING
- 250 -> NOTICE
- 200 -> INFO
- 100 -> DEBUG

Each handler logs messages with level more than or equals to some given predefined level.
With Monolog you can make a handler with minimum level of INFO. Any message with level INFO or
above will be logged. A messages with level DEBUG will not be logged.

SugiPHP logger on the other hand is not limited to only those predefined log levels. It can
handle arbitrary levels and can filter (log or not) any combination of them. You can
use handler with a filter 
```
"all -debug -system"
```
This will log any message with any level except
messages with level "debug" and "system". Or you can make a filter like 
```
"none +alert +special"
```
which will not log any messages except those with "alert" and "special" levels. Note
that "emergency" level will not be logged since it is not included in the filter. SugiPHP logger
does not have levels higher or lower than others. Each message level is treated as any other, and
has no any "weight" or whatsoever.
 