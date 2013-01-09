#!/bin/sh

ssh -i $GIT_SSH_KEY -p $GIT_SSH_PORT $1 $2
