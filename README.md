# flow

## Terms
* *.log - current log file containing a slice of the stream for analysis;
* *.pre - prepared file containing information about the minimum, maximum and average values of the stream;
* *.email - file prepared for sending by mail.


## Processing route
log > pre -> email

1. Script '__flow__' generates files __*.log__ in temporary folder __.log__.
2. Script __cron/parser__ processes the files __*.log__ into __*.pre__ in temporary folder __.log__.
3. Script __crom/email__ prepares *.email files for sending by mail in folder
