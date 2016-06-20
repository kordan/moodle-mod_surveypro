#!/bin/sh

echo '*****************************************************************'
for PLUGIN in age autofill boolean character checkbox date datetime fileupload integer multiselect numeric radiobutton rate recurrence select shortdate textarea time; do
    cd /Applications/MAMP/htdocs/head/mod/surveypro/field/$PLUGIN
    echo '*****************************************************************'
    echo 'Inspecting plugin: '$PLUGIN
    echo '*****************************************************************'
    grep -rn 'get_string(' * | grep surveyprofield | grep -v surveyprofield_$PLUGIN
    grep -rn 'surveyproformat' *
    for WRONGPLUGIN in age autofill boolean character checkbox date datetime fileupload integer multiselect numeric radiobutton rate recurrence select shortdate textarea time; do
        if [ "$WRONGPLUGIN" != "$PLUGIN" ]; then
            echo '-----------------------------------------------------------------'
            echo 'Searching for: "'$WRONGPLUGIN'" in the frame of the '$PLUGIN' plugin'
            echo '-----------------------------------------------------------------'
            grep -rnw 'surveyprofield_'$WRONGPLUGIN *
        fi
        echo
    done
    echo
done

echo
echo '*****************************************************************'
for PLUGIN in fieldset fieldsetend label pagebreak; do
    cd /Applications/MAMP/htdocs/head/mod/surveypro/format/$PLUGIN
    echo '*****************************************************************'
    echo 'Inspecting plugin: '$PLUGIN
    echo '*****************************************************************'
    grep -rn 'get_string(' * | grep surveyprofield | grep -v surveyproformat_$PLUGIN
    grep -rn 'surveyprofield' *
    for WRONGPLUGIN in fieldset fieldsetend label pagebreak; do
        if [ "$WRONGPLUGIN" != "$PLUGIN" ]; then
            echo '-----------------------------------------------------------------'
            echo 'Searching for: "'$WRONGPLUGIN'" in the frame of the '$PLUGIN' plugin'
            echo '-----------------------------------------------------------------'
            grep -rnw 'surveyproformat_'$WRONGPLUGIN *
        fi
        echo
    done
    echo
done
