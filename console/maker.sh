#!/bin/bash

if [ "$#" -ne 2 ]; then
    echo "you should pass two parameters to use maker command"
    exit
fi

php start.php $1 $2