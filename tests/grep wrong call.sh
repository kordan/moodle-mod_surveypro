#!/bin/sh

echo '************************'
for PLUGIN in age autofill boolean character checkbox date datetime fileupload integer multiselect numeric radiobutton rate recurrence select shortdate textarea time; do
    cd /Applications/MAMP/htdocs/head/mod/surveypro/field/$PLUGIN
    echo 'Inspecting plugin: '$PLUGIN
    grep -rn 'get_string(' * | grep surveyprofield | grep -v surveyprofield_$PLUGIN
    grep -rn 'surveyproformat' *
    echo
done

echo
echo '************************'
for PLUGIN in fieldset fieldsetend label pagebreak; do
    cd /Applications/MAMP/htdocs/head/mod/surveypro/format/$PLUGIN
    echo 'Inspecting plugin: '$PLUGIN
    grep -rn 'get_string(' * | grep surveyprofield | grep -v surveyproformat_$PLUGIN
    grep -rn 'surveyprofield' *
    echo
done
