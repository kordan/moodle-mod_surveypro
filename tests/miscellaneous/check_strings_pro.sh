#!/bin/sh

GENERIC_FRAME='*\*\*\*\*\*\*************************************************'

echo $GENERIC_FRAME
echo "You are runing the script: `basename "$0"` from: `dirname "$0"`"
echo "The current working directory is `pwd`"

echo $GENERIC_FRAME
echo 'Drop here a "lang file" to scan to verify the real use of its string'
echo 'TAKE CARE: your <<newmodule>> "lang file" is supposed to be in <<newmodule>>/lang/en/'
echo '           and is supposed to be named: <<newmodule>>.php'
read langfilepath
echo $GENERIC_FRAME

# set -x
cd $(dirname $langfilepath)
cd ../..

surveyprobasepath=`pwd`
excludefilename=`pwd`
excludefilename=`basename "$excludefilename"`
regex="string\[['|\"](.*)['|\"]\]"

langfilepath=()

# il file di lingua del modulo
langfilepath+=("lang/en/$excludefilename.php")

surveyprosubplugin='field'
surveypropluginlist=( age autofill boolean character checkbox date datetime fileupload integer multiselect numeric radiobutton rate recurrence select shortdate textarea time )
for surveypropluginname in "${surveypropluginlist[@]}"
do
    # echo surveyprosubplugin = $surveyprosubplugin
    # echo surveypropluginname = $surveypropluginname
	langfilepath+=($surveyprosubplugin/$surveypropluginname/lang/en/surveypro"$surveyprosubplugin"_"$surveypropluginname".php)
done

surveyprosubplugin='format'
surveypropluginlist=( fieldset fieldsetend label pagebreak )
for surveypropluginname in "${surveypropluginlist[@]}"
do
    # echo surveyprosubplugin = $surveyprosubplugin
    # echo surveypropluginname = $surveypropluginname
	langfilepath+=($surveyprosubplugin/$surveypropluginname/lang/en/surveypro"$surveyprosubplugin"_"$surveypropluginname".php)
done

surveyprosubplugin='template'
surveypropluginlist=( attls collespreferred collesactual collesactualpreferred criticalincidents )
for surveypropluginname in "${surveypropluginlist[@]}"
do
    # echo surveyprosubplugin = $surveyprosubplugin
    # echo surveypropluginname = $surveypropluginname
	langfilepath+=($surveyprosubplugin/$surveypropluginname/lang/en/surveypro"$surveyprosubplugin"_"$surveypropluginname".php)
done

surveyprosubplugin='report'
surveypropluginlist=( attachments_overview colles count frequency missing )
for surveypropluginname in "${surveypropluginlist[@]}"
do
    # echo surveyprosubplugin = $surveyprosubplugin
    # echo surveypropluginname = $surveypropluginname
	langfilepath+=($surveyprosubplugin/$surveypropluginname/lang/en/surveypro"$surveyprosubplugin"_"$surveypropluginname".php)
done

# routine di controllo
# for langfilepath in "${langfilepath[@]}"
# do
# 	echo langfilepath = $langfilepath
# done

for langfilepath in "${langfilepath[@]}"
do
	echo
	langfilepath=$surveyprobasepath/$langfilepath
	# echo langfilepath = $langfilepath

    cd $(dirname $langfilepath)
    cd ../..
    excludefilename=$(basename $langfilepath)

    echo $GENERIC_FRAME
    echo Apparently useless strings in $excludefilename
    messagewritten=0
    while read langfile_line || [[ -n "$langfile_line" ]] # to read last line too
    do
        # individua la parola nella riga
        # echo
        # echo $langfile_line
        if [[ $langfile_line =~ $regex ]]; then
            #echo $BASH_REMATCH is what I wanted
            langstring=`echo ${BASH_REMATCH[1]}`

            # cerca la parola nella cartella
            output=`grep -r --exclude="$excludefilename" $langstring *`
            if [ "${#output}" = 0 ]; then
                messagewritten=1
                echo '    '$langstring
            fi
        fi
    done < $langfilepath
    if [[ $messagewritten = 0 ]]; then
        echo '    Not any string appears to be useless. Congratulation!'
    fi
done




