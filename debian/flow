#!/bin/bash

echo
echo '----------------------------------------------------'
echo 'A script for processing a data stream by ura@itx.ru.'
echo

[[ ! -f flow.lib ]] || [[ ! -f flow.cfg ]] && echo "Error: configure file isn't find or invalid startup directory." && exit

# ------------------------------------------------------------------------------
. flow.cfg
. flow.lib

# ------------------------------------------------------------------------------
if [[ $IN == 'demo' ]]; then
    PWD=`pwd`; cd ../demo; tar -xjf in.tar.bz2; cd $PWD
    ls ../demo/in | sort | while read a; do \
        cat ../demo/in/$a >> ../demo/in.log
    done
    cat ../demo/in.log | doIt
    rm -f ../demo/in.log
    rm -dr ../demo/in

    #bzip2 -d ../demo/in2.tar.bz2 -c | tar -x -O | sort -s -k 1,1 | doIt
    #bzip2 -d ../demo/in.tar.bz2 -c | tar -x -O | doIt
else
    # For example:
    # This is example for simple numberic data stream.
    # $ cat $IN | awk '{ print strftime("%Y-%m-%d_%H-%M-%S")" "$0; }' | doIt
    #
    # This is example for Industrial scales Newton NT-200A.
    # The flow of values is:
    # ST,GT,2      0 kg
    # ,              ,-
    # ,           10 kg
    # ,           15 kg
    # ,          -10 kg
    # ST,GT,2      0 kg
    #
    # $ cat $IN | awk '{
    #    if($0 !~ "kg" || $1 == ",-" || $0 ~ ST,GT,2) next;
    #    print strftime("%Y-%m-%d_%H-%M-%S")" "$2;
    #}' | doIt

    # This is simple variant for timestamp & numeric stream 
    cat $IN | doIt
fi
