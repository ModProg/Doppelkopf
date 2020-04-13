mkdir PNG
for file in *.svg; do inkscape -z -w 72 "$file" -e "PNG/${file%.svg}.png"; done;
cd PNG
convert 9* -append s9.png
convert 10* -append s10.png
convert j* -append sj.png
convert q* -append sq.png
convert k* -append sk.png
convert a* -append sa.png
convert s9* s10* sj* sq* sk* sa* +append cards.png
convert cards.png -background none -extent "$(identify -format '%wx%w' cards.png)" cards.png
rm s*.png
