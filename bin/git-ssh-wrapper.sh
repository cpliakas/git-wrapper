#!/bin/sh

if [ "$GIT_SSH_KEY" != "" ];then
    GIT_SSH_KEY="-i $GIT_SSH_KEY"
fi

if [ "$GIT_SSH_PORT" != "" ];then
    GIT_SSH_PORT="-p $GIT_SSH_PORT"
fi

ssh $GIT_SSH_KEY $GIT_SSH_PORT -o StrictHostKeyChecking=no -o IdentitiesOnly=yes $1 $2
