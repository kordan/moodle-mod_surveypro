#!/bin/sh

#set -x
GENERIC_FRAME='*\*\*\*\*\*\*************************************************'

echo $GENERIC_FRAME
echo "You are runing the script: `basename "$0"` from: `dirname "$0"`"
echo "The current working directory is `pwd`"

echo $GENERIC_FRAME
echo 'Drop here a "lang file" to scan to verify the real use of its string'
echo 'TAKE CARE: your <<newmodule>> "lang file" is supposed to be in <<newmodule>>/lang/en/'
echo '           and is supposed to be named: <<newmodule>>.php'
read langfile_path
echo $GENERIC_FRAME

cd $(dirname $langfile_path)
cd ../..

# set -x
excludefilename=`pwd`
excludefilename=`basename "$excludefilename"`
regex="string\[['|\"](.*)['|\"]\]"

echo 'Apparently useless strings'
while read langfile_line
do
    # individua la parola nella riga
    #echo
    #echo $langfile_line
    if [[ $langfile_line =~ $regex ]]; then
        #echo $BASH_REMATCH is what I wanted
        langstring=`echo ${BASH_REMATCH[1]}`
        # echo $langstring

        # cerca la parola nella cartella
        output=`grep -r --exclude="$excludefilename.php" $langstring *`
        if [ "${#output}" = 0 ]; then
            echo '    '$langstring
        fi
    fi

done < $langfile_path