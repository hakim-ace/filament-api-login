#!/bin/bash

git add .
git commit -m "$1"
git push origin main

# ./sendGit.sh "feat: add user mapping configuration"