#!/bin/bash

if [ ! "$#" -ge 2 ]; then
    echo "you should pass at least two parameters to use maker command"
else
    DIR="$( dirname "$0" )"

    declare -a params=("" "") 

    index=0
    
    while (( $# > 0 ))
    do
        case $1 in
            -m) 
                if [[ "$2" ]]; then
                    model=$2
                    shift
                fi        
                ;;
            *)  
                if [[ ! -z "$1" ]] && [[ ! "$1" =~ ^-+ ]]; then
                    params[index]="$1"
                    let index++
                fi
                ;;     
        esac
        shift
    done

    # php start.php --task=make:api --class=UsersAPI --model=Session
    php $DIR/start.php --task=${params[0]} --class=${params[1]} --model=${model}

fi