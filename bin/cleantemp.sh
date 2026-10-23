#!/bin/sh

path_to_script_directory="$(dirname "$0")"
cd "$path_to_script_directory/..";

if [ -d temp/cache ]; then
    rm -R temp/cache
    echo "CACHE DELETED";
fi

if [ -d temp/proxies ]; then
    rm -R temp/proxies
    echo "PROXIES DELETED";
fi

if [ -d temp/critical-section ]; then
    rm -R temp/critical-section
    echo "CRITICAL-SECTION DELETED";
fi

if [ -d log ]; then
    rm -rf log/*
    echo "LOG DELETED";
fi