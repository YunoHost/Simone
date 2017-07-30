
# Where Simone is installed in the filesystem
readonly SIMONE_ROOT="/var/www/simone"

# Email with which anonymous commits will be done
readonly AUTHOR_EMAIL="simone@yunohost.org"

# This is the user to which the branch will be pushed and from which the PR
# will be created
readonly BOTUSER="yunohost-bot"

# This is the 'prod' repo, i.e. the destination of the anonymous PRs
# Should be 'yunohost/doc' once it is put in production
readonly REPO="yunohost/doc"

# This is the key used by ssh during git push operations
# It should be registered on the appropriate account (probably yunohost-bot)
readonly REPO_KEY="$SIMONE_ROOT/config/id_rsa"

# This is the authorization token used to submit the pull request using 
# Github's API. This should be a token for user yunohost-bot with public_repo
# permissions.
readonly REPO_TOKEN=$(cat $SIMONE_ROOT/config/github_token)
