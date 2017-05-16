#!/bin/bash

set -eu

readonly HUB="/var/www/Simone/hub/bin/hub"
readonly AUTHOR_EMAIL="yunobot@some.domain.tld"

function main()
{
    local ID=$1
    local PAGE="$(cat _pending/$ID/page).md"
    local DESCR=$(cat _pending/$ID/descr)
    local PRURL_FILE="../_pending/$ID/pr";
    local BRANCH="anonymous-$ID";

    cd _botclone
    sudo git checkout master
    sudo git pull

    if [ $(git branch --list | grep "^  $BRANCH$") ]
    then
        sudo git branch -D $BRANCH
    fi
    sudo git checkout -b $BRANCH
    cd ..
    cp _pending/$ID/content _botclone/$PAGE
    cd _botclone/
    git add $PAGE
    
    export GIT_AUTHOR_NAME="Yunobot"
    export GIT_AUTHOR_EMAIL=$AUTHOR_EMAIL
    export GIT_COMMITTER_NAME="Yunobot"
    export GIT_COMMITTER_EMAIL=$AUTHOR_EMAIL
     
    sudo git commit $PAGE -m "$DESCR"

    sudo git push origin $BRANCH
    sudo $HUB pull-request -m "[Anonymous contrib] $DESCR" > $PRURL_FILE;

}

echo "zob" >> /var/log/simone.log
main $1 >> ./debug.log 2>&1

