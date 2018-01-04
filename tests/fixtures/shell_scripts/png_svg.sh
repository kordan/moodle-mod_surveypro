#!/bin/bash

function checkicon() {
    type=$1
    plugin=$2

    cd $surveyprobasepath/$type/$plugin/pix

    echo
    echo Inspecting $plugin $type
    for extension in "${extensions[@]}"
    do
        if [ -f icon.$extension ]; then
            echo icon.$extension found.
        else
            messagesent=1
            echo -e ${RED}TAKE CARE! icon.$extension was NOT found.${NC}
        fi
    done

    iconcount=`ls -l * | wc -l`
    if [ $iconcount != 2 ]; then
        messagesent=1
        echo -e ${RED}TAKE CARE! The folder surveypro/$type/$plugin/pix/ has $iconcount files instead of 2.${NC}
    fi
}

GENERIC_FRAME='*\*\*\*\*\*\*************************************************'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo $GENERIC_FRAME
echo "You are runing the script: `basename "$0"` from: `dirname "$0"`"
echo "The current working directory is `pwd`"
echo $GENERIC_FRAME

surveyprobasepath=`dirname "$0"`/../../../
cd "$surveyprobasepath"
surveyprobasepath=`pwd`

messagesent=0
extensions=( svg png )

type='field'
plugins=( age autofill boolean character checkbox date datetime fileupload integer multiselect numeric radiobutton rate recurrence select shortdate textarea time )
for plugin in "${plugins[@]}"
do
    # echo type = $type
    # echo plugin = $plugin
    checkicon $type $plugin
done

type='format'
plugins=( fieldset fieldsetend label pagebreak )
for plugin in "${plugins[@]}"
do
    # echo type = $type
    # echo plugin = $plugin
    checkicon $type $plugin
done

type='template'
plugins=( attls collespreferred collesactual collesactualpreferred criticalincidents )
for plugin in "${plugins[@]}"
do
    # echo type = $type
    # echo plugin = $plugin
    checkicon $type $plugin
done

type='report'
plugins=( attachments colles delayedusers frequency responsesperuser userspercount )
for plugin in "${plugins[@]}"
do
    # echo type = $type
    # echo plugin = $plugin
    checkicon $type $plugin
done

echo
echo
echo
if [ "$messagesent" == 0 ]; then
    echo 'Ok. All seems to be fine here.'
else
    echo 'ATTENTION! At least one error was found.'
fi
