#!/bin/bash

total_lines=$(wc -l < readme.md)
half_lines=$((total_lines / 2))
head -n $half_lines readme.md > build/first_half.md
tail -n +$((half_lines + 1)) build/readme.md > build/second_half.md
cat build/first_half.md | pandoc --ascii --from markdown --to html > build/first_half.html
cat build/second_half.md | pandoc --ascii --from markdown --to html > build/second_half.html
sed -i -e "1i 1=" build/first_half.html
sed -i -e "1i 2=" build/second_half.html
cat build/first_half.html
cat build/first_half.html | base64 --wrap=0
cat build/second_half.html
cat build/second_half.html | base64 --wrap=0

# Base 64 helper
cp base64.html build
sed -i -e "1i 3=" build/base64.html
echo -e "\n\n"
cat build/base64.html | base64 --wrap=0
