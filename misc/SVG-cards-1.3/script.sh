for file in *.svg; do inkscape -z -w 512 "$file" -e "PNG/${file%.svg}.png"; done;
