#!/bin/bash

if [ "$#" -ne 2 ]; then
    echo "you should pass two parameters to use maker command1"
else
    DIR="$( dirname "$0" )"
    php $DIR/start.php $1 $2
fi