#!/bin/sh
ssh -i $GIT_SSH_KEY -p $GIT_SSH_PORT $GIT_SSH_OPT_UserKnownHostsFile -o StrictHostKeyChecking=no -o IdentitiesOnly=yes "$@"
