#!/bin/sh

echo '************************'
for PLUGIN in age autofill boolean character checkbox date datetime fileupload integer multiselect numeric radiobutton rate recurrence select shortdate textarea time; do
    cd /Applications/MAMP/htdocs/head/mod/surveypro/field/$PLUGIN
    echo $PLUGIN
    grep -rn 'get_string(' * | grep surveyprofield | grep -v surveyprofield_$PLUGIN
done

echo '************************'
for PLUGIN in fieldset fieldsetend label pagebreak; do
    cd /Applications/MAMP/htdocs/head/mod/surveypro/format/$PLUGIN
    echo $PLUGIN
    grep -rn 'get_string(' * | grep surveyprofield | grep -v surveyproformat_$PLUGIN
done

echo '************************'



