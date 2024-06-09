xgettext --no-wrap -o assets/lang_src/messages.pot --copyright-holder=Wavelog --from-code=UTF-8 --keyword=__ --keyword=_ngettext:1,2 --keyword=_pgettext:1c,2 -L PHP $(find . -name "*.php")

for po in $(find . -name "*.po"); do
    msgmerge --no-wrap -UN --verbose "$po" assets/lang_src/messages.pot;
done

find . -name "*.po~" -delete

for po in $(find . -name "*.po"); do
    mo="${po%.po}.mo";
    msgfmt --verbose -o "$mo" "$po";
done