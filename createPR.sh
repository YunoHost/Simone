#!/bin/bash

set -eux

source config/config.sh

# Dirty trick so that there's no need for www-data to have a .ssh folder
readonly GIT_SSH_COMMAND="ssh -i $REPO_KEY -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no \$*"
function _git()
{
    echo "Running git $@"
    echo "$GIT_SSH_COMMAND" > ./simone_ssh
    chmod +x ./simone_ssh
    GIT_TRACE=1 GIT_SSH='./simone_ssh' git "$@"
    rm ./simone_ssh
}

function createPR()
{
    local BRANCH=$1
    local TITLE=${2//$'\n'/'\n'}

    ANSWER=$(curl https://api.github.com/repos/$REPO/pulls \
                  -H "Authorization: token $REPO_TOKEN" \
                  --data '{   "title":"'"$TITLE"'",
                               "head":"'"$BOTUSER"':'"$BRANCH"'",
                               "base":"master",
                               "maintainer_can_modify":true }')

    echo "$ANSWER" >&2

    echo "$ANSWER" \
    | grep "^  \"html_url\":" \
    | awk '{print $2}' \
    | tr '"' ' ' \
    | awk '{print $1}'
}

function validateID()
{
    local ID="$1"

    if ! (echo "$ID" | grep -E "^[0-9_-]{19}$" > /dev/null)
    then
        echo "Invalid ID format"
        exit 1
    fi

    if [ ! -d "_pending_contrib/$ID" ]
    then
        echo "No pending request for ID $ID"
        exit 2
    fi
}

function main()
{
    local ID="$1"
    local PAGE="$(cat _pending_contrib/$ID/page).md"
    local DESCR_FILE="_pending_contrib/$ID/descr"
    local PRURL_FILE="_pending_contrib/$ID/pr";
    local BRANCH="anonymous-$ID";

    cd _botfork
    _git checkout master
    _git pull origin master

    if git branch --list | grep "^  $BRANCH$" > /dev/null
    then
        _git branch -D $BRANCH
    fi
    _git checkout -b $BRANCH
    cd ..
    cp _pending_contrib/$ID/content _botfork/$PAGE
    cd _botfork/
    _git add $PAGE

    export GIT_AUTHOR_NAME="Yunobot"
    export GIT_AUTHOR_EMAIL=$AUTHOR_EMAIL
    export GIT_COMMITTER_NAME="Yunobot"
    export GIT_COMMITTER_EMAIL=$AUTHOR_EMAIL

    _git commit $PAGE -F ../$DESCR_FILE

    _git push botfork $BRANCH --force

    local TITLE=$(echo -n "[Anonymous contrib] "; cat "../$DESCR_FILE")
    createPR "$BRANCH" "$TITLE" > ../$PRURL_FILE
}

validateID "$1"
main "$1" >> ./debug.log 2>&1

