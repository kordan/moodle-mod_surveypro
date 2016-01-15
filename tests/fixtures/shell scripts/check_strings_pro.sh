#!/bin/bash

GENERIC_FRAME='*\*\*\*\*\*\*************************************************'

echo $GENERIC_FRAME
echo "You are runing the script: `basename "$0"` from: `dirname "$0"`"
echo "The current working directory is `pwd`"

echo $GENERIC_FRAME
# echo 'Drop here a "lang file" to scan in order to verify the real use of its string'
# echo 'TAKE CARE: your <<newmodule>> "lang file" is supposed to be in <<newmodule>>/lang/en/'
# echo '           and is supposed to be named: <<newmodule>>.php'
# read langfilepath
# echo $GENERIC_FRAME

langfilepath=`dirname "$0"`/../../../../
mydir=`dirname "$langfilepath"`
cd "$mydir"

surveyprobasepath=`pwd`
excludefilename=`pwd`
excludefilename=`basename "$excludefilename"`

langfilepath=()

# il file di lingua del modulo
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
    # echo surveyprosubplugin = $surveyprosubplugin
    # echo surveypropluginname = $surveypropluginname
    langfilepath+=($surveyprosubplugin/$surveypropluginname/lang/en/surveypro"$surveyprosubplugin"_"$surveypropluginname".php)
done

surveyprosubplugin='template'
surveypropluginlist=( attls collespreferred collesactual collesactualpreferred criticalincidents )
for surveypropluginname in "${surveypropluginlist[@]}"
do
    # echo surveyprosubplugin = $surveyprosubplugin
    # echo surveypropluginname = $surveypropluginname
    langfilepath+=($surveyprosubplugin/$surveypropluginname/lang/en/surveypro"$surveyprosubplugin"_"$surveypropluginname".php)
done

surveyprosubplugin='report'
surveypropluginlist=( attachments_overview colles count frequency missing )
for surveypropluginname in "${surveypropluginlist[@]}"
do
    # echo surveyprosubplugin = $surveyprosubplugin
    # echo surveypropluginname = $surveypropluginname
    langfilepath+=($surveyprosubplugin/$surveypropluginname/lang/en/surveypro"$surveyprosubplugin"_"$surveypropluginname".php)
done

# routine di controllo
# for langfilepath in "${langfilepath[@]}"
# do
#     echo langfilepath = $langfilepath
# done
# read a

# langkeyregex=\^\\\$"string\[['|\"](.*)['|\"]\]"
langkeyregex=\^\\\$"string\[['|\"]([^ ]*)['|\"]\]"

endoflangkey=( _help _err _group _descr _check _header )
beginoflangkey=( surveypro: )

# set -x
for langfilepath in "${langfilepath[@]}"
do
    echo

    langfilepath=$surveyprobasepath/$langfilepath
    mydir=`dirname "$langfilepath"`
    cd "$mydir"

    # cd $(dirname $langfilepath)
    cd ../..
    excludefilename=$(basename $langfilepath)

    echo $GENERIC_FRAME
    echo Apparently useless strings in $excludefilename
    messagewritten=0
    while read langfile_line || [[ -n "$langfile_line" ]] # to read last line too
    do
        # individua la parola nella riga
        # echo
        # echo 'I do search in: '$langfile_line
        if [[ $langfile_line =~ $langkeyregex ]]; then
            langkey=`echo ${BASH_REMATCH[1]}`

            # I look for the extracted word into surveypro folder
            if [[ $excludefilename = 'surveypro.php' ]]; then
                myoutput=`grep -rP "(get_string|print_error|lang_string)\(['\"]$langkey['\"], ['\"](mod_)?surveypro['\"]" *`
            else
                # get type and plugin from the path
                # langfilepath: /Applications/MAMP/htdocs/head/mod/surveypro/field/select/lang/en/surveyprofield_select.php
                typepluginregex="/mod/surveypro/(.*)/(.*)/lang/en"
                if [[ $langfilepath =~ $typepluginregex ]]; then
                    mytype=${BASH_REMATCH[1]}
                    myplugin=${BASH_REMATCH[2]}
                    myoutput=`grep -rP "(get_string|print_error|lang_string)\(['\"]$langkey['\"], *['\"](mod_)?surveypro$mytype(_)$myplugin['\"]" *`
                else
                    # something was wrong. I use the standard grep
                    myoutput=`grep -rP "(get_string|print_error|lang_string)\(['\"]$langkey['\"], *['\"](mod_)?surveypro" *`
                fi
            fi
            if [[ -z "$myoutput" ]]; then
                # try to exclude get_string($fieldname
                myoutput=`grep -rP "[\$fieldname = ['\"]$langkey['\"];" *`
                if [[ -z "$myoutput" ]]; then

                    langkeyinuse=0
                    for keyend in "${endoflangkey[@]}"
                    do
                        # try to exclude strings ending with keyend
                        n=${#keyend}
                        stringedge=`echo ${langkey:(-$n)}`
                        if [ "${stringedge}" = $keyend ]; then
                            langkeyinuse=1
                            break # for keyend in "${endoflangkey[@]}"
                        fi
                    done

                    if [[ $langkeyinuse = 1 ]]; then
                        continue # while read langfile_line
                    fi

                    for keystart in "${beginoflangkey[@]}"
                    do
                        # try to exclude strings beginning with keystart
                        n=${#keystart}
                        stringedge=`echo ${langkey:0:$n}`
                        if [ "${stringedge}" = $keystart ]; then
                            langkeyinuse=1
                            break # for keystart in "${beginoflangkey[@]}"
                        fi
                    done

                    if [[ $langkeyinuse = 0 ]]; then
                        messagewritten=1
                        echo '    '$langkey
                    fi
                fi
            fi
        fi
    done < $langfilepath
    if [[ $messagewritten = 0 ]]; then
        echo '    Not any string appears to be useless. Congratulation!'
    fi
done
